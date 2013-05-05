define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/on',
    'dojo/number',
    'dojo/promise/all',
    'dojo/topic',
    'dojo/json',
    'dojo/topic',
    'dojo/store/JsonRest',
    'dojo/store/Memory',
    'dijit/Tooltip',
    'phpr/BookingList/BookingBlock',
    'phpr/Api',
    'phpr/Timehelper',
    'dojo/text!phpr/template/bookingList/bookingCreator.html',
    'phpr/TimeBox'
], function(
    declare,
    lang,
    array,
    on,
    number,
    all,
    topic,
    json,
    topic,
    JsonRest,
    Memory,
    Tooltip,
    BookingBlock,
    api,
    timehelper,
    templateString
) {
    return declare([BookingBlock], {
        templateString: templateString,
        store: null,
        projectDeferred: null,

        buildRendering: function() {
            this.inherited(arguments);

            this.date.set('value', new Date());
            this.own(this.form.on('submit', lang.hitch(this, this._submit)));
        },

        _setBookingAttr: function(booking) {
            var formatTimeString = function(date) {
                return number.format(date.getHours(), {pattern: '00'}) + ':' +
                    number.format(date.getMinutes(), {pattern: '00'});
            };

            this.project.set('value', '' + booking.projectId);
            var startDatetime = timehelper.datetimeToJsDate(booking.startDatetime);
            this.start.set('value', formatTimeString(startDatetime));
            this.date.set('value', startDatetime);

            if (booking.endTime) {
                var endDatetime = timehelper.timeToJsDate(booking.endTime);
                this.end.set('value', formatTimeString(endDatetime));
            }

            if (booking.notes) {
                this.notes.set('value', booking.notes);
            }

            this.booking = booking;
        },

        postCreate: function() {
            if (!this.store) {
                this.store = new JsonRest({
                    target: 'index.php/Timecard/Timecard/'
                });
            }

            this.own(on(this.notes, 'keydown', lang.hitch(this, function(event) {
                if (event.keyIdentifier === "Enter") {
                    if (event.shiftKey) {
                        this.notes.set('value', this.notes.get('value') + "\n");
                    } else {
                        this.form.submit();
                    }
                    event.preventDefault();
                }
            })));

            this.end.validate = this._endValidateFunction(this.end.validate, this.start);
        },

        _showErrorInWarningIcon: api.errorHandlerForTag('bookingCreator'),

        _submit: function(evt) {
            if (evt) {
                evt.stopPropagation();
            }

            if (this.form.validate()) {
                var data = this.form.get('value');
                var sendData = this._prepareDataForSend(data);
                this.emit("create", data);
                if (sendData) {
                    var d = this.store.put(sendData).then(
                        function() {
                            topic.publish('notification/clear', 'bookingCreator');
                            topic.publish('timecard/bookingCreated', sendData);
                        },
                        lang.hitch(this, function(error) {
                            try {
                                var msg = json.parse(error.responseText, true);
                                if (msg.error && msg.error === 'overlappingEntry') {
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

        _markOverlapError: function() {
            this.start.set('state', 'Error');
            this.end.focus();
            this.end.set('state', 'Error');
            this.end.set('message', 'The entry overlaps with an existing one');
        },

        _prepareDataForSend: function(data) {
            var ret = {};
            var startTime = timehelper.parseTime(data.start);
            var endTime = timehelper.parseTime(data.end);

            if (!startTime) {
                return false;
            }

            if (endTime) {
                endTime = timehelper.jsDateToIsoTime(endTime) + ':00';
            }
            ret.endTime = endTime;

            if (data.id) {
                ret.id = data.id;
            }

            var startDatetime = new Date(data.date.getTime());
            startDatetime.setHours(startTime.getHours());
            startDatetime.setMinutes(startTime.getMinutes());

            ret.startDatetime = timehelper.jsDateToIsoDatetime(startDatetime) + ':00';

            ret.notes = data.notes || '';

            ret.projectId = data.project || '1';

            return ret;
        },

        _setDateAttr: function(date) {
            this.date.set('value', date);
        },

        _endValidateFunction: function(originalValidate, startTextbox) {
            return function(isFocused) {
                var valid = originalValidate.apply(this, arguments);
                if (!valid) {
                    return valid;
                }

                var endText = this.get('value');
                var forceTooltip = false;
                if (endText.match(/^\d{3}$/)) {
                    if (isFocused) {
                        // The user might be in the process of entering a 4-digit number with no separator.
                        return valid;
                    } else {
                        // The user just moved his focus elsewhere. this.displayMessage (called by _setMessageAttr) will
                        // _not_ create a tooltip, because it assumes this already happened after the input was made.
                        forceTooltip = true;
                    }
                }

                var startValue = parseInt(startTextbox.get('value').replace(/D/g, '')),
                    endValue = parseInt(endText.replace(/\D/g, ''), 10);
                if (startValue >= endValue) {
                    this._maskValidSubsetError = false;
                    this.focusNode.setAttribute("aria-invalid", "true");
                    this.set('state', 'Error');
                    var message = 'End time must be after start time';
                    this.set('message', message);

                    if (forceTooltip) {
                        Tooltip.show(message, this.domNode, this.tooltipPosition, !this.isLeftToRight());
                    }
                    return false;
                }
                return true;
            };
        }
    });
});

