<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'scopes' => [
        'https://www.googleapis.com/auth/content',
        'https://www.googleapis.com/auth/siteverification'
    ],
    'contentApis' => [
        'getMainMerchantAccount' => [
            "https://www.googleapis.com/content/v2.1/accounts/authinfo?key="
        ],
        'getAccountInfo' => [
            "https://shoppingcontent.googleapis.com/content/v2.1/",
            "/accounts/",
            "?key="
        ],
        'getSubMerchantAccounts' => [
            'https://www.googleapis.com/content/v2.1/',
            "/accounts?key="
        ],
        'getAllProducts' => [
            'https://www.googleapis.com/content/v2.1/',
            "/products?key="
        ],
        'addProduct' => [
            'https://shoppingcontent.googleapis.com/content/v2.1/',
            '/products?key='
        ],
        'addBulkProducts' => [
            'https://shoppingcontent.googleapis.com/content/v2.1/products/batch'
        ],
        'removeBulkProducts' => [
            'https://shoppingcontent.googleapis.com/content/v2.1/products/batch'
        ],
        'updateAccountInfo' => [
            'https://www.googleapis.com/content/v2.1/',
            '/accounts/',
            '?key='
        ],
        'updateShippingSettings' => [
            "https://shoppingcontent.googleapis.com/content/v2.1/",
            "/shippingsettings/",
            "?key="
        ],
        'claimWebsite' => [
            'https://www.googleapis.com/content/v2.1/',
            '/accounts/',
            '/claimwebsite?key='
        ],
        'refreshToken' => [
            'https://oauth2.googleapis.com/token'
        ],
        'revokeToken' => [
            'https://oauth2.googleapis.com/revoke?token='
        ],
        'getStatuses' => [
            'https://shoppingcontent.googleapis.com/content/v2.1/productstatuses/batch'
        ],
        'getProduct' => [
            'https://shoppingcontent.googleapis.com/content/v2.1/',
            '/products/',
            '?key='
        ]
    ],
    'siteVerificationApis' => [
        'getSiteVerificationToken' => [
            'https://www.googleapis.com/siteVerification/v1/token?key='
        ],
        'verifySite' => [
            'https://www.googleapis.com/siteVerification/v1/webResource?verificationMethod=META&key='
        ]
    ],
    'strings' => [
       'AutomaticShippingName' => 'Free Shipping By ALPHA',
       'AutomaticShippingLabel' => 'Free Shipping'
    ]
];
