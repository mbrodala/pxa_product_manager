<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Collection;

use Pixelant\PxaProductManager\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class CategoriesCollector
 * @package Pixelant\PxaProductManager\Collection
 */
class CategoriesCollector
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository = null;

    /**
     * CategoriesCollector constructor.
     */
    public function __construct()
    {
        $this->categoryRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(CategoryRepository::class);
    }

    /**
     * Collect all parents trees for given list
     *
     * @param array $categoriesUids
     * @return array
     */
    public function collectParentsUidsForList(array $categoriesUids): array
    {
        $result = [];
        foreach ($categoriesUids as $categoriesUid) {
            $result = array_merge($result, $this->collectParentsUids($categoriesUid));
        }

        return array_unique($result);
    }

    /**
     * Collect all parents if given category
     *
     * @param int $categoryUid
     * @return array
     */
    public function collectParentsUids(int $categoryUid): array
    {
        $parents = [$categoryUid];
        $iteration = 0;

        while ($parentUid = $this->getParentUid($categoryUid)) {
            $parents[] = $parentUid;

            $categoryUid = $parentUid;
            $iteration++;

            if ($iteration > 9999) {
                throw new \LogicException("Reach maximum iterations level '$iteration'.", 1569580348546);
            }
        }

        return $parents;
    }

    /**
     * Get parent of given category
     *
     * @param int $categoryUid
     * @return int|null
     */
    protected function getParentUid(int $categoryUid): ?int
    {
        $cache = $this->getRunTimeCache();
        $cacheIdentifier = sha1('cache_pxapm_categories_parent' . $categoryUid);

        if ($cache->has($cacheIdentifier)) {
            return $cache->get($cacheIdentifier);
        }

        $parent = $this->categoryRepository->findParentUid($categoryUid);
        $cache->set($cacheIdentifier, $parent);

        return $parent;
    }

    /**
     * @return FrontendInterface
     */
    protected function getRunTimeCache(): FrontendInterface
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_runtime');
    }

    /**
     * @return FrontendInterface
     */
    protected function getCategoriesCache(): FrontendInterface
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_pxa_product_manager_category');
    }
}
