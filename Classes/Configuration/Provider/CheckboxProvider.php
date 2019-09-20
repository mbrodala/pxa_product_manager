<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

/**
 * Class CheckboxProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
class CheckboxProvider extends AbstractProvider
{
    /**
     * Return TCA configuration
     *
     * @return array
     */
    public function getFieldConfiguration(): array
    {
        return $this->getInitialConfiguration();
    }
}
