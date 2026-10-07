<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root class namespace for Livewire component classes in
    | your application.
    |
    */

    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value is used to specify where Livewire component Blade templates are
    | stored when running `artisan make:livewire`.
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | The default layout view that will be used when a component has no layout
    | declared via the #[Layout] attribute.
    |
    */

    'layout' => 'components.layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Livewire Assets URL / Injection
    |--------------------------------------------------------------------------
    |
    | `inject_assets` toggles whether Livewire injects its own scripts/styles.
    | `asset_url` overrides the base URL Livewire uses when resolving its assets.
    | `csp_safe` embeds Livewire's scripts without an external <script> tag so
    | the app can ship a Content-Security-Policy header.
    |
    */

    'inject_assets' => true,
    'asset_url' => null,
    'csp_safe' => false,

    /*
    |--------------------------------------------------------------------------
    | Navigate
    |--------------------------------------------------------------------------
    |
    | Controls the "navigate" SPA progress bar injected by Livewire.
    |
    */

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire stores uploads in a temporary directory before the application
    | stores them. The framework default `max:12288` temp rule silently rejects
    | any file over 12 MB — the feed allows 64 MB videos and 20 MB documents, so
    | the cap is raised to 64 MB to match.
    |
    | PHP's own upload_max_filesize / post_max_size must also allow this size
    | (see public/.htaccess and the README "Feed media" note for local dev).
    |
    */

    'temporary_file_upload' => [
        'disk' => null, // defaults to the application's default filesystem
        'rules' => ['required', 'file', 'max:65536'], // 64 MB
        'directory' => null,
        'middleware' => 'throttle:60,1',
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
            // The feed accepts these containers too — without them temporaryUrl()
            // throws FileNotPreviewableException for videos in these formats.
            'webm', 'ogg', 'ogv',
        ],
        'max_upload_time' => 5,
        'cleanup' => true,
        'cleanup_interval' => 1440, // in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Render On Redirect
    |--------------------------------------------------------------------------
    |
    | Whether Livewire renders a view before redirecting.
    |
    */

    'render_on_redirect' => false,

    /*
    |--------------------------------------------------------------------------
    | Eloquent Model Binding (legacy)
    |--------------------------------------------------------------------------
    |
    | Previous Livewire versions bound directly to model properties via
    | wire:model by default; explicit wire:model.live is preferred.
    |
    */

    'legacy_model_binding' => false,

    /*
    |--------------------------------------------------------------------------
    | Pagination Theme
    |--------------------------------------------------------------------------
    |
    | Default pagination theme for the `livewire:tailwind` / `livewire:bootstrap`
    | pagination views.
    |
    */

    'pagination_theme' => 'tailwind',
];
