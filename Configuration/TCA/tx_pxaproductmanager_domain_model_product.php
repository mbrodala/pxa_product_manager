<?php
defined('TYPO3_MODE') || die('Access denied.');

return (function () {
    $ll = 'LLL:EXT:pxa_product_manager/Resources/Private/Language/locallang_db.xlf:';

    return [
        'ctrl' => [
            'title' => $ll . 'tx_pxaproductmanager_domain_model_product',
            'label' => 'name',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'dividers2tabs' => true,
            'sortby' => 'sorting',
            'versioningWS' => true,
            'origUid' => 't3_origuid',
            'languageField' => 'sys_language_uid',
            'transOrigPointerField' => 'l10n_parent',
            'transOrigDiffSourceField' => 'l10n_diffsource',
            'delete' => 'deleted',
            'enablecolumns' => [
                'disabled' => 'hidden',
                'starttime' => 'starttime',
                'endtime' => 'endtime',
            ],
            'searchFields' => 'name,sku,teaser',
            'thumbnail' => 'images',
            'iconfile' => 'EXT:pxa_product_manager/Resources/Public/Icons/Svg/product.svg'
        ],
        // @codingStandardsIgnoreStart
        'interface' => [
            'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, slug, sku, price, tax_rate, teaser, description, disable_single_view, related_products, images, links, fal_links, sub_products, meta_description, keywords, alternative_title, attributes_values, attributes_files',
        ],

        'types' => [
            '1' => [
                'showitem' => '--palette--;;core, name, slug, sku, --palette--;;price, teaser, description,
--div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories,--palette--;;paletteAttributes,
--div--;' . $ll . 'tx_pxaproductmanager_domain_model_product.tab.assets, images, assets,
--div--;' . $ll . 'tx_pxaproductmanager_domain_model_product.tab.relations, related_products, sub_products, accessories, fal_links, links,
--div--;' . $ll . 'tx_pxaproductmanager_domain_model_product.tab.metadata, meta_description, keywords, alternative_title,
--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, --palette--;;access'
// @codingStandardsIgnoreEnd
            ],
        ],
        'palettes' => [
            '1' => ['showitem' => ''],
            'paletteAttributes' => ['showitem' => ''],
            'price' => ['showitem' => 'price, tax_rate'],
            'core' => ['showitem' => 'hidden, sys_language_uid, l10n_parent, l10n_diffsource'],
            'access' => ['showitem' => 'disable_single_view, --linebreak--, starttime, endtime'],
        ],
        'columns' => [
            'sys_language_uid' => [
                'exclude' => true,
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'special' => 'languages',
                    'items' => [
                        [
                            'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                            -1,
                            'flags-multiple'
                        ],
                    ],
                    'default' => 0,
                ]
            ],
            'l10n_parent' => [
                'displayCond' => 'FIELD:sys_language_uid:>:0',
                'exclude' => true,
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['', 0],
                    ],
                    'foreign_table' => 'tx_pxaproductmanager_domain_model_product',
                    'foreign_table_where' => 'AND tx_pxaproductmanager_domain_model_product.pid=###CURRENT_PID###' .
                        ' AND tx_pxaproductmanager_domain_model_product.sys_language_uid IN (-1,0)',
                    'default' => 0
                ],
            ],
            'l10n_diffsource' => [
                'config' => [
                    'type' => 'passthrough',
                ],
            ],
            'hidden' => [
                'exclude' => true,
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
                'config' => [
                    'type' => 'check',
                ],
            ],
            'starttime' => [
                'exclude' => true,
                'l10n_mode' => 'exclude',
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 16,
                    'eval' => 'datetime,int',
                    'default' => 0,
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                ],
            ],
            'endtime' => [
                'exclude' => true,
                'l10n_mode' => 'exclude',
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
                'config' => [
                    'type' => 'input',
                    'renderType' => 'inputDateTime',
                    'size' => 16,
                    'eval' => 'datetime,int',
                    'default' => 0,
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                ],
            ],
            'name' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.name',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim,required'
                ],
            ],
            'slug' => [
                'exclude' => true,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.slug',
                'config' => [
                    'type' => 'slug',
                    'size' => 50,
                    'generatorOptions' => [
                        'fields' => ['name'],
                        'replacements' => [
                            '/' => '-'
                        ],
                    ],
                    'fallbackCharacter' => '-',
                    'eval' => 'uniqueInPid',
                    'default' => ''
                ]
            ],
            'sku' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.sku',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'trim'
                ],
            ],
            'price' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.price',
                'config' => [
                    'type' => 'input',
                    'size' => 5,
                    'eval' => 'double2'
                ],
            ],
            'tax_rate' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.tax_rate',
                'config' => [
                    'type' => 'input',
                    'size' => 5,
                    'range' => [
                        'lower' => 0,
                        'upper' => 100,
                    ],
                    'eval' => 'double2',
                    'default' => 0.00
                ],
            ],
            'description' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.description',
                'config' => [
                    'type' => 'text',
                    'cols' => 40,
                    'rows' => 15,
                    'eval' => 'trim',
                    'enableRichtext' => true
                ]
            ],
            'disable_single_view' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.disable_single_view',
                'config' => [
                    'type' => 'check',
                    'default' => 0
                ],
            ],
            'related_products' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.related_products',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'foreign_table' => 'tx_pxaproductmanager_domain_model_product',
                    'foreign_table_where' => \Pixelant\PxaProductManager\Utility\TCAUtility::getRelatedProductsForeignTableWherePid() .
                        ' AND tx_pxaproductmanager_domain_model_product.uid != ###THIS_UID###' .
                        ' AND tx_pxaproductmanager_domain_model_product.sys_language_uid IN (-1,0)' .
                        ' ORDER BY tx_pxaproductmanager_domain_model_product.name',
                    'MM' => 'tx_pxaproductmanager_product_product_mm',
                    'size' => 10,
                    'autoSizeMax' => 30,
                    'maxitems' => 9999,
                    'multiple' => 0,
                    'enableMultiSelectFilterTextfield' => true,
                    'fieldControl' => [
                        'editPopup' => [
                            'disabled' => false
                        ],
                        'addRecord' => [
                            'disabled' => false,
                        ]
                    ]
                ]
            ],
            'images' => [
                'exclude' => true,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.images',
                'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'images',
                    [
                        'appearance' => [
                            'createNewRelationLinkTitle' =>
                                'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                            'showPossibleLocalizationRecords' => false,
                            'showRemovedLocalizationRecords' => true,
                            'showAllLocalizationLink' => false,
                            'showSynchronizationLink' => false
                        ],
                        'foreign_match_fields' => [
                            'fieldname' => 'images',
                            'tablenames' => 'tx_pxaproductmanager_domain_model_product',
                            'table_local' => 'sys_file',
                        ],
                        // @codingStandardsIgnoreStart
                        'overrideChildTca' => [
                            'types' => [
                                '0' => [
                                    'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                    'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                    'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                    'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                    'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                    'showitem' => '
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                                    --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                    --palette--;;filePalette'
                                ]
                            ]
                        ],
                        // @codingStandardsIgnoreEnd
                        'behaviour' => [
                            'allowLanguageSynchronization' => true
                        ],
                    ],
                    $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
                ),
            ],
            \Pixelant\PxaProductManager\Configuration\AttributesTCA\DefaultConfigurationProvider::ATTRIBUTE_FAL_DB_FIELD_NAME => [
                'exclude' => 0,
                'label' => \Pixelant\PxaProductManager\Configuration\AttributesTCA\DefaultConfigurationProvider::ATTRIBUTE_FAL_DB_FIELD_NAME,
                'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    \Pixelant\PxaProductManager\Configuration\AttributesTCA\DefaultConfigurationProvider::ATTRIBUTE_FAL_DB_FIELD_NAME,
                    [
                        'appearance' => [
                            'createNewRelationLinkTitle' =>
                                'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                            'showPossibleLocalizationRecords' => false,
                            'showRemovedLocalizationRecords' => true,
                            'showAllLocalizationLink' => false,
                            'showSynchronizationLink' => false
                        ],
                        'foreign_match_fields' => [
                            'fieldname' => \Pixelant\PxaProductManager\Configuration\AttributesTCA\DefaultConfigurationProvider::ATTRIBUTE_FAL_DB_FIELD_NAME,
                            'tablenames' => 'tx_pxaproductmanager_domain_model_product',
                            'table_local' => 'sys_file',
                        ],
                        // @codingStandardsIgnoreStart
                        'overrideChildTca' => [
                            'types' => [
                                '0' => [
                                    'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                    'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                    'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                    'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                    'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                    'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPalette,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                                ]
                            ]
                        ]
                    ]
                // @codingStandardsIgnoreEnd
                ),
            ],
            'fal_links' => [
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.fal_links',
                'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    'fal_links',
                    [
                        'behaviour' => [
                            'allowLanguageSynchronization' => true
                        ],
                        'appearance' => [
                            'createNewRelationLinkTitle' => $ll .
                                'tx_pxaproductmanager_domain_model_product.fal_links.add_button',
                            'fileUploadAllowed' => false,
                        ],
                        'foreign_match_fields' => [
                            'fieldname' => 'fal_links',
                            'tablenames' => 'tx_pxaproductmanager_domain_model_product',
                            'table_local' => 'sys_file',
                        ],
                        'overrideChildTca' => [
                            'types' => [
                                '0' => [
                                    'showitem' => '
                                    --palette--;;basicoverlayPalette,
                                    --palette--;;filePalette,'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                    'showitem' => '
                                    --palette--;;basicoverlayPalette,
                                    --palette--;;filePalette,'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                    'showitem' => '
                                    --palette--;;basicoverlayPalette,
                                    --palette--;;filePalette,'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                    'showitem' => '
                                    --palette--;;basicoverlayPalette,
                                    --palette--;;filePalette,'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                    'showitem' => '
                                    --palette--;;basicoverlayPalette,
                                    --palette--;;filePalette,'
                                ],
                                \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                    'showitem' => '
                                    --palette--;;basicoverlayPalette,
                                    --palette--;;filePalette,'
                                ]
                            ]
                        ]
                    ]
                )
            ],
            'links' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.links',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_pxaproductmanager_domain_model_link',
                    'foreign_field' => 'product',
                    'foreign_sortby' => 'sorting',
                    'maxitems' => 9999,
                    'appearance' => [
                        'collapseAll' => 1,
                        'levelLinksPosition' => 'top',
                        'showSynchronizationLink' => 1,
                        'showPossibleLocalizationRecords' => 1,
                        'showAllLocalizationLink' => 1
                    ]
                ],
            ],
            'sub_products' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.sub_products',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'foreign_table' => 'tx_pxaproductmanager_domain_model_product',
                    'foreign_table_where' => \Pixelant\PxaProductManager\Utility\TCAUtility::getSubProductsForeignTableWherePid() .
                        ' AND tx_pxaproductmanager_domain_model_product.uid != ###THIS_UID###' .
                        ' AND tx_pxaproductmanager_domain_model_product.sys_language_uid IN (-1,0)' .
                        ' ORDER BY tx_pxaproductmanager_domain_model_product.name',
                    'MM' => 'tx_pxaproductmanager_product_subproducts_product_mm',
                    'size' => 10,
                    'autoSizeMax' => 30,
                    'maxitems' => 9999,
                    'multiple' => 0,
                    'enableMultiSelectFilterTextfield' => true,
                    'fieldControl' => [
                        'editPopup' => [
                            'disabled' => false
                        ],
                        'addRecord' => [
                            'disabled' => false,
                        ]
                    ]
                ]
            ],
            'keywords' => [
                'exclude' => true,
                'label' => $GLOBALS['TCA']['pages']['columns']['keywords']['label'],
                'config' => [
                    'type' => 'text',
                    'cols' => 30,
                    'rows' => 5,
                    'behaviour' => [
                        'allowLanguageSynchronization' => true
                    ],
                ]
            ],
            'meta_description' => [
                'exclude' => true,
                'label' => $GLOBALS['TCA']['pages']['columns']['description']['label'],
                'config' => [
                    'type' => 'text',
                    'cols' => 30,
                    'rows' => 5,
                    'behaviour' => [
                        'allowLanguageSynchronization' => true
                    ],
                ]
            ],
            'alternative_title' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.alternative_title',
                'config' => [
                    'type' => 'input',
                    'size' => 30
                ]
            ],
            'assets' => [
                'label' => 'LLL:EXT:frontend/Resources/Private/Language/Database.xlf:tt_content.asset_references',
                'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig('assets', [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/Database.xlf:tt_content.asset_references.addFileReference'
                    ],
                    'foreign_match_fields' => [
                        'fieldname' => 'assets',
                        'tablenames' => 'tx_pxaproductmanager_domain_model_product',
                        'table_local' => 'sys_file',
                    ],
                    // custom configuration for displaying fields in the overlay/reference table
                    // behaves the same as the image field.
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                                'showitem' => '
                                --palette--;LLL:EXT:lang/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ],
                    ],
                ], $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'])
            ],
            'teaser' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.teaser',
                'config' => [
                    'type' => 'text',
                    'cols' => 40,
                    'rows' => 5,
                    'eval' => 'trim',
                    'enableRichtext' => false
                ]
            ],
            'accessories' => [
                'exclude' => 0,
                'label' => $ll . 'tx_pxaproductmanager_domain_model_product.accessories',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'foreign_table' => 'tx_pxaproductmanager_domain_model_product',
                    'foreign_table_where' => \Pixelant\PxaProductManager\Utility\TCAUtility::getAccessoriesForeignTableWherePid() .
                        ' AND tx_pxaproductmanager_domain_model_product.uid != ###THIS_UID###' .
                        ' AND tx_pxaproductmanager_domain_model_product.sys_language_uid IN (-1,0)' .
                        ' ORDER BY tx_pxaproductmanager_domain_model_product.name',
                    'MM' => 'tx_pxaproductmanager_product_accessories_product_mm',
                    'size' => 10,
                    'autoSizeMax' => 30,
                    'maxitems' => 9999,
                    'multiple' => 0,
                    'enableMultiSelectFilterTextfield' => true,
                    'fieldControl' => [
                        'editPopup' => [
                            'disabled' => false
                        ],
                        'addRecord' => [
                            'disabled' => false,
                        ]
                    ]
                ]
            ],
            'attributes_values' => [
                'exclude' => false,
                'label' => 'attributes_values',
                'config' => [
                    'type' => 'text'
                ]
            ],
            'crdate' => [
                'label' => 'crdate',
                'config' => [
                    'type' => 'passthrough'
                ]
            ],
            'tstamp' => [
                'label' => 'tstamp',
                'config' => [
                    'type' => 'passthrough'
                ]
            ],
            'deleted' => [
                'label' => 'deleted',
                'config' => [
                    'type' => 'passthrough'
                ]
            ],
        ],
    ];
})();
