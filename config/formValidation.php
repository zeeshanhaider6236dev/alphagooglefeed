<?php
return [
    'syncForm' => [
        'country'               =>  [ 'required', 'exists:countries,code' ],
        "shipping"              =>  [ "required", "string", "in:auto,manual" ],
        "productIdFormat"       =>  [ "required", "string", "in:global,sku,variant" ],
        "whichProducts"         =>  [ "required", "string", "in:all,collection" ],
        "collectionType"        =>  [ "required_if:whichProducts,collection", "string", "in:auto,custom" ],
        'collectionsId'         =>  [ "required_if:whichProducts,collection", "integer" ],
        'productTitle'          =>  [ 'required', "string", 'in:default,seo' ],
        "productdescription"    =>  [ 'required', "string", 'in:default,seo' ],
        "variantSubmission"     =>  [ "required", "string", "in:first,all" ],
        'gtinSubmission'        =>  [ "nullable", "in:1", 'integer' ],
        "salePrice"             =>  [ "nullable", "in:1", 'integer' ],
        "secondImage"           =>  [ "nullable", "in:1", 'integer' ],
        "additionalImages"      =>  [ "nullable", "in:1", 'integer' ],
        "product_category_id"   =>  [ "nullable", "exists:product_categories,id", "integer" ],
        "ageGroup"              =>  [ "required", "in:newborn,infant,toddler,kids,adult", 'string' ],
        "gender"                =>  [ "required", "in:male,female,unisex", 'string' ],
        "productCondition"      =>  [ "required", "in:new,refurbished,used", 'string']
    ],
    'SyncNowForm' => [
        "variantId"             =>  [ "required", "regex:/^[0-9]{1,20}\d:[0-9]{1,20}\d$/"]
    ],
    'PupdateForm' => [
        'title'                 =>  [ 'nullable', "string", "max:150" ],
        'description'           =>  [ 'nullable', 'string', "max:4990"],
        'product_category_id'   =>  [ 'nullable', 'exists:product_categories,id', 'integer' ],
        "ageGroup"              =>  [ "required", "in:newborn,infant,toddler,kids,adult", 'string' ],
        "gender"                =>  [ "required", "in:male,female,unisex", 'string' ],
        "productCondition"      =>  [ "required", "in:new,refurbished,used", 'string'],
        "customLabel"           =>  [ "nullable", "array", "max:5"],
        "customLabel.*"         =>  [ "required", "string", "max:191"],
        'products'              =>  [ 'required', 'array' ],
        'products.*'            =>  [ 'required', 'integer' ]
    ],
    'PdeleteForm' => [
        'id'             =>  [ "required", "exists:shop_products,productId" ]
    ]
];