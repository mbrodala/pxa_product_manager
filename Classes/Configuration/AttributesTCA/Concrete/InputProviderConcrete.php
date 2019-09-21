<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete;

/**
 * Class InputProvider
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete
 */
class InputProviderConcrete extends ConcreteAbstractProvider
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
            $configuration['config']['eval'] = $configuration['config']['eval']
                ? $configuration['config']['eval'] . ',required'
                : 'required';
        }

        return $configuration;
    }
}
