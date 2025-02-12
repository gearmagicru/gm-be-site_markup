/*!
 * Контроллер представления панели визуального редактора.
 * Модуль "Визуальный редактор".
 * Copyright 2015 Вeб-студия GearMagic. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://gearmagic.ru/license/
 */

Ext.define('Gm.be.site_markup.PanelController', {
    extend: 'Ext.app.ViewController',
    alias: 'controller.gm-be-site_markup-panel',

    /**
     * Возвращает URL-адрес фрейма.
     * @param {Boolean} returnValue Возвращать URL-адрес или объект URL.
     * @return {Object|String}
     */
    getUrl: function (returnValue = true) {
        let url = Ext.getCmp('gm-site_markup__url');
        return returnValue ? url.getValue() : url;    
    },

    /**
     * Возвращает фрейм.
     * @return {Object}
     */
    getFrame: function () { return Ext.getCmp('gm-site_markup__frame'); },

    /**
     * Возвращает идентификатор статьи.
     * @return {Number}
     */
    getArticleId: function () { return this.getFrame().getWin().Gm.Markup.articleId; },

    /**
     * Возвращает идентификатор типа статьи.
     * @return {Number}
     */
     getAtypeId: function () { return this.getFrame().getWin().Gm.Markup.atypeId; },

    /**
     * Возвращает идентификатор категории статьи.
     * @return {Number}
     */
    getCategoryId: function () { return this.getFrame().getWin().Gm.Markup.categoryId; },

    /**
     * Срабатывает при клике на кнопку "Включить / отключить разметку страниц".
     * @param {Ext.button.Button} me
     * @param {Boolean} pressed
     * @param {Object} eOpts
     */
    onSetMarkup: function (me, pressed, eOpts) {
        this.getFrame().reload();

        let btns = ['article', 'acategory', 'seo', 'cmps', 'blocks'];
        for (btn of btns) {
            Ext.getCmp('gm-site_markup__btn-' + btn).setDisabled(!pressed);
        }
        if (pressed) {
            me.setIconCls('g-icon-svg ' + me.iconClsOn + ' g-icon_size_button_medium');
            Ext.util.Cookies.set(me.token.name, me.token.key);
        } else {
            me.setIconCls('g-icon-svg ' + me.iconClsOff + ' g-icon_size_button_medium');
            Ext.util.Cookies.set(me.token.name, null);
        }
    },

    /**
     * Срабатывает при клике на кнопку "Главная страница".
     * @param {Ext.button.Button} me
     * @param {Ext.event.Event} e
     */
    onHome: function (me, e) {
        this.getFrame().loadSrc(me.homeUrl);
        this.getUrl(false).setValue(me.homeUrl);
    },

    /**
     * Срабатывает при клике на кнопку "Обновить страницу".
     * @param {Ext.button.Button} me
     * @param {Ext.event.Event} e
     */
     onFrameReload: function (me, e) { this.getFrame().loadSrc(this.getUrl()); },

    /**
     * Срабатывает при клике на кнопку "Перейти".
     * @param {Ext.button.Button} me
     * @param {Ext.event.Event} e
     */
     onApplyURL: function (me, e) {
         let url = this.getUrl();

        if (url.indexOf(me.homeUrl) === -1)
            Ext.Msg.error(me.msgBadUrl);
        else
            this.getFrame().loadSrc(url);
    },

    /**
     * Срабатывает при клике на кнопку "Редактировать статью".
     * @param {Ext.button.Split|Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
    onEditArticle: function (me, e) {
        let articleId = this.getArticleId(),
            atypeId   = this.getAtypeId();

        if (articleId > 0)
            Gm.getApp().widget.load(me.route + '/' + articleId + '?type=' + atypeId);
        else
            Ext.Msg.error(me.msgError);
    },

    /**
     * Срабатывает при клике на кнопку "Добавить статью".
     * @param {Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
     onAddArticle: (me, e) => { Gm.getApp().widget.load(me.route); },

    /**
     * Срабатывает при клике на кнопку "Удалить статью".
     * @param {Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
    onDeleteArticle: function (me, e)  {
        var frame = this.getFrame(),
            articleId = this.getArticleId();

        if (articleId > 0) {
            Gm.makeRequest({
                route: me.route + '/' + articleId,
                confirm: me.msgConfirm,
                afterRequest: (success, response) => {
                    if (success && response.success) {
                        frame.reload();
                    }
                }
            });
        } else
            Ext.Msg.error(me.msgError);
    },

    /**
     * Срабатывает при клике на кнопку "Редактировать категорию".
     * @param {Ext.button.Split|Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
    onEditCategory: function (me, e) {
        let categoryId = this.getCategoryId();

        if (categoryId > 0)
            Gm.getApp().widget.load(me.route + '/' + categoryId);
        else
            Ext.Msg.error(me.msgError);
    },

    /**
     * Срабатывает при клике на кнопку "Добавить категорию".
     * @param {Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
    onAddCategory: (me, e) => { Gm.getApp().widget.load(me.route); },

    /**
     * Срабатывает при клике на кнопку "Удалить категорию".
     * @param {Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
    onDeleteCategory: function (me, e)  {
        var frame = this.getFrame(),
            categoryId = this.getCategoryId();

        if (categoryId > 0) {
            Gm.makeRequest({
                route: me.route + '/' + categoryId,
                confirm: me.msgConfirm,
                afterRequest: (success, response) => {
                    if (success && response.success) {
                        frame.reload();
                    }
                }
            });
        } else
            Ext.Msg.error(me.msgError);
    },

    /**
     * Загрузка виджета.
     * @param {Ext.menu.Item} me
     * @param {Ext.event.Event} e
     */
    onLoadWidget: (me) => { Gm.getApp().widget.load(me.route); },

    /**
     * Открытие страницы в новой влкадке.
     * @param {Ext.button.Button} me
     * @param {Ext.event.Event} e
     */
    onOpenWindow: function (me) { window.open(this.getUrl(), '_blank'); },

    /**
     * Предыдущая страница фрейма.
     * @param {Ext.button.Button} me
     * @param {Ext.event.Event} e
     */
    onBackUrl: function (me, e) { this.getFrame().history.back(); },

    /**
     * Следующая страница фрейма.
     * @param {Ext.button.Button} me
     * @param {Ext.event.Event} e
     */
    onForwardUrl: function (me, e) { this.getFrame().history.forward(); }
});