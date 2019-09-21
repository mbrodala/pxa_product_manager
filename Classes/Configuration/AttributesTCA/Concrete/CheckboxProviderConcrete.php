<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete;

/**
 * Class CheckboxProvider
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete
 */
class CheckboxProviderConcrete extends ConcreteAbstractProvider
{
    /**
     * Return TCA configuration
     *
     * @return array
     */
    public function getTCAFieldConfiguration(): array
    {
        return $this->getInitialConfiguration();
    }
}
