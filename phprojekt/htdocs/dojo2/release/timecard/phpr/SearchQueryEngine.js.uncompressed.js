define("phpr/SearchQueryEngine", [
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/Deferred'
], function(
    lang,
    array,
    Deferred,
    Memory
) {
    return function(query, options) {
        var parts = query.name.split('');
        var regexInner = parts.join('.*');
        var regex = new RegExp('^.*' + regexInner + '.*$', 'i');
        var sort = function (a, b) {
            if (a && a.name && b && b.name) {
                return a.name.localeCompare(b.name);
            } else if (!a || !a.name) {
                return -1;
            } else if (!b || !b.name) {
                return 1;
            }

            return 0;
        };

        var execute = function(data) {
            var results = array.filter(data, function(item) {
                return regex.test(item.name);
            });

            if (query.name !== '') {
                results.sort(sort);

                var last = null;
                results = results.filter(function(item) {
                    if (!item.hasOwnProperty('name')) {
                        return false;
                    }

                    var ret = item.name !== last;
                    last = item.name;
                    return ret;
                });
            }

            if (options && (options.start || options.count)) {
                var total = results.length;
                results = results.slice(options.start || 0, (options.start || 0) + (options.count || Infinity));
                results.total = total;
            }

            return results;
        };

        return execute;
    };
});
