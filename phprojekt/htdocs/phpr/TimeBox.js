define([
    'dojo/_base/declare',
    'dijit/form/ValidationTextBox'
], function(
    declare,
    ValidationTextBox
) {
    function pattern(allowEmpty) {
        var hours = '([01]?\\d|2[0123])',
            minutes = '([01-5]\\d)',
            separator = '[:\\. ]?';

        var p = '(' + hours + separator + minutes + ')';
        if (allowEmpty) {
            p += '?';
        }

        return p;
    }

    return declare([ValidationTextBox], {
        allowEmpty: false,

        invalidMessage: 'Invalid time format',

        pattern: pattern(false),

        _setAllowEmptyAttr: function(/* boolean */ allowEmpty) {
            this._set('allowEmpty', allowEmpty);

            this.set('pattern', pattern(allowEmpty));
        }
    });
});
