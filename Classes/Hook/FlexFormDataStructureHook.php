<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormDataStructureHook
 * @package Pixelant\PxaProductManager\Hook
 */
class FlexFormDataStructureHook
{
    /**
     * Flexform identifier
     *
     * @var string
     */
    protected $identifier = 'pxaproductmanager_pi1,list';

    protected $defaultFlexform = 'EXT:pxa_product_manager/Configuration/FlexForms/flexform_commom.xml';

    public function parseDataStructureByIdentifierPostProcess(array $dataStructure, array $identifier)
    {
        if ($identifier['dataStructureKey'] === $this->identifier) {
            $xml = file_get_contents(GeneralUtility::getFileAbsFileName($this->defaultFlexform));

            ArrayUtility::mergeRecursiveWithOverrule($dataStructure, GeneralUtility::xml2array($xml));
        }

        return $dataStructure;
    }
}
