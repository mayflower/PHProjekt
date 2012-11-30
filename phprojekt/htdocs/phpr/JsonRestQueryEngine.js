define([
    'dojo/_base/array',
    'dojo/json',
    'phpr/Timehelper'
], function(array, json, time) {
    var isDate = function(dateString) {
        return ("" + new Date(dateString)) == dateString;
    };

    var isoDateRegex = /\d{4}-\d{2}-\d{2}/;
    var isIsoDate = function(dateString) {
        return isoDateRegex.test("" + dateString);
    };

    var isoDatetimeRegex = /\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?/;
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
                input = new Date(input);
                fieldType = 'Date';
                break;
            case 'IsoDatetime':
                input = time.datetimeToJsDate(input);
                fieldType = 'Date';
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

                    var fieldType = getFieldType(item[fieldName]);

                    var conditions = filter[fieldName];
                    for (var conditionName in conditions) {
                        var conditionVariable = conditions[conditionName];
                        if (!conditionHolds(conditionName, fieldType, item[fieldName], conditionVariable)) {
                            return false;
                        }
                    }
                }

                return true;
            });
        };
    };
});
