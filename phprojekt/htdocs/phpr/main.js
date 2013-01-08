define([
    'phpr/Api',
    'phpr/BaseLayout',
    'phpr/ViewManager',
    'dojo/dom-construct',
    'dojo/_base/window',
    'dojo/window',
    'dojo/parser',
    'dojo/domReady!'
], function(api, BaseLayout, ViewManager, dom, win, window) {
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

    if (!started && !starting) {
        starting = true;
        api.config.set('csrfToken', window.get(win.doc).csrfToken);
        loadInitData();
    }
});
