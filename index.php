<?php
require_once "controllers/template.controller.php";

$route = $_GET['route'] ?? 'home';

// Dependencias por módulo
$moduleDependencies = [
    'home' => [],
    'users' => [
        ['controller' => 'users.controller.php', 'model' => 'users.model.php']
    ],
    'verticals' => [
        ['controller' => 'verticals.controller.php', 'model' => 'vertical.model.php']
    ],
    'clients' => [
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php'],
        ['controller' => 'users.controller.php', 'model' => 'users.model.php'],
        ['controller' => 'verticals.controller.php', 'model' => 'vertical.model.php']
    ],
    'projects' => [
        ['controller' => 'projects.controller.php', 'model' => 'projects.model.php'],
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php']
    ],
    'platforms' => [
        ['controller' => 'platforms.controller.php', 'model' => 'platforms.model.php']
    ],
    'formats' => [
        ['controller' => 'formats.controller.php', 'model' => 'formats.model.php'],
        ['controller' => 'platforms.controller.php', 'model' => 'platforms.model.php']
    ],
    'objectives' => [
        ['controller' => 'objectives.controller.php', 'model' => 'objetives.model.php']
    ],
    'campaignTypes' => [
        ['controller' => 'campaignTypes.controller.php', 'model' => 'campaignTypes.model.php']
    ],
    'channels' => [
        ['controller' => 'channels.controller.php', 'model' => 'channels.model.php']
    ],
    'campaigns' => [
        ['controller' => 'campaigns.controller.php', 'model' => null],
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php'],
        ['controller' => 'projects.controller.php', 'model' => 'projects.model.php'],
        ['controller' => 'platforms.controller.php', 'model' => 'platforms.model.php'],
        ['controller' => 'formats.controller.php', 'model' => 'formats.model.php'],
        ['controller' => 'objectives.controller.php', 'model' => 'objetives.model.php'],
        ['controller' => 'periods.controller.php', 'model' => null]
    ],
    'periods' => [
        ['controller' => 'periods.controller.php', 'model' => null]
    ],
    'urls' => [
        ['controller' => 'urls.controller.php', 'model' => null],
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php'],
        ['controller' => 'projects.controller.php', 'model' => 'projects.model.php'],
        ['controller' => 'campaigns.controller.php', 'model' => null],
        ['controller' => 'periods.controller.php', 'model' => null]
    ],
    'comments' => [
        ['controller' => 'comments.controller.php', 'model' => null],
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php'],
        ['controller' => 'platforms.controller.php', 'model' => 'platforms.model.php'],
        ['controller' => 'periods.controller.php', 'model' => null]
    ],
    'mediaMixRealEstate' => [
        ['controller' => 'mediaMixRealEstate.controller.php', 'model' => 'mediaMixRealEstate.model.php'],
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php'],
        ['controller' => 'periods.controller.php', 'model' => null]
    ],
    'mediaMixRealEstateDetails' => [
        ['controller' => 'mediaMixRealEstateDetails.controller.php', 'model' => null],
        ['controller' => 'projects.controller.php', 'model' => 'projects.model.php'],
        ['controller' => 'objectives.controller.php', 'model' => 'objetives.model.php'],
        ['controller' => 'platforms.controller.php', 'model' => 'platforms.model.php'],
        ['controller' => 'channels.controller.php', 'model' => 'channels.model.php'],
        ['controller' => 'formats.controller.php', 'model' => 'formats.model.php'],
        ['controller' => 'campaignTypes.controller.php', 'model' => 'campaignTypes.model.php']
    ],
    'mediaMixEcommerce' => [
        ['controller' => 'mediaMixEcommerce.controller.php', 'model' => null],
        ['controller' => 'clients.controller.php', 'model' => 'clients.model.php'],
        ['controller' => 'periods.controller.php', 'model' => null]
    ],
    'mediaMixEcommerceDetails' => [
        ['controller' => 'mediaMixEcommerceDetails.controller.php', 'model' => null],
        ['controller' => 'projects.controller.php', 'model' => 'projects.model.php'],
        ['controller' => 'objectives.controller.php', 'model' => 'objetives.model.php'],
        ['controller' => 'platforms.controller.php', 'model' => 'platforms.model.php'],
        ['controller' => 'channels.controller.php', 'model' => 'channels.model.php'],
        ['controller' => 'formats.controller.php', 'model' => 'formats.model.php'],
        ['controller' => 'campaignTypes.controller.php', 'model' => 'campaignTypes.model.php']
    ],
    'mediaMixOthers' => [
        ['controller' => 'mediaMixOthers.controller.php', 'model' => 'mediaMixOthers.model.php']
    ]
];

if (isset($moduleDependencies[$route])) {
    foreach ($moduleDependencies[$route] as $dep) {
        require_once "controllers/" . $dep['controller'];
        if (!empty($dep['model'])) {
            require_once "models/" . $dep['model'];
        }
    }
}

$template = new ControllerTemplate();
$template -> ctrTemplate();
