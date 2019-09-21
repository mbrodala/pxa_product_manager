<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Configuration\AttributesTCA;

use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Class DefaultConfigurationProvider
 * @package Pixelant\PxaProductManager\Configuration\AttributesTCA
 */
class DefaultConfigurationProvider implements AttributeTCAConfigurationProviderInterface
{

    /**
     * Prefix for TCA fields
     */
    const ATTRIBUTE_FIELD_PREFIX = 'tx_pxaproductmanager_attribute_';

    /**
     * Prefix for FAL fields
     */
    const ATTRIBUTE_FAL_FIELD_PREFIX = 'tx_pxaproductmanager_file_attribute_';

    /**
     * Database field name of attribute files.
     * This field name is also used in sys_file_reference
     * where relation to products is kept
     */
    const ATTRIBUTE_FAL_DB_FIELD_NAME = 'attributes_files';

    /**
     * Database field name of attribute values on JSON format
     */
    const ATTRIBUTES_VALUES_DB_FIELD_NAME = 'attributes_values';

    /**
     * Check if given TCA field is attribute
     *
     * @param string $fieldName
     * @return bool
     */
    public function isFieldAttributeTCAField(string $fieldName): bool
    {
        return StringUtility::beginsWith($fieldName, self::ATTRIBUTE_FIELD_PREFIX);
    }

    /**
     * Determinate attribute UID from TCA field name
     *
     * @param string $fieldName
     * @return int
     */
    public function determinateAttributeUid(string $fieldName): int
    {
        return (int)substr($fieldName, 31);
    }

    /**
     * Check if given TCA field is FAL attribute
     *
     * @param string $fieldName
     * @return bool
     */
    public function isFieldFalAttributeTCAField(string $fieldName): bool
    {
        return StringUtility::beginsWith($fieldName, self::ATTRIBUTE_FAL_FIELD_PREFIX);
    }

    /**
     * Determinate FAL attribute UID from TCA field name
     *
     * @param string $fieldName
     * @return int
     */
    public function determinateFalAttributeUid(string $fieldName): int
    {
        return (int)substr($fieldName, 36);
    }
}
