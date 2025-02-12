<?php
/**
 * Этот файл является частью модуля веб-приложения GearMagic.
 * 
 * Пакет русской локализации.
 * 
 * @link https://gearmagic.ru
 * @copyright Copyright (c) 2015 Веб-студия GearMagic
 * @license https://gearmagic.ru/license/
 */

return [
    '{name}'        => 'Визуальный редактор',
    '{description}' => 'Конструктор страниц веб-сайта',
    '{permissions}' => [
        'any'    => ['Полный доступ', 'Просмотр и внесение изменений в страницы веб-сайта'],
        'view'   => ['Просмотр', 'Browsing website pages'],
    ],

    // Module
    'Open in visual editor' => 'Открыть в визуальном редакторе',

    // Panel: панель инструментов
    'Enter the URL of your website in the bar and click the "Go" button' 
        => 'Укажите в строке URL-адрес вашего веб-сайта и нажмите кнопку "Перейти"',
    'Go' => 'Перейти',
    'Home' => 'Главная страница',
    'Back to previous page' => 'На предыдущую страницу',
    'To next page' => 'На следующую страницу',
    'Article' => 'Статья',
    'Article category' => 'Категория статьи',
    'You have entered the URL incorrectly' => 'Вы неправильно указали URL-адрес.',
    'Edit article' => 'Редактировать статью',
    'Add an article' => 'Добавить статью',
    'Delete article' => 'Удалить статью',
    'All acticles' => 'Все статьи',
    'Edit category' => 'Редактировать категорию',
    'Add an category' => 'Добавить категорию',
    'Delete category' => 'Удалить категорию',
    'All categories' => 'Все категории',
    'Enable / disable page markup' => 'Включить / отключить разметку страниц',
    'Refresh page' => 'Обновить страницу',
    'Help' => 'Помощь',
    'Search Engine Optimization' => 'Поисковая оптимизация',
    'Information about the site' => 'Информармация о сайте',
    'Open page in new browser tab' => 'Открыть страницу в новой вкладке браузера',
    'Components on the current page' => 'Компоненты текущей страницы',
    'Fragments of the current page' => 'Фрагменты текущей страницы',
    // Panel: сообщения
    'Are you sure you want to delete the article?' => 'Вы действительно хотите удалить статью?',
    'Current website page article not found!' => 'Статья текущей страницы веб-сайта не найдена!',
    'Are you sure you want to delete the category?' => 'Вы действительно хотите удалить категорию?',
    'Current website page category not found!' => 'Категория текущей страницы веб-сайта не найдена!',

    // MarkupSettings: заголовок
    'Widget markup settings' => 'Настройка разметки',
    // MarkupSettings: всплывающие сообщения / текст
    'The markup settings completed successfully' => 'Настройка разметки успешно выполнена.',

    // MarkupBlock
    '#{block.title}' => 'Фрагмент "{0}"',
    // MarkupBlock: заголовок
    'Block' => 'Фрагмент',
    'Saving a block' => 'Изменение фрагмента',
    // MarkupBlock: сообщения
    'Block "{0}" successfully changed' => 'Фрагмент "{0}" успешно изменён.',
    // MarkupBlock: ошибки
    'Error getting a mutable fragment from a template (fragment selected incorrectly)' 
        => 'Ошибка получения изменяемого фрагмента из шаблона (неправильно выделен фрагмент).',
    'Error writing text fragment to template "{0}"' => 'Ошибка записи фрагмента текста в шаблон "{0}".'
];
