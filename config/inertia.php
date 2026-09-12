<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Server-Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configure if and how Inertia uses Server-Side Rendering
    | to pre-render the initial visits made to your application's pages.
    |
    */

    'ssr' => [

        'enabled' => true,

        'url' => 'http://127.0.0.1:13714',

    ],

    /*
    |--------------------------------------------------------------------------
    | Page Component Paths
    |--------------------------------------------------------------------------
    |
    | The `page_paths` and `page_extensions` options define where to look
    | for page components and which file extensions to consider.
    |
    */

    'ensure_pages_exist' => false,

    'page_paths' => [
        resource_path('js/pages'),
        resource_path('js/Pages'),
    ],

    'page_extensions' => [
        'js',
        'jsx',
        'svelte',
        'ts',
        'tsx',
        'vue',
    ],

    'use_script_element_for_initial_page' => (bool) env('INERTIA_USE_SCRIPT_ELEMENT_FOR_INITIAL_PAGE', false),

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Inertia components on the
    | filesystem when testing.
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => [
            resource_path('js/pages'),
            resource_path('js/Pages'),
        ],

        'page_extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

];
