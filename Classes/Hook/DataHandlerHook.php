<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook;

use Pixelant\PxaProductManager\Configuration\AttributesTCA\AttributeConfigurationProviderFactory;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\DefaultConfigurationProvider as TCAConfiguration;
use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use Pixelant\PxaProductManager\Utility\MainUtility;
use Pixelant\PxaProductManager\Utility\ProductUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package pxa_products
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DataHandlerHook
{
    /**
     * Save product attributes as JSON
     *
     * @param array $fieldsArray
     * @param string $table
     * @param string|int $id
     */
    // @codingStandardsIgnoreStart
    public function processDatamap_preProcessFieldArray(array &$fieldsArray, string $table, $id): void
    {// @codingStandardsIgnoreEnd
        if ($table === 'tx_pxaproductmanager_domain_model_product'
            && MathUtility::canBeInterpretedAsInteger($id)
        ) {
            $attributesData = [];
            $attributesFiles = [];
            $configurationProvider = AttributeConfigurationProviderFactory::createDefault();

            /*
             * Go through all fields and fetch attributes values
             */
            foreach ($fieldsArray as $fieldName => $value) {
                if ($configurationProvider->isFieldAttributeTCAField($fieldName)) {
                    $attributeId = $configurationProvider->determinateAttributeUid($fieldName);
                    $attributesData[$attributeId] = $value;

                    unset($fieldsArray[$fieldName]);
                } elseif ($configurationProvider->isFieldFalAttributeTCAField($fieldName) && !empty($value)) {
                    $attributesFiles[] = $value;
                    unset($fieldsArray[$fieldName]);
                }
            }

            if (!empty($attributesFiles)) {
                $fieldsArray[TCAConfiguration::ATTRIBUTE_FAL_DB_FIELD_NAME] = implode(',', $attributesFiles);
            }

            if (!empty($attributesData)) {
                $fieldsArray[TCAConfiguration::ATTRIBUTES_VALUES_DB_FIELD_NAME] = json_encode($attributesData);
            }
        }
    }


    /**
     * @TODO remove?
     * Set custom sorting for product
     *
     * @param $status
     * @param $table
     * @param $id
     * @param $fieldArray
     * @param $pObj
     */
    // @codingStandardsIgnoreStart
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj)
    {
        return;
        // @codingStandardsIgnoreEnd
        if ($table == 'tx_pxaproductmanager_domain_model_product') {
            /** @var ProductRepository $productRepository */
            $productRepository = MainUtility::getObjectManager()->get(ProductRepository::class);

            /** @var Product $product */
            $product = $productRepository->findByIdentifier($id);

            if ($product) {
                $product->setCustomSorting(ProductUtility::getCalculatedCustomSorting($product));

                if ($product->_isDirty()) {
                    $productRepository->update($product);

                    /** @var PersistenceManager $persistenceManager */
                    $persistenceManager = MainUtility::getObjectManager()->get(PersistenceManager::class);
                    $persistenceManager->persistAll();
                }
            }
        }
    }
}
