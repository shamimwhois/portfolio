<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Social channels
    |--------------------------------------------------------------------------
    |
    | The channels an admin can configure from Admin → Settings → Social.
    | Each channel defines its label, icon name, brand accent, an optional
    | url prefix/suffix transform, and the placeholder shown in the URL field.
    | Channels with a URL (and enabled) render in the site footer.
    |
    */

    'channels' => [
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'color' => '#1877f2',
            'placeholder' => 'https://facebook.com/username',
        ],
        'github' => [
            'label' => 'GitHub',
            'icon' => 'github',
            'color' => '#333333',
            'placeholder' => 'https://github.com/username',
        ],
        'telegram' => [
            'label' => 'Telegram',
            'icon' => 'telegram',
            'color' => '#229ed9',
            'placeholder' => 'https://t.me/username',
        ],
        'whatsapp' => [
            'label' => 'WhatsApp',
            'icon' => 'whatsapp',
            'color' => '#25d366',
            'placeholder' => 'https://wa.me/19999999999',
        ],
        'x' => [
            'label' => 'X (Twitter)',
            'icon' => 'x',
            'color' => '#111111',
            'placeholder' => 'https://x.com/username',
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'icon' => 'linkedin',
            'color' => '#0a66c2',
            'placeholder' => 'https://linkedin.com/in/username',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon' => 'instagram',
            'color' => '#e1306c',
            'placeholder' => 'https://instagram.com/username',
        ],
        'youtube' => [
            'label' => 'YouTube',
            'icon' => 'youtube',
            'color' => '#ff0000',
            'placeholder' => 'https://youtube.com/@channel',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedded social feed
    |--------------------------------------------------------------------------
    |
    | An optional third-party embed snippet (e.g. an Instagram post embed or an
    | X timeline script) rendered in a home-page section when enabled.
    |
    */

    'embed' => [
        'enabled' => false,
        'title' => 'From the community',
        'html' => '',
    ],

];
