<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Backend\FormDataProvider;

use Pixelant\PxaProductManager\Collection\ProductAttributesCollector;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\AttributeConfigurationProviderFactory;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\ConcreteProviderInterface;
use Pixelant\PxaProductManager\Domain\Model\Attribute;
use Pixelant\PxaProductManager\Domain\Model\AttributeSet;
use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Traits\TranslateBeTrait;
use Pixelant\PxaProductManager\Utility\MainUtility;
use Pixelant\PxaProductManager\Utility\TCAUtility;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Form data provider hook, add TCA on a fly
 *
 * @package Pixelant\PxaProductManager\Backend\FormDataProvider
 */
class ProductEditFormInitialize implements FormDataProviderInterface
{
    use TranslateBeTrait;

    /**
     * @var ConcreteProviderInterface[]
     */
    protected $dataProviders = [];

    /**
     * @var array
     */
    protected $attributeValues = [];

    /**
     * Product
     * @var Product
     */
    protected $product = null;

    /**
     * Create TCA configuration
     *
     * @param array $result
     * @return array
     */
    public function addData(array $result): array
    {
        if ($result['tableName'] !== 'tx_pxaproductmanager_domain_model_product') {
            return $result;
        }

        $isNew = StringUtility::beginsWith($result['databaseRow']['uid'], 'NEW');

        if (!$isNew) {
            $this->init($result['databaseRow']);

            $attributesCollector = $this->getProductAttributesCollector();

            if ($attributesCollector->getAttributes()->count()) {
                $this->populateTCA($attributesCollector->getAttributesSets(), $result['processedTca']);
                $result['databaseRow'] = $this->simulateDataValues($attributesCollector->getAttributes(), $result['databaseRow']);

                if (is_array($result['defaultLanguageDiffRow'])) {
                    $diffKey = sprintf(
                        '%s:%d',
                        $result['tableName'],
                        $result['databaseRow']['uid']
                    );

                    if (array_key_exists($diffKey, $result['defaultLanguageDiffRow'])) {
                        $this->setDiffData(
                            $result['defaultLanguageDiffRow'][$diffKey],
                            $result['defaultLanguageRow']
                        );
                    }
                }
            } else {
                $this->showNotificationMessage('tca.notification_no_attributes_available');
            }
        } else {
            $this->showNotificationMessage('tca.notification_first_save');
        }

        return $result;
    }

    /**
     * Init product object
     * @param array $row
     * @return Product
     */
    protected function init(array $row): void
    {
        $this->product = MainUtility::singleRowToExtbaseObject(Product::class, $row);
        $this->attributeValues = $this->product->getAttributesValuesArray();
    }

    /**
     * Add attributes configuration to TCA
     *
     * @param ObjectStorage $attributesSets
     * @param array &$tca
     */
    protected function populateTCA(ObjectStorage $attributesSets, array &$tca)
    {
        $productAttributesSetsTCA = [];

        /** @var AttributeSet $attributesSet */
        foreach ($attributesSets as $attributesSet) {
            // Populate TCA
            /** @var Attribute $attribute */
            foreach ($attributesSet->getAttributes() as $attribute) {
                $tcaConfigurationProvider = $this->getAttributeTCAConfigurationProvider($attribute);

                $fieldName = $tcaConfigurationProvider->getTCAFieldName();
                $tcaConfiguration = $tcaConfigurationProvider->getTCAFieldConfiguration();

                $tca['columns'][$fieldName] = $tcaConfiguration;
                $GLOBALS['TCA']['tx_pxaproductmanager_domain_model_product']['columns'][$fieldName] = $tcaConfiguration;

                // Array with all additional attributes
                $productAttributesSetsTCA[$attributesSet->getUid()]['fields'][] = $fieldName;
            }

            $productAttributesSetsTCA[$attributesSet->getUid()]['label'] = $attributesSet->getName();
        }

        $showItems = $this->generateTCAShowItemString($productAttributesSetsTCA);
        if (!empty($showItems)) {
            foreach ($tca['types'] as &$type) {
                $type = str_replace(
                    ',--palette--;;paletteAttributes',
                    $showItems,
                    $type
                );
            }
        }
    }

    /**
     * Generate show items string for TCA
     *
     * @param array $attributesTCAFields
     * @return string
     */
    protected function generateTCAShowItemString(array $attributesTCAFields): string
    {
        $showItems = '';

        foreach ($attributesTCAFields as $attributeTCASet) {
            if (!empty($attributeTCASet['fields'])) {
                $showItems = sprintf(
                    ',--div--;%s,%s',
                    $attributeTCASet['label'],
                    implode(', ', $attributeTCASet['fields'])
                );
            }
        }

        return $showItems;
    }

    /**
     * Simulate DB data for attributes
     *
     * @param ObjectStorage $attributes
     * @param array $dbRow
     * @return array
     */
    protected function simulateDataValues(ObjectStorage $attributes, array $dbRow): array
    {
        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $tcaConfigurationProvider = $this->getAttributeTCAConfigurationProvider($attribute);

            $fieldName = $tcaConfigurationProvider->getTCAFieldName();
            $fieldValue = $tcaConfigurationProvider->convertRawValueToTCAValue($this->attributeValues);

            if ($fieldValue !== null) {
                $dbRow[$fieldName] = $fieldValue;
            }
        }

        return $dbRow;
    }

    /**
     * Set difference between translated and original product attribute values
     *
     * @param array $diffRow
     * @param array $defaultLanguageRow
     */
    protected function setDiffData(array &$diffRow, array &$defaultLanguageRow)
    {
        $attributeUidToValues = [];

        if (!empty($diffRow['serialized_attributes_values'])) {
            $attributeUidToValues = unserialize($diffRow['serialized_attributes_values']);
        }

        foreach ($attributeUidToValues as $attributeUid => $attributeValue) {
            $field = TCAUtility::getAttributeTCAFieldName($attributeUid);
            $diffRow[$field] = $attributeValue;
            $defaultLanguageRow[$field] = $attributeValue;
        }
    }

    /**
     * Show notification message for user
     *
     * @param string $label
     */
    protected function showNotificationMessage(string $label)
    {
        /** @var FlashMessage $flashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $this->translate($label),
            $this->translate('tca.notification_title'),
            FlashMessage::INFO,
            true
        );

        $flashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)->getMessageQueueByIdentifier(
            'core.template.flashMessages'
        );

        $flashMessageQueue->enqueue($flashMessage);
    }

    /**
     * @return ProductAttributesCollector
     */
    protected function getProductAttributesCollector(): ProductAttributesCollector
    {
        return GeneralUtility::makeInstance(ProductAttributesCollector::class, $this->product);
    }

    /**
     * Get configuration provider for TCA
     *
     * @param Attribute $attribute
     * @return ConcreteProviderInterface
     */
    protected function getAttributeTCAConfigurationProvider(Attribute $attribute): ConcreteProviderInterface
    {
        if (isset($this->dataProviders[$attribute->getUid()])) {
            return $this->dataProviders[$attribute->getUid()];
        }

        $tcaConfigurationProvider = AttributeConfigurationProviderFactory::createConcrete($attribute);

        $this->dataProviders[$attribute->getUid()] = $tcaConfigurationProvider;
        return $tcaConfigurationProvider;
    }
}
