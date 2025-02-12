/*!
 * Контроллер представления управляющего фрейма.
 * Модуль "Визуальный редактор".
 * Copyright 2015 Вeб-студия GearMagic. Anton Tivonenko <anton.tivonenko@gmail.com>
 * https://gearmagic.ru/license/
 */

Ext.define('Gm.be.site_markup.FrameController', {
    extend: 'Gm.view.form.PanelController',
    alias: 'controller.gm-be-site_markup-frame',

    /**
     * Возвращает URL-адрес фрейма.
     * @param {Boolean} returnValue Возвращать URL-адрес или объект URL.
     * @return {Object|String}
     */
     getNavUrl: (returnValue = true) => {
        let url = Ext.getCmp('gm-site_markup__url');
        return returnValue ? url.getValue() : url;    
    },

    /**
     * Возвращает кнопку перехода на следующий URL-адреса фрейма.
     * @return {Ext.button.Button|null}
     */
    getNavBack: () => { return Ext.getCmp('gm-site_markup__nav-back'); },

    /**
     * Возвращает кнопку перехода на следующий URL-адреса фрейма.
     * @return {Ext.button.Button|null}
     */
    getNavForward: () => { return Ext.getCmp('gm-site_markup__nav-forward'); },

    /**
     * Возвращает кнопку включения разметки.
     * @return {Ext.button.Button|null}
     */
     getBtnMarkup: () => { return Ext.getCmp('gm-site_markup__btn-markup'); },
    
    /**
     * Возвращает кнопку компонентов текущей страницы.
     * @return {Ext.button.SplitButton|null}
     */
    getBtnComponents: () => { return Ext.getCmp('gm-site_markup__btn-cmps'); },

    /**
     * Возвращает кнопку фрагментов текущей страницы.
     * @return {Ext.button.SplitButton|null}
     */
    getBtnBlocks: () => { return Ext.getCmp('gm-site_markup__btn-blocks'); },

    /**
     * Обновляет меню кнопки компонентов страницы.
     * @param {Object} components Конфигурации компонентов на странице.
     */
    updateBtnComponents: function (components) {
        let btn = this.getBtnComponents();

        btn.menu.removeAll();
        // все компоненты фрейма
        for (let componentId in components) {
            let cmp = components[componentId];
            if (cmp.control || null) {
                let co = cmp.control,
                    item = {
                        text: co.text || 'unknow component',
                        handler: () => {
                            Gm.getApp().widget.load(co.route, co.params || {});
                        }
                    };
                if (Ext.isDefined(co.icon))
                    item.icon = co.icon;
                else
                    item.iconCls = 'gm-site_markup__btn-cmps';
                btn.menu.add(item);
            }
        }
        btn.setDisabled(btn.menu.items.getCount() == 0);
    },

    /**
     * Обновляет меню кнопки фрагментов страницы.
     * @param {Object} blocks Конфигурации фрагментов на странице.
     */
    updateBtnBlocks: function (blocks) {
        let btn = this.getBtnBlocks();

        btn.menu.removeAll();
        // все блоки фрейма
        for (let blockId in blocks) {
            let block = blocks[blockId],
                item = {
                    text: block.title,
                    handler: () => {
                        Gm.getApp().widget.load(
                            '@backend/site-markup/block',
                            {
                                id: block.callId,
                                html: block.html,
                                title: block.title,
                                calledFrom: block.calledFrom
                            }
                        );
                    }
                };
                item.iconCls = 'gm-site_markup__btn-blocks';
            btn.menu.add(item);
        }
        btn.setDisabled(btn.menu.items.getCount() == 0);
    },

    /**
     * Событие загрузки фрейма.
     * @param {Gm.be.site_markup.IFrame} me
     */
    onFrameLoad: function (me) {
        let iframe = me.getWin();

        if (!Ext.isDefined(iframe.Gm)) return;

        // устанавливает панели навигации URL-адрес
        this.getNavUrl(false).setValue(me.getSrc());
        this.getNavBack().setDisabled(!me.history.canBack());
        this.getNavForward().setDisabled(!me.history.canForward());

        if (!this.getBtnMarkup().pressed) return;

        // меню компонентов
        this.updateBtnComponents(iframe.Gm.Markup.getCmpConfig());
        // меню фрагментов
        this.updateBtnBlocks(iframe.Gm.Markup.getBlocksConfig());

        // событие клика на кнопке управления блоком разметки
        iframe.Gm.Markup.onBlockCtrlClick = (blockConfig) => {
            Gm.getApp().widget.load(
                '@backend/site-markup/block',
                {
                    id: blockConfig.callId,
                    html: blockConfig.html,
                    title: blockConfig.title,
                    calledFrom: blockConfig.calledFrom
                }
            );
        }
    },

    /**
     * Событие изменения URL-адреса фрейма.
     * @param {Gm.be.site_markup.IFrame} me
     */
    onFrameChangeUrl: function (me) {
        this.getNavUrl(false).setValue(me.getWin().location.href);
    }
});