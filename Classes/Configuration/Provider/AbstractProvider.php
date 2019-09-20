<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

use Pixelant\PxaProductManager\Domain\Model\Attribute;

/**
 * Class AbstractProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
abstract class AbstractProvider implements AttributeTCAConfigurationProvider
{
    /**
     * Prefix for TCA fields
     */
    protected $attributeFieldPrefix = 'tx_pxaproductmanager_attribute_';

    /**
     * Prefix for FAL fields
     */
    protected $attributeFalFeildPrefix = 'tx_pxaproductmanager_file_attribute_';

    /**
     * @var Attribute
     */
    protected $attribute = null;

    /**
     * @var array
     */
    protected static $attributesTCAConfiguration = [
        Attribute::ATTRIBUTE_TYPE_INPUT => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ]
        ],

        Attribute::ATTRIBUTE_TYPE_TEXT => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'text',
                'cols' => '48',
                'rows' => '8',
                'eval' => 'trim'
            ]
        ],

        Attribute::ATTRIBUTE_TYPE_CHECKBOX => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'check',
                'items' => []
            ]
        ],

        Attribute::ATTRIBUTE_TYPE_DROPDOWN => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [],
                'size' => 1,
                'maxitems' => 1
            ]
        ],

        Attribute::ATTRIBUTE_TYPE_MULTISELECT => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => [],
                'size' => 10,
                'maxitems' => 99,
                'multiple' => 0,
            ]
        ],

        Attribute::ATTRIBUTE_TYPE_DATETIME => [
            'exclude' => false,
            'label' => '',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime'
            ]
        ],

        Attribute::ATTRIBUTE_TYPE_LINK => [
            'exclude' => false,
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '256',
                'eval' => 'trim',
                'renderType' => 'inputLink',
                'softref' => 'typolink'
            ],
        ],
    ];

    /**
     * @param Attribute $attribute
     */
    public function __construct(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Get attribute field name for TCA
     *
     * @return string
     */
    public function getFieldName(): string
    {
        $prefix = $this->attribute->isFalType()
            ? $this->attributeFieldPrefix
            : $this->attributeFalFeildPrefix;

        return $prefix . $this->attribute->getUid();
    }

    /**
     * Initial TCA configuration
     *
     * @return array
     */
    protected function getInitialConfiguration(): array
    {
        $configuration = static::$attributesTCAConfiguration[$this->attribute->getType()];
        $configuration['label'] = $this->attribute->getName();

        if ($this->attribute->getDefaultValue()) {
            $configuration['config']['default'] = $this->attribute->getDefaultValue();
        }

        return $configuration;
    }

    /**
     * Shortcut method
     *
     * @return bool
     */
    protected function isRequired(): bool
    {
        return $this->attribute->isRequired();
    }
}
