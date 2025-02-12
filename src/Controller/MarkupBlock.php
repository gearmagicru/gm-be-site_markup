<?php
/**
 * Этот файл является частью расширения модуля веб-приложения GearMagic.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

namespace Gm\Backend\SiteMarkup\Controller;

use Gm;
use Gm\Stdlib\BaseObject;
use Gm\Panel\Widget\Form;
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Widget\EditWindow;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер изменения текста блока (фрагмента) страницы.
 * 
 * Действия контроллера:
 * - view, вывод интерфейса формы с фрагментом текста;
 * - update, изменение фрагмента текста.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\SiteMarkup\Controller
 * @since 1.0
 */
class MarkupBlock extends FormController
{
    /**
     * {@inheritdoc}
     */
    protected string $defaultModel = 'MarkupBlock';

    /**
     * Виджет редактора.
     * 
     * @see MarkupBlock::getEditorWidget()
     * 
     * @var BaseObject
     */
    protected BaseObject $editor;

    /**
     * Возвращает виджет редактора.
     * 
     * @return BaseObject
     */
    protected function getEditorWidget(): BaseObject
    {
        if (!isset($this->editor)) {
            $this->editor = Gm::$app->widgets->get('gm.wd.codemirror', [
                'fileExtension' => 'html'
            ]);
        }
        return $this->editor;
    }

    /**
     * {@inheritdoc}
     */
    public function createWidget(): EditWindow
    {
        /** @var EditWindow $window */
        $window = parent::createWidget();

        /** @var null|\Gm\View\Widget|\Gm\Stdlib\BaseObject $editor */
        $editor = $this->getEditorWidget();
        if ($editor) {
            /** @var array $content */
            $content = $editor->run();
            $content['name'] = 'html';
        } else {
            $content = [
                'xtype'  => 'textarea',
                'name'   => 'html',
                'anchor' => '100% 100%'
            ];
        }

        // панель формы (Gm.view.form.Panel GmJS)
        $window->form->autoScroll = true;
        $window->form->router->route = Gm::alias('@match', '/markup-block');
        $window->form->items = [
            $content,
            [
                'xtype' => 'hidden',
                'name'  => 'id'
            ],
            [
                'xtype' => 'hidden',
                'name'  => 'calledFrom'
            ],
            [
                'xtype' => 'hidden',
                'name'  => 'title'
            ]
        ];
        $window->form->router = [
            'id'    => '0',
            'route' => Gm::alias('@match', '/block'),
            'state' => Form::STATE_CUSTOM,
            'rules' => [
                'update' => '{route}/update/{id}',
                'data'   => '{route}/data/{id}'
                ]
        ];
        $window->form->loadDataAfterRender = false;
        $window->form->buttons = ExtForm::buttons([
            'help' => ['subject' => 'markupblock'], 'save', 'cancel'
        ]);

        // окно компонента (Ext.window.Window Sencha ExtJS)
        $window->iconCls = 'g-icon-svg g-icon-m_edit';
        $window->width = 600;
        $window->height = 400;
        $window->resizable = true;
        $window->maximizable = true;
        $window->layout = 'fit';
        return $window;
    }

    /**
     * Действие "view" выводит интерфейс формы с фрагментом текста.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var array $post Параметры запроса */
        $post = $request->getPost(['id', 'html', 'title', 'calledFrom']);
        foreach ($post as $key => $value) {
            if ($value === null) {
                $response
                    ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', [$key]));
                return $response;
            }
            if ($key === 'id' && empty($value)) {
                $response
                    ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', [$key]));
                return $response;
            }
        }

        /** @var false|EditWindow $widget */
        $widget = $this->getWidget();
        // если была ошибка при формировании виджета
        if ($widget === false) {
            return $response;
        }

        $widget->title = $this->module->t('#{block.title}', [$post['title']]);
        $widget->titleTpl = $widget->title;
        $widget->form->items[0]['value'] = $post['html'];
        $widget->form->items[1]['value'] = $post['id'];
        $widget->form->items[2]['value'] = $post['calledFrom'];
        $widget->form->items[3]['value'] = $post['title'];
        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);

        /** @var null|object|\Gm\Stdlib\BaseObject $editor */
        $editor = $this->getEditorWidget();
        // добавление в ответ скриптов 
        if ($editor) {
            if (method_exists($editor, 'initResponse')) {
                $editor->initResponse($response);
            }
        }
        return $response;
    }

    /**
     * Действие "update" изменяет фрагмента текста.
     * 
     * @return Response
     */
    public function updateAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var \Gm\Backend\SiteMarkup\Model\MarkupBlock $form */
        $form = $this->getModel($this->defaultModel);
        if ($form === false) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', [$this->defaultModel]));
            return $response;
        }

        // загрузка атрибутов в модель из запроса
        if (!$form->load($request->getPost())) {
            $response
                ->meta->error(Gm::t(BACKEND, 'No data to perform action'));
            return $response;
        }

        // валидация атрибутов модели
        if (!$form->validate()) {
            $response
                ->meta->error(Gm::t(BACKEND, 'Error filling out form fields: {0}', [$form->getError()]));
            return $response;
        }

        // сохранение атрибутов модели
        if (!$form->save()) {
            $response
                ->meta->error(
                    $form->hasErrors() ? $form->getError() : Gm::t(BACKEND, 'Could not save data')
                );
            return $response;
        }
        return $response;
    }
}
