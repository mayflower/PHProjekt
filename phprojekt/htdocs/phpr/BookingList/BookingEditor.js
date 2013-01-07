define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/html',
    'dojo/Evented',
    'dojo/date',
    'dojo/date/locale',
    'dojo/dom-style',
    'dijit/_FocusMixin',
    'phpr/BookingList/BookingCreator.js',
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
