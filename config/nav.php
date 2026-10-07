<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public site navigation
    |--------------------------------------------------------------------------
    |
    | Each item may define:
    |   label    - visible text
    |   route    - named route (resolved via route())
    |   anchor   - optional #fragment appended to the route URL
    |   role     - visibility gate: any (default), guest, auth, admin, customer
    |   icon     - optional icon name rendered in the mobile panel
    |   section  - grouping key for the mobile drawer (explore | connect | account);
    |              a divider + label is drawn before the first item of a section
    |   desktop  - when true, the item also appears in the desktop nav (curated
    |              set — everything else lives in the mobile drawer)
    |
    */

    'primary' => [
        ['label' => 'About', 'route' => 'home', 'anchor' => 'about', 'icon' => 'user', 'section' => 'explore', 'desktop' => true],
        ['label' => 'Projects', 'route' => 'home', 'anchor' => 'projects', 'icon' => 'folder', 'section' => 'explore', 'desktop' => true],
        ['label' => 'Packages', 'route' => 'home', 'anchor' => 'stack', 'icon' => 'box', 'section' => 'explore'],
        ['label' => 'Service List', 'route' => 'servicelist', 'icon' => 'receipt', 'section' => 'explore', 'desktop' => true],
        ['label' => 'Group Buy', 'route' => 'servicegroup', 'icon' => 'box', 'section' => 'explore', 'desktop' => true],
        ['label' => 'Skills', 'route' => 'home', 'anchor' => 'skills', 'icon' => 'chart', 'section' => 'explore'],
        ['label' => 'Plans', 'route' => 'plan', 'icon' => 'layers', 'section' => 'explore', 'desktop' => true],
        ['label' => 'Timeline', 'route' => 'home', 'anchor' => 'timeline', 'icon' => 'activity', 'section' => 'explore'],
        ['label' => 'Blog', 'route' => 'blog.index', 'icon' => 'doc', 'section' => 'connect', 'desktop' => true],
        ['label' => 'Feed', 'route' => 'feed', 'icon' => 'chat', 'section' => 'connect', 'desktop' => true],
        ['label' => 'FAQ', 'route' => 'home', 'anchor' => 'faq-section', 'icon' => 'chat', 'section' => 'connect'],
        ['label' => 'Contact', 'route' => 'contact', 'icon' => 'mail', 'section' => 'connect', 'desktop' => true],
        ['label' => 'Admin', 'route' => 'admin.dashboard', 'role' => 'admin', 'icon' => 'shield', 'section' => 'account'],
        ['label' => 'Panel', 'route' => 'panel.dashboard', 'role' => 'customer', 'icon' => 'dashboard', 'section' => 'account'],
        ['label' => 'Logout', 'route' => 'logout', 'logout' => true, 'icon' => 'logout', 'section' => 'account'],
        ['label' => 'Login', 'route' => 'login', 'role' => 'guest', 'icon' => 'user', 'section' => 'account'],
        ['label' => 'Register', 'route' => 'register', 'role' => 'guest', 'icon' => 'plus', 'section' => 'account'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public footer quick access
    |--------------------------------------------------------------------------
    */

    'footer' => [
        ['label' => 'Blog Nodes', 'route' => 'blog.index'],
        ['label' => 'Community Feed', 'route' => 'feed'],
        ['label' => 'Package Center', 'route' => 'home', 'anchor' => 'stack'],
        ['label' => 'Subscription Plans', 'route' => 'plan'],
        ['label' => 'Service List', 'route' => 'servicelist'],
        ['label' => 'Group Buy', 'route' => 'servicegroup'],
        ['label' => 'Admin Console', 'route' => 'admin.dashboard', 'role' => 'admin'],
        ['label' => 'Customer Panel', 'route' => 'panel.dashboard', 'role' => 'customer'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer panel sidebar
    |--------------------------------------------------------------------------
    */

    'panel' => [
        ['label' => 'Dashboard', 'route' => 'panel.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Start a Project', 'route' => 'panel.orders.create', 'icon' => 'plus'],
        ['label' => 'Billing', 'route' => 'panel.invoices', 'icon' => 'receipt'],
        ['label' => 'My Projects', 'route' => 'panel.projects', 'icon' => 'folder'],
        ['label' => 'Services', 'route' => 'panel.services', 'icon' => 'layers'],
        ['label' => 'Support', 'route' => 'panel.support', 'icon' => 'chat'],
        ['label' => 'Profile', 'route' => 'panel.profile', 'icon' => 'user'],
        ['label' => 'Admin Console', 'route' => 'admin.dashboard', 'icon' => 'shield', 'role' => 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin console sidebar
    |--------------------------------------------------------------------------
    */

    'admin' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
        ['label' => 'Users', 'route' => 'admin.users', 'icon' => 'users'],
        ['label' => 'Client Projects', 'route' => 'admin.client-projects', 'icon' => 'folder'],
        ['label' => 'Portfolio', 'icon' => 'sparkle', 'children' => [
            ['label' => 'Home (Hero)', 'route' => 'admin.settings.site', 'params' => ['section' => 'hero'], 'icon' => 'home'],
            ['label' => 'About', 'route' => 'admin.settings.site', 'params' => ['section' => 'about'], 'icon' => 'user'],
            ['label' => 'Timeline', 'route' => 'admin.timeline', 'icon' => 'activity'],
            ['label' => 'Skills', 'route' => 'admin.skills', 'icon' => 'chart'],
            ['label' => 'Selected Work', 'route' => 'admin.projects', 'icon' => 'folder'],
        ]],
        ['label' => 'Services', 'route' => 'admin.services', 'icon' => 'layers'],
        ['label' => 'Packages', 'route' => 'admin.packages', 'icon' => 'box'],
        ['label' => 'Group Buy', 'route' => 'admin.group-packages', 'icon' => 'box'],
        ['label' => 'Blog', 'route' => 'admin.blog', 'icon' => 'doc'],
        ['label' => 'Feed', 'route' => 'admin.feed', 'icon' => 'message'],
        ['label' => 'Invoices', 'route' => 'admin.invoices', 'icon' => 'receipt'],
        ['label' => 'FAQ', 'route' => 'admin.faqs', 'icon' => 'chat'],
        ['label' => 'Messages', 'route' => 'admin.messages', 'icon' => 'mail'],
        ['label' => 'Support', 'route' => 'admin.conversations', 'icon' => 'chat'],
        ['label' => 'Settings', 'icon' => 'settings', 'children' => [
            ['label' => 'Site', 'route' => 'admin.settings.site'],
            ['label' => 'SEO', 'route' => 'admin.settings.seo'],
            ['label' => 'Theme Manager', 'route' => 'admin.settings.appearance'],
            ['label' => 'AI Assistant', 'route' => 'admin.settings.ai'],
            ['label' => 'Payments', 'route' => 'admin.settings.payments'],
            ['label' => 'Social', 'route' => 'admin.settings.social'],
            ['label' => 'Integrations', 'route' => 'admin.settings.integrations'],
        ]],
    ],

];
