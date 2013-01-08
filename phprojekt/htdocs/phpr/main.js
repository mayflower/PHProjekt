define([
    'exports',
    'phpr/Api',
    'phpr/BaseLayout',
    'dojo/dom-construct',
    'dojo/_base/window',
    'phpr/ViewManager',
    'dojo/DeferredList',
    'dojo/_base/array',
    'dojo/_base/lang'
], function(exports, api, BaseLayout, dom, win, ViewManager, DeferredList, array, lang) {
    var started = false;
    var starting = false;
    var viewManager = null;

    var loadInitData = function() {

        api.getData('index.php/Core/module/jsonGetGlobalModules').then(function(data) {
            var globalModules = data.data;
            api.config.set('globalModules', globalModules);

            var baseLayout = new BaseLayout({}, dom.create('div', null, win.body()));
            baseLayout.startup();

            viewManager = new ViewManager(baseLayout);
            viewManager.startup();

            started = true;
            starting = false;
        });
    };

    exports.startup = function(csrfToken) {
        if (!started && !starting) {
            starting = true;
            api.config.set('csrfToken', csrfToken);
            loadInitData();
        }
    };
});
