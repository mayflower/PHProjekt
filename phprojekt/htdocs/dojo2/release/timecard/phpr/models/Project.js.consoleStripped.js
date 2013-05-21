define("phpr/models/Project", [
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
    exports.getProjects = function() {
        var opts = {recursive: true, projectId: 1};

        var def = new Deferred();
        var projectsCache;

        api.getData('index.php/Project/Project',
            {query: opts}
        ).then(function(result) {
            var projects = {
                1: {
                    id: '1',
                    title: 'Unassigned'
                }
            };

            array.forEach(result, function(p) {
                projects[p.id] = p;
            });

            projectsCache = projects;

            var d = def;
            def = null;
            d.resolve(projects);
        }, api.defaultErrorHandler);

        exports.getProjects = function() {
            if (def) {
                return def;
            } else {
                var d = new Deferred();
                d.resolve(projectsCache);
                return d;
            }
        };

        return exports.getProjects();
    };

    exports.getRecentProjects = function(params) {
        var optsDef = {count: 2};
        lang.mixin(optsDef, params);

        var def = new Deferred();
        all({
            projects: exports.getProjects(),
            recent: api.getData(
                 'index.php/Timecard/index/recentProjects',
                 {query: {n: optsDef.count}})
        }).then(function(result) {
            var projects = array.map(result.recent, function(id) {
                return result.projects[id];
            });

            def.resolve(projects);
        }, api.defaultErrorHandler);

        return def;
    };

    exports.getManagedProjects = function() {
        var def = new Deferred();

        all({
            projects: exports.getProjects(),
            managed: api.getData('index.php/Project/index/managedProjects')
        }).then(function(result) {
            var projects = {};
            array.forEach(result.managed, function(id) {
                projects[id] = result.projects[id];
            });

            def.resolve(projects);
        }, api.defaultErrorHandler);

        return def;
    };

    exports.getBookedProjects = function() {
        var def = new Deferred();

        all({
            projects: exports.getProjects(),
            booked: api.getData('index.php/Timecard/index/bookedProjects')
        }).then(function(result) {
                var projects = {};
                array.forEach(result.booked, function(id) {
                    projects[id] = result.projects[id];
                });

                def.resolve(projects);
            }, api.defaultErrorHandler);

        return def;
    };
});
