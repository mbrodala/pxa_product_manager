<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\Provider;

use Pixelant\PxaProductManager\Domain\Model\Option;

/**
 * Class SelectBoxProvider
 * @package Pixelant\PxaProductManager\Configuration\Provider
 */
class SelectBoxProvider extends AbstractProvider
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
            $configuration['config']['minitems'] = 1;
        }

        $options = [];
        /** @var Option $option */
        foreach ($this->attribute->getOptions() as $option) {
            $options[] = [$option->getValue(), $option->getUid()];
        }

        if (empty($options)) {
            $configuration['label'] .= ' (This attribute has no options. Please configure the attribute and add some options to it.)';
        }

        $configuration['config']['items'] = $options;

        return $configuration;
    }
}
