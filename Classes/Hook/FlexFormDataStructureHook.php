<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Hook;

use Pixelant\PxaProductManager\Utility\FlexformUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\SingletonInterface;

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
     * Last selected action
     *
     * @var string
     */
    protected $lastSelectedAction = '';

    /**
     * @var FlexformUtility
     */
    protected $flexformUtility = null;

    /**
     * @var FlexFormService
     */
    protected $flexformService = null;

    /**
     * Initialize
     *
     * @param FlexformUtility $flexformUtility
     * @param FlexFormService $flexFormService
     */
    public function __construct(FlexformUtility $flexformUtility, FlexFormService $flexFormService)
    {
        $this->flexformUtility = $flexformUtility;
        $this->flexformService = $flexFormService;
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
            $flexformSettings = $this->flexformService->convertFlexFormContentToArray($row['pi_flexform']);

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
                $dataStructure = $this->flexformUtility->loadDefaultDataStructure($dataStructure);

                // Load action structure
                $dataStructure = $this->flexformUtility->loadActionDataStructure(
                    $dataStructure,
                    $this->lastSelectedAction
                );
            }
        }

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

        foreach ($this->flexformUtility->getAllRegisteredActions() as $action) {
            $items[] = [
                $action['label'], $action['action']
            ];
        }

        return $dataStructure;
    }
}
