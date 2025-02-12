<?php
/**
 * Модуль веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\SiteMarkup;

/**
 * Модуль визуального редактора.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\SiteMarkup
 * @since 1.0
 */
class Module extends \Gm\Panel\Module\Module
{
    /**
     * {@inheritdoc}
     */
    public string $id = 'gm.be.site_markup';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        $self = $this;

        // событие перед выводом параметров в шаблон workspace
        $this->on('gm.be.articles:onGridView', function ($module, $widget) use ($self) {
            $widget->grid->popupMenu['width'] = 240;
            $widget->grid->popupMenu['items'][] = '-';
            $widget->grid->popupMenu['items'][] = [
                'text'    => $self->t('Open in visual editor'),
                'icon'    => $self->getAssetsUrl() . '/images/icon_small.svg',
                'handler' => 'loadWidget',
                'handlerArgs' => [
                      'route'   => '@backend/site-markup?url={url}',
                      'pattern' => 'grid.popupMenu.activeRecord'
                  ]
            ];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function controllerMap(): array
    {
        return [
            'settings' => 'MarkupSettings',
            'block'    => 'MarkupBlock'
        ];
    }
}
