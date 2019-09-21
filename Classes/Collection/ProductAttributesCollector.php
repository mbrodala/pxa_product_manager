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
    }

    /**
     * @return ObjectStorage
     */
    public function getAttributes(): ObjectStorage
    {
        if ($this->attributes === null) {
            $attributeSets = $this->getAttributesSets();
            $attributes = [];

            /** @var AttributeSet $attributeSet */
            foreach ($attributeSets as $attributeSet) {
                $attributes = array_merge($attributes, $attributeSet->getAttributes()->toArray());
            }

            $this->attributes = new ObjectStorage();
            $this->attachToStorage(array_unique($attributes), $this->attributes);
        }

        return $this->attributes;
    }

    /**
     * @return ObjectStorage
     */
    public function getAttributesSets(): ObjectStorage
    {
        if ($this->attributesSets === null) {
            $attributesSets = $this->collectAttributesSets($this->product->getCategories());

            $this->attributesSets = new ObjectStorage();
            $this->attachToStorage($attributesSets, $this->attributesSets);
        }

        return $this->attributesSets;
    }

    /**
     * Attach to object storage
     */
    protected function attachToStorage(array $items, ObjectStorage $storage): void
    {
        foreach ($items as $item) {
            $storage->attach($item);
        }
        $storage->_memorizeCleanState();
    }

    /**
     * Go through every category and collect it parents, so we can get all attributes
     *
     * @param ObjectStorage $categories
     * @return array
     */
    protected function collectAttributesSets(ObjectStorage $categories): array
    {
        if ($categories->count() === 0) {
            return [];
        }

        $cacheHash = $this->getCacheHash($categories);
        if (isset(static::$cache[$cacheHash])) {
            return static::$cache[$cacheHash];
        }

        $attributesSets = [];
        /** @var Category $category */
        foreach ($categories as $category) {
            $categoriesParentsCollection = $this->getCategoriesCollector()->collectParentsTree($category);

            /** @var Category $categoryTreeItem */
            foreach ($categoriesParentsCollection as $categoryTreeItem) {
                $categoryAttributesSets = $categoryTreeItem->getAttributeSets();

                $attributesSets = array_merge($attributesSets, $categoryAttributesSets->toArray());
            }
        }

        static::$cache[$cacheHash] = array_unique($attributesSets);

        return static::$cache[$cacheHash];
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
