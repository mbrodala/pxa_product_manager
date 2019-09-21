<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete;

use Pixelant\PxaProductManager\Configuration\AttributesTCA\AttributeTCAConfigurationProviderInterface;
use Pixelant\PxaProductManager\Domain\Model\Attribute;

/**
 * Interface ConcreteProviderInterface
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete\Concrete
 */
interface ConcreteProviderInterface extends AttributeTCAConfigurationProviderInterface
{
    /**
     * @param Attribute $attribute
     */
    public function __construct(Attribute $attribute);

    /**
     * Return TCA configuration
     *
     * @return array
     */
    public function getTCAFieldConfiguration(): array;

    /**
     * Return field name
     *
     * @return string
     */
    public function getTCAFieldName(): string;

    /**
     * Convert product attribute raw value to TCA field value
     *
     * @param array $rawAttributesValues
     * @return mixed
     */
    public function convertRawValueToTCAValue(array $rawAttributesValues);
}
