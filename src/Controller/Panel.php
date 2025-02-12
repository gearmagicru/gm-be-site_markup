<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\SiteMarkup\Controller;

use Gm;
use Gm\Helper\Url;
use Gm\Panel\Http\Response;
use Gm\Panel\Widget\TabWidget;
use Gm\Panel\Controller\BaseController;

/**
 * Контроллер панели визуального редактора.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\SiteMarkup\Controller
 * @since 1.0
 */
class Panel extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function createWidget(): TabWidget
    {
        /** @var string $frameUrl URL-адрес фрейма */
        $frameUrl = Gm::$app->request->getQuery('url', Url::home());

        /** @var TabWidget $tab */
        $tab = new TabWidget();

        // панель вкладки компонента (Ext.tab.Panel Ext JS)
        $tab->id = $this->module->viewId('tab'); 
        $tab->title = '#{name}';
        $tab->tooltip = [
            'icon'  => $tab->imageSrc('/icon.svg'),
            'title' => '#{name}',
            'text'  => '#{description}'
        ];
        $tab->icon = $tab->imageSrc('/icon_small.svg');

        // панель инструментов (Ext.toolbar.Toolbar Ext JS)
        $tab->lbar = [
            'padding'    => '0 2px 0 0',
            'controller' => 'gm-be-site_markup-panel',
            'cls'        => 'gm-site_markup__toolbar',
            'items'      => [
                [
                    'id'           => $this->module->viewId('btn-markup'),
                    'xtype'        => 'button',
                    'cls'          => 'gm-site_markup__btn',
                    'iconCls'      => 'g-icon-svg gm-site_markup__icon-ligh' . (Gm::$app->isViewMarkup() ? 'on' : 'off') . ' g-icon_size_button_medium',
                    'iconClsOff'   => 'gm-site_markup__icon-lighoff',
                    'iconClsOn'    => 'gm-site_markup__icon-lighon',
                    'iconAlign'    => 'top',
                    'margin'       => 0,
                    'enableToggle' => true,
                    'width'        => 40,
                    'pressed'      => Gm::$app->isViewMarkup(),
                    'tooltip'      => '#Enable / disable page markup',
                    'focusable'    => false,
                    'token'        => [
                        'name' => Gm::$app->request->markupCookieName,
                        'key'  => Gm::$app->request->markupValidationKey
                    ],
                    'listeners' => [
                        'toggle' => 'onSetMarkup',
                    ]
                ],
                [
                    'xtype'      => 'splitbutton',
                    'cls'        => 'gm-site_markup__btn',
                    'iconCls'    => 'g-icon-svg gm-site_markup__icon-refresh g-icon_size_24',
                    'iconAlign'  => 'top',
                    'arrowAlign' => 'right',
                    'tooltip'    => '#Refresh page',
                    'focusable'  => false,
                    'width'      => 55,
                    'margin'     => '1px 0 0 0',
                    'handler'    => 'onFrameReload',
                    'menuAlign'  => 'tr',
                    'menu'       => [
                        'items' => [
                            'xtype'       => 'form',
                            'cls'         => 'g-form-filter',
                            'flex'        => 1,
                            'width'       => 400,
                            'autoHeight'  => true,
                            'bodyPadding' => '5',
                            'items' => [
                                [
                                    'id'         => 'gm-site_markup__url',
                                    'xtype'      => 'textfield',
                                    'fieldLabel' => 'URL',
                                    'labelAlign' => 'right',
                                    'tooltip'    => '#Enter the URL of your website in the bar and click the "Go" button',
                                    'labelWidth' => 45,
                                    'value'      => $frameUrl,
                                    'anchor'     => '100%',
                                    
                                ]
                            ],
                            'buttons'     => [
                                [
                                    'text'    => '#Home',
                                    'homeUrl' =>  Url::home(),
                                    'handler' => 'onHome'
                                ],
                                [
                                    'text'      => '#Go',
                                    'homeUrl'   =>  Url::home(),
                                    'msgBadUrl' => '#You have entered the URL incorrectly',
                                    'handler'   => 'onApplyURL'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'xtype'  => 'container',
                    'layout' => 'hbox',
                    'items'  => [
                        [
                            'id'         => $this->module->viewId('nav-back'),
                            'xtype'      => 'button',
                            'cls'        => 'gm-site_markup__btn',
                            'iconCls'    => 'g-icon-svg gm-site_markup__icon-back g-icon_size_24',
                            'iconAlign'  => 'top',
                            'tooltip'    => '#Back to previous page',
                            'focusable'  => false,
                            'disabled'   => true,
                            'handler'    => 'onBackUrl',
                        ],
                        [
                            'id'         => $this->module->viewId('nav-forward'),
                            'xtype'      => 'button',
                            'cls'        => 'gm-site_markup__btn',
                            'iconCls'    => 'g-icon-svg gm-site_markup__icon-forward g-icon_size_24',
                            'iconAlign'  => 'top',
                            'tooltip'    => '#To next page',
                            'focusable'  => false,
                            'disabled'   => true,
                            'handler'    => 'onForwardUrl',
                        ]
                    ]
                ],                
                [
                    'id'         => 'gm-site_markup__btn-article',
                    'disabled'   =>  !Gm::$app->isViewMarkup(),
                    'xtype'      => 'splitbutton',
                    'cls'        => 'gm-site_markup__btn',
                    'iconCls'    => 'g-icon-svg gm-site_markup__icon-article g-icon_size_24',
                    'iconAlign'  => 'top',
                    'arrowAlign' => 'right',
                    'focusable'  => false,
                    'width'      => 40,
                    'margin'     => '1px 0 0 0',
                    'tooltip'    => '#Edit article',
                    'msgError'   => '#Current website page article not found!',
                    'route'      => '@backend/articles/form/view',
                    'handler'    => 'onEditArticle',
                    'menuAlign'  => 'tr',
                    'menu'       => [
                        'title' => '#Article',
                        'items' => [
                            [
                                'text'     => '#Edit article',
                                'iconCls'  => 'gm-site_markup__icon-edit',
                                'msgError' => '#Current website page article not found!',
                                'route'    => '@backend/articles/form/view',
                                'handler'  => 'onEditArticle',
                            ],
                            '-',
                            [
                                'text'    => '#Add an article',
                                'iconCls' => 'gm-site_markup__icon-article-add',
                                'route'   => '@backend/articles/form',
                                'handler' => 'onAddArticle'
                            ],
                            [
                                'text'       => '#Delete article',
                                'iconCls'    => 'gm-site_markup__icon-article-delete',
                                'msgError'   => '#Current website page article not found!',
                                'msgConfirm' => '#Are you sure you want to delete the article?',
                                'route'      => '@backend/articles/form/delete',
                                'handler'    => 'onDeleteArticle',
                            ],
                            '-',
                            [
                                'text'    => '#All acticles',
                                'iconCls' => 'gm-site_markup__icon-article-all',
                                'route'   => '@backend/articles',
                                'handler' => 'onLoadWidget'
                            ]
                        ]
                    ]
                ],
                [
                    'id'         => 'gm-site_markup__btn-acategory',
                    'disabled'   => !Gm::$app->isViewMarkup(),
                    'xtype'      => 'splitbutton',
                    'cls'        => 'gm-site_markup__btn',
                    'iconCls'    => 'g-icon-svg gm-site_markup__icon-acategory g-icon_size_24',
                    'iconAlign'  => 'top',
                    'arrowAlign' => 'right',
                    'focusable'  => false,
                    'width'      => 40,
                    'margin'     => '1px 0 0 0',
                    'tooltip'    => '#Edit category',
                    'msgError'   => '#Current website page category not found!',
                    'route'      => '@backend/article-categories/form/view',
                    'handler'    => 'onEditCategory',
                    'menuAlign'  => 'tr',
                    'menu'       => [
                        'title' => '#Article category',
                        'items' => [
                            [
                                'text'     => '#Edit category',
                                'iconCls'  => 'gm-site_markup__icon-edit',
                                'msgError' => '#Current website page category not found!',
                                'route'    => '@backend/article-categories/form/view',
                                'handler'  => 'onEditCategory',
                            ],
                            '-',
                            [
                                'text'    => '#Add an category',
                                'iconCls' => 'gm-site_markup__icon-acategory-add',
                                'route'   => '@backend/article-categories/form',
                                'handler' => 'onAddCategory'
                            ],
                            [
                                'text'       => '#Delete category',
                                'iconCls'    => 'gm-site_markup__icon-acategory-delete',
                                'msgError'   => '#Current website page category not found!',
                                'msgConfirm' => '#Are you sure you want to delete the category?',
                                'route'      => '@backend/article-categories/form/delete',
                                'handler'    => 'onDeleteCategory',
                            ],
                            '-',
                            [
                                'text'    => '#All categories',
                                'iconCls' => 'gm-site_markup__icon-acategory-all',
                                'route'   => '@backend/article-categories',
                                'handler' => 'onLoadWidget'
                            ]
                        ]
                    ]
                ],
                [
                    'id'         => 'gm-site_markup__btn-seo',
                    'disabled'   => !Gm::$app->isViewMarkup(),
                    'xtype'      => 'splitbutton',
                    'cls'        => 'gm-site_markup__btn',
                    'iconCls'    => 'g-icon-svg gm-site_markup__icon-seo g-icon_size_24',
                    'iconAlign'  => 'top',
                    'arrowAlign' => 'right',
                    'focusable'  => false,
                    'width'      => 40,
                    'margin'     => '1px 0 0 0',
                    'tooltip'    => '#Search Engine Optimization',
                    'route'      => '@backend/config/page',
                    'handler'    => 'onLoadWidget',
                    'menuAlign'  => 'tr',
                    'menu'       => [
                        'title' => '#Search Engine Optimization',
                        'items' => [
                            [
                                'text'     => '#Information about the site',
                                'iconCls'  => 'gm-site_markup__icon-edit',
                                'route'    => '@backend/config/page',
                                'handler'  => 'onLoadWidget',
                            ]
                        ]
                    ]
                ],
                [
                    'id'         => 'gm-site_markup__btn-cmps',
                    'disabled'   => !Gm::$app->isViewMarkup(),
                    'xtype'      => 'splitbutton',
                    'cls'        => 'gm-site_markup__btn',
                    'iconCls'    => 'g-icon-svg gm-site_markup__icon-components g-icon_size_24',
                    'iconAlign'  => 'top',
                    'arrowAlign' => 'right',
                    'focusable'  => false,
                    'width'      => 40,
                    'margin'     => '1px 0 0 0',
                    'tooltip'    => '#Components on the current page',
                    'menuAlign'  => 'tr',
                    'menu'       => [
                        'width' => 250,
                        'title' => '#Components on the current page',
                        'items' => []
                    ]
                ],
                [
                    'id'         => 'gm-site_markup__btn-blocks',
                    'disabled'   => !Gm::$app->isViewMarkup(),
                    'xtype'      => 'splitbutton',
                    'cls'        => 'gm-site_markup__btn',
                    'iconCls'    => 'g-icon-svg gm-site_markup__icon-blocks g-icon_size_24',
                    'iconAlign'  => 'top',
                    'arrowAlign' => 'right',
                    'focusable'  => false,
                    'width'      => 40,
                    'margin'     => '1px 0 0 0',
                    'tooltip'    => '#Fragments of the current page',
                    'menuAlign'  => 'tr',
                    'menu'       => [
                        'width' => 250,
                        'title' => '#Fragments of the current page',
                        'items' => []
                    ]
                ],
                [
                    'xtype'     => 'button',
                    'cls'       => 'gm-site_markup__btn',
                    'iconCls'   => 'g-icon-svg gm-site_markup__icon-blank g-icon_size_button_medium',
                    'iconAlign' => 'top',
                    'margin'    => '1px 0 0 0',
                    'width'     => 40,
                    'tooltip'   => '#Open page in new browser tab',
                    'focusable' => false,
                    'handler'   => 'onOpenWindow'
                ],
                [
                    'xtype'     => 'button',
                    'cls'       => 'gm-site_markup__btn',
                    'iconCls'   => 'g-icon-svg gm-site_markup__icon-help g-icon_size_button_medium',
                    'iconAlign' => 'top',
                    'margin'    => '1px 0 0 0',
                    'width'     => 40,
                    'tooltip'   => '#Help',
                    'focusable' => false,
                    'route'     => '@backend/guide/modal/view?component=module:' . $this->module->getId() . '&subject=panel',
                    'handler'   => 'onLoadWidget'
                ],
            ]
        ];


        // контейнер (Ext.container.Container Ext JS)
        $tab->items = [
            'xtype'      => 'container',
            'layout'     => 'fit',
            'cls'        => 'g-panel_background',
            'controller' =>'gm-be-site_markup-frame',
            'items'      => [
                'id'    => $this->module->viewId('frame'),
                'xtype' => 'g-iframe',
                'src'   => $frameUrl,
                'listeners' => [
                    'load'      => 'onFrameLoad',
                    'changeurl' => 'onFrameChangeUrl'
                ]
            ]
        ];

        $tab
            ->setNamespaceJS('Gm.be.site_markup')
            ->addRequire('Gm.view.IFrame')
            ->addRequire('Gm.be.site_markup.PanelController')
            ->addRequire('Gm.be.site_markup.FrameController')
            ->addCss('/panel.css');
        return $tab;
    }

    /**
     * Действие "index" выводит интерфейса панели.
     * 
     * @return Response
     */
    public function indexAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var TabWidget $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }
}
