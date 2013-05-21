define("phpr/TimeBox", [
    'dojo/_base/declare',
    'dijit/form/ValidationTextBox',
    'phpr/Timehelper'
], function(
    declare,
    ValidationTextBox,
    timehelper
) {
    return declare([ValidationTextBox], {
        allowEmpty: false,

        invalidMessage: 'Invalid time format',

        pattern: timehelper.timeRegexpString,

        _setAllowEmptyAttr: function(/* boolean */ allowEmpty) {
            this._set('allowEmpty', allowEmpty);

            var pattern = timehelper.timeRegexpString;
            if (allowEmpty) {
                pattern += '?';
            }
            this.set('pattern', pattern);
        }
    });
});
