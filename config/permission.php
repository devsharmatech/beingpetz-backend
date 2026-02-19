<?php

return [
    'menu_items' => [
        'dashboard' => [
            'route' => 'admin.dashboard', 
            'icon' => 'fas fa-tachometer-alt', 
            'label' => 'Dashboard'
        ],
        'categories' => [
            'route' => 'admin.categories.index', 
            'icon' => 'fas fa-list-alt', 
            'label' => 'Category'
        ],
        'blogs' => [
            'route' => 'admin.blogs.index', 
            'icon' => 'fas fa-blog', 
            'label' => 'Blogs'
        ],
        'events' => [
            'route' => 'admin.events.list', 
            'icon' => 'fas fa-calendar-alt', 
            'label' => 'Events'
        ],
        'pets' => [
            'route' => 'admin.pets.list', 
            'icon' => 'fas fa-paw', 
            'label' => 'Pets'
        ],
        'parents' => [
            'route' => 'admin.parents.index', 
            'icon' => 'fas fa-user-friends', 
            'label' => 'Parents'
        ],
        'uservendors' => [
            'route' => 'admin.uservendors.index', 
            'icon' => 'fas fa-user-friends', 
            'label' => 'Users & Vendors'
        ],
        'community' => [
            'route' => 'admin.community.index', 
            'icon' => 'fas fa-users', 
            'label' => 'Community'
        ],
        'services' => [
            'route' => 'admin.services.index', 
            'icon' => 'fas fa-concierge-bell', 
            'label' => 'Services'
        ],
        'banner' => [
            'route' => 'admin.banner.index', 
            'icon' => 'fas fa-images', 
            'label' => 'Banner'
        ],
        'service-banner' => [
            'route' => 'admin.service-banner.index', 
            'icon' => 'fas fa-ad', 
            'label' => 'Service Banner'
        ],
        'post' => [
            'route' => 'admin.post.index', 
            'icon' => 'fas fa-newspaper', 
            'label' => 'Post Manager'
        ],
        'reports' => [
            'route' => 'admin.reports.index', 
            'icon' => 'fas fa-flag', 
            'label' => 'Reported Manager'
        ],
        'messages' => [
            'route' => 'admin.messages.index', 
            'icon' => 'fas fa-comments', 
            'label' => 'Messages Manager'
        ],
        'notifications' => [
            'route' => 'admin.notifications.index', 
            'icon' => 'fas fa-bell', 
            'label' => 'Push Notification'
        ],
        'settings' => [
            'route' => 'admin.settings.index', 
            'icon' => 'fas fa-cogs', 
            'label' => 'Settings'
        ],
    ],

    'default_permissions' => [
        'admin' => ['*'], // All permissions
        'vendor' => ['dashboard', 'services', 'banner', 'service-banner'],
        'user' => ['dashboard', 'pets', 'parents', 'community'],
    ],
];