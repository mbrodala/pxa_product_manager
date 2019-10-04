<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook;

use Pixelant\PxaProductManager\Utility\FlexformUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormDataStructureHook
 * @package Pixelant\PxaProductManager\Hook
 */
class FlexFormDataStructureHook implements SingletonInterface
{
    /**
     * Flexform identifier
     *
     * @var string
     */
    protected $identifier = 'pxaproductmanager_pi1,list';

    /**
     * Default flexform loaded for all actions
     * @var string
     */
    protected $defaultFlexform = 'EXT:pxa_product_manager/Configuration/FlexForms/Parts/flexform_common.xml';

    /**
     * Flexform actions
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Last selected action
     *
     * @var string
     */
    protected $lastSelectedAction = '';

    /**
     * Initialize
     */
    public function __construct()
    {
        $this->actions = $this->getFlexformUtility()->getAllRegisteredActions();
    }

    /**
     * Save last selected action
     *
     * @param array $fieldTCA
     * @param string $table
     * @param string $field
     * @param array $row
     * @param array $identifier
     * @return array
     */
    public function getDataStructureIdentifierPostProcess(
        array $fieldTCA,
        string $table,
        string $field,
        array $row,
        array $identifier
    ): array {
        if ($identifier['dataStructureKey'] === $this->identifier
            && is_string($row['pi_flexform'])
            && !empty($row['pi_flexform'])
        ) {
            $flexformSettings = GeneralUtility::makeInstance(FlexFormService::class)
                ->convertFlexFormContentToArray($row['pi_flexform']);

            $this->lastSelectedAction = $flexformSettings['switchableControllerActions'];
        }

        return $identifier;
    }

    /**
     * Modify product manager flexform structure
     *
     * @param array $dataStructure
     * @param array $identifier
     * @return array
     */
    public function parseDataStructureByIdentifierPostProcess(array $dataStructure, array $identifier)
    {
        if ($identifier['dataStructureKey'] === $this->identifier) {
            // Add action
            $dataStructure = $this->addSwitchableControllerActions($dataStructure);

            if (!empty($this->lastSelectedAction)) {
                // Add default conf
                $dataStructure = $this->updateDataStructureWithFlexform($dataStructure, $this->defaultFlexform);

                $dataStructure = $this->modifyDataStructureAccordingToSelectAction($dataStructure);
            }
        }

        return $dataStructure;
    }

    /**
     * Update data structure according to action settings
     *
     * @param array $dataStructure
     * @return array
     */
    protected function modifyDataStructureAccordingToSelectAction(array $dataStructure): array
    {
        foreach ($this->actions as $action) {
            if ($action['action'] === $this->lastSelectedAction) {
                $lastActionConfig = $action;
                break;
            }
        }

        if (isset($lastActionConfig)) {
            // Load sub-form
            foreach ($lastActionConfig['flexforms'] as $flexform) {
                $dataStructure = $this->updateDataStructureWithFlexform($dataStructure, $flexform);
            }

            // Remove exclude fields
            foreach ($lastActionConfig['excludeFields'] as $excludeField) {
                foreach ($dataStructure['sheets'] as $sheet => $sheetConf) {
                    foreach ($sheetConf['ROOT']['el'] as $field => $fieldConf) {
                        if ($field === $excludeField) {
                            unset($dataStructure['sheets'][$sheet]['ROOT']['el'][$field]);
                        }
                    }
                }
            }
        }

        return $dataStructure;
    }

    /**
     * Update data structure
     *
     * @param array $dataStructure
     * @param string $flexformPath
     * @return array
     */
    protected function updateDataStructureWithFlexform(array $dataStructure, string $flexformPath): array
    {
        $fullPath = GeneralUtility::getFileAbsFileName($flexformPath);
        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Colud not find flexform with path '$fullPath'(given path '$flexformPath')", 1570185225935);
        }

        $xml = file_get_contents($fullPath);
        ArrayUtility::mergeRecursiveWithOverrule($dataStructure, GeneralUtility::xml2array($xml));

        return $dataStructure;
    }

    /**
     * Return data structure with actions
     *
     * @param array $dataStructure
     * @return array
     */
    protected function addSwitchableControllerActions(array $dataStructure): array
    {
        $items = &$dataStructure['sheets']['sDEF']['ROOT']['el']['switchableControllerActions']['TCEforms']['config']['items'];

        foreach ($this->actions as $action) {
            $items[] = [
                $action['label'], $action['action']
            ];
        }

        return $dataStructure;
    }

    /**
     * @return FlexformUtility
     */
    protected function getFlexformUtility(): FlexformUtility
    {
        return GeneralUtility::makeInstance(FlexformUtility::class);
    }
}
