<?php

return [

    /**
     * Configure the EDD Lumen key to make secure call
     *
     */
    'edd_key' => env('EDD_KEY'),
    'app_wpdrift_url' => env('APP_WPDRIFT_URL'),
    Jenssegers\Mongodb\MongodbQueueServiceProvider::class,
];
