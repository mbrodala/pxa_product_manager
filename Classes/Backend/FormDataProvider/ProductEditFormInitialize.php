<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Backend\FormDataProvider;

use Pixelant\PxaProductManager\Collection\CategoriesCollector;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\AttributeConfigurationProviderFactory;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\ConcreteProviderInterface;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\DefaultConfigurationProvider;
use Pixelant\PxaProductManager\Domain\Model\Attribute;
use Pixelant\PxaProductManager\Domain\Model\AttributeSet;
use Pixelant\PxaProductManager\Domain\Repository\AttributeSetRepository;
use Pixelant\PxaProductManager\Domain\Repository\CategoryRepository;
use Pixelant\PxaProductManager\Traits\TranslateBeTrait;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository = null;

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
            $allParentCategoriesUids = $this->getCategoriesCollector()->collectParentsUidsForList(
                $this->categoryRepository->findUidsByProduct((int)$result['databaseRow']['uid'])
            );

            $attributesSets = $this->attributeSetRepository->findByCategoriesUids($allParentCategoriesUids);

            if (!empty($attributesSets)) {
                $this->populateTCA($attributesSets, $result['processedTca']);
                $result['databaseRow'] = $this->simulateDataValues($attributesSets, $result['databaseRow']);

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
     * Init
     * @param array $row
     */
    protected function init(array $row): void
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->categoryRepository = $objectManager->get(CategoryRepository::class);
        $this->attributeSetRepository = $objectManager->get(AttributeSetRepository::class);

        $this->attributeValues = json_decode($row[DefaultConfigurationProvider::ATTRIBUTES_VALUES_DB_FIELD_NAME], true);
    }

    /**
     * Add attributes configuration to TCA
     *
     * @param array $attributesSets
     * @param array &$tca
     */
    protected function populateTCA(array $attributesSets, array &$tca)
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
     * @param array $attributesSets
     * @param array $dbRow
     * @return array
     */
    protected function simulateDataValues(array $attributesSets, array $dbRow): array
    {
        /** @var AttributeSet $attributesSet */
        foreach ($attributesSets as $attributesSet) {
            /** @var Attribute $attribute */
            foreach ($attributesSet->getAttributes() as $attribute) {
                $tcaConfigurationProvider = $this->getAttributeTCAConfigurationProvider($attribute);

                $fieldName = $tcaConfigurationProvider->getTCAFieldName();
                $fieldValue = $tcaConfigurationProvider->convertRawValueToTCAValue($this->attributeValues);

                if ($fieldValue !== null) {
                    $dbRow[$fieldName] = $fieldValue;
                }
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
        $fieldDB = DefaultConfigurationProvider::ATTRIBUTES_VALUES_DB_FIELD_NAME;

        if (!empty($diffRow[$fieldDB])) {
            $attributeUidToValues = json_decode($diffRow[$fieldDB], true);
        }

        foreach ($attributeUidToValues as $attributeUid => $attributeValue) {
            // Diff row should have same attributes as default
            if (!isset($this->dataProviders[$attributeUid])) {
                continue;
            }

            $tcaConfigurationProvider = $this->dataProviders[$attributeUid];

            $fieldName = $tcaConfigurationProvider->getTCAFieldName();

            $diffRow[$fieldName] = $attributeValue;
            // $defaultLanguageRow[$fieldName] = $attributeValue; @TODO why this is here?
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
     * @return CategoriesCollector
     */
    protected function getCategoriesCollector(): CategoriesCollector
    {
        return GeneralUtility::makeInstance(CategoriesCollector::class);
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
