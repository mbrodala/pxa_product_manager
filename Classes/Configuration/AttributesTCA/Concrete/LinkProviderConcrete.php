<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete;

/**
 * Class LinkProvider
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete
 */
class LinkProviderConcrete extends ConcreteAbstractProvider
{
    /**
     * Return TCA configuration
     *
     * @return array
     */
    public function getTCAFieldConfiguration(): array
    {
        $configuration = $this->getInitialConfiguration();

        if ($this->isRequired()) {
            $configuration['config']['eval'] = 'required';
        }

        return $configuration;
    }
}
