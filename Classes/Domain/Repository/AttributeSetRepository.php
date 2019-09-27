<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Domain\Repository;

use Pixelant\PxaProductManager\Domain\Model\AttributeSet;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class AttributeSetRepository
 * @package Pixelant\PxaProductManager\Domain\Repository
 */
class AttributeSetRepository extends Repository
{
    /**
     * Find attribute sets by categories uids
     *
     * @param array $categoriesUids
     * @return array
     */
    public function findByCategoriesUids(array $categoriesUids): array
    {
        if (empty($categoriesUids)) {
            return [];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pxaproductmanager_domain_model_attributeset');

        $attributesSets = $queryBuilder
            ->select('tx_pxaproductmanager_domain_model_attributeset.*')
            ->from('tx_pxaproductmanager_domain_model_attributeset')
            ->join(
                'tx_pxaproductmanager_domain_model_attributeset',
                'tx_pxaproductmanager_category_attributeset_mm',
                'mm',
                $queryBuilder->expr()->eq(
                    'tx_pxaproductmanager_domain_model_attributeset.uid',
                    $queryBuilder->quoteIdentifier('mm.uid_foreign')
                )
            )
            ->join(
                'mm',
                'sys_category',
                'categories',
                $queryBuilder->expr()->eq(
                    'mm.uid_local',
                    $queryBuilder->quoteIdentifier('categories.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->in(
                    'categories.uid',
                    $queryBuilder->createNamedParameter($categoriesUids, Connection::PARAM_INT_ARRAY)
                )
            )
            ->execute()
            ->fetchAll();

        if (!empty($attributesSets)) {
            $dataMapper = $this->objectManager->get(DataMapper::class);

            return $dataMapper->map(AttributeSet::class, $attributesSets);
        }

        return [];
    }
}
