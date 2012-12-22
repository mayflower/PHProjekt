define([
    'exports',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/Deferred',
    'dojo/promise/all',
    'phpr/Api'
], function(exports, array, lang, Deferred, all, api) {
    exports.getProjects = function(params) {
        var optsDef = {recursive: true, projectId: 1};
        lang.mixin(optsDef, params);

        var def = new Deferred();
        api.getData('index.php/Project/Project',
            {query: optsDef}
        ).then(function(result) {
            var projects = {};
            array.forEach(result, function(p) {
                projects[p.id] = p;
            });

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
