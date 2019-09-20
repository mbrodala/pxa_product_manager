<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Backend\FormDataProvider;

use Pixelant\PxaProductManager\Collection\ProductAttributesCollector;
use Pixelant\PxaProductManager\Configuration\Provider\AttributeConfigurationProviderFactory;
use Pixelant\PxaProductManager\Configuration\Provider\AttributeTCAConfigurationProvider;
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
     * @var AttributeTCAConfigurationProvider[]
     */
    protected $dataProviders = [];

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
            /** @var Product $product */
            $product = MainUtility::singleRowToExtbaseObject(Product::class, $result['databaseRow']);
            $attributesCollector = $this->getProductAttributesCollector($product);

            if ($attributesCollector->getAttributes()->count()) {
                $this->populateTCA($attributesCollector->getAttributesSets(), $result['processedTca']);
                $this->simulateDataValues($attributesCollector->getAttributes(), $product);

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

                $fieldName = $tcaConfigurationProvider->getFieldName();
                $tcaConfiguration = $tcaConfigurationProvider->getFieldConfiguration();

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
     * @param Product $product
     */
    protected function simulateDataValues(ObjectStorage $attributes, Product $product): void
    {
        $attributeUidToValue = $product->getAttributeValuesRaw();

        /** @var Attribute $attribute */
        foreach ($attributes as $attribute) {
            $tcaConfigurationProvider = $this->getAttributeTCAConfigurationProvider($attribute);
            $fieldName = $tcaConfigurationProvider->getFieldName();

            if (array_key_exists($attribute->getUid(), $attributeUidToValue)) {
                switch ($attribute->getType()) {
                    case Attribute::ATTRIBUTE_TYPE_DROPDOWN:
                    case Attribute::ATTRIBUTE_TYPE_MULTISELECT:
                        $dbRow[$fieldName] = GeneralUtility::trimExplode(
                            ',',
                            $attributeUidToValue[$attribute->getUid()],
                            true
                        );
                        break;
                    default:
                        $dbRow[$fieldName] = $attributeUidToValue[$attribute->getUid()];
                }
            } elseif ($attribute->getDefaultValue()
                && $attribute->getType() !== Attribute::ATTRIBUTE_TYPE_MULTISELECT
            ) {
                $dbRow[$fieldName] = $attribute->getDefaultValue();
            }
        }
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
     * @param Product $product
     * @return ProductAttributesCollector
     */
    protected function getProductAttributesCollector(Product $product): ProductAttributesCollector
    {
        return GeneralUtility::makeInstance(ProductAttributesCollector::class, $product);
    }

    /**
     * Get configuration provider for TCA
     *
     * @param Attribute $attribute
     * @return AttributeTCAConfigurationProvider
     */
    protected function getAttributeTCAConfigurationProvider(Attribute $attribute): AttributeTCAConfigurationProvider
    {
        if (isset($this->dataProviders[$attribute->getUid()])) {
            return $this->dataProviders[$attribute->getUid()];
        }

        $tcaConfigurationProvider = AttributeConfigurationProviderFactory::create($attribute);

        $this->dataProviders[$attribute->getUid()] = $tcaConfigurationProvider;
        return $tcaConfigurationProvider;
    }
}
