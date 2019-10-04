<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Utility;

/**
 * Class FlexformUtility
 * @package Pixelant\PxaProductManager\Utility
 */
class FlexformUtility
{
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
        ],
        [
            'action' => 'Product->show',
            'label' => 'flexform.mode.product_show',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_show.xml',
            ],
            'excludeFields' => [
                'settings.pids.singleViewPid'
            ]
        ],
        [
            'action' => 'Product->customProductsList;Product->show',
            'label' => 'flexform.mode.product_custom_products_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_custom_products_list.xml',
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_show.xml',
            ]
        ],
        [
            'action' => 'Product->lazyList;AjaxProducts->loadLazyList;Product->show',
            'label' => 'flexform.mode.product_lazy_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_lazy_list.xml',
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_products_orderings.xml',
            ],
        ],
        [
            'action' => 'Product->wishList;Product->finishOrder',
            'label' => 'flexform.mode.product_wish_list',
            'flexforms' => [
                'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_with_list.xml'
            ]
        ],
        [
            'action' => 'Product->compareView',
            'label' => 'flexform.mode.product_compare_view',
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
                $action['flexforms'] ?? [],
                $action['excludeFields'] ?? []
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
     * Get all actions
     *
     * @return array
     */
    public function getAllRegisteredActions(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['pxa_product_manager']['switchableControllerActions']['items'] ?? [];
    }
}
