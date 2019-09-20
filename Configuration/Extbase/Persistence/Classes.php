<?php
declare(strict_types = 1);

return [
    Pixelant\PxaProductManager\Domain\Model\Image::class => [
        'tableName' => 'sys_file_reference',
        'properties' => [
            'useInListing' => [
                'fieldName' => 'pxapm_use_in_listing',
            ],
            'mainImage' => [
                'fieldName' => 'pxapm_main_image',
            ],
        ],
    ],

    Pixelant\PxaProductManager\Domain\Model\AttributeFalFile::class => [
        'tableName' => 'sys_file_reference'    ,
        'properties' => [
            'attribute' => [
                'fieldName' => 'pxa_attribute',
            ],
        ],
    ],

    Pixelant\PxaProductManager\Domain\Model\Category::class => [
        'tableName' => 'sys_category'    ,
        'properties' => [
            'attributeSets' => [
                'fieldName' => 'pxapm_attributes_sets',
            ],
            'pxapm_image' => [
                'fieldName' => 'image',
            ],
            'pxapm_description' => [
                'fieldName' => 'description',
            ],
            'pxapm_subcategories' => [
                'fieldName' => 'subCategories',
            ],
            'pxapm_tax_rate' => [
                'fieldName' => 'taxRate',
            ],
            'pxapm_slug' => [
                'fieldName' => 'slug',
            ],
        ],
    ],
];
