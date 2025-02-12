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
use Gm\Panel\Http\Response;
use Gm\Panel\Helper\ExtForm;
use Gm\Panel\Controller\FormController;

/**
 * Контроллер настройки разметки компонента.
 * 
 * Действия контроллера:
 * - view, вывод интерфейса настроек виджета;
 * - data, вывод настроек виджета по указанному идентификатору;
 * - update, изменение настроек виджета по указанному идентификатору.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Gm\Backend\SiteMarkup\Controller
 * @since 1.0
 */
class MarkupSettings extends FormController
{
    /**
     * {@inheritdoc}
     */
    public function translateAction(mixed $params, string $default = null): ?string
    {
        switch ($this->actionName) {
            // вывод интерфейса
            case 'view':
            // просмтор настроек
            case 'data':
                return Gm::t(BACKEND, "{{$this->actionName} properties action}");

            default:
                return parent::translateAction(
                    $params,
                    $default ?: Gm::t(BACKEND, "{{$this->actionName} properties action}")
                );
        }
    }

    /**
     * Возвращает идентификатор выбранного виджета.
     *
     * @return int
     */
    public function getIdentifier(): int
    {
        return (int) Gm::$app->router->get('id');
    }

    /**
     * Действие "view" выводит интерфейс настройки разметки компонента.
     * 
     * @return Response
     */
    public function viewAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();

        /** @var null|int $id Идентификатор виджета */
        $id = $this->getIdentifier();
        if (empty($id)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams */
        $widgetParams = Gm::$app->widgets->getRegistry()->getAt($id);
        // если виджет не найден
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$id]));
            return $response;
        }

        // для доступа к пространству имён объекта
        Gm::$loader->addPsr4($widgetParams['namespace']  . NS, Gm::$app->modulePath . $widgetParams['path'] . DS . 'src');

        $settingsClass = $widgetParams['namespace'] . NS . 'Settings\MarkupSettings';
        if (!class_exists($settingsClass)) {
            $response
                ->meta->error($this->module->t('Unable to create widget object "{0}"', [$settingsClass]));
            return $response;
        }

        // т.к. виджет самостоятельно не может подключать свою локализацию (в данном случаи делает это модуль), 
        // то добавляем шаблон локализации виджета модулю
        $category = Gm::$app->translator->getCategory($this->module->id);
        $category->patterns['widget'] = [
            'basePath' => Gm::$app->modulePath . $widgetParams['path'] . DS . 'lang',
            'pattern'  => 'text-%s.php',
        ];
        $this->module->addTranslatePattern('widget');

        /** @var object|Gm\Panel\Widget\MarkupSettingsWindow $widget Виджет настроек разметки */
        $widget = Gm::createObject($settingsClass);
        if ($widget instanceof Gm\Panel\Widget\MarkupSettingsWindow) {
            // панель формы (Gm.view.form.Panel GmJS)
            $widget->form->router->id    = $id;
            $widget->form->buttons = ExtForm::buttons([
                'help' => [
                    'component' => 'widget:' . $widgetParams['id'],
                    'subject'   => 'markupsettings'
                ], 
                'save', 'cancel'
            ]);
            $widget->title = $this->module->t('{markupsettings.title}');
            $widget->titleTpl = $widget->title;
        }

        $response
            ->setContent($widget->run())
            ->meta
                ->addWidget($widget);
        return $response;
    }

    /**
     * Действие "update" изменяет настройки виджета по указанному идентификатору.
     * 
     * @return Response
     */
    public function updateAction(): Response
    {
        /** @var Response $response */
        $response = $this->getResponse();
        /** @var \Gm\Http\Request $request */
        $request = Gm::$app->request;

        /** @var null|int $id Идентификатор виджета */
        $id = $this->getIdentifier();
        if (empty($id)) {
            $response
                ->meta->error(Gm::t('app', 'Parameter "{0}" not specified', ['id']));
            return $response;
        }

        /** @var null|array $widgetParams Параметры виджета */
        $widgetParams = Gm::$app->widgets->getRegistry()->getAt($id);
        // если виджет не найден
        if ($widgetParams === null) {
            $response
                ->meta->error($this->module->t('There is no widget with the specified id "{0}"', [$id]));
            return $response;
        }

        /** @var null|\Gm\Data\Model\RecordModel|\Gm\Panel\Data\Model\WidgetSettingsModel $model */
        $model = Gm::$app->widgets->getModel(
            'MarkupSettings', $widgetParams['id'], ['basePath' => Gm::$app->modulePath . $widgetParams['path'], 'module' => $this->module]
        );
        // если модель данных не определена
        if ($model === null) {
            $response
                ->meta->error(Gm::t('app', 'Could not defined data model "{0}"', ['MarkupSettings']));
            return $response;
        }

        /** @var null|\Gm\Data\Model\RecordModel|\Gm\Panel\Data\Model\WidgetSettingsModel $form */
        $form = $model->get();
        if ($form === null) {
            $response
                ->meta->error(
                    $model->hasErrors() ? $model->getError() : $this->t('Unable to get widget settings')
                );
            return $response;
        }

        // т.к. виджет самостоятельно не может подключать свою локализацию (в данном случаи делает это модуль), 
        // то добавляем шаблон локализации виджета модулю
        $category = Gm::$app->translator->getCategory($this->module->id);
        $category->patterns['widget'] = [
            'basePath' => Gm::$app->modulePath . $widgetParams['path'] . DS . 'lang',
            'pattern'  => 'text-%s.php',
        ];
        $this->module->addTranslatePattern('widget');

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
        } else {
            // всплывающие сообщение
            $response
                ->meta
                    ->cmdComponent($this->module->viewId('frame'), 'reload')
                    ->cmdPopupMsg(
                        $this->module->t('The markup settings completed successfully'), 
                        $this->module->t('Widget markup settings'), 
                        'accept'
                );
        }
        return $response;
    }
}
