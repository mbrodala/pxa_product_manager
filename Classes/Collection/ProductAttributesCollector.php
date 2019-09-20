<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Collection;

use Pixelant\PxaProductManager\Domain\Model\AttributeSet;
use Pixelant\PxaProductManager\Domain\Model\Category;
use Pixelant\PxaProductManager\Domain\Model\Product;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ProductAttributesCollector
 * @package Pixelant\PxaProductManager\Collection
 */
class ProductAttributesCollector
{
    /**
     * @var Product
     */
    protected $product = null;

    /**
     * All attributes related to product
     *
     * @var ObjectStorage
     */
    protected $attributes = null;

    /**
     * Attributes grouped by sets
     *
     * @var ObjectStorage
     */
    protected $attributesSets = null;

    /**
     * Collections cache
     *
     * @var array
     */
    protected static $cache = [];

    /**
     * Initialize attributes collection
     *
     * @param Product $product Product extbase object
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
        $this->attributes = new ObjectStorage();
        $this->attributesSets = new ObjectStorage();

        // Init
        $this->init();
    }

    /**
     * @return ObjectStorage
     */
    public function getAttributes(): ObjectStorage
    {
        return $this->attributes;
    }

    /**
     * @return ObjectStorage
     */
    public function getAttributesSets(): ObjectStorage
    {
        return $this->attributesSets;
    }

    /**
     * Collect all attributes for given product
     */
    protected function init(): void
    {
        $categories = $this->product->getCategories();

        list($attributes, $attributesSets) = $this->collectAttributesAndSets($categories);

        foreach ($attributes as $attribute) {
            $this->attributes->attach(clone $attribute);
        }
        foreach ($attributesSets as $attributesSet) {
            $this->attributesSets->attach(clone $attributesSet);
        }
    }

    /**
     * Go through every category and collect it parent, so we can get all attributes
     *
     * @param ObjectStorage $categories
     * @return array
     */
    protected function collectAttributesAndSets(ObjectStorage $categories): array
    {
        if ($categories->count() === 0) {
            return [
                [],
                []
            ];
        }

        $cacheHash = $this->getCacheHash($categories);
        if (isset(static::$cache[$cacheHash])) {
            return static::$cache[$cacheHash];
        }

        $attributesSets = [];
        $attributes = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $categoriesParentsCollection = $this->getCategoriesCollector()->collectParentsTree($category);

            /** @var Category $categoryTreeItem */
            foreach ($categoriesParentsCollection as $categoryTreeItem) {
                $categoryTreeItemAttributesSets = $categoryTreeItem->getAttributeSets();

                $attributesSets = array_merge($attributesSets, $categoryTreeItemAttributesSets->toArray());

                /** @var AttributeSet $categoryTreeItemAttributesSet */
                foreach ($categoryTreeItemAttributesSets as $categoryTreeItemAttributesSet) {
                    $attributes = array_merge($attributes, $categoryTreeItemAttributesSet->getAttributes()->toArray());
                }
            }
        }

        $result = [
            array_unique($attributes),
            array_unique($attributesSets)
        ];

        static::$cache[$cacheHash] = $result;

        return $result;
    }

    /**
     * Generate unique identifier for categories storage
     *
     * @param ObjectStorage $categories
     * @return string
     */
    protected function getCacheHash(ObjectStorage $categories): string
    {
        $uids = array_map(
            function ($category) {
                return $category->getUid();
            },
            $categories->toArray()
        );

        return sha1(implode(',', $uids));
    }

    /**
     * @return CategoriesCollector
     */
    protected function getCategoriesCollector(): CategoriesCollector
    {
        return GeneralUtility::makeInstance(CategoriesCollector::class);
    }
}
