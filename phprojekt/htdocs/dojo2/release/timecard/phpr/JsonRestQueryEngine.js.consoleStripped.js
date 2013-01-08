define("phpr/JsonRestQueryEngine", [
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/json',
    'phpr/Timehelper'
], function(array, lang, json, time) {
    var isDate = function(dateString) {
        return ("" + new Date(dateString)) == dateString;
    };

    var isoDateRegex = /^\d{4}-\d{2}-\d{2}$/;
    var isIsoDate = function(dateString) {
        return isoDateRegex.test("" + dateString);
    };

    var isoDatetimeRegex = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/;
    var isIsoDatetime = function(dateString) {
        return isoDatetimeRegex.test("" + dateString);
    };

    var getFieldType = function(input) {
        if (isDate(input)) {
            return 'Date';
        }
        if (isIsoDate(input)) {
            return 'IsoDate';
        }
        if (isIsoDatetime(input)) {
            return 'IsoDatetime';
        }
    };

    var conditionHolds = function(condition, fieldType, input, compareTo) {
        switch (fieldType) {
            case 'IsoDate':
                /* falls through */
            case 'IsoDatetime':
                input = time.datetimeToJsDate(input);
                compareTo = time.datetimeToJsDate(compareTo);
                fieldType = 'Date';
                break;
        }

        switch (fieldType) {
            case 'Date':
                switch (condition) {
                    case '!ge':
                        return (new Date(input).getTime() >= new Date(compareTo).getTime());
                    case '!lt':
                        return (new Date(input).getTime() < new Date(compareTo).getTime());
                }
        }

        return false;
    };

    return function(query, options) {
        return function(inputData) {
            if (!query || !query.hasOwnProperty('filter')) {
                return inputData;
            }

            return array.filter(inputData, function(item) {
                var match = true;
                var filter = json.parse(query.filter);
                for (var fieldName in filter) {
                    if (!item.hasOwnProperty(fieldName)) {
                        return false;
                    }

                    var value = lang.trim(item[fieldName]);

                    var fieldType = getFieldType(value);

                    var conditions = filter[fieldName];
                    for (var conditionName in conditions) {
                        var conditionVariable = conditions[conditionName];
                        if (!conditionHolds(conditionName, fieldType, value, conditionVariable)) {
                            return false;
                        }
                    }
                }

                return true;
            });
        };
    };
});
