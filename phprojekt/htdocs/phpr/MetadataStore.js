define([
    'exports',
    'dojo/data/ItemFileReadStore',
    'phpr/Api',
    'dojo/topic',
    'dojo/_base/lang',
    'dojo/Deferred'
], function(exports, readStore, api, topic, lang, Deferred) {
    var cache = {};

    var forEachCacheItem = function(fun) {
        for (var module in this._cache) {
            if (this._cache.hasOwnProperty(module)) {
                for (var projectId in this._cache[module]) {
                    if (this._cache[module].hasOwnProperty(projectId)) {
                        fun(this._cache[module][projectId]);
                    }
                }
            }
        }
    };

    var subscription = topic.subscribe('phpr/updateCacheData', {}, function() {
        forEachCacheItem(function(item) {
            if (item.hasOwnProperty('deferred')) {
                return;
            }
            if (item.hasOwnProperty('data')) {
                delete item.data;
            }
        });
    });

    exports.metadataFor = function(module, projectId) {
        if (projectId === undefined) {
            throw 'No projectId provided in phpr.Metadatastore::metadataFor!';
        }

        if (!cache.hasOwnProperty(module) ||
            !cache[module].hasOwnProperty(projectId) ||
            (!cache[module][projectId].hasOwnProperty('data') &&
                !cache[module][projectId].hasOwnProperty('deferred'))) {
            if (!cache.hasOwnProperty(module)) {
                cache[module] = {};
            }
            cache[module][projectId] = {};
            var url = 'index.php/' + module + '/index/metadata';

            var query = {
                projectId: parseInt(projectId, 10)
            };

            var def = api.getData(url, {query: query}).then(
                lang.hitch(this, function (data) {
                    cache[module][projectId].data = data;
                    delete cache[module][projectId].deferred;
                    return data;
                }),
                function(err) {
            });
            cache[module][projectId].deferred = def;
            return def;
        } else if (cache[module][projectId].hasOwnProperty('data')) {
            var def = new Deferred();
            def.resolve(cache[module][projectId].data);
            return def;
        } else {
            return cache[module][projectId].deferred;
        }
    };
});
