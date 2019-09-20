<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

use Pixelant\PxaProductManager\Domain\Model\Attribute;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class FalProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
class FalProvider extends AbstractProvider
{
    /**
     * Name of the field in DB
     */
    const DB_FIELD_NAME = 'attribute_files';

    /**
     * Return TCA configuration
     *
     * @return array
     */
    public function getFieldConfiguration(): array
    {
        if ($this->attribute->getType() === Attribute::ATTRIBUTE_TYPE_IMAGE) {
            $allowedFileTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
            $label = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference';
        } else {
            $allowedFileTypes = '';
            $label = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:media.addFileReference';
        }

        $configuration = $this->getFalFieldTCAConfiguration(
            $this->getFieldName(),
            $this->attribute->getUid(),
            $this->attribute->getName(),
            $label,
            $allowedFileTypes
        );

        $configuration['label'] = $this->attribute->getName();

        return $configuration;
    }

    /**
     * Fal dynamic configuration
     *
     * @param string $fieldName
     * @param int $attributeUid
     * @param string $attributeName
     * @param string $addNewLabel
     * @param string $allowedFileExtensions
     * @param string $disallowedFileExtensions
     * @return array
     */
    protected function getFalFieldTCAConfiguration(
        string $fieldName,
        int $attributeUid,
        string $attributeName,
        string $addNewLabel = '',
        string $allowedFileExtensions = '',
        string $disallowedFileExtensions = ''
    ): array {
        return [
            'exclude' => false,
            'label' => '',
            // @codingStandardsIgnoreStart
            'config' => ExtensionManagementUtility::getFileFieldTCAConfig(
                $fieldName,
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => $addNewLabel,
                        'showPossibleLocalizationRecords' => false,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => false,
                        'showSynchronizationLink' => false,
                        'collapseAll' => true
                    ],
                    'foreign_match_fields' => [
                        'fieldname' => self::DB_FIELD_NAME,
                        'tablenames' => 'tx_pxaproductmanager_domain_model_product',
                        'table_local' => 'sys_file',
                        'pxa_attribute' => $attributeUid
                    ],
                    'overrideChildTca' => [
                        'columns' => [
                            'pxa_attribute' => [
                                'config' => [
                                    'items' => [
                                        [
                                            $attributeName,
                                            $attributeUid
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'types' => [
                            '0' => [
                                'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPaletteAttribute,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                            ],
                            File::FILETYPE_TEXT => [
                                'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPaletteAttribute,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                            ],
                            File::FILETYPE_IMAGE => [
                                'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPaletteAttribute,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                            ],
                            File::FILETYPE_AUDIO => [
                                'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPaletteAttribute,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                            ],
                            File::FILETYPE_VIDEO => [
                                'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPaletteAttribute,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                            ],
                            File::FILETYPE_APPLICATION => [
                                'showitem' => '
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;pxaProductManagerPaletteAttribute,
                            --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette'
                            ]
                        ]
                    ]
                ],
                $allowedFileExtensions,
                $disallowedFileExtensions
            )
            // @codingStandardsIgnoreEnd
        ];
    }
}
