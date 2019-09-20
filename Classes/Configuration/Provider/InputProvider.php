<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

/**
 * Class InputProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
class InputProvider extends AbstractProvider
{
    /**
     * Return TCA configuration
     *
     * @return array
     */
    public function getFieldConfiguration(): array
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
