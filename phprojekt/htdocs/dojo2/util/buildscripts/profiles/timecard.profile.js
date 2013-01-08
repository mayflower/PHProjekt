var profile = (function() {
    return {
        version: "1.0.0",
        releaseDir: "../../../release/",
        releaseName: "timecard",
        action: "release",
        mini: true,
        cssOptimize: "comments",
        optimize: "closure",
        layerOptimize: "closure",
        selectorEngine: "acme",

        layers: {
            "phpr/main": {
                include: [
                    "dojo/domReady",
                    "dojo/parser",
                    "dijit/dijit",
                    "phpr/main",
                    "phpr/run"
                ],
                boot: true
            }
        },

        packages: [
            { name: "dojo", location: "../../../../dojo2/dojo" },
            { name: "dijit", location: "../../../../dojo2/dijit" },
            { name: "dojox", location: "../../../../dojo2/dojox" },
            { name: "phpr", location: "../../../../phpr" }
        ],

        resourceTags: {
            amd: function(filename, mid) {
                return (/\.js$/).test(filename);
            }
        },

        // these are all the has feature that affect the loader and/or the bootstrap
        // the settings below are optimized for the smallest AMD loader that is configurable
        // and include dom-ready support
        staticHasFeatures: {
            // dojo/dojo
            'config-dojo-loader-catches': 0,

            // dojo/dojo
            'config-tlmSiblingOfDojo': 0,

            // dojo/dojo
            'dojo-amd-factory-scan': 0,

            // dojo/dojo
            'dojo-combo-api': 0,

            // dojo/_base/config, dojo/dojo
            'dojo-config-api': 1,

            // dojo/main
            'dojo-config-require': 0,

            // dojo/_base/kernel
            'dojo-debug-messages': 0,

            // dojo/dojo
            'dojo-dom-ready-api': 1,

            // dojo/main
            'dojo-firebug': 0,

            // dojo/_base/kernel
            'dojo-guarantee-console': 1,

            // dojo/has
            'dojo-has-api': 1,

            // dojo/dojo
            'dojo-inject-api': 1,

            // dojo/_base/config, dojo/_base/kernel, dojo/_base/loader, dojo/ready
            'dojo-loader': 1,

            // dojo/dojo
            'dojo-log-api': 0,

            // dojo/_base/kernel
            'dojo-modulePaths': 0,

            // dojo/_base/kernel
            'dojo-moduleUrl': 0,

            // dojo/dojo
            'dojo-publish-privates': 0,

            // dojo/dojo
            'dojo-requirejs-api': 0,

            // dojo/dojo
            'dojo-sniff': 0,

            // dojo/dojo, dojo/i18n, dojo/ready
            'dojo-sync-loader': 0,

            // dojo/dojo
            'dojo-test-sniff': 0,

            // dojo/dojo
            'dojo-timeout-api': 0,

            // dojo/dojo
            'dojo-trace-api': 0,

            // dojo/dojo
            'dojo-undef-api': 0,

            // dojo/_base/xhr
            'dojo-xhr-factory': 0,

            // dojo/_base/loader, dojo/dojo, dojo/on
            'dom': 1,

            // dojo/dojo
            'host-browser': 1,

            // dojo/_base/array, dojo/_base/connect, dojo/_base/kernel, dojo/_base/lang
            'extend-dojo': 1
        }

    };
})();
