<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA;

use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\CheckboxProviderConcrete;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\ConcreteProviderInterface;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\FalProviderConcrete;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\InputProviderConcrete;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\LinkProviderConcrete;
use Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\SelectBoxProviderConcrete;
use Pixelant\PxaProductManager\Domain\Model\Attribute;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AttributeConfigurationProviderFactory
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA
 */
class AttributeConfigurationProviderFactory
{
    /**
     * Factory method
     *
     * @param Attribute $attribute
     * @return ConcreteProviderInterface
     */
    public static function createConcrete(Attribute $attribute): ConcreteProviderInterface
    {
        switch (true) {
            case $attribute->isInputType():
                return GeneralUtility::makeInstance(InputProviderConcrete::class, $attribute);
            case $attribute->isSelectBoxType():
                return GeneralUtility::makeInstance(SelectBoxProviderConcrete::class, $attribute);
            case $attribute->isCheckboxType():
                return GeneralUtility::makeInstance(CheckboxProviderConcrete::class, $attribute);
            case $attribute->isLinkType():
                return GeneralUtility::makeInstance(LinkProviderConcrete::class, $attribute);
            case $attribute->isFalType():
                return GeneralUtility::makeInstance(FalProviderConcrete::class, $attribute);
        }

        throw new \UnexpectedValueException("Attribute with type '{$attribute->getType()}' not supported.", 1568986135545);
    }

    /**
     * Get default provider
     *
     * @return DefaultConfigurationProvider
     */
    public static function createDefault(): DefaultConfigurationProvider
    {
        return GeneralUtility::makeInstance(DefaultConfigurationProvider::class);
    }
}
