<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Utility;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexformUtility
 * @package Pixelant\PxaProductManager\Utility
 */
class FlexformUtility
{
    /**
     * Default flexform loaded for all actions
     * @var string
     */
    public static $defaultFlexform = 'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_common.xml';

    /**
     * LLL path
     * @var string
     */
    protected $ll = 'LLL:EXT:pxa_product_manager/Resources/Private/Language/locallang_be.xlf:';

    /**
     * Default flexform actions
     * @var array
     */
    protected $defaultSwitchableActions = [
        [
            'action' => 'Product->list;Product->show',
            'label' => 'flexform.mode.product_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_list.xml',
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_products_orderings.xml',
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_show.xml',
            ],
            'excludeFields' => [],
        ],
        [
            'action' => 'Product->show',
            'label' => 'flexform.mode.product_show',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_show.xml',
            ],
            'excludeFields' => [
                'settings.pids.singleViewPid'
            ],
        ],
        [
            'action' => 'Product->customProductsList;Product->show',
            'label' => 'flexform.mode.product_custom_products_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_custom_products_list.xml',
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_show.xml',
            ],
            'excludeFields' => [],
        ],
        [
            'action' => 'Product->lazyList;AjaxProducts->loadLazyList;Product->show',
            'label' => 'flexform.mode.product_lazy_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_lazy_list.xml',
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_products_orderings.xml',
            ],
            'excludeFields' => [],
        ],
        [
            'action' => 'Product->wishList;Product->finishOrder',
            'label' => 'flexform.mode.product_wish_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_with_list.xml'
            ],
            'excludeFields' => [],
        ],
        [
            'action' => 'Product->compareView',
            'label' => 'flexform.mode.product_compare_view',
            'flexforms' => [],
            'excludeFields' => [],
        ],
    ];

    /**
     * Register default actions
     */
    public function registerDefaultAction(): void
    {
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'])) {
            $GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'] = [];
        }

        foreach ($this->defaultSwitchableActions as $action) {
            $this->addSwitchableControllerAction(
                $action['action'],
                $this->ll . $action['label'],
                $action['flexforms'],
                $action['excludeFields']
            );
        }
    }

    /**
     * Add action to flexform of product manager
     *
     * @param string $action Action: Product->action
     * @param string $label Label path with LLL:ext:
     * @param array $flexforms Array with subflexforms path
     * @param array $excludeFields Force flexform fields to be excluded
     */
    public function addSwitchableControllerAction(string $action, string $label, array $flexforms = [], array $excludeFields = []): void
    {
        $items = &$GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'];
        $items[] = compact('action', 'label', 'flexforms', 'excludeFields');
    }

    /**
     * Remove action from flexform
     *
     * @param string $action
     */
    public function removeSwitchableControllerAction(string $action): void
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'][$action])) {
            unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'][$action]);
        }
    }

    /**
     * Get action configuration
     *
     * @param string $action
     * @return array|null
     */
    public function getSwitchableControllerActionConfiguration(string $action): ?array
    {
        foreach ($this->getAllRegisteredActions() as $registeredAction) {
            if ($registeredAction['action'] === $action) {
                return $registeredAction;
            }
        }

        return null;
    }

    /**
     * Get all actions
     *
     * @return array
     */
    public function getAllRegisteredActions(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'] ?? [];
    }

    /**
     * Load all actions default data structure
     *
     * @param array $dataStructure
     * @return array
     */
    public function loadDefaultDataStructure(array $dataStructure): array
    {
        return $this->updateDataStructureWithFlexform($dataStructure, static::$defaultFlexform);
    }

    /**
     * Load flexforms data structure from flexforms subparts
     *
     * @param array $dataStructure
     * @param string $action
     * @return array
     */
    public function loadActionDataStructure(array $dataStructure, string $action): array
    {
        $actionConfig = $this->getSwitchableControllerActionConfiguration($action);

        if ($actionConfig !== null) {
            // Load sub-form
            foreach ($actionConfig['flexforms'] as $flexform) {
                $dataStructure = $this->updateDataStructureWithFlexform($dataStructure, $flexform);
            }

            // Exclude fields
            foreach ($actionConfig['excludeFields'] as $excludeField) {
                foreach ($dataStructure['sheets'] as $sheet => $sheetConf) {
                    foreach ($sheetConf['ROOT']['el'] as $field => $fieldConf) {
                        if ($field === $excludeField) {
                            unset($dataStructure['sheets'][$sheet]['ROOT']['el'][$field]);
                        }
                    }
                }
            }
        }

        return $dataStructure;
    }

    /**
     * Update data structure
     *
     * @param array $dataStructure
     * @param string $flexformPath
     * @return array
     */
    protected function updateDataStructureWithFlexform(array $dataStructure, string $flexformPath): array
    {
        $fullPath = GeneralUtility::getFileAbsFileName($flexformPath);
        if (!file_exists($fullPath)) {
            throw new \RuntimeException(
                "Colud not find flexform with path '$fullPath'(given path '$flexformPath')",
                1570185225935
            );
        }

        $xml = file_get_contents($fullPath);
        ArrayUtility::mergeRecursiveWithOverrule($dataStructure, GeneralUtility::xml2array($xml));

        return $dataStructure;
    }
}
