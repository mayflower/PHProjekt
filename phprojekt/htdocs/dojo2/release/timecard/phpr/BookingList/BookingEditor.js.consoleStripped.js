require({cache:{
'url:phpr/template/bookingList/bookingEditor.html':"<div class=\"bookingCreator\">\n    <div data-dojo-attach-point=\"form\" data-dojo-type=\"dijit/form/Form\">\n        <table><tr><td>\n            <div class=\"first\">\n                <select data-dojo-attach-point=\"project\"\n                        class=\"project\"\n                        name=\"project\"\n                        data-dojo-type=\"dijit/form/FilteringSelect\"\n                        data-dojo-props=\"labelType: 'html', searchAttr: 'name', labelAttr: 'label', queryExpr: '*$\\{0\\}*'\">\n                    <option value=\"1\"><span class=\"projectId\">1</span> Unassigned</option>\n                </select>\n                <input type=\"text\"\n                       name=\"start\"\n                       data-dojo-type=\"dijit/form/ValidationTextBox\"\n                       data-dojo-attach-point=\"start\"\n                       data-dojo-props=\"pattern: this._getStartRegexp, invalidMessage: 'Invalid time format'\"\n                       class=\"time\"/>\n                -\n                <input type=\"text\"\n                       name=\"end\"\n                       data-dojo-type=\"dijit/form/ValidationTextBox\"\n                       data-dojo-attach-point=\"end\"\n                       data-dojo-props=\"pattern: this._getEndRegexp, invalidMessage: 'Invalid time format'\"\n                       class=\"time\"/>\n                <input type=\"text\"\n                       name=\"date\"\n                       data-dojo-type=\"phpr/DateTextBox\"\n                       value=\"today\"\n                       data-dojo-attach-point=\"date\"\n                       class=\"date\"/>\n                <a href=\"javascript: void(0)\" class=\"notesIcon\" data-dojo-attach-point=\"notesIcon\"><div><b></b></div></a>\n                <button data-dojo-type=\"dijit/form/Button\"\n                        type=\"submit\"\n                        data-dojo-attach-point=\"submitButton\"\n                        class=\"submitButton\"></button>\n            </div>\n            <div data-dojo-attach-point=\"notesContainer\" class=\"second open\">\n                <input type=\"text\"\n                       name=\"notes\"\n                       class=\"notes\"\n                       data-dojo-type=\"dijit/form/TextBox\"\n                       data-dojo-attach-point=\"notes\"/>\n            </div>\n        </td><td class=\"editButtonCell\">\n            <button data-dojo-type=\"dijit/form/Button\" type=\"button\" data-dojo-attach-point=\"confirmEditButton\"\n                data-dojo-props=\"iconClass: 'editIcon'\"\n                data-dojo-attach-event=\"onClick:_submit\" class=\"confirmEditButton\">Save</button>\n        </td></tr></table>\n    </div>\n</div>\n"}});
define("phpr/BookingList/BookingEditor", [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/html',
    'dojo/Evented',
    'dojo/date',
    'dojo/date/locale',
    'dojo/dom-style',
    'dijit/_FocusMixin',
    'phpr/BookingList/BookingCreator',
    'phpr/Api',
    'phpr/Timehelper',
    'dojo/text!phpr/template/bookingList/bookingEditor.html'
], function(declare, lang, on, html, Evented, date, locale, style,
        _FocusMixin,
        BookingCreator,
        api, time,
        templateString) {
    return declare([BookingCreator, Evented, _FocusMixin], {
        booking: null,

        baseClass: 'bookingEditor',

        templateString: templateString,

        constructor: function() {
            this.booking = {};
        },

        startup: function() {
            this.inherited(arguments);
            this.start.focus();
        },

        _cancel: function() {
            this.emit('editCancel');
        },

        _showErrorInWarningIcon: api.errorHandlerForTag('bookingEditor'),

        _submit: function(evt) {
            evt.stopPropagation();
            if (this.form.validate()) {
                var data = this.form.get('value');
                var sendData = this._prepareDataForSend(lang.mixin({}, this.booking, data));
                if (sendData) {
                    this.store.put(sendData).then(
                        function() {
                            topic.publish('notification/clear', 'bookingEditor');
                        },
                        lang.hitch(this, function(error) {
                            try {
                                var msg = json.parse(error.responseText, true);
                                if (msg.message && msg.message.match(/entry.*overlaps.*existing/)) {
                                    this._markOverlapError();
                                } else {
                                    this._showErrorInWarningIcon(error);
                                }
                            } catch (e) {
                                this._showErrorInWarningIcon(error);
                            }
                        })
                    );
                }
            }
            return false;
        },

        _onBlur: function() {
            this._cancel();
        }
    });
});
