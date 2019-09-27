<?php
declare(strict_types=1);

namespace Pixelant\PxaProductManager\Domain\Model;

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

use Pixelant\PxaProductManager\Utility\AttributeHolderUtility;
use Pixelant\PxaProductManager\Utility\ProductUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 *
 *
 * @package pxa_product_manager
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Product extends AbstractEntity
{

    /**
     * Categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\Category>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $categories;

    /**
     * name
     *
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $slug = '';

    /**
     * sku
     *
     * @var string
     */
    protected $sku;

    /**
     * Price
     *
     * @var float
     */
    protected $price = 0.0;

    /**
     * taxRate
     *
     * @var float $taxRate
     */
    protected $taxRate = 0.00;

    /**
     * description
     *
     * @var string
     */
    protected $description = '';

    /**
     * disableSingleView
     *
     * @var boolean
     */
    protected $disableSingleView = false;

    /**
     * @var string
     */
    protected $alternativeTitle = '';

    /**
     * @var string
     */
    protected $keywords = '';

    /**
     * @var string
     */
    protected $metaDescription = '';

    /**
     * relatedProducts
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\Product>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $relatedProducts;

    /**
     * Images
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\Image>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $images;

    /**
     * Attributes files
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\AttributeFalFile>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $attributeFiles;

    /**
     * links
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\Link>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $links;

    /**
     * subProducts
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\Product>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $subProducts;

    /**
     * Fal links
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $falLinks;

    /**
     * @var int
     */
    protected $crdate;

    /**
     * @var int
     */
    protected $tstamp;

    /**
     * @var boolean
     */
    protected $hidden;

    /**
     * @var boolean
     */
    protected $deleted;

    /**
     * Attribute values
     *
     * @var string
     */
    protected $attributesValues = '';

    /**
     * Product main image
     *
     * @var Image
     */
    protected $mainImage;

    /**
     * Product listing image
     * @var Image
     */
    protected $thumbnailImage;

    /**
     * Assets
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $assets;

    /**
     * teaser
     *
     * @var string
     */
    protected $teaser = '';

    /**
     * accessories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Pixelant\PxaProductManager\Domain\Model\Product>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
     */
    protected $accessories;

    /**
     * __construct
     *
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties.
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        /**
         * Do not modify this method!
         * It will be rewritten on each save in the extension builder
         * You may modify the constructor of this class instead
         */
        $this->relatedProducts = new ObjectStorage();

        $this->images = new ObjectStorage();

        $this->attributeFiles = new ObjectStorage();

        $this->links = new ObjectStorage();

        $this->subProducts = new ObjectStorage();

        $this->falLinks = new ObjectStorage();

        $this->categories = new ObjectStorage();

        $this->assets = new ObjectStorage();

        $this->accessories = new ObjectStorage();
    }

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * Returns the sku
     *
     * @return string $sku
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Sets the sku
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Format price
     *
     * @return string
     */
    public function getFormatPrice(): string
    {
        return ProductUtility::formatPrice($this->price);
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * Returns the taxRate
     *
     * @return float $taxRate
     */
    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    /**
     * Sets the taxRate
     *
     * @param float $taxRate
     * @return void
     */
    public function setTaxRate(float $taxRate): void
    {
        $this->taxRate = $taxRate;
    }

    /**
     * Returns the relatedProducts
     *
     * @return ObjectStorage $relatedProducts
     */
    public function getRelatedProducts(): ObjectStorage
    {
        return $this->relatedProducts;
    }

    /**
     * Sets the relatedProducts
     *
     * @param ObjectStorage $relatedProducts
     * @return void
     */
    public function setRelatedProducts(ObjectStorage $relatedProducts): void
    {
        $this->relatedProducts = $relatedProducts;
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Returns the images
     *
     * @return ObjectStorage $images
     */
    public function getImages(): ObjectStorage
    {
        return $this->images;
    }

    /**
     * Sets the images
     *
     * @param ObjectStorage $images
     * @return void
     */
    public function setImages(ObjectStorage $images): void
    {
        $this->images = $images;
    }

    /**
     * Returns the Attribute files
     *
     * @return ObjectStorage
     */
    public function getAttributeFiles(): ObjectStorage
    {
        return $this->attributeFiles;
    }

    /**
     * Sets the Attribute files
     *
     * @param ObjectStorage $files
     * @return void
     */
    public function setAttributeFiles(ObjectStorage $files): void
    {
        $this->attributeFiles = $files;
    }

    /**
     * Returns the categories
     *
     * @return ObjectStorage $categories
     */
    public function getCategories(): ObjectStorage
    {
        return $this->categories;
    }

    /**
     * Returns the categories
     *
     * @return Category
     */
    public function getFirstCategory(): Category
    {
        $this->categories->rewind();
        return $this->categories->current();
    }

    /**
     * Sets the categories
     *
     * @param ObjectStorage $categories
     * @return void
     */
    public function setCategories(ObjectStorage $categories): void
    {
        $this->categories = $categories;
    }

    /**
     * Get main image of product
     *
     * @return Image|null
     */
    public function getMainImage(): ?Image
    {
        if ($this->mainImage === null) {
            $this->mainImage = $this->getImageFor('mainImage');
        }
        return $this->mainImage;
    }

    /**
     * Product thumbnail
     *
     * @return Image|null
     */
    public function getThumbnail(): ?Image
    {
        if ($this->thumbnailImage === null) {
            $this->thumbnailImage = $this->getImageFor('useInListing');
        }
        return $this->thumbnailImage;
    }

    /**
     * Returns the links
     *
     * @return ObjectStorage links
     */
    public function getLinks(): ObjectStorage
    {
        return $this->links;
    }

    /**
     * Sets the links
     *
     * @param ObjectStorage $links
     */
    public function setLinks(ObjectStorage $links): void
    {
        $this->links = $links;
    }

    /**
     * Sets the disableSingleView
     *
     * @param boolean $disableSingleView
     * @return void
     */
    public function setDisableSingleView(bool $disableSingleView): void
    {
        $this->disableSingleView = $disableSingleView;
    }

    /**
     * Returns the boolean state of disableSingleView
     *
     * @return boolean
     */
    public function isDisableSingleView(): bool
    {
        return $this->disableSingleView;
    }

    /**
     * Returns the subProducts
     *
     * @return ObjectStorage $subProducts
     */
    public function getSubProducts(): ObjectStorage
    {
        return $this->subProducts;
    }

    /**
     * Sets the subProducts
     *
     * @param ObjectStorage $subProducts
     * @return void
     */
    public function setSubProducts(ObjectStorage $subProducts): void
    {
        $this->subProducts = $subProducts;
    }


    /**
     * Get alternative title
     *
     * @return string
     */
    public function getAlternativeTitle(): string
    {
        return $this->alternativeTitle;
    }

    /**
     * Set alternative title
     *
     * @param string $alternativeTitle
     * @return void
     */
    public function setAlternativeTitle(string $alternativeTitle): void
    {
        $this->alternativeTitle = $alternativeTitle;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * Set keywords
     *
     * @param string $keywords keywords
     * @return void
     */
    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * Get metaDescription
     *
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription metaDescription
     * @return void
     */
    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * Sorted images
     *
     * @return ObjectStorage
     */
    public function getImagesExcludeMainImage(): ObjectStorage
    {
        if ($this->getImages()->count() > 1 && $this->getMainImage() !== null) {
            $images = new ObjectStorage();
            $mainImage = $this->getMainImage();

            foreach ($this->getImages() as $image) {
                if ($image->getUid() !== $mainImage->getUid()) {
                    $images->attach($image);
                }
            }

            return $images;
        }

        return $this->getImages();
    }

    /**
     * Returns the falLinks
     *
     * @return ObjectStorage $falLinks
     */
    public function getFalLinks(): ObjectStorage
    {
        return $this->falLinks;
    }

    /**
     * Sets the falLinks
     *
     * @param ObjectStorage $falLinks
     * @return void
     */
    public function setFalLinks(ObjectStorage $falLinks): void
    {
        $this->falLinks = $falLinks;
    }

    /**
     * @return string
     */
    public function getAttributesValues(): string
    {
        return $this->attributesValues;
    }

    /**
     * @param string $attributesValues
     */
    public function setAttributesValues(string $attributesValues): void
    {
        $this->attributesValues = $attributesValues;
    }

    /**
     * Get Hidden
     *
     * @return boolean
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Set Hidden
     *
     * @param boolean $hidden
     * @return void
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * Get Deleted
     *
     * @return boolean
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    /**
     * Returns the assets
     *
     * @return ObjectStorage $assets
     */
    public function getAssets(): ObjectStorage
    {
        return $this->assets;
    }

    /**
     * Sets the assets
     *
     * @param ObjectStorage $assets
     * @return void
     */
    public function setAssets(ObjectStorage $assets): void
    {
        $this->assets = $assets;
    }

    /**
     * Returns the teaser
     *
     * @return string $teaser
     */
    public function getTeaser(): string
    {
        return $this->teaser;
    }

    /**
     * Sets the teaser
     *
     * @param string $teaser
     * @return void
     */
    public function setTeaser(string $teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * Returns the accessories
     *
     * @return ObjectStorage $accessories
     */
    public function getAccessories(): ObjectStorage
    {
        return $this->accessories;
    }

    /**
     * Sets the accessories
     *
     * @param ObjectStorage $accessories
     * @return void
     */
    public function setAccessories(ObjectStorage $accessories): void
    {
        $this->accessories = $accessories;
    }

    /**
     * @return float
     */
    public function getTax(): float
    {
        die(__METHOD__);
        return $this->getPrice() * ($this->getTaxRateRecursively() / 100);
    }

    /**
     * @return string
     */
    public function getFormatTax(): string
    {
        die(__METHOD__);
        return ProductUtility::formatPrice($this->getTax());
    }

    /**
     * @return float
     */
    public function getTaxRateRecursively(): float
    {
        die(__METHOD__);

        // If tax rate is set on product level - return it
        // or it was set from category tax
        if (!empty($this->taxRate)) {
            return $this->taxRate;
        }

        // Else get the tax rate from categories
        $taxRate = 0;

        $categoriesTree = ProductUtility::getProductCategoriesParentsTree($this->getUid());
        /** @var Category $category */
        foreach ($categoriesTree as $category) {
            $taxRate = $category->getTaxRate();
            if ($taxRate > 0) {
                $this->taxRate = $taxRate; // Save value for future calls
                break;
            }
        }

        return $taxRate;
    }

    /**
     * Get image for different views
     *
     * @param string $propertyName
     * @return null|Image
     */
    protected function getImageFor($propertyName): ?Image
    {
        if ($this->images->count()) {
            /** @var Image $image */
            foreach ($this->images as $image) {
                if (ObjectAccess::isPropertyGettable($image, $propertyName)
                    && ObjectAccess::getProperty($image, $propertyName) === true
                ) {
                    return $image;
                }
            }

            // use first if no result
            $this->images->rewind();
            return $this->images->current();
        }

        return null;
    }

    /**
     * Initialize attributes
     */
    protected function initializeAttributes()
    {
        die(__METHOD__);

        $this->attributes = new ObjectStorage();

        /** @var AttributeHolderUtility $attributeHolder */
        $attributeHolder = GeneralUtility::makeInstance(AttributeHolderUtility::class);
        $attributeHolder->start($this->getUid());

        $this->attributesGroupedBySets = $attributeHolder->getAttributeSets();

        $attributesValues = (array)unserialize($this->getSerializedAttributesValues());

        /** @var Attribute $attribute */
        foreach ($attributeHolder->getAttributes() as $attribute) {
            $id = $attribute->getUid();

            if ($attribute->isFalType()) {
                $falFiles = [];
                /** @var AttributeFalFile $falReference */
                foreach ($this->attributeFiles->toArray() as $falReference) {
                    if ($falReference->getAttribute() === $id) {
                        $falFiles[] = $falReference;
                    }
                }

                $attribute->setValue($falFiles);
            } elseif (array_key_exists($id, $attributesValues)) {
                $value = $attributesValues[$id];

                switch ($attribute->getType()) {
                    case Attribute::ATTRIBUTE_TYPE_DROPDOWN:
                    case Attribute::ATTRIBUTE_TYPE_MULTISELECT:
                        $options = [];

                        /** @var Option $option */
                        foreach ($attribute->getOptions() as $option) {
                            if (GeneralUtility::inList($value, $option->getUid())) {
                                $options[] = $option;
                            }
                        }

                        $attribute->setValue($options);
                        break;
                    case Attribute::ATTRIBUTE_TYPE_DATETIME:
                        if ($value) {
                            try {
                                $value = new \DateTime($value);
                            } catch (\Exception $exception) {
                                $value = '';
                            }
                        }
                        $attribute->setValue($value);
                        break;
                    default:
                        $attribute->setValue($value);
                }
            }

            $this->attributes->attach($attribute);
            $this->attributesIdentifiersArray[$attribute->getIdentifier() ?: $attribute->getUid()] = $attribute;
        }
    }
}
