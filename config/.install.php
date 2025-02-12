<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Файл конфигурации установки модуля.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    'use'         => BACKEND,
    'id'          => 'gm.be.site_markup',
    'name'        => 'Visual editor',
    'description' => 'Website page markup',
    'namespace'   => 'Gm\Backend\SiteMarkup',
    'path'        => '/gm/gm.be.site_markup',
    'route'       => 'site-markup',
    'routes'      => [
        [
            'type'    => 'crudSegments',
            'options' => [
                'module'   => 'gm.be.site_markup',
                'route'    => 'site-markup',
                'prefix'   => BACKEND,
                'constraints' => ['id'],
                'defaults' => [
                    'controller' => 'panel'
                ]
            ]
        ]
    ],
    'locales'     => ['ru_RU', 'en_GB'],
    'permissions' => ['any', 'view', 'info'],
    'events'      => ['gm.be.articles:onGridView'],
    'required'    => [
        ['php', 'version' => '8.2'],
        ['app', 'code' => 'GM CMS']
    ]
];
