/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Calendar.Form");

dojo.declare("phpr.Calendar.Form", phpr.Default.Form, {
    // Urls
    _relatedDataUrl: null,

    // General
    _owner:              true,
    _currentDate:        null,
    _currentTime:        null,
    _updateCacheIdArray: null,
    _relatedData:        [],

    // Participants
    _participantsRender:    null,

    // Events
    // Events Buttons
    _eventForDateField: null,
    _eventForTimeField: null,

    // Submit
    _multipleEvents:       null,
    _multipleParticipants: null,
    _participantsInDb:     0,
    _participantsInTab:    0,

    init:function(id, params) {
        // Summary:
        //    Init the form for a new render.
        this._multipleEvents       = null;
        this._multipleParticipants = null;

        this.inherited(arguments);
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for this form.
        this.inherited(arguments);

        // Delete the cache of the 3 urls for every related event?
        if (this._relatedData && this._relatedData.relatedEvents) {
            // Make an array with the related events
            this._updateCacheIdArray = this._relatedData.relatedEvents.split(',');
            if (this._updateCacheIdArray.length > 0 && this._useCache) {
                this._updateCacheIds();
            }
        }
        phpr.DataStore.deleteData({url: this._relatedDataUrl});
    },

    /************* Private functions *************/

    _constructor:function(module, subModules) {
        // Summary:
        //    Construct the form only one time.
        this.inherited(arguments);

        // Calendar vars
        this._participantsRender = new phpr.Calendar.Participants(this._module);
        this._eventForDateField  = null;
        this._eventForTimeField  = null;
    },

    _initData:function() {
        // Get all the active users
        this._userStore = new phpr.Store.User();
        this._initDataArray.push({'store': this._userStore});

        // Get the participants and related events
        this._relatedDataUrl = phpr.webpath + 'index.php/Calendar/index/jsonGetRelatedData/id/' + this._id;
        this._initDataArray.push({'url': this._relatedDataUrl});

        // Get the tags
        this._tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
            + '/id/' + this._id;
        this._initDataArray.push({'url': this._tagUrl});
    },

    _setPermissions:function(data) {
        if (this._id > 0) {
            this._accessPermissions = true;
            this._writePermissions  = true;
            this._deletePermissions = true;
        }
    },

    _addModuleTabs:function(data) {
        this._owner = true;
        if (this._id > 0) {
            this._owner = data[0]['rights']['currentUser']['admin'];
        }
        this._addParticipantsTab(data);
        this._addRecurrenceTab(data);
        this._addNotificationTab(data);
        this._addHistoryTab();
    },

    _addRecurrenceTab:function(data) {
        // Summary:
        //    Adds a tab for recurrence.
        // Description:
        //    Adds a tab to configure the rules if/when the event will reoccure.
        var recurrenceTab = '';

        // Preset values
        var values = {
            FREQ:     'NONE',
            INTERVAL: 1,
            UNTIL:    '',
            BYDAY:    ''
        };

        // Parse data to fill the form
        if (data[0].rrule && data[0].rrule.length > 0) {
            var rrule = data[0].rrule.split(';');
            for (var i = 0; i < rrule.length; i++) {
                var rule  = rrule[i].split('=');
                var name  = rule[0];
                var value = rule[1];
                switch (name) {
                    case 'UNTIL':
                        value = dojo.date.locale.parse(value, {datePattern: "yyyyMMdd'T'HHmmss'Z'", selector: 'date'});
                        value = dojo.date.add(value, 'minute', -value.getTimezoneOffset());
                        value = dojo.date.locale.format(value, {datePattern: 'yyyy-MM-dd', selector: 'date'});
                        break;
                }
                values[name] = value;
            }
        }

        // Create ranges
        var rangeFreq = new Array(
            {'id': 'NONE',    'name': phpr.nls.get('None')},
            {'id': 'DAILY',   'name': phpr.nls.get('Daily')},
            {'id': 'WEEKLY',  'name': phpr.nls.get('Weekly')},
            {'id': 'MONTHLY', 'name': phpr.nls.get('Monthly')},
            {'id': 'YEARLY',  'name': phpr.nls.get('Yearly')}
        );

        var rangeByday = new Array(
            {'id': 'MO', 'name': phpr.nls.get('Monday')},
            {'id': 'TU', 'name': phpr.nls.get('Tuesday')},
            {'id': 'WE', 'name': phpr.nls.get('Wednesday')},
            {'id': 'TH', 'name': phpr.nls.get('Thursday')},
            {'id': 'FR', 'name': phpr.nls.get('Friday')},
            {'id': 'SA', 'name': phpr.nls.get('Saturday')},
            {'id': 'SU', 'name': phpr.nls.get('Sunday')}
        );

        // Create table
        var tableTabId = 'Recurrence';
        this._fieldTemplate.createTable(tableTabId);

        // Add fields
        // If the user is not the owner, can see the recurrence but disabled (add hidden fields for keep the value)
        if (this._id > 0) {
            var disabled = !this._owner;
        } else {
            var disabled = false;
        }
        var intervalHelp = phpr.nls.get('The interval for the option selected in Repeats.')
            + '<br />' + phpr.nls.get('E.g.: Repeats Weekly - Interval 2, that will create one event every 2 weeks.');
        var untilHelp = phpr.nls.get('The day the recurrence will stop happening.')
            + '<br />' + phpr.nls.get('The last event\'s day could not match this day.');

        // rruleFreq
        var fieldValues = {
            label:    phpr.nls.get('Repeats'),
            id:       'rruleFreq',
            type:     'selectbox',
            tab:      tableTabId,
            value:    values.FREQ,
            range:    rangeFreq,
            required: true,
            disabled: disabled,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // rruleInterval
        var fieldValues = {
            label:     phpr.nls.get('Interval'),
            id:        'rruleInterval',
            type:      'text',
            tab:       tableTabId,
            value:     values.INTERVAL,
            maxLength: 10,
            required:  true,
            disabled:  disabled,
            hint:      intervalHelp
        };
        this._fieldTemplate.addRow(fieldValues);

        // rruleUntil
        var fieldValues = {
            label:    phpr.nls.get('Until'),
            id:       'rruleUntil',
            type:     'date',
            tab:      tableTabId,
            value:    values.UNTIL,
            required: false,
            disabled: disabled,
            hint:     untilHelp
        };
        this._fieldTemplate.addRow(fieldValues);

        // rruleByDay
        var fieldValues = {
            label:    phpr.nls.get('Weekdays'),
            id:       'rruleByDay',
            type:     'multipleselectbox',
            tab:      tableTabId,
            value:    values.BYDAY,
            range:    rangeByday,
            required: false,
            disabled: disabled,
            hint:     ''
        };
        this._fieldTemplate.addRow(fieldValues);

        // Add the tab to the form
        var tabId  = 'tabRecurrence-' + this._module;
        var formId = 'recurrenceFormTab-' + this._module;
        this._addTab(this._fieldTemplate.getTable(tableTabId), tabId, 'Recurrence', formId);
    },

    _addParticipantsTab:function(data) {
        // Summary:
        //    Participants tab.
        // Description:
        //    Display all the users for add into the event.
        var currentUser             = data[0]['rights']['currentUser']['userId'] || 0;
        this._relatedData           = phpr.DataStore.getData({url: this._relatedDataUrl});

        var tabId  = 'tabParticipants-' + this._module;
        var formId = 'participantsFormTab-' + this._module;
        this._addTab(null, tabId, 'Participants', formId);

        var data = {
            userList:          this._userStore.getList(),
            participants:      this._relatedData.participants,
            accessPermissions: this._owner,
            currentUser:       currentUser
        };

        this._participantsRender.createTable(data);

        if (dijit.byId(formId).getChildren().length == 0) {
            dijit.byId(formId).domNode.appendChild(this._participantsRender.getTable());
        }
    },

    _postRenderForm:function() {
        // Summary:
        //    User functions after render the form.
        // Description:
        //    Add an onChange event for datetime values.
        if (dijit.byId('startDatetime_forDate-Calendar')) {
            this._currentDate = dijit.byId('startDatetime_forDate-Calendar').value;
            if (!this._eventForDateField) {
                this._eventForDateField = dojo.connect(dojo.byId('startDatetime_forDate-Calendar'), 'onblur', this,
                    '_startDateBlur');
                this._events.push('_eventForDateField');
            }
        }
        if (dijit.byId('startDatetime_forTime-Calendar')) {
            this._currentTime = dijit.byId('startDatetime_forTime-Calendar').value;
            if (!this._eventForTimeField) {
                this._eventForTimeField = dojo.connect(dojo.byId('startDatetime_forTime-Calendar'), 'onblur', this,
                    '_startTimeBlur');
                this._events.push('_eventForTimeField');
            }
        }
    },

    _startDateBlur:function() {
        // Summary:
        //    Checks whether to change the End date according to the modification of Start date.
        // Description:
        //   If it has changed to a valid date,
        //   then add or substract the difference between previous and current value to the End date.
        var startDate = dijit.byId('startDatetime_forDate-Calendar');
        if (this._currentDate != startDate.value) {
            if (startDate.isValid()) {
                var endDate = dijit.byId('endDatetime_forDate-Calendar');
                var diff    = dojo.date.difference(this._currentDate, startDate.value, 'day');
                endDate.set('value', dojo.date.add(endDate.value, 'day', diff));
                this._currentDate = startDate.value;
            }
        }
    },

    _startTimeBlur:function() {
        // Summary:
        //    Checks whether to change the End time according to the modification of Start time.
        // Description:
        //    If it has changed to a valid time,
        //    then add or substract the difference between previous and current value to the End time.
        var startTime = dijit.byId('startDatetime_forTime-Calendar');
        if (this._currentTime != startTime.value) {
            if (startTime.isValid()) {
                var endTime = dijit.byId('endDatetime_forTime-Calendar');
                diff        = dojo.date.difference(this._currentTime, startTime.value, 'hour');
                endTime.set('value', dojo.date.add(endTime.value, 'hour', diff));
                this._currentTime = startTime.value;
            }
        }
    },

    _prepareSubmission:function() {
        // Summary:
        //    Prepares the data for submission.
        if (!this.inherited(arguments)) {
            return false;
        }

        this._participantsInDb  = (this._relatedData.participants)
            ? this._relatedData.participants.split(',').length : 0;
        this._participantsInTab = dojo.query('.participantRow', this._participantsRender.getTable()).length;

        if (this._id > 0) {
            if (this._sendData['rruleFreq'] != 'NONE' && null === this._multipleEvents) {
                // If the event has recurrence ask what to modify
                this._showEventSelector('Edit', '_submitForm');
                return false;
            } else if (null === this._multipleParticipants) {
                if (this._participantsInDb > 1 && (this._participantsInDb == this._participantsInTab)) {
                    // If there is at least one user in Participant tab and the user hasn't changed that tab, ask him
                    this._showParticipSelector('Edit', '_submitForm');
                    return false;
                } else if (this._participantsInDb != this._participantsInTab) {
                    // If the user has modified Participant tab, changes apply for all participants
                    this._multipleParticipants = true;
                } else if (this._participantsInDb < 1) {
                    // If there was no user in Participant tab and neither now, the action is obvious:
                    this._multipleParticipants = false;
                }
            }
        }

        // Check if rule for recurrence is set
        if (this._id > 0 && false === this._multipleEvents) {
            this._sendData['rrule'] = null;
        } else if (this._sendData['rruleFreq'] != 'NONE') {
            // Set frequence
            var rrule = 'FREQ=' + this._sendData['rruleFreq'];

            // Set until value if available
            if (this._sendData['rruleUntil']) {
                var until = this._sendData['rruleUntil'];
                if (!until.setHours) {
                    until = phpr.Date.isoDateTojsDate(until);
                }
                var startDatetime = phpr.Date.isoDatetimeTojsDate(this._sendData['startDatetime']);
                until.setHours(startDatetime.getHours());
                until.setMinutes(startDatetime.getMinutes());
                until.setSeconds(startDatetime.getSeconds());
                until = dojo.date.add(until, 'minute', until.getTimezoneOffset());
                rrule += ';UNTIL=' + dojo.date.locale.format(until, {datePattern: 'yyyyMMdd\'T\'HHmmss\'Z\'',
                    selector: 'date'});
                this._sendData['rruleUntil'] = null;
            }

            // Set interval if available
            if (this._sendData['rruleInterval']) {
                rrule += ';INTERVAL=' + this._sendData['rruleInterval'];
                this._sendData['rruleInterval'] = null;
            }

            // Set weekdays if available
            if (this._sendData['rruleByDay[]']) {
                rrule += ';BYDAY=' + this._sendData['rruleByDay[]'];
                this._sendData['rruleByDay[]'] = null;
            } else if (this._sendData['rruleByDay']) {
                rrule += ';BYDAY=' + this._sendData['rruleByDay'];
                this._sendData['rruleByDay'] = null;
            }
            this._sendData['rruleFreq'] = null;

            this._sendData['rrule'] = rrule;
        } else {
            this._sendData['rrule'] = null;
        }

        delete (this._sendData['rruleByDay[]']);
        delete (this._sendData['rruleFreq']);
        delete (this._sendData['rruleInterval']);
        delete (this._sendData['rruleUntil']);

        this._sendData['multipleEvents']       = this._multipleEvents;
        this._sendData['multipleParticipants'] = this._multipleParticipants;

        return true;
    },

    _showEventSelector:function(action, nextFunction) {
        // Summary:
        //    This function shows the event dialog options.
        var dialog = dijit.byId('eventSelectorDialog-' + this._module);
        if (!dialog) {
            // Create the dialog
            var content = new dijit.layout.ContentPane({
                style: 'border: 2px solid #294064; padding: 7px;'
            }, document.createElement('div'));

            var title             = document.createElement('h2');
            title.id              = 'eventSelectorTitle-' + this._module;
            title.style.textAlign = 'center';

            // Add button for one event
            var singleEvent = new dijit.form.Button({
                label:    phpr.nls.get(action + ' just this occurrence'),
                alt:      phpr.nls.get(action + ' just this occurrence'),
                onClick:  dojo.hitch(this, function(e) {
                    this._multipleEvents = false;
                    dijit.byId('eventSelectorDialog-' + this._module).hide();
                    eval('this.' + nextFunction + '()');
                })
            });

            // Add button for multiple event
            var multipleEvent = new dijit.form.Button({
                label:   phpr.nls.get(action + ' all occurrences'),
                alt:     phpr.nls.get(action + ' all occurrences'),
                onClick: dojo.hitch(this, function(e) {
                    this._multipleEvents = true;
                    dijit.byId('eventSelectorDialog-' + this._module).hide();
                    eval('this.' + nextFunction + '()');
                })
            })

            var container = document.createElement('div');
            content.domNode.appendChild(singleEvent.domNode);
            content.domNode.appendChild(multipleEvent.domNode);
            container.appendChild(title);
            container.appendChild(content.domNode);

            var dialog = new dijit.Dialog({
                id:      'eventSelectorDialog-' + this._module,
                title:   phpr.nls.get('Calendar'),
                content: container
            });
        } else {
            var title = dojo.byId('eventSelectorTitle-' + this._module);
        }

        title.innerHTML = phpr.nls.get(action + ' repeating events');
        dialog.show();
    },

    _showParticipSelector:function(action, nextFunction) {
        // Summary:
        //    This function shows the participant dialog options.
        var dialog = dijit.byId('participantSelectorDialog-' + this._module);
        if (!dialog) {
            // Create the dialog
            var content = new dijit.layout.ContentPane({
                style: 'border: 2px solid #294064; padding: 7px;'
            }, document.createElement('div'));

            var title             = document.createElement('h2');
            title.innerHTML       = phpr.nls.get('To whom will this apply');
            title.style.textAlign = 'center';

            // Add button for one Participant
            var singleParticipant = new dijit.form.Button({
                label: phpr.nls.get(action + ' just for me'),
                alt:   phpr.nls.get(action + ' just for me'),
                onClick:  dojo.hitch(this, function(e) {
                    this._multipleParticipants = false;
                    dijit.byId('participantSelectorDialog-' + this._module).hide();
                    eval('this.' + nextFunction + '()');
                })
            });

            // Add button for multiple Participants
            var multipleParticipants = new dijit.form.Button({
                label:   phpr.nls.get(action + ' for all participants'),
                alt:     phpr.nls.get(action + ' for all participants'),
                onClick: dojo.hitch(this, function(e) {
                    this._multipleParticipants = true;
                    dijit.byId('participantSelectorDialog-' + this._module).hide();
                    eval('this.' + nextFunction + '()');
                })
            });

            var container = document.createElement('div');
            content.domNode.appendChild(singleParticipant.domNode);
            content.domNode.appendChild(multipleParticipants.domNode);
            container.appendChild(title);
            container.appendChild(content.domNode);

            var dialog = new dijit.Dialog({
                id:      'participantSelectorDialog-' + this._module,
                title:   phpr.nls.get('Calendar'),
                content: container
            });
        }

        dialog.show();
    },

    _submitForm:function() {
        // Summary:
        //    Submit the forms.
        // Description:
        //    Add params for update the views.
        if (!this._prepareSubmission()) {
            return false;
        }

        // Save data
        phpr.send({
            url: phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' + phpr.currentProjectId
                + '/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               var newItem = false;
               if (!this._id) {
                   var newItem = true;
                   this._id    = data['id'];
               }
               if (data.type == 'success') {
                   // Save tags
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module
                            + '/id/' + this._id,
                        content:   this._sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            if (this._sendData['string']) {
                                new phpr.handleResponse('serverFeedback', data);
                            }
                            if (data.type == 'success') {
                                dojo.publish(phpr.module + '.updateCacheData', [this._id,
                                    phpr.Date.getIsoDate(this._sendData['startDatetime_forDate']),
                                    phpr.Date.getIsoDate(this._sendData['endDatetime_forDate']), newItem]);
                                dojo.publish(phpr.module + '.setUrlHash', [phpr.module]);
                            }
                        })
                    });
                }
            })
        });
    },

    _deleteForm:function() {
        // Summary:
        //    Delete an item.
        var rruleFreq           = dijit.byId('recurrenceFormTab-' + this._module).get('value')['rruleFreq-Calendar'];
        this._participantsInDb  = (this._relatedData.participants)
            ? this._relatedData.participants.split(',').length : 0;
        this._participantsInTab = dojo.query('.participantRow', this._participantsRender.getTable()).length;

        if (this._id > 0) {
            if (rruleFreq != 'NONE' && null === this._multipleEvents) {
                // If the event has recurrence ask what to modify
                this._showEventSelector('Delete', '_deleteForm');
                return false;
            } else if (null === this._multipleParticipants) {
                if (this._participantsInDb > 1 && (this._participantsInDb == this._participantsInTab)) {
                    // If there is at least one user in Participant tab and the user hasn't changed that tab, ask him
                    this._showParticipSelector('Delete', '_deleteForm');
                    return false;
                } else if (this._participantsInDb != this._participantsInTab) {
                    // If the user has modified Participant tab, changes apply for all participants
                    this._multipleParticipants = true;
                } else if (this._participantsInDb < 1) {
                    // If there was no user in Participant tab and neither now, the action is obvious:
                    this._multipleParticipants = false;
                }
            }
        }

        this._sendData['multipleEvents']       = this._multipleEvents;
        this._sendData['multipleParticipants'] = this._multipleParticipants;

        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonDeleteTags/moduleName/' + phpr.module
                            + '/id/' + this._id,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type == 'success') {
                                dojo.publish(phpr.module + '.updateCacheData', [this._id,
                                    phpr.Date.getIsoDate(this._sendData['startDatetime_forDate']),
                                    phpr.Date.getIsoDate(this._sendData['endDatetime_forDate']), false]);
                                dojo.publish(phpr.module + '.setUrlHash', [phpr.module]);
                            }
                        })
                    });
               }
            })
        });
    },

    _updateCacheIds:function() {
        // Summary:
        //    This function deletes the cache of the 3 urls for the ids stored in _updateCacheIdArray.
        for (var idPos in this._updateCacheIdArray) {
            var id         = this._updateCacheIdArray[idPos];
            var url        = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + id;
            var relatedUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetRelatedData/id/' + id;
            var tagUrl     = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
                + '/id/' + id;
            phpr.DataStore.deleteData({url: url});
            phpr.DataStore.deleteData({url: relatedUrl});
            phpr.DataStore.deleteData({url: tagUrl});
        }
    }
});
