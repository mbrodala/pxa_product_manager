<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Collection;

use Pixelant\PxaProductManager\Domain\Model\Category;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class CategoriesCollector
 * @package Pixelant\PxaProductManager\Collection
 */
class CategoriesCollector
{
    /**
     * Get parents tree of given category
     *
     * @param Category $category
     * @return ObjectStorage
     */
    public function collectParentsTree(Category $category): ObjectStorage
    {
        $collection = new ObjectStorage();
        $collection->attach($category);
        $uniqueParents = [];

        while ($parent = $category->getParent()) {
            $category = $parent;

            if (in_array($category->getUid(), $uniqueParents, true)) {
                throw new \UnexpectedValueException("Parent with UID {$category->getUid()} found more than once when building parents tree", 1568977127221);
            }
            $uniqueParents[] = $category->getUid();
            $collection->attach($category);
        }

        return $collection;
    }
}
