<?php
defined('TYPO3_MODE') || die;

(function () {
    // Extbase
    $extbaseContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
    $extbaseContainer->registerImplementation(
        \Pixelant\PxaProductManager\Attributes\ValueMapper\MapperServiceInterface::class,
        \Pixelant\PxaProductManager\Attributes\ValueMapper\MapperService::class
    );
    $extbaseContainer->registerImplementation(
        \Pixelant\PxaProductManager\Attributes\ValueUpdater\UpdaterInterface::class,
        \Pixelant\PxaProductManager\Attributes\ValueUpdater\ValueUpdaterService::class
    );

    // Configure plugin
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Pixelant.pxa_product_manager',
        'Pi1',
        [
            'Product' => 'list, show,',
            'Category' => 'list',
        ],
        // non-cacheable actions
        [
        ]
    );


    // Register field control for identifier attribute
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1534315213786] = [
        'nodeName' => 'attributeIdentifierControl',
        'priority' => 30,
        'class' => \Pixelant\PxaProductManager\Backend\FormEngine\FieldControl\AttributeIdentifierControl::class
    ];

    // Add attributes fields to Product edit form
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Pixelant\PxaProductManager\Backend\FormDataProvider\ProductEditFormManipulation::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowInitializeNew::class,
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class
        ]
    ];

    // Modify data structure of flexform. Hook will dynamically load flexform parts for selected action
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['pxa_product_manager'] =
        \Pixelant\PxaProductManager\Hook\FlexFormDataStructureHook::class;

    // Register default plugin actions with flexform settings
    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Pixelant\PxaProductManager\Configuration\Flexform\Registry::class)->registerDefaultActions();

    // Register hook to show plugin flexform settings preview
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['pxaproductmanager_pi1']['pxa_product_manager'] =
        \Pixelant\PxaProductManager\Hook\PageLayoutView::class . '->getExtensionSummary';

    // Include page TS
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="DIR:EXT:pxa_product_manager/Configuration/TypoScript/PageTS/" extensions="ts">'
    );

    // LinkHandler
    // t3://pxappm?product=[product_id]
    // t3://pxappm?category=[category_id]
    $linkType = 'pxappm';
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['linkHandler'][$linkType] = \Pixelant\PxaProductManager\LinkHandler\LinkHandling::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['linkHandler'][$linkType] = \Pixelant\PxaProductManager\LinkHandler\LinkHandlingFormData::class;
    $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder'][$linkType] = \Pixelant\PxaProductManager\Service\TypolinkBuilderService::class;


    // Draw header hook
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook']['pxa_product_manager'] =
        \Pixelant\PxaProductManager\Hook\PageHookRelatedCategories::class . '->render';
})();
