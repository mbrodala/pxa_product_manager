<?php
defined('TYPO3_MODE') || die;

(function () {
    $extKey = 'pxa_product_manager';

    // @codingStandardsIgnoreStart
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Pixelant.' . $extKey,
        'Pi1',
        [
            'Product' => 'list, show, wishList, finishOrder, lazyList, comparePreView, compareView, groupedList, promotionList',
            'Navigation' => 'show',
            'AjaxProducts' => 'ajaxLazyList, latestVisited',
            'AjaxJson' => 'toggleWishList, toggleCompareList, loadCompareList, emptyCompareList, loadWishList, addLatestVisitedProduct',
            'Filter' => 'showFilter'
        ],
        // non-cacheable actions
        [
            'Product' => 'wishList, finishOrder, comparePreView, compareView',
            'AjaxProducts' => 'ajaxLazyList, latestVisited',
            'AjaxJson' => 'toggleWishList, toggleCompareList, loadCompareList, emptyCompareList, loadWishList, addLatestVisitedProduct'
        ]
    );
    // @codingStandardsIgnoreEnd

    // Register cart as another plugin. Otherwise it has conflict
    // with product single view
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Pixelant.' . $extKey,
        'Pi2',
        [
            'Product' => 'wishListCart, compareListCart',
        ],
        // non-cacheable actions
        [
        ]
    );

    // @codingStandardsIgnoreStart
    // Page module hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxaproductmanager_pi1']['pxa_product_manager'] =
        \Pixelant\PxaProductManager\Hook\PageLayoutView::class . '->getExtensionSummary';

    /*
     * Hook into form engine in order to create TCA fields configuration on fly
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Pixelant\PxaProductManager\Backend\FormDataProvider\ProductEditFormInitialize::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class
        ]
    ];

    // @TODO remove?
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Pixelant\PxaProductManager\Backend\FormDataProvider\OrderEditFormInitialize::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class
        ]
    ];

    // LinkHandler
    // t3://pxappm?product=[product_id]
    // t3://pxappm?category=[category_id]
    $linkType = 'pxappm';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler'][$linkType] = \Pixelant\PxaProductManager\LinkHandler\LinkHandling::class;
    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder'][$linkType] = \Pixelant\PxaProductManager\LinkHandler\ProductLinkBuilder::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler'][$linkType] = \Pixelant\PxaProductManager\LinkHandler\LinkHandlingFormData::class;

    // Register cache
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pxa_pm_categories']['frontend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_pxa_pm_categories'] = [
            'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
            'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
            'options' => [
                'defaultLifetime' => 0
            ],
            'groups' => ['all']
        ];
    }
    // @codingStandardsIgnoreEnd

    // Include page typoscript
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:pxa_product_manager/Configuration/TypoScript/PageTS/rteTsConfig.ts">'
    );

    // Register field control for identifier
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1534315213786] = [
        'nodeName' => 'attributeIdentifierControl',
        'priority' => 30,
        'class' => \Pixelant\PxaProductManager\Backend\FormEngine\FieldControl\AttributeIdentifierControl::class
    ];

    // Register the class to be available in 'eval' of TCA
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\Pixelant\PxaProductManager\Backend\Evaluation\LcFirstEvaluation::class] = '';

    // Modify data structure of flexform
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing'][$extKey] =
        \Pixelant\PxaProductManager\Hook\FlexFormDataStructureHook::class;

    // Plugin actions
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Pixelant\PxaProductManager\Utility\FlexformUtility::class)->registerDefaultAction();
})();
