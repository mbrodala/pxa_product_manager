<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Collection;

use Pixelant\PxaProductManager\Domain\Model\Product;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class ProductAttributesCollection
 * @package Pixelant\PxaProductManager\Collection
 */
class ProductAttributesCollection
{
    /**
     * @var int
     */
    protected $productUid = 0;

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
     * Initialize attributes collection
     *
     * @param Product|int $product Product extbase object or uid
     */
    public function __construct($product)
    {
        if (!is_int($product) || !($product instanceof Product)) {
            throw new \InvalidArgumentException(sprintf('Expect product to be integer or Product object, "%s" type given', gettype($product)));
        }

        $this->productUid = is_object($product) ? $product->getUid() : $product;

        // Init
        $this->init();
    }

    /**
     * Collect all attributes for given product
     */
    protected function init(): void
    {

    }
}
