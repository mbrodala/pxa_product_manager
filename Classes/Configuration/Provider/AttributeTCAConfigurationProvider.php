<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

use Pixelant\PxaProductManager\Domain\Model\Attribute;

/**
 * Interface AttributeTCAConfigurationProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
interface AttributeTCAConfigurationProvider
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
    public function getFieldConfiguration(): array;

    /**
     * Return field name
     *
     * @return string
     */
    public function getFieldName(): string;
}
