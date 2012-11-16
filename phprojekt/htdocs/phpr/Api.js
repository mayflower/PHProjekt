define([
    'exports',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/request/xhr'
], function(exports, lang, array, xhr) {
    var config = (function() {
        var config = {};

        return {
            get: function(key) {
                return config.hasOwnProperty(key) ? config[key] : null;
            },
            set: function(key, value) {
                if (key) {
                    config[key] = value;
                }
            }
        };
    })();

    exports.config = config;

    exports.getData = function(url, options) {
        var params = lang.mixin({
            handleAs: 'json',
            headers: {
                'X-CSRFToken': this.config.get('csrfToken')
            }
        }, options || {});

        return xhr.get(url, params);
    };

    exports.isGlobalModule = function(module) {
        var globals = config.get('globalModules');
        for (var id in globals) {
            if (globals[id].name == module) {
                return true;
            }
        }

        return false;
    };

    exports.getModulePermissions = function(projectId) {
        var modulePermissionsUrl = 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + projectId;
        return exports.getData(modulePermissionsUrl);
    };
});
