<?php
return [
    //apis used for the app
    'apis' => [
        //get shop settings i.e. shop api
        'shop' => [
            '/admin/api/2020-07/shop.json'
        ],
        //get all themes api
        'getAllThemes' => [
            '/admin/api/2020-04/themes.json'
        ],
        //save script tag api
        'saveScriptTag' => [
            '/admin/api/2020-04/script_tags.json'
        ],
        //get single asset of a theme
        'getSingleAsset' => [
            '/admin/api/2020-04/themes/',
            '/assets.json'
        ],
        //save single asset of a theme
        'saveSingleAsset' => [
            '/admin/api/2020-04/themes/',
            '/assets.json'
        ],
        //delete asset of a theme
        'deleteAsset' => [
            '/admin/api/2020-04/themes/',
            '/assets.json'
        ],
        //products count
        'getProductsCount' => [
            '/admin/api/2020-10/products/count.json'
        ],
        //apis for testing purpose

        // get all webhooks
        'getWebhooks' => [
            '/admin/api/2020-10/webhooks.json'
        ],
        'cancelCharge' => [
            '/admin/api/2020-07/recurring_application_charges/',
            '.json'
        ],
        'getProducts' => [
            '/admin/api/2020-10/products.json'
        ],
        'getProductById' => [
            '/admin/api/2020-10/products/',
            '.json'
        ],
        'getCustomCollection' => [
            '/admin/api/2020-10/custom_collections.json'
        ],
        'getAutomaticCollection' => [
            '/admin/api/2020-07/smart_collections.json'
        ],
        "getSingleAutomaticCollection" => [
            "/admin/api/2020-10/smart_collections/",
            ".json"
        ],
        "getSingleCustomCollection" => [
            "/admin/api/2020-10/custom_collections/",
            ".json"
        ],
        "shippingApi" => [
            "/admin/api/2020-10/shipping_zones.json"
        ],
        'getCollectionProducts' => [
            "/admin/api/2020-10/collections/",
            "/products.json"
        ],
        "getVariants" => [
            "/admin/api/2020-10/products/",
            '/variants.json'
        ],
        'getProductCollectionIds' => [
            '/admin/api/2020-10/collects.json'
        ],
        'getProductMetaFields' => [
            '/admin/api/2020-10/products/',
            '/metafields.json'
        ]
    ],
    "graphQl" => [
        "apis" => [
            'getProductsBySearch' => [
                '{
                    products(query:"title:',
                    '*",',
                    ':',
                    '',
                    '',
                    ')
                    {
                        pageInfo{
                            hasNextPage
                            hasPreviousPage
                        }
                        edges {
                            cursor
                            node {
                                id
                                title
                                featuredImage {
                                    src
                                }
                                publishedAt
                            }
                        }
                    }
                }'
            ],
            "getVarientByProductId" => [
                '{
                    product(id: "gid://shopify/Product/',
                    '") 
                    {
                        variants(first: 100) {
                            edges {
                                node {
                                    image {
                                        src
                                    }
                                    id
                                    sku
                                    title
                                }
                            }
                        }
                    }
                }'
            ],
            // "insertPrivateMetaField" => [
            //     'mutation($input: ProductInput!) {
            //         productUpdate(input: $input) {
            //             product {
            //                 id
            //             }
            //         }
            //     }'
            // ],
            // "getPrivateMetaField" => [
            //     '{
            //         product(id: "gid://shopify/Product/',
            //         '") {
            //             metafield(namespace: "',
            //             '", key: "',
            //             '") {
            //                 value
            //             }
            //         }
            //     }'
            // ],
            "insertGoogleStatusTags" => [
                'mutation tagsAdd($id: ID!, $tags: [String!]!) {
                    tagsAdd(id: $id, tags: $tags) {
                        node {
                            id
                        }
                    }
                }'
            ],
            "removeGoogleStatusTags" => [
                'mutation tagsRemove($id: ID!, $tags: [String!]!) {
                    tagsRemove(id: $id, tags: $tags) {
                        node {
                            id
                        }
                    }
                }'
            ],
        ]
    ],
    // constant string used in the app
    'strings' => [
        "graphQlProductIdentifier" => "gid://shopify/Product/",
        "graphQlVarientIdentifier" => "gid://shopify/ProductVariant/",
        "privateMetaFieldsPrefix" => env('APP_URL'),
        "googleStatusApproved" => "Approved",
        "googleStatusDisapproved" => "Disapproved",
        "googleStatusPending" => "Pending",
        'app_include' => [
            "\n{% comment %}//alpha Google Feed Start{% endcomment %}\n",
            "\n {% comment %}//alpha Google Feed End{% endcomment %}\n"
        ],
        'app_start_identifier' => "\n{% comment %}//alpha Google Feed Start{% endcomment %}\n",
        'app_end_identifier' => "\n {% comment %}//alpha Google Feed End{% endcomment %}\n",
        'app_include_before_tag' => "<meta",
        'theme_liquid_file' => "layout/theme.liquid",
        'userEmailAddress' => env('MAIL_USERNAME')
    ],
    'plans' => [ 'Basic','Small','Medium','Ultimate' ]

    // 'assets' => [
    //     'flags' => [
    //         "key" => "assets/alphaCurrencyFlags.png",
    //         "src" => "assets/images/flags.png"
    //     ],
    //     'css' => [
    //         "key" => "assets/alphaCurrencyCss.css",
    //         "src" => "assets/css/css.css"
    //     ]
    //     ],
    // 'defaults' => [
    //     'default_countries' => [
    //         'USD','EUR','GBP','CAD','AUD'
    //     ]
    // ]
];
