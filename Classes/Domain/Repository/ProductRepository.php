<?php

namespace Pixelant\PxaProductManager\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Pixelant\PxaProductManager\Domain\Model\Category;
use Pixelant\PxaProductManager\Domain\Model\DTO\Demand;
use Pixelant\PxaProductManager\Domain\Model\DTO\DemandInterface;
use Pixelant\PxaProductManager\Domain\Model\Filter;
use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Utility\CategoryUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 *
 *
 * @package pxa_product_manager
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ProductRepository extends AbstractDemandRepository
{
    /**
     * @var AttributeValueRepository
     */
    protected $attributeValueRepository;

    /**
     * @var \Pixelant\PxaProductManager\Domain\Repository\FilterRepository
     */
    protected $filterRepository = null;

    /**
     * @param AttributeValueRepository $attributeValueRepository
     */
    public function injectAttributeValueRepository(AttributeValueRepository $attributeValueRepository)
    {
        $this->attributeValueRepository = $attributeValueRepository;
    }

    /**
     * @param FilterRepository $filterRepository
     */
    public function injectFilterRepository(FilterRepository $filterRepository)
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * Override basic method. Set special ordering for categories if it's not multiple
     *
     * @param DemandInterface|Demand $demand
     * @return QueryResultInterface
     */
    public function findDemandedByQueryBuilder(DemandInterface $demand): QueryResultInterface
    {
        $queryBuilder = $this->getFindDemandedQueryBuilder($demand);
        $statement = $this->getSQL($queryBuilder);
        $query = $this->createQuery();
        return $query->statement($statement)->execute();
        // return $queryBuilder->execute()->fetchAll();
    }


    /**
     * Count results for demand
     *
     * @param DemandInterface $demand
     * @return int
     */
    public function countByDemand(DemandInterface $demand): int
    {
        $queryBuilder = $this->getFindDemandedQueryBuilder($demand);
        $statement = $this->getSQL($queryBuilder);
        $query = $this->createQuery();
        return $query->statement($statement)->count();
    }

    /**
     * Override basic method. Set special ordering for categories if it's not multiple
     *
     * @param DemandInterface|Demand $demand
     * @return array
     */
    public function getAvailableFilteringAttributesByDemand(DemandInterface $demand): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category_record_mm');

        // standard query part, pid and allowed categories
        $queryBuilder->select('aval.value as value')
            ->from('sys_category_record_mm', 'allowedProductCategories')
            ->join(
                'allowedProductCategories',
                'tx_pxaproductmanager_domain_model_product',
                'product',
                $queryBuilder->expr()->eq(
                    'product.uid',
                    $queryBuilder->quoteIdentifier('allowedProductCategories.uid_foreign')
                )
            )
            ->join(
                'product',
                'tx_pxaproductmanager_domain_model_attributevalue',
                'aval',
                $queryBuilder->expr()->eq(
                    'aval.product',
                    $queryBuilder->quoteIdentifier('product.uid')
                )
            )
            ->join(
                'aval',
                'tx_pxaproductmanager_domain_model_attribute',
                'attr',
                $queryBuilder->expr()->eq(
                    'attr.uid',
                    $queryBuilder->quoteIdentifier('aval.attribute')
                )
            )
            ->where(
                $queryBuilder->expr()->in(
                    'allowedProductCategories.uid_local',
                    $queryBuilder->createNamedParameter(
                        $demand->getCategories(),
                        \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->in(
                    'product.pid',
                    $queryBuilder->createNamedParameter(
                        $demand->getStoragePid(),
                        \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->in(
                    'attr.type',
                    $queryBuilder->createNamedParameter(
                        [4,5],
                        \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->neq(
                    'aval.value',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->gt(
                    'aval.value',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->groupBy('aval.value');

        $this->addQueryBuilderIncludeDiscontinued($demand, $queryBuilder);

        $this->addQueryBuilderFilters($demand, $queryBuilder);

        if ($demand->getLimit()) {
            $queryBuilder->setMaxResults($demand->getLimit());
        }
        if ($demand->getOffSet()) {
            $queryBuilder->setFirstResult($demand->getOffSet());
        }

        // $statement = $this->getSQL($queryBuilder);
        $attributes = $queryBuilder->execute()->fetchAll();
        $attributes = array_column($attributes, 'value');
        $attributes = array_map('intval', $attributes);

        return $attributes ?? [];
    }

    /**
     * Override basic method. Set special ordering for categories if it's not multiple
     *
     * @param DemandInterface|Demand $demand
     * @return array
     */
    public function getAvailableFilteringCategoriesByDemand(DemandInterface $demand, Filter $filter): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category_record_mm');
        $queryBuilder->getRestrictions()->removeAll();
        // standard query part, pid and allowed categories
        $queryBuilder->select(
            'level1.uid as l1',
            'level2.uid as l2',
            'level3.uid as l3',
            'level4.uid as l4',
            'level5.uid as l5',
            'level6.uid as l6'
        )
            ->from('sys_category_record_mm', 'allowedProductCategories')
            ->join(
                'allowedProductCategories',
                'tx_pxaproductmanager_domain_model_product',
                'product',
                $queryBuilder->expr()->eq(
                    'product.uid',
                    $queryBuilder->quoteIdentifier('allowedProductCategories.uid_foreign')
                )
            )
            ->join(
                'product',
                'tx_pxaproductmanager_domain_model_attributevalue',
                'aval',
                $queryBuilder->expr()->eq(
                    'aval.product',
                    $queryBuilder->quoteIdentifier('product.uid')
                )
            )
            ->join(
                'aval',
                'tx_pxaproductmanager_domain_model_attribute',
                'attr',
                $queryBuilder->expr()->eq(
                    'attr.uid',
                    $queryBuilder->quoteIdentifier('aval.attribute')
                )
            )
            ->join(
                'allowedProductCategories',
                'sys_category',
                'level1',
                $queryBuilder->expr()->eq(
                    'level1.uid',
                    $queryBuilder->quoteIdentifier('allowedProductCategories.uid_local')
                )
            )
            ->leftJoin(
                'level1',
                'sys_category',
                'level2',
                $queryBuilder->expr()->eq(
                    'level2.uid',
                    $queryBuilder->quoteIdentifier('level1.parent')
                )
            )
            ->leftJoin(
                'level2',
                'sys_category',
                'level3',
                $queryBuilder->expr()->eq(
                    'level3.uid',
                    $queryBuilder->quoteIdentifier('level2.parent')
                )
            )
            ->leftJoin(
                'level3',
                'sys_category',
                'level4',
                $queryBuilder->expr()->eq(
                    'level4.uid',
                    $queryBuilder->quoteIdentifier('level3.parent')
                )
            )
            ->leftJoin(
                'level4',
                'sys_category',
                'level5',
                $queryBuilder->expr()->eq(
                    'level5.uid',
                    $queryBuilder->quoteIdentifier('level4.parent')
                )
            )
            ->leftJoin(
                'level5',
                'sys_category',
                'level6',
                $queryBuilder->expr()->eq(
                    'level6.uid',
                    $queryBuilder->quoteIdentifier('level5.parent')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'product.deleted',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->in(
                    'allowedProductCategories.uid_local',
                    $queryBuilder->createNamedParameter(
                        $demand->getCategories(),
                        \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                    )
                ),
                $queryBuilder->expr()->in(
                    'product.pid',
                    $queryBuilder->createNamedParameter(
                        $demand->getStoragePid(),
                        \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                    )
                )
            )
            ->groupBy(
                'level1.uid',
                'level2.uid',
                'level3.uid',
                'level4.uid',
                'level5.uid',
                'level6.uid'
            );

        $this->addQueryBuilderIncludeDiscontinued($demand, $queryBuilder);

        $this->addQueryBuilderFilters($demand, $queryBuilder);

        if ($demand->getLimit()) {
            $queryBuilder->setMaxResults($demand->getLimit());
        }
        if ($demand->getOffSet()) {
            $queryBuilder->setFirstResult($demand->getOffSet());
        }

        // $statement = $this->getSQL($queryBuilder);
        $categories = $queryBuilder->execute()->fetchAll();
        $childCategories = [];

        if (!empty($categories) && count($categories) > 0) {
            // Find "parent category column" (should be same in all records)
            $parentCategory = $filter->getParentCategory()->getUid();
            $parentIndex = 0;
            // Need to check all rows if not in same level
            // And find "parent category column" and add category
            foreach ($categories as $index => $columns) {
                foreach ($columns as $key => $value) {
                    if ($value == $parentCategory) {
                        $parentIndex = (int)str_replace('l', '', $key) - 1;
                        $targetColumn = 'l' . $parentIndex;
                        array_push($childCategories, $categories[$index][$targetColumn]);
                    }
                }
            }
            $categoryList = array_values(array_unique($childCategories));
        }

        return $categoryList ?? [];
    }

    /**
     * Override basic method. Set special ordering for categories if it's not multiple
     *
     * @param DemandInterface|Demand $demand
     * @return QueryResultInterface
     */
    public function findDemanded(DemandInterface $demand): QueryResultInterface
    {
        if ($demand->getOrderBy() !== 'categories' || count($demand->getCategories()) > 1) {
            // return parent::findDemanded($demand);
            $queryBuilder = $this->getFindDemandedQueryBuilder($demand);
            $statement = $this->getSQL($queryBuilder);
            $query = $this->createQuery();
            return $query->statement($statement)->execute();
        } else {
            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_category_record_mm');
            $queryBuilder->getRestrictions()->removeAll();

            $statement = $queryBuilder
                ->select('uid_foreign')
                ->from('sys_category_record_mm')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid_local',
                        $queryBuilder->createNamedParameter(
                            $demand->getCategories()[0],
                            Connection::PARAM_INT
                        )
                    ),
                    $queryBuilder->expr()->eq(
                        'tablenames',
                        $queryBuilder->createNamedParameter(
                            'tx_pxaproductmanager_domain_model_product',
                            Connection::PARAM_STR
                        )
                    ),
                    $queryBuilder->expr()->eq(
                        'fieldname',
                        $queryBuilder->createNamedParameter(
                            'categories',
                            Connection::PARAM_STR
                        )
                    )
                )
                ->orderBy('sorting')
                ->execute();

            $uidsOrder = '';
            while ($uid = $statement->fetchColumn(0)) {
                $uidsOrder .= ',' . $uid;
            }
            unset($statement);

            if (empty($uidsOrder)) {
                return parent::findDemanded($demand);
            } else {
                // If sorting is set to categories and we have one category
                $query = $this->createDemandQuery($demand);
                /** @var Typo3DbQueryParser $queryParser */
                $queryParser = $this->objectManager->get(Typo3DbQueryParser::class);

                $productsQueryBuilder = $queryParser->convertQueryToDoctrineQueryBuilder($query);

                // add orderings
                $productsQueryBuilder->add(
                    'orderBy',
                    'FIELD(`tx_pxaproductmanager_domain_model_product`.`uid`' . $uidsOrder . ') '
                    . $demand->getOrderDirection()
                );

                $queryParameters = [];

                foreach ($productsQueryBuilder->getParameters() as $key => $value) {
                    // prefix array keys with ':'
                    //all non numeric values have to be quoted
                    $queryParameters[':' . $key] = (is_numeric($value)) ? $value : "'" . $value . "'";
                }

                $statement = strtr($productsQueryBuilder->getSQL(), $queryParameters);

                return $query->statement($statement)->execute();
            }
        }
    }

    /**
     * If order is by category need to override basic order function
     *
     * @param QueryInterface $query
     * @param DemandInterface|Demand $demand
     */
    public function setOrderings(QueryInterface $query, DemandInterface $demand)
    {
        // If sorting is set by categories, we need to create a special query
        if ($demand->getOrderBy() !== 'categories') {
            parent::setOrderings($query, $demand);

            $orderings = $query->getOrderings();
            // Include name as second sorting if not already chosen
            if (!array_key_exists('name', $orderings)) {
                $orderings['name'] = QueryInterface::ORDER_ASCENDING;

                $query->setOrderings($orderings);
            }
        } else {
            $demand->setOrderBy('categories.sorting');
            parent::setOrderings($query, $demand);
        }
    }

    /**
     * Find all product with storage or all
     *
     * @param bool $respectStorage
     * @return QueryResultInterface
     */
    public function findAll($respectStorage = true)
    {
        $query = $this->createQuery();

        if (!$respectStorage) {
            $query->getQuerySettings()->setRespectStoragePage(false);
        }

        return $query->execute();
    }

    /**
     * Find products by categories
     *
     * @param array $categories
     * @param array $orderings
     * @param string $conjunction
     * @param int $limit
     * @return array|QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findProductsByCategories(
        array $categories,
        array $orderings = ['sorting' => QueryInterface::ORDER_ASCENDING],
        string $conjunction = 'and',
        int $limit = 0
    ) {
        if (empty($categories)) {
            return [];
        }

        // Find products our own way, because CategoryCollection::load doesn't have options to set the ordering
        $query = $this->createQuery();

        $constraints = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $constraints[] = $query->contains('categories', $category);
        }

        $query->matching(
            $this->createConstraintFromConstraintsArray(
                $query,
                $constraints,
                $conjunction
            )
        );

        $query->setOrderings($orderings);

        // Set limit
        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $query->execute();
    }

    /**
     * Same as findProductsByCategories, but doesn't respect disable field and storage
     *
     * @param array $categories
     * @param string $conjunction
     * @return array|QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllProductsByCategories(
        array $categories,
        string $conjunction = 'or'
    ) {
        if (empty($categories)) {
            return [];
        }

        // Find products our own way, because CategoryCollection::load doesn't have options to set the ordering
        $query = $this->createQuery();
        $query
            ->getQuerySettings()
            ->setRespectStoragePage(false)
            ->setIgnoreEnableFields(true)
            ->setEnableFieldsToBeIgnored(['disabled']);

        $constraints = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $constraints[] = $query->contains('categories', $category);
        }

        $query->matching(
            $this->createConstraintFromConstraintsArray(
                $query,
                $constraints,
                $conjunction
            )
        );

        $query->setOrderings(['sorting' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute();
    }

    /**
     * Count products for category
     *
     * @param Category $category
     * @return int
     */
    public function countByCategory(Category $category): int
    {
        $query = $this->createQuery();

        $query->matching(
            $query->contains('categories', $category)
        );

        return $query->count();
    }

    /**
     * findProductsByUIds
     *
     * @param array $uids
     * @return QueryResultInterface|array
     */
    public function findProductsByUids(array $uids = [])
    {
        if (empty($uids)) {
            return [];
        }

        $query = $this->createQuery();

        // Disable language and storage check, because we are using uids
        $query
            ->getQuerySettings()
            ->setRespectSysLanguage(false)
            ->setRespectStoragePage(false);

        $query->matching(
            $query->in('uid', $uids)
        );

        return $query->execute();
    }

    /**
     * Add possibility do disable enable fields when find by uid
     *
     * @param int $uid
     * @param bool $respectEnableFields
     * @return null|Product
     */
    public function findByUid($uid, bool $respectEnableFields = true)
    {
        $query = $this->createQuery();

        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setRespectStoragePage(false);

        if (false === $respectEnableFields) {
            $query->getQuerySettings()->setIgnoreEnableFields(true);
        }

        $query->matching(
            $query->equals('uid', (int)$uid)
        );

        return $query->execute()->getFirst();
    }

    /**
     * Create constraints for all demand options
     *
     * @param QueryInterface $query
     * @param DemandInterface|Demand $demand
     * @return array
     */
    protected function createConstraints(QueryInterface $query, DemandInterface $demand): array
    {
        $constraints = [];

        if (!$demand->getIncludeDiscontinued()) {
            $constraints['discontinued'] = $this->createDiscontinuedConstraints($query);
        }

        if (!empty($demand->getCategories())) {
            $constraints['categories'] = $this->createCategoryConstraints(
                $query,
                $demand->getCategories(),
                $demand->getCategoryConjunction()
            );
        }

        if (!empty($demand->getFilters())) {
            $filterConstraints = $this->createFilteringConstraints(
                $query,
                $demand->getFilters(),
                $demand->getFiltersConjunction()
            );
            if ($filterConstraints !== false) {
                $constraints['filters'] = $filterConstraints;
            }
        }

        return $constraints;
    }

    /**
     * Filters
     * Filters are generated on FE in ProductManager.Filtering.js
     * Array
     * (
     *  [2-13] => Array // type + uid of filter
     * (
     *  [attributeUid] => 13 // UID of attribute or parent category
     *  [value] => Array // array of values
     * (
     *      [0] => 3
     *  )
     * )
     * @param QueryInterface $query
     * @param array $filtersData
     * @param string $conjunction
     * @return mixed
     */
    protected function createFilteringConstraints(QueryInterface $query, array $filtersData, string $conjunction = 'or')
    {
        $constraints = [];
        $ranges = [];

        foreach ($filtersData as $filterData) {
            if (!empty($filterData['value']) && !empty($filterData['uid'])) {
                /** @var Filter $filter */
                $filter = $this->filterRepository->findByUid((int)$filterData['uid']);
                if ($filter === null) {
                    continue;
                }

                $filterConjunction = $filter->getConjunctionAsString();
                switch ($filter->getType()) {
                    case Filter::TYPE_ATTRIBUTES:
                        $filterConstraints = [];
                        $attributeValues = $this->attributeValueRepository->findAttributeValuesByAttributeAndValues(
                            (int)$filterData['attributeUid'],
                            $filterData['value'],
                            $filterConjunction,
                            true
                        );
                        if (empty($attributeValues)) {
                            // force no result for filter constraint if no value was found but filter was set on FE
                            $filterConstraints[] = $query->contains('attributeValues', 0);
                        } else {
                            foreach ($attributeValues as $attributeValue) {
                                $filterConstraints[] = $query->contains('attributeValues', $attributeValue['uid']);
                            }
                        }

                        if (!empty($filterConstraints)) {
                            $constraints[] = $this->createConstraintFromConstraintsArray(
                                $query,
                                $filterConstraints,
                                'or'
                            );
                        }
                        break;
                    case Filter::TYPE_CATEGORIES:
                        $categoriesConstraints = [];
                        foreach ($filterData['value'] as $value) {
                            $categoriesConstraints[] = $query->contains('categories', $value);
                        }

                        $constraints[] = $this->createConstraintFromConstraintsArray(
                            $query,
                            $categoriesConstraints,
                            $filterConjunction
                        );
                        break;
                    case Filter::TYPE_ATTRIBUTES_MINMAX:
                        // need to just prebuild array since minmax attribute filter can consist of two inputs
                        list($value, $rangeType) = $filterData['value'];
                        $rangeKey = (int)$filterData['attributeUid'];

                        $ranges[$rangeKey][$rangeType] = $value;

                        break;
                    default:
                        // only two are supported for now
                }
            }
        }

        // go through ranges after all filters have been processed
        // since they can have value from two filter inputs
        if (!empty($ranges)) {
            foreach ($ranges as $attributeId => $range) {
                $rangeConstraints = [];

                $attributeValues = $this->attributeValueRepository->findAttributeValuesByAttributeAndMinMaxOptionValues(
                    (int)$attributeId,
                    isset($range['min']) ? (int)$range['min'] : null,
                    isset($range['max']) ? (int)$range['max'] : null
                );

                if (empty($attributeValues)) {
                    // force no result for filter constraint if no value was found but filter was set on FE
                    $rangeConstraints[] = $query->contains('attributeValues', 0);
                } else {
                    foreach ($attributeValues as $attributeValue) {
                        $rangeConstraints[] = $query->contains('attributeValues', $attributeValue['uid']);
                    }
                }

                if (!empty($rangeConstraints)) {
                    $constraints[] = $this->createConstraintFromConstraintsArray(
                        $query,
                        $rangeConstraints,
                        'or'
                    );
                }
            }
        }

        if (!empty($constraints)) {
            return $this->createConstraintFromConstraintsArray(
                $query,
                $constraints,
                strtolower($conjunction)
            );
        }

        return false;
    }

    /**
     * Create categories constraints
     *
     * @param QueryInterface $query
     * @param array $categories
     * @param string $conjunction
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface
     */
    protected function createCategoryConstraints(QueryInterface $query, array $categories, string $conjunction = 'or')
    {
        $constraints = [];

        foreach ($categories as $category) {
            $constraints[] = $query->contains('categories', $category);
        }

        return $this->createConstraintFromConstraintsArray(
            $query,
            $constraints,
            strtolower($conjunction)
        );
    }

    /**
     * Create discontinued constraints
     *
     * @param QueryInterface $query
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface
     */
    protected function createDiscontinuedConstraints(QueryInterface $query)
    {
        $constraints = [];

        // include if discontinued isn't set
        $constraints['ns'] = $query->equals('discontinued', 0);
        // or discontinued is greater than today
        $constraints['gt'] = $query->greaterThan('discontinued', new \DateTime('00:00'));

        return $this->createConstraintFromConstraintsArray(
            $query,
            $constraints,
            'or'
        );
    }
    /**
     * Override basic method. Set special ordering for categories if it's not multiple
     *
     * @param DemandInterface|Demand $demand
     * @return QueryBuilder
     */
    protected function getFindDemandedQueryBuilder(DemandInterface $demand): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pxaproductmanager_domain_model_product');
        $subQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_category_record_mm');

        // standard query part, pid and allowed categories
        $queryBuilder->select('product.*')
            ->from('tx_pxaproductmanager_domain_model_product', 'product')
            ->where(
                $queryBuilder->expr()->eq(
                    'product.pid',
                    $queryBuilder->createNamedParameter($demand->getStoragePid(), \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->in(
                    'product.uid',
                    $subQueryBuilder
                        ->select('uid_foreign')
                        ->from('sys_category_record_mm')
                        ->where(
                            $queryBuilder->expr()->in(
                                'sys_category_record_mm.uid_local',
                                $queryBuilder->createNamedParameter(
                                    $demand->getCategories(),
                                    \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                                )
                            )
                        )
                        ->getSQL()
                )
            );

        $this->addQueryBuilderIncludeDiscontinued($demand, $queryBuilder);

        $this->addQueryBuilderFilters($demand, $queryBuilder);

        $this->addQueryBuilderOrderBy($demand, $queryBuilder);

        if ($demand->getLimit()) {
            $queryBuilder->setMaxResults($demand->getLimit());
        }
        if ($demand->getOffSet()) {
            $queryBuilder->setFirstResult($demand->getOffSet());
        }
        return $queryBuilder;
    }

    /**
     * Override basic method. Set special ordering for categories if it's not multiple
     *
     * @param DemandInterface|Demand $demand
     * @return void
     */
    protected function addQueryBuilderFilters(DemandInterface $demand, QueryBuilder &$queryBuilder): void
    {
        $ranges = [];
        if (!empty($demand->getFilters())) {
            foreach ($demand->getFilters() as $identifier => $filterData) {
                if (!empty($filterData['value']) && !empty($filterData['uid'])) {
                    /** @var Filter $filter */
                    $filter = $this->filterRepository->findByUid((int)$filterData['uid']);
                    if ($filter === null) {
                        continue;
                    }
                    switch ($filter->getType()) {
                        case Filter::TYPE_ATTRIBUTES:
                            $subQuery = $this->getSubQueryForAttributes($queryBuilder, $filter, $filterData['value']);
                            $queryBuilder->andWhere(
                                $queryBuilder->expr()->in(
                                    'product.uid',
                                    '('.$subQuery.')'
                                )
                            );
                            break;
                        case Filter::TYPE_CATEGORIES:
                            $subQuery = $this->getSubQueryForCategories($queryBuilder, $filter, $filterData['value']);
                            $queryBuilder->andWhere(
                                $queryBuilder->expr()->in(
                                    'product.uid',
                                    '('.$subQuery.')'
                                )
                            );
                            break;
                        case Filter::TYPE_ATTRIBUTES_MINMAX:
                            list($value, $rangeType) = $filterData['value'];
                            $rangeKey = (int)$filterData['attributeUid'];
                            $ranges[$rangeKey][$rangeType] = $value;
                            $ranges[$rangeKey]['filter'] = $filter;
                            break;
                        default:
                            // only two are supported for now
                    }
                }
            }
        }

        // go through ranges after all filters have been processed
        // since they can have value from two filter inputs
        if (!empty($ranges)) {
            foreach ($ranges as $attributeId => $range) {
                $subQuery = $this->getSubQueryForAttributesMinMax($queryBuilder, $range);
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in(
                        'product.uid',
                        '('.$subQuery.')'
                    )
                );
            }
        }
    }

    /**
     * Get Subquery for attributes
     *
     * @param QueryBuilder $queryBuilder Use same querybuilder so we can replace correct params later
     * @param Filter $filter
     * @param array $values
     * @return string
     */
    protected function getSubQueryForAttributes(QueryBuilder &$queryBuilder, Filter $filter, array $values): string
    {
        $subQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pxaproductmanager_domain_model_attributevalue');

        $subQueryBuilder->select('product')
            ->from('tx_pxaproductmanager_domain_model_attributevalue')
            ->where(
                $subQueryBuilder->expr()->eq(
                    'attribute',
                    $queryBuilder->createNamedParameter($filter->getAttribute()->getUid(), \PDO::PARAM_INT)
                )
            );

        $filterConjunction = $filter->getConjunctionAsString();
        if ($filterConjunction === Filter::CONJUNCTION_AND && count($values) > 0) {
            foreach ($values as $value) {
                $subQueryBuilder->andWhere(
                    $subQueryBuilder->expr()->eq(
                        'value',
                        $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
                    )
                );
            }
        } else {
            $subQueryBuilder->andWhere(
                $subQueryBuilder->expr()->in(
                    'value',
                    $queryBuilder->createNamedParameter(
                        $values,
                        \TYPO3\CMS\Core\Database\Connection::PARAM_STR_ARRAY
                    )
                )
            );
        }
        return $subQueryBuilder->getSQL();
    }

    /**
     * Get Subquery for categories
     *
     * @param QueryBuilder $queryBuilder Use same querybuilder so we can replace correct params later
     * @param Filter $filter
     * @param array $values
     * @return string
     */
    protected function getSubQueryForCategories(QueryBuilder &$queryBuilder, Filter $filter, array $values): string
    {
        // Include "child" categories
        $categories = CategoryUtility::getCategoriesRootLine($values);
        $categories = array_map('intval', $categories);

        $subQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pxaproductmanager_domain_model_attributevalue');

        $subQueryBuilder->select('uid_foreign')
            ->from('sys_category_record_mm');

        $filterConjunction = $filter->getConjunctionAsString();
        if ($filterConjunction === Filter::CONJUNCTION_AND && count($categories) > 0) {
            foreach ($categories as $value) {
                $subQueryBuilder->andWhere(
                    $subQueryBuilder->expr()->eq(
                        'uid_local',
                        $queryBuilder->createNamedParameter($value, \PDO::PARAM_STR)
                    )
                );
            }
        } else {
            $subQueryBuilder->andWhere(
                $subQueryBuilder->expr()->in(
                    'uid_local',
                    $queryBuilder->createNamedParameter(
                        $categories,
                        \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                    )
                )
            );
        }
        return $subQueryBuilder->getSQL();
    }

    /**
     * Get Subquery for attributes minmax
     *
     * @param QueryBuilder $queryBuilder Use same querybuilder so we can replace correct params later
     * @param Filter $filter
     * @param array $values
     * @return string
     */
    protected function getSubQueryForAttributesMinMax(QueryBuilder &$queryBuilder, array $range): string
    {
        $subQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_pxaproductmanager_domain_model_attributevalue');

        $subQueryBuilder->select('product')
            ->from('tx_pxaproductmanager_domain_model_attributevalue')
            ->where(
                $subQueryBuilder->expr()->eq(
                    'attribute',
                    $queryBuilder->createNamedParameter($range['filter']->getUid(), \PDO::PARAM_INT)
                )
            );
        if (isset($range['min'])) {
            $subQueryBuilder->andWhere(
                $subQueryBuilder->expr()->gte(
                    'value',
                    $queryBuilder->createNamedParameter((int)$range['min'], \PDO::PARAM_INT)
                )
            );
        }
        if (isset($range['max'])) {
            $subQueryBuilder->andWhere(
                $subQueryBuilder->expr()->lte(
                    'value',
                    $queryBuilder->createNamedParameter((int)$range['max'], \PDO::PARAM_INT)
                )
            );
        }
        return $subQueryBuilder->getSQL();
    }

    /**
     * Add discontinued part to query if needed
     *
     * @param DemandInterface $demand
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    protected function addQueryBuilderIncludeDiscontinued(DemandInterface $demand, QueryBuilder &$queryBuilder): void
    {
        // include discontinued part
        if (!$demand->getIncludeDiscontinued()) {
            $ts = new \DateTime('00:00');
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq(
                        'product.discontinued',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->gt(
                        'discontinued',
                        $queryBuilder->createNamedParameter($ts->getTimestamp(), \PDO::PARAM_INT)
                    )
                )
            );
        }
    }

    /**
     * Add orderBy to query if needed
     *
     * @param DemandInterface $demand
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    protected function addQueryBuilderOrderBy(DemandInterface $demand, QueryBuilder &$queryBuilder): void
    {
        // If sorting is set by categories, we need to create a special quer
        if ($demand->getOrderBy()
            && GeneralUtility::inList($demand->getOrderByAllowed(), $demand->getOrderBy())
        ) {
            if ($demand->getOrderBy() !== 'categories') {
                switch (strtolower($demand->getOrderDirection())) {
                    case 'desc':
                        $orderDirection = QueryInterface::ORDER_DESCENDING;
                        break;
                    default:
                        $orderDirection = QueryInterface::ORDER_ASCENDING;
                }
                $queryBuilder->orderBy(
                    $demand->getOrderBy(),
                    $orderDirection
                );
                if ($demand->getOrderBy() !== 'name') {
                    $queryBuilder->addOrderBy(
                        'name',
                        'ASC'
                    );
                }
            } else {
                // TODO: make it possible to order by categories
            }
        }
    }

    protected function getSQL(QueryBuilder $queryBuilder): string
    {
        $queryParameters = [];
        foreach ($queryBuilder->getParameters() as $key => $value) {
            // prefix array keys with ':'
            //all non numeric values have to be quoted
            if (is_array($value)) {
                $value = implode(',', $value);
                $queryParameters[':' . $key] = (is_numeric($value[0])) ? $value : "'" . $value . "'";
            } else {
                $queryParameters[':' . $key] = (is_numeric($value)) ? $value : "'" . $value . "'";
            }
        }
        $statement = strtr($queryBuilder->getSQL(), $queryParameters);
        return $statement;
    }
}
