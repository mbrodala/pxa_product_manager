<?php

namespace Pixelant\PxaProductManager\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017
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

use Pixelant\PxaProductManager\Domain\Model\Attribute;
use Pixelant\PxaProductManager\Domain\Model\Category;
use Pixelant\PxaProductManager\Domain\Model\DTO\Demand;
use Pixelant\PxaProductManager\Domain\Model\DTO\FiltersAvailableOptions;
use Pixelant\PxaProductManager\Domain\Model\Filter;
use Pixelant\PxaProductManager\Domain\Model\Product;
use Pixelant\PxaProductManager\Domain\Repository\AttributeValueRepository;
use Pixelant\PxaProductManager\Domain\Repository\CategoryRepository;
use Pixelant\PxaProductManager\Domain\Repository\FilterRepository;
use Pixelant\PxaProductManager\Domain\Repository\OrderConfigurationRepository;
use Pixelant\PxaProductManager\Domain\Repository\OrderRepository;
use Pixelant\PxaProductManager\Domain\Repository\ProductRepository;
use Pixelant\PxaProductManager\Navigation\CategoriesNavigationTreeBuilder;
use Pixelant\PxaProductManager\Traits\SignalSlot\DispatcherTrait;
use Pixelant\PxaProductManager\Utility\CategoryUtility;
use Pixelant\PxaProductManager\Utility\MainUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 * @package Pixelant\PxaProductManager\Controller
 */
class AbstractController extends ActionController
{
    use DispatcherTrait;

    /**
     * productRepository
     *
     * @var ProductRepository
     */
    protected $productRepository = null;

    /**
     * productRepository
     *
     * @var FilterRepository
     */
    protected $filterRepository = null;

    /**
     * categoryRepository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * @var OrderRepository
     */
    protected $orderRepository = null;

    /**
     * @var OrderConfigurationRepository
     */
    protected $orderConfigurationRepository = null;

    /**
     * @var AttributeValueRepository
     */
    protected $attributeValueRepository = null;

    /**
     * @param AttributeValueRepository $attributeValueRepository
     */
    public function injectAttributeValueRepository(AttributeValueRepository $attributeValueRepository)
    {
        $this->attributeValueRepository = $attributeValueRepository;
    }

