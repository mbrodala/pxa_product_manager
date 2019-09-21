<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA;

/**
 * Interface AttributeTCAConfigurationProvider
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA
 */
interface AttributeTCAConfigurationProviderInterface
{
    /**
     * Check if given TCA field is attribute
     *
     * @param string $fieldName
     * @return bool
     */
    public function isFieldAttributeTCAField(string $fieldName): bool;

    /**
     * Check if given TCA field is FAL attribute
     *
     * @param string $fieldName
     * @return bool
     */
    public function isFieldFalAttributeTCAField(string $fieldName): bool;

    /**
     * Determinate attribute UID from TCA field name
     *
     * @param string $fieldName
     * @return int
     */
    public function determinateAttributeUid(string $fieldName): int;

    /**
     * Determinate FAL attribute UID from TCA field name
     *
     * @param string $fieldName
     * @return int
     */
    public function determinateFalAttributeUid(string $fieldName): int;
}
