<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

/**
 * Class LinkProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
class LinkProvider extends AbstractProvider
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
            $configuration['config']['eval'] = 'required';
        }

        return $configuration;
    }
}
