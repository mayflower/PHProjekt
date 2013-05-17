define([
    'exports',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/_base/json',
    'dojo/Deferred',
    'dojo/promise/all',
    'phpr/Api'
], function(
    exports,
    array,
    lang,
    json,
    Deferred,
    all,
    api
) {
    var projectsCache = {};
    var makeKey = function(params) {
        var vals = [];

        for (var i in params) {
            if (params.hasOwnProperty(i)) {
                vals.push(i);
            }
        }

        vals.sort();

        var key = '';

        vals.forEach(function(val) {
            key += json.toJson(val) + '_' + json.toJson(params[val]);
        });

        return key;
    };

    exports.getProjects = function(params) {
        var optsDef = {recursive: true, projectId: 1};
        lang.mixin(optsDef, params || {});

        var key = makeKey(optsDef);
        var def = new Deferred();

        if (projectsCache.hasOwnProperty(key)) {
            var item = projectsCache[key];
            if (item.hasOwnProperty('def')) {
                return item.def;
            } else {
                def.resolve(item.val);
                return def;
            }
        }

        projectsCache[key] = {
            def: def
        };

        api.getData('index.php/Project/Project',
            {query: optsDef}
        ).then(function(result) {
            var projects = {};
            array.forEach(result, function(p) {
                projects[p.id] = p;
            });

            delete projectsCache[key].def;
            projectsCache[key].val = projects;

            def.resolve(projects);
        }, api.defaultErrorHandler);

        return def;
    };

    exports.getRecentProjects = function(params) {
        var optsDef = {count: 2};
        lang.mixin(optsDef, params);

        var def = new Deferred();
        all({
            projects: exports.getProjects(),
            recent: api.getData(
                 'index.php/Timecard/index/jsonRecentProjects',
                 {query: {n: optsDef.count}})
        }).then(function(result) {
            var projects = array.map(result.recent, function(id) {
                return result.projects[id];
            });

            def.resolve(projects);
        }, api.defaultErrorHandler);

        return def;
    };
});
