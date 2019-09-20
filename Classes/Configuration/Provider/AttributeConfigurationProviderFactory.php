<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

use Pixelant\PxaProductManager\Domain\Model\Attribute;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AttributeConfigurationProviderFactory
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
class AttributeConfigurationProviderFactory
{
    /**
     * Factory method
     *
     * @param Attribute $attribute
     * @return AttributeTCAConfigurationProvider
     */
    public static function create(Attribute $attribute): AttributeTCAConfigurationProvider
    {
        switch (true) {
            case $attribute->isInputType():
                return GeneralUtility::makeInstance(InputProvider::class, $attribute);
            case $attribute->isSelectBoxType():
                return GeneralUtility::makeInstance(SelectBoxProvider::class, $attribute);
            case $attribute->isCheckboxType():
                return GeneralUtility::makeInstance(CheckboxProvider::class, $attribute);
            case $attribute->isLinkType():
                return GeneralUtility::makeInstance(LinkProvider::class, $attribute);
            case $attribute->isFalType():
                return GeneralUtility::makeInstance(FalProvider::class, $attribute);
        }

        throw new \UnexpectedValueException("Attribute with type '{$attribute->getType()}' not supported.", 1568986135545);
    }
}
