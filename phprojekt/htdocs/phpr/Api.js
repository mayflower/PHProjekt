define([
    'exports',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/request/xhr',
    'dojo/Deferred',
    'dojo/json',
    'dojo/topic'
], function(exports, lang, array, xhr, Deferred, json, topic) {
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

    exports.projectTitleForId = function(id) {
        var titlesById = null;
        var def = new Deferred();

        exports.getData(
            'index.php/Project/Project',
            {query: {projectId: 1, recursive: true}}
        ).then(function(projects) {
            titlesById = {};
            array.forEach(projects, function(p) {
                titlesById[p.id] = p.title;
            });

            def.resolve(titlesById);
            def = null;
        });

        exports.projectTitleForId = function(id) {
            if (id == 1) {
                var d = new Deferred();
                d.resolve('Unassigned');
                return d;
            } else if (titlesById === null) {
                return def.then(function(idMap) {
                    return idMap[id];
                });
            } else {
                var d = new Deferred();
                d.resolve(titlesById[id]);
                return d;
            }
        };

        return exports.projectTitleForId(id);
    };

    exports.getModulePermissions = function(projectId) {
        var modulePermissionsUrl = 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + projectId;
        return exports.getData(modulePermissionsUrl);
    };

    var publishError = function(msg, tag) {
        topic.publish('notification', {message: msg}, tag);
    };

    exports.errorHandlerForTag = function(tag) {
        return function(err) {
            try {
                msg = json.parse(err, true);
                if (msg.message) {
                    return publishError(msg.message, tag);
                }
            } catch (e) {
            }

            try {
                if (err && err.response && err.response.text) {
                    msg = json.parse(err.response.text, true);
                    if (msg.message) {
                        return publishError(msg.message, tag);
                    }
                }
            } catch (e) {
            }

            return publishError(err, tag);
        };
    };

    exports.defaultErrorHandler = exports.errorHandlerForTag(undefined);
});
