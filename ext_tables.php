<?php
defined('TYPO3_MODE') || die;

(function() {
    $extKey = 'pxa_product_manager';

    // Register plugin
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        $extKey,
        'Pi1',
        'Product Manager'
    );

    $tables = [
        'tx_pxaproductmanager_domain_model_product',
        'tx_pxaproductmanager_domain_model_attribute',
        'tx_pxaproductmanager_domain_model_attributeset',
        'tx_pxaproductmanager_domain_model_attributevalue',
        'tx_pxaproductmanager_domain_model_option',
        'tx_pxaproductmanager_domain_model_link',
        'tx_pxaproductmanager_domain_model_filter',
        'tx_pxaproductmanager_domain_model_order',
        'tx_pxaproductmanager_domain_model_orderconfiguration',
        'tx_pxaproductmanager_domain_model_orderformfield'
    ];

    // @codingStandardsIgnoreStart
    foreach ($tables as $table) {
        if ($table !== 'tx_pxaproductmanager_domain_model_attributevalue'
            || $table !== 'tx_pxaproductmanager_domain_model_order'
        ) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
                $table,
                'EXT:pxa_product_manager/Resources/Private/Language/locallang_csh_' . $table . '.xlf'
            );
        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($table);
    }

    /*
     * Register datahandler hook
     * It will transform attributes to JSON
     */
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][$extKey] = \Pixelant\PxaProductManager\Hook\DataHandlerHook::class;

    // Add plugin to content element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:pxa_product_manager/Configuration/TypoScript/PageTS/contentElementWizard.ts">'
    );
    // @codingStandardsIgnoreEnd

    // Link handler
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:pxa_product_manager/Configuration/TypoScript/PageTS/linkHandler.ts">'
    );

    $icons = [
        'ext-pxa-product-manager-wizard-icon' => 'package.svg',
    ];

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );

    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:pxa_product_manager/Resources/Public/Icons/Svg/' . $path]
        );
    }

    // Register BE module
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Pixelant.' . $extKey,
        'web',          // Main area
        'mod1',         // Name of the module
        '',             // Position of the module
        [
            'BackendManager' => 'index, listCategories, listProducts, listOrders, showOrder, deleteOrder, toggleOrderState'
        ],
        [          // Additional configuration
            'access' => 'user,group',
            'icon' => 'EXT:' . $extKey . '/ext_icon.svg',
            'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_mod.xml',
        ]
    );
})();
