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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Calendar2.Form");

dojo.declare("phpr.Calendar2.Form", phpr.Default.DialogForm, {
    _multipleEvents:       null,
    _multipleParticipants: null,
    _owner:                null,
    _currentDate:          null,
    _currentTime:          null,
    _updateCacheIds:       null,
    _participantsInDb:     null,
    _participantsInTab:    null,
    _originalData:         null,

    _FRMWIDG_BASICDATA:  0,
    _FRMWIDG_PARTICIP:   1,
    _FRMWIDG_RECURRENCE: 2,

    initData: function() {
        // Get the tags
        this._tagUrl = 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module + '/id/' +
            this.id;
        this._initData.push({'url': this._tagUrl});
    },

    setPermissions: function(data) {
        if (this.id > 0) {
            this._accessPermissions = true;
            this._writePermissions  = true;
            this._deletePermissions = true;
        }
    },

    prepareSubmission: function() {
        if (!this.inherited(arguments)) {
            return false;
        }

        if (this.id > 0) {
            if (this._originalData && this._originalData.rrule && null === this._multipleEvents) {
                // If the event has recurrence ask what to modify
                this.showEventSelector('Edit', "submitForm");
                return false;
            }
        }

        // Check if rule for recurrence is set
        if (this.id > 0 && false === this._multipleEvents) {
            this.sendData.rrule = null;
        } else if (this.sendData.rruleFreq) {
            // Set frequence
            var rrule = 'FREQ=' + this.sendData.rruleFreq;

            // Set until value if available
            if (this.sendData.rruleUntil) {
                until = this.sendData.rruleUntil;
                if (!until.setHours) {
                    until = phpr.date.isoDateTojsDate(until);
                }
                var startDatetime = phpr.date.isoDatetimeTojsDate(this.sendData.start);
                until.setHours(startDatetime.getHours());
                until.setMinutes(startDatetime.getMinutes());
                until.setSeconds(startDatetime.getSeconds());
                until = dojo.date.add(until, 'minute', until.getTimezoneOffset());
                rrule += ';UNTIL=' + dojo.date.locale.format(until, {datePattern: 'yyyyMMdd\'T\'HHmmss\'Z\'',
                    selector: 'date'});
                this.sendData.rruleUntil = null;
            }

            // Set interval if available
            if (this.sendData.rruleInterval) {
                rrule += ';INTERVAL=' + this.sendData.rruleInterval;
                this.sendData.rruleInterval = null;
            }

            // Set weekdays if available
            if (this.sendData['rruleByDay[]']) {
                rrule += ';BYDAY=' + this.sendData['rruleByDay[]'];
                this.sendData['rruleByDay[]'] = null;
            } else if (this.sendData.rruleByDay) {
                rrule += ';BYDAY=' + this.sendData.rruleByDay;
                this.sendData.rruleByDay = null;
            }
            this.sendData.rruleFreq = null;

            this.sendData.rrule = rrule;
        } else {
            this.sendData.rrule = null;
        }

        this.sendData.multipleEvents       = this._multipleEvents;
        this.sendData.multipleParticipants = this._multipleParticipants;

        return true;
    },

    addModuleTabs: function(data) {
        this._owner = true;
        if (this.id > 0) {
            this._owner = data[0].rights[phpr.currentUserId].admin;
        }

        var def;

        if (this._owner) {
            def = this.addParticipantsTab(data);
        }

        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addRecurrenceTab(data);
        }));
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addNotificationTab(data);
        }));
        def = dojo.when(def, dojo.hitch(this, function() {
            return this.addHistoryTab();
        }));
        return def;
    },

    postRenderForm: function() {
        // Summary:
        //    User functions after render the form
        // Description:
        //    Apply for special events on the fields
        this.inherited(arguments);
        startDate = dijit.byId('start_forDate');
        startTime = dijit.byId('start_forTime');

        endDate   = dijit.byId('end_forDate');
        endTime   = dijit.byId('end_forTime');

        if (startDate) {
            this._currentDate = startDate.value;
            dojo.connect(startDate, "onChange", this, 'startDateBlur');
        }

        if (startTime) {
            this._currentTime = startTime.value;
            dojo.connect(startTime, "onChange", this, 'startTimeBlur');
        }

        if (endDate) {
            dojo.connect(endDate, "onChange", this, 'updateAllAvailabilityStatuses');
        }

        if (endTime) {
            dojo.connect(endTime, "onChange", this, 'updateAllAvailabilityStatuses');
        }
    },

    startDateBlur: function() {
        // Summary:
        //    Checks whether to change the End date according to the modification of Start date
        // Description:
        //   If it has changed to a valid date, then add or substract the difference between previous and current value
        // to the End date
        if (this._currentDate != dijit.byId('start_forDate').value && dijit.byId('start_forDate').isValid()) {
            diff = dojo.date.difference(
                    this._currentDate,
                    dijit.byId('start_forDate').value,
                    'day'
            );
            dijit.byId('end_forDate').set(
                    'value',
                    dojo.date.add(dijit.byId('end_forDate').value, 'day', diff)
            );
            this._currentDate = dijit.byId('start_forDate').value;
        } else {
            // If we changed the end field, this gets triggered there.
            this.updateAllAvailabilityStatuses();
        }
    },

    startTimeBlur: function() {
        // Summary:
        //    Checks whether to change the End time according to the modification of Start time
        // Description:
        //    If it has changed to a valid time, then add or substract the difference between previous and current value
        // to the End time
        if (this._currentTime != dijit.byId('start_forTime').value && dijit.byId('start_forTime').isValid()) {
            diff = dojo.date.difference(
                    this._currentTime,
                    dijit.byId('start_forTime').value,
                    'minute'
            );
            dijit.byId('end_forTime').set(
                    'value',
                    dojo.date.add(
                        dijit.byId('end_forTime').value,
                        'minute',
                        diff
                    )
            );
            this._currentTime = dijit.byId('start_forTime').value;
        } else {
            // If we changed the end field, this gets triggered there.
            this.updateAllAvailabilityStatuses();
        }
    },

    addParticipantsTab: function(data) {
        // Summary:
        //    Participants tab
        // Description:
        //    Display all the users for add into the event
        var userList       = phpr.userStore.getList();
        var currentUser    = data[0].rights[phpr.currentUserId] ? phpr.currentUserId : 0;
        var participantIds = data[0].participants;
        var participants   = [];
        var users          = [];
        var statuses       = data[0].confirmationStatuses;

        if (userList) {
            for (var i in userList) {
                // Make an array with the users except the current one
                if (userList[i].id != currentUser) {
                    users.push({'id': userList[i].id, 'name': userList[i].display});
                }
            }
        }

        // Make an array with the current participants
        for (var i in participantIds) {
            if (participantIds[i] != currentUser) {
                var userName;
                for (var j in userList) {
                    if (userList[j].id == participantIds[i]) {
                        userName = userList[j].display;
                        break;
                    }
                }
                var statusClass;
                switch (statuses[participantIds[i]]) {
                    case "1": // pending
                        statusClass = "notice";
                        break;
                    case "2": // accepted
                        statusClass = "success";
                        break;
                    case "3": // rejected
                        statusClass = "error";
                        break;
                }

                participants.push({
                    'userId':   participantIds[i],
                    'userName': userName,
                    'statusClass': statusClass
                });
            }
        }
        this._participantsInDb  = participants.length;
        this._participantsInTab = participants.length;

        // Template for the participants tab
        this._participantData = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Calendar2.template.participanttab.html",
            templateData: {
                participantUserText:            phpr.nls.get('User'),
                participantActionText:          phpr.nls.get('Action'),
                participantAvailabilityText:    phpr.nls.get('Availability'),
                users:                          users,
                currentUser:                    currentUser,
                participants:                   participants
            }
        });

        var def = this.addTab([this._participantData], 'tabParticipant', 'Participants', 'participantFormTab');
        def.then(dojo.hitch(this, function() {
            // Add button for participant
            var params = {
                label:     '',
                iconClass: 'add',
                alt:       'Add',
                baseClass: 'smallIcon'
            };
            newParticipant = new dijit.form.Button(params);
            this._participantData.participantAddButton.appendChild(newParticipant.domNode);
            dojo.connect(newParticipant, "onClick", dojo.hitch(this, "newParticipant"));

            // Delete buttons for participant
            for (var i in participants) {
                var userId     = participants[i].userId;
                var buttonName = "participantDeleteButton" + userId;
                var params = {
                    label: '',
                    iconClass: 'cross',
                    alt: 'Delete',
                    baseClass: 'smallIcon'
                };

                var tmp = new dijit.form.Button(params);
                this._participantData[buttonName].appendChild(tmp.domNode);
                dojo.connect(tmp, "onClick", dojo.hitch(this, "deleteParticipant", userId));
            }
        }));
    },

    newParticipant: function() {
        // Summary:
        //    Add a new row of one participant
        // Description:
        //    Add a the row of one participant
        var userId = this._participantData.dataParticipantAdd.get('value');
        if (!dojo.byId("trParticipantFor" + userId) && userId > 0) {
            phpr.destroyWidget("dataParticipant[" + userId + "]");
            phpr.destroyWidget("ParticipantDeleteButton" + userId);

            var userName = this._participantData.dataParticipantAdd.get('displayedValue');
            var table    = this._participantData.participantTable;
            var row      = table.insertRow(table.rows.length);
            row.id       = "trParticipantFor" + userId;

            var cell = row.insertCell(0);
            cell.innerHTML = '<input id="dataParticipant[' + userId + ']" name="newParticipants[]" ' +
                ' type="hidden" value="' + userId + '" dojoType="dijit.form.TextBox" />' + userName;
            var cell = row.insertCell(1);
            cell.innerHTML = '<div id="participantDeleteButton' + userId + '"></div>';
            var cell = row.insertCell(2);
            cell.innerHTML = '<img id="participantAvailabilityIndicator' + userId + '"/>';

            this.updateAvailabilityStatus(userId);

            dojo.parser.parse(row);

            var buttonName = "participantDeleteButton" + userId;
            var params = {
                label:     '',
                iconClass: 'cross',
                alt:       'Delete',
                baseClass: 'smallIcon'
            };
            var tmp = new dijit.form.Button(params);
            dojo.byId(buttonName).appendChild(tmp.domNode);
            dojo.connect(dijit.byId(tmp.id), "onClick", dojo.hitch(this, "deleteParticipant", userId));

            this._participantsInTab += 1;
        }
    },

    updateAvailabilityStatus: function(userId) {
        cell = dojo.byId('participantAvailabilityIndicator' + userId);
        if (!cell) {
            return;
        }
        dojo.attr(cell, 'src', '/img/ajax-loader-small.gif');
        dojo.attr(cell, 'title', phpr.nls.get('Checking availability...'));

        phpr.send({
            url: 'index.php/Calendar2/Index/jsonCheckAvailability',
            content: {
                user:  userId,
                start: dojo.byId('start').value,
                end:   dojo.byId('end').value
            }
        }).then(function(data) {
            if (data && data.available) {
                dojo.attr(cell, 'src', '/css/themes/phprojekt/images/tick.gif');
                dojo.attr(cell, 'title', phpr.nls.get('The participant is available'));
            } else {
                dojo.attr(cell, 'src', '/css/themes/phprojekt/images/warning.png');
                dojo.attr(cell, 'title', phpr.nls.get('The participant is not available'));
            }
        });
    },

    updateAllAvailabilityStatuses: function(userId) {
        dojo.query('[id^=participantAvailabilityIndicator]').forEach(
            function(node) {
                this.updateAvailabilityStatus(
                        dojo.attr(node, 'id').match('[0-9]+$')
                );
            },
            this
        );
    },

    deleteParticipant: function(userId) {
        // Summary:
        //    Remove the row of one participant
        // Description:
        //    Remove the row of one participant
        //    and destroy all the used widgets
        phpr.destroyWidget("dataParticipant[" + userId + "]");
        phpr.destroyWidget("participantDeleteButton" + userId);

        var e      = dojo.byId("trParticipantFor" + userId);
        var parent = e.parentNode;
        parent.removeChild(e);
        this._participantsInTab -= 1;
    },

    addRecurrenceTab: function(data) {
        // Summary:
        //    Adds a tab for recurrence
        // Description:
        //    Adds a tab to configure the rules if/when the event will reoccure
        var recurrenceTab = [];

        // Preset values
        var values = {
            FREQ: '',
            INTERVAL: 1,
            UNTIL: '',
            BYDAY: ''
        };

        // Parse data to fill the form
        if (data[0].rrule && data[0].rrule.length > 0) {
            var rrule = data[0].rrule.split(';');
            for (var i = 0; i < rrule.length; i++) {
                rule  = rrule[i].split('=');
                name  = rule[0];
                value = rule[1];
                if (name === 'UNTIL') {
                    value = dojo.date.locale.parse(value, {datePattern: "yyyyMMdd'T'HHmmss'Z'", selector: 'date'});
                    value = dojo.date.add(value, 'minute', -value.getTimezoneOffset());
                    value = dojo.date.locale.format(value, {datePattern: 'yyyy-MM-dd', selector: 'date'});
                }
                values[name] = value;
            }
        }

        // Create ranges
        var rangeFreq = new Array(
            {'id': '', 'name': phpr.nls.get('None')},
            {'id': 'DAILY', 'name': phpr.nls.get('Daily')},
            {'id': 'WEEKLY', 'name': phpr.nls.get('Weekly')},
            {'id': 'MONTHLY', 'name': phpr.nls.get('Monthly')},
            {'id': 'YEARLY', 'name': phpr.nls.get('Yearly')}
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

        // Add fields
        // If the user is not the owner, can see the recurrence but disabled (add hidden fields for keep the value)
        var disabled;
        if (this.id > 0) {
            disabled = !this._owner;
        } else {
            disabled = false;
        }
        var intervalHelp = phpr.nls.get('The interval for the option selected in Repeats.') + '<br />' +
            phpr.nls.get('E.g.: Repeats Weekly - Interval 2, that will create one event every 2 weeks.');
        var untilHelp = phpr.nls.get('The day the recurrence will stop happening.') + '<br />' +
            phpr.nls.get('The last event\'s day could not match this day.');
        recurrenceTab.push(this.fieldTemplate.selectRender(rangeFreq, phpr.nls.get('Repeats'), 'rruleFreq', values.FREQ,
            false, disabled));
        recurrenceTab.push(this.fieldTemplate.textFieldRender(phpr.nls.get('Interval'), 'rruleInterval',
            values.INTERVAL, 10, false, disabled, intervalHelp));
        recurrenceTab.push(this.fieldTemplate.dateRender(phpr.nls.get('Until'), 'rruleUntil', values.UNTIL, false,
            disabled, untilHelp));
        recurrenceTab.push(this.fieldTemplate.multipleSelectRender(rangeByday, phpr.nls.get('Weekdays'), 'rruleByDay',
            values.BYDAY, false, disabled));

        // Add the tab to the form
        return this.addTab(recurrenceTab, 'tabRecurrence', 'Recurrence', 'recurrenceTab');
    },

    deleteForm: function() {
        // Summary:
        //    This function is responsible for deleting a dojo element
        // Description:
        //    This function calls jsonDeleteAction

        var rruleFreq = this.formsWidget[this._FRMWIDG_RECURRENCE].get('value').rruleFreq;
        if (this.id > 0) {
            // If the event has recurrence or is at least one participant added in participants tab, ask what to modify
            if (rruleFreq && null === this._multipleEvents) {
                this.showEventSelector('Delete', "deleteForm");
                return false;
            }
        }

        this.sendData.multipleEvents       = this._multipleEvents;
        this.sendData.multipleParticipants = this._multipleParticipants;

        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id + '/occurrence/' +
                    this._originalData.occurrence,
            content:   this.sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    // reload the page and trigger the form load
                    phpr.pageManager.modifyCurrentState(
                        {
                            id: undefined
                        }, {
                            forceModuleReload: true
                        }
                    );
                }
            }
        }));
    },

    showEventSelector: function(action, nextFunction) {
        var view = phpr.viewManager.getView();
        view.eventSelectorContainer.destroyDescendants();

        view.eventSelectorTitle.innerHTML = phpr.nls.get(action + ' repeating events');
        view.eventSelectorDialog.set('title', phpr.nls.get('Calendar2'));

        // Add button for one event
        var params = {
            label: phpr.nls.get(action + ' just this occurrence'),
            alt:   phpr.nls.get(action + ' just this occurrence')
        };
        var singleEvent = new dijit.form.Button(params);
        view.eventSelectorContainer.domNode.appendChild(singleEvent.domNode);
        dojo.connect(singleEvent, "onClick", dojo.hitch(this, function() {
            this._multipleEvents = false;
            view.eventSelectorDialog.hide();
            eval('this.' + nextFunction + '()');
        }));

        // Add button for multiple event
        var params = {
            label: phpr.nls.get(action + ' all occurrences after this one'),
            alt:   phpr.nls.get(action + ' all occurrences after this one')
        };
        var multipleEvent = new dijit.form.Button(params);
        view.eventSelectorContainer.domNode.appendChild(multipleEvent.domNode);
        dojo.connect(multipleEvent, "onClick", dojo.hitch(this, function() {
            this._multipleEvents = true;
            view.eventSelectorDialog.hide();
            eval('this.' + nextFunction + '()');
        }));

        view.eventSelectorDialog.show();
    },

    updateData: function() {
        this.inherited(arguments);

        // Delete the cache of the 3 urls for every related event?
        if (this._relatedData && this._relatedData.relatedEvents) {
            // Make an array with the related events
            this._updateCacheIds = this._relatedData.relatedEvents.split(',');
            if (this._updateCacheIds.length > 0 && this.useCache) {
                this.updateCacheIds();
            }
        }
        phpr.DataStore.deleteData({url: this._relatedDataUrl});
    },

    updateCacheIds: function() {
        // Summary:
        //    This function deletes the cache of the 3 urls for the ids stored in _updateCacheIds
        for (var idPos in this._updateCacheIds) {
            var id         = this._updateCacheIds[idPos];
            var url        = 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + id;
            var relatedUrl = 'index.php/' + phpr.module + '/index/jsonGetRelatedData/id/' + id;
            var tagUrl     = 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module +
                '/id/' + id;
            phpr.DataStore.deleteData({url: url});
            phpr.DataStore.deleteData({url: relatedUrl});
            phpr.DataStore.deleteData({url: tagUrl});
        }
    },

    setUrl: function(params) {
        this._url = 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/' +
            phpr.currentProjectId + '/id/' + this.id + '/occurrence/' + params.recurrenceId +
            '/userId/' + this.main.getActiveUser().id;
    },

    // We have to overwrite this function here because we need to use the id
    // returned from the server to store the tags, not the original id.
    // This is because the save could result in the event being represented by
    // another line in the db than before.
    submitForm: function(evt) {
        // Summary:
        //    This function is responsible for submitting the formdata
        // Description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine

        if (!this.prepareSubmission()) {
            return false;
        }

        this.setSubmitInProgress(true);
        phpr.send({
            url: 'index.php/' + phpr.module + '/index/jsonSave/nodeId/' + phpr.currentProjectId +
                '/id/' + this.id + '/occurrence/' + this._originalData.occurrence +
                '/userId/' + this.main.getActiveUser().id,
            content:   this.sendData
        }).then(dojo.hitch(this, function(data) {
            new phpr.handleResponse('serverFeedback', data);
            if (data.type == 'success') {
                this.id = data.id;
                return phpr.send({
                    url: 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module + '/id/' +
                        this.id,
                    content:   this.sendData
                });
            } else {
                this.setSubmitInProgress(false);
            }
        })).then(dojo.hitch(this, function(data) {
            this.setSubmitInProgress(false);
            if (data) {
                if (this.sendData.string) {
                    new phpr.handleResponse('serverFeedback', data);
                }
                if (data.type == 'success') {
                    this.publish("updateCacheData");
                    // reload the page and trigger the form load
                    phpr.pageManager.modifyCurrentState(
                        {
                            id: undefined
                        }, {
                            forceModuleReload: true
                        }
                    );
                }
            }
        }));

        return false;
    },

    getFormData: function() {
        // Summary:
        //    Override this method to save whether we have a recurrence set on
        //    the server.
        phpr.Calendar2.Form.superclass.getFormData.apply(this);
        var data = phpr.DataStore.getData({url: this._url});
        if (data.length > 0) {
            this._originalData = data[0];
        }
    }
});
