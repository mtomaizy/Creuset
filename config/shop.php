<?php

return [

    'currency'          => env('SHOP_CURRENCY', 'GBP'),
    'currency_symbol'   => env('SHOP_CURRENCY_SYMBOL', '&pound;'),

    'products_per_page' => env('PRODUCTS_PER_PAGE', 8),

    /*
     * Must be a multiple of 12
     */
    'products_per_row'  => env('PRODUCTS_PER_ROW', 4),

    /*
     * Number of minutes a pending cart will be regarded as abandoned
     */
    'order_time_limit'  => env('ORDER_TIME_LIMIT', 15),

];
