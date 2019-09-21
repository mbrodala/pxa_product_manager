<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete;

use Pixelant\PxaProductManager\Domain\Model\Option;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SelectBoxProvider
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA\Concrete
 */
class SelectBoxProviderConcrete extends ConcreteAbstractProvider
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

    /**
     * Convert product attribute raw value to TCA field value
     *
     * @param array $rawAttributesValues
     * @return mixed
     */
    public function convertRawValueToTCAValue(array $rawAttributesValues)
    {
        $value = parent::convertRawValueToTCAValue($rawAttributesValues);

        return GeneralUtility::trimExplode(',', $value, true);
    }
}