    /**
     * @param ProductRepository $productRepository
     */
    public function injectProductRepository(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param FilterRepository $filterRepository
     */
    public function injectFilterRepository(FilterRepository $filterRepository)
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param OrderRepository $orderRepository
     */
    public function injectOrderRepository(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderConfigurationRepository $orderConfigurationRepository
     */
    public function injectOrderConfigurationRepository(OrderConfigurationRepository $orderConfigurationRepository)
    {
        $this->orderConfigurationRepository = $orderConfigurationRepository;
    }

    /**
     * Get category
     *
     * @param int $category
     * @return Category|object
     */
    protected function determinateCategory($category = 0)
    {
        if ($category) {
            $categoryUid = $category;
        } elseif (!empty($this->settings['category'])) {
            $categoryUid = (int)$this->settings['category'];
        }

        $category = $this->categoryRepository->findByUid($categoryUid ?? 0);

        if ($category === null) {
            $this->addFlashMessage(
                'Couldn\'t determine category, please check your selection.',
                'Error',
                FlashMessage::ERROR
            );
        }

        return $category;
    }

    /**
     * Generate categories tree
     *
     * @return array
     */
    protected function getNavigationTree()
    {
        $activeCategory = MainUtility::getActiveCategoryFromRequest();
        $excludeCategories = GeneralUtility::intExplode(
            ',',
            $this->settings['excludeCategories'],
            true
        );

        /** @var CategoriesNavigationTreeBuilder $treeBuilder */
        $treeBuilder = GeneralUtility::makeInstance(CategoriesNavigationTreeBuilder::class);

        $treeBuilder
            ->setExpandAll((bool)$this->settings['navigationExpandAll'])
            ->setHideCategoriesWithoutProducts((bool)$this->settings['navigationHideCategoriesWithoutProducts'])
            ->setExcludeCategories($excludeCategories);

        // set custom order
        if (!empty($orderings = $this->getOrderingsForCategories())) {
            $treeBuilder->setOrderings($orderings);
        }

        return $treeBuilder->buildTree(
            (int)$this->settings['category'],
            $activeCategory
        );
    }

    /**
     * Generate root line array of demand categories
     *
     * @param array $allowedCategories
     * @param array $excludeCategories
     * @return array
     */
    protected function getDemandCategories(array $allowedCategories = [], array $excludeCategories = [])
    {
        $allowedCategories = CategoryUtility::getCategoriesRootLine(
            $allowedCategories
        );

        return array_diff($allowedCategories, $excludeCategories);
    }

    /**
     * Generate ordering array for categories
     *
     * @return array
     */
    protected function getOrderingsForCategories(): array
    {
        if ($this->settings['orderCategoriesBy'] && $this->settings['orderCategoriesDirection']) {
            switch (strtolower($this->settings['orderCategoriesDirection'])) {
                case 'desc':
                    $orderDirection = QueryInterface::ORDER_DESCENDING;
                    break;
                default:
                    $orderDirection = QueryInterface::ORDER_ASCENDING;
            }

            return [
                $this->settings['orderCategoriesBy'] => $orderDirection
            ];
        }

        return [];
    }

    /**
     * Translate label
     *
     * @param string $key
     * @param array $arguments
     * @return string
     */
    protected function translate(string $key, array $arguments = null): string
    {
        return LocalizationUtility::translate($key, 'PxaProductManager', $arguments) ?? '';
    }

    /**
     * Create demand without limit
     *
     * @param Demand $demand
     * @return Demand
     */
    protected function getDemandNoLimit(Demand $demand): Demand
    {
        $demandNoLimit = clone $demand;

        $demandNoLimit->setLimit(0);
        $demandNoLimit->setOffSet(0);

        return $demandNoLimit;
    }

    /**
     * Create object with available filters options
     *
     * @param Demand $demand
     * @param bool $hideNoResult
     * @return FiltersAvailableOptions
     */
    protected function createFiltersAvailableOptions(Demand $demand, bool $hideNoResult = false): FiltersAvailableOptions
    {
        $demand = $this->getDemandNoLimit($demand);

        $filtersAvailableOptions = GeneralUtility::makeInstance(FiltersAvailableOptions::class);

        if (!$hideNoResult) {
            $filtersAvailableOptions->allowAll();

            return $filtersAvailableOptions;
        }

        $productsSubQuery = $this->createProductsSubQuery($demand);

        $attributeOptions = $this->attributeValueRepository->findAvailableFilterOptions($productsSubQuery);
        $availableCategories = $this->categoryRepository->findCategoriesUidsByProductsQuery($productsSubQuery);

        $filtersAvailableOptions->setAvailableCategoriesForAll(
            $availableCategories
        );
        $filtersAvailableOptions->setAvailableAttributesForAll(
            $attributeOptions
        );

        // Now get results per filter
        /*$filters = $demand->getFilters();
        foreach ($filters as $key => $demandFilter) {
            $filter = $this->filterRepository->findByUid((int)$demandFilter['uid']);
            if ($filter === null) {
                continue;
            }

            // Get options variants for all 'OR' filters
            if ($filter->getConjunctionAsString() === Filter::CONJUNCTION_OR) {
                // Create new filters
                $demandFiltersVariant = $filters;
                unset($demandFiltersVariant[$key]);
                // Set new filters
                $demand->setFilters($demandFiltersVariant);

                // Get result for new filters
                if ($filter->getType() === Filter::TYPE_CATEGORIES) {
                    /*$filtersAvailableOptions->setAvailableCategoriesForFilter(
                        $filter->getUid(),
                        $this->getAvailableFilteringCategoriesForProducts($allAvailableProductsVariant)
                    );
                } else {
                    $filtersAvailableOptions->setAvailableAttributesForFilter(
                        $filter->getUid(),
                        $this->attributeValueRepository->findAvailableFilterOptions($demand)
                    );
                }
            }
        }*/

        return $filtersAvailableOptions;
    }

    /**
     * Create products sub-query in order to fetch available options
     *
     * @param Demand $demand
     * @return string
     */
    protected function createProductsSubQuery(Demand $demand): string
    {
        $productsQueryBuilder = $this->productRepository->createQueryBuilderByDemand($demand);
        $productsQueryBuilder->select('tx_pxaproductmanager_domain_model_product.uid');

        $queryParameters = [];

        foreach ($productsQueryBuilder->getParameters() as $key => $value) {
            // prefix array keys with ':'
            //all non numeric values have to be quoted
            $queryParameters[':' . $key] = (is_numeric($value)) ? $value : "'" . $value . "'";
        }

        return strtr($productsQueryBuilder->getSQL(), $queryParameters);
    }

    /**
     * Check if options without results need to be hidden
     *
     * @return bool
     */
    protected function hideFilterOptionsNoResult(): bool
    {
        return (int)$this->settings['hideFilterOptionsNoResult'] === 1;
    }

    /**
     * Add labels for JS
     *
     * @return void
     */
    protected function getFrontendLabels()
    {
        static $jsLabelsAdded;

        if ($jsLabelsAdded === null) {
            $labelsJs = [];
            if (is_array($this->settings['translateJsLabels'])) {
                foreach ($this->settings['translateJsLabels'] as $translateJsLabelSet) {
                    $translateJsLabels = GeneralUtility::trimExplode(',', $translateJsLabelSet, true);
                    foreach ($translateJsLabels as $translateJsLabel) {
                        $labelsJs[$translateJsLabel] = $this->translate($translateJsLabel);
                    }
                }
            }
            if (!empty($labelsJs)) {
                $this->getPageRenderer()->addInlineLanguageLabelArray($labelsJs);
            }

            $jsLabelsAdded = true;
        }
    }

    /**
     * Sort query result according to uid list order
     *
     * @param QueryResultInterface $queryResults
     * @param array $uidList
     * @return array
     */
    protected function sortQueryResultsByUidList(QueryResultInterface $queryResults, array $uidList): array
    {
        $result = [];

        foreach ($queryResults as $queryResult) {
            $uid = ObjectAccess::getProperty($queryResult, 'uid');
            $result[$uid] = $queryResult;
        }

        if (!empty($result)) {
            $uidList = array_intersect($uidList, array_keys($result));
            // sort to have same order as in list
            $result = array_replace(array_flip($uidList), $result);
        }

        return $result;
    }

    /**
     * @return object|PageRenderer
     */
    protected function getPageRenderer()
    {
        return GeneralUtility::makeInstance(PageRenderer::class);
    }
}
