/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Calendar.Main");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    _date:               new Date(),
    _urlUserList:        null,
    _usersSelectionMode: false,
    _usersSelected:      Array(),

    constructor:function() {
        this.module = "Calendar";
        this.loadFunctions(this.module);
        dojo.subscribe(this.module + ".loadGrid", this, "loadGrid");
        dojo.subscribe(this.module + ".loadDayListSelf", this, "loadDayListSelf");
        dojo.subscribe(this.module + ".loadDayListSelect", this, "loadDayListSelect");
        dojo.subscribe(this.module + ".loadWeekList", this, "loadWeekList");
        dojo.subscribe(this.module + ".showFormFromList", this, "showFormFromList");
        dojo.subscribe(this.module + ".dayViewClick", this, "dayViewClick");
        dojo.subscribe(this.module + ".weekViewClick", this, "weekViewClick");
        dojo.subscribe(this.module + ".setDate", this, "setDate");
        dojo.subscribe(this.module + ".highlightBarMainSelection", this, "highlightBarMainSelection");
        dojo.subscribe(this.module + ".highlightBarUserSelection", this, "highlightBarUserSelection");
        dojo.subscribe(this.module + ".userSelfClick", this, "userSelfClick");
        dojo.subscribe(this.module + ".userSelectionClick", this, "userSelectionClick");
        dojo.subscribe(this.module + ".showSelector", this, "showSelector");
        dojo.subscribe(this.module + ".selectorRender", this, "selectorRender");
        dojo.subscribe(this.module + ".usersSelectionDoneClick", this, "usersSelectionDoneClick");

        this.gridWidget          = phpr.Calendar.Grid;
        this.dayListSelfWidget   = phpr.Calendar.ViewDayListSelf;
        this.dayListSelectWidget = phpr.Calendar.ViewDayListSelect;
        this.weekListWidget      = phpr.Calendar.ViewWeekList;
        this.formWidget          = phpr.Calendar.Form;
        this.treeWidget          = phpr.Calendar.Tree;
        this._listMode           = 'grid';

    },

    reload:function(mode, date) {
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session
        // important set the global phpr.module to the module which is currently loaded!!!

        phpr.module = this.module;
        this.render(["phpr.Calendar.template", "mainContent.html"], dojo.byId('centerMainContent') ,{
            view:       phpr.nls.get('View'),
            list:       phpr.nls.get('List'),
            day:        phpr.nls.get('Day'),
            week:       phpr.nls.get('Week'),
            changeDate: phpr.nls.get('Change date'),
            today:      phpr.nls.get('Today'),
            user:       phpr.nls.get('User'),
            self:       phpr.nls.get('Self'),
            selection:  phpr.nls.get('Selection')
        });
        dijit.byId("selectDate").attr('value', new Date(this._date.getFullYear(), this._date.getMonth(), this._date.getDate()));
        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            this.setSubGlobalModulesNavigation();
        } else {
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);

        if (mode != null) {
            this._listMode = mode;
        }
        if (date != null) {
            this._date = date;
        }

        switch (this._listMode) {
            case 'dayListSelf':
                this.loadDayListSelf();
                break;
            case 'dayListSelect':
                this.loadDayListSelect();
                break;
            case 'weekList':
                this.loadWeekList();
                break;
            case 'grid':
            default:
                this.loadGrid();
                break;
        }
    },

    loadGrid:function() {
        // summary:
        //   This function loads the Dojo Grid
        this._listMode     = 'grid';
        this.dayListSelf   = null;
        this.dayListSelect = null;
        this.weekList      = null;
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'none';
        dojo.byId('scheduleGroupBar').style.display = 'none';
        this.highlightBarMainSelection();
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    loadDayListSelf:function(date) {
        // summary:
        //    This function loads the Day List in Self mode
        this._listMode = 'dayListSelf';
        this.grid          = null;
        this.dayListSelect = null;
        this.weekList      = null;
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'inline';
        dojo.byId('scheduleGroupBar').style.display = 'inline';
        this.highlightBarMainSelection();
        this.highlightBarUserSelection();
        if (date != null) {
            this._date = date;
        }
        this.dayListSelf = new this.dayListSelfWidget(this, phpr.currentProjectId, this._date);
    },

    loadDayListSelect:function(date) {
        // summary:
        //    This function loads the Day List in a Selection mode
        this._listMode = 'dayListSelect';
        this.grid         = null;
        this.dayListSelf  = null;
        this.weekList     = null;
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'inline';
        dojo.byId('scheduleGroupBar').style.display = 'inline';
        this.highlightBarMainSelection();
        this.highlightBarUserSelection();
        if (date != null) {
            this._date = date;
        }
        this.dayListSelf = new this.dayListSelectWidget(this, phpr.currentProjectId, this._date,
                                                        this._usersSelected);
    },

    loadWeekList:function(date) {
        // summary:
        //    This function loads the Week List
        this._listMode     = 'weekList';
        this.grid          = null;
        this.dayListSelf   = null;
        this.dayListSelect = null;
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dojo.byId('scheduleBar').style.display      = 'inline';
        dojo.byId('scheduleGroupBar').style.display = 'none';
        this.highlightBarMainSelection();
        if (date != null) {
            this._date = date;
        }
        this.weekList = new this.weekListWidget(this, phpr.currentProjectId, this._date);
    },

    showFormFromList:function(rowID) {
        // summary:
        //    This function opens an specific item clicked from the views
        this.publish("openForm", [rowID]);
    },

    updateCacheData:function() {
        // summary:
        //    This function reload the grid place with the result of a search or a tag
        // description:
        //    The server return the found records and the function display it
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
        if (this.dayListSelf) {
            this.dayListSelf.updateData();
        }
        if (this.weekList) {
            this.weekList.updateData();
        }
    },

    dayViewClick:function() {
        // summary:
        //    This function loads the Day List with the entered date, if any.
        if (dijit.byId('selectDate').attr('value') != null) {
            if (!this._usersSelectionMode) {
                this.loadDayListSelf(dijit.byId('selectDate').attr('value'));
            } else {
                this.loadDayListSelect(dijit.byId('selectDate').attr('value'));
            }
        }
    },

    weekViewClick:function() {
        // summary:
        //    This function loads the Week List with the entered date, if any.
        if (dijit.byId('selectDate').attr('value') != null) {
            this.loadWeekList(dijit.byId('selectDate').attr('value'));
        }
    },

    setDate:function(day) {
        // summary
        //    This function is called by the buttons '<< Today >>' to load a specific date into the Day or Week List
        var PREVIOUS = 0;
        var TODAY    = 1;
        var NEXT     = 2;

        if (this.dayListSelf) {
            var interval = 'day';
        } else if (this.weekList) {
            var interval = 'week';
        }

        switch (day) {
            case PREVIOUS:
                var dCurrentDate = dijit.byId('selectDate').attr('value');
                this._date       = dojo.date.add(dCurrentDate, interval, -1);
                break;
            case TODAY:
            default:
                this._date = new Date();
                break;
            case NEXT:
                var dCurrentDate = dijit.byId('selectDate').attr('value');
                this._date       = dojo.date.add(dCurrentDate, interval, 1);
                break;
        }
        dijit.byId("selectDate").attr('value', this._date);
        if (this.dayListSelf) {
            this.loadDayListSelf(this._date);
        } else if (this.weekList) {
            this.loadWeekList(this._date);
        }
    },

    openForm:function(/*int*/ id, /*String*/ module, /*String*/ startDate, /*String*/ startTime) {
        // Summary:
        //    This function opens a new Detail View

        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        if (id == undefined) {
            var params = new Array();

            if (this._listMode == 'grid') {
                params['startDate'] = '';
                params['startTime'] = '08:00';
                params['endTime']   = '10:00';

            } else if ((this._listMode.substr(0,7) == 'dayList') || (this._listMode == 'weekList')) {

                if (startDate != undefined) {
                    params['startDate'] = startDate;

                } else {
                    var tmpDate      = dijit.byId("selectDate").attr('value');
                    var selectedDate = tmpDate.getFullYear()
                        + '-'
                        + dojo.number.format(tmpDate.getMonth() + 1, {pattern: '00'})
                        + '-'
                        + dojo.number.format(tmpDate.getDate(), {pattern: '00'});
                    params['startDate'] = selectedDate;
                }

                if (startTime == undefined) {
                    params['startTime'] = '08:00';
                    params['endTime']   = '10:00';

                } else {
                    params['startTime'] = startTime;
                    // Generate the End Time, 2 hours after the Start Time
                    var temp         = startTime.split(':');
                    var startHour    = parseInt(temp[0], 10);
                    var startMinutes = parseInt(temp[1], 10);
                    startHour += 2;
                    endTime = dojo.number.format(startHour, {pattern: '00'}) + ':' + dojo.number.format(startMinutes, {pattern: '00'});
                    params['endTime'] = endTime;
                }
            }
        }
        this.form = new this.formWidget(this, id, module, params);
    },

    highlightBarMainSelection:function() {
        // Summary:
        //    This function highlights setting to bold the button of the selected view mode (and unbolding the rest)

        if (this._listMode == 'grid') {
            dojo.byId('gridBarButton').style.fontWeight = 'bold';
            dojo.byId('gridBarButton').blur();
        } else {
            dojo.byId('gridBarButton').style.fontWeight = '';
        }

        if (this._listMode.substr(0, 7) == 'dayList') {
            dojo.byId('dayBarButton').style.fontWeight = 'bold';
            dojo.byId('dayBarButton').blur();
        } else {
            dojo.byId('dayBarButton').style.fontWeight = '';
        }

        if (this._listMode == 'weekList') {
            dojo.byId('weekBarButton').style.fontWeight = 'bold';
            dojo.byId('weekBarButton').blur();
        } else {
            dojo.byId('weekBarButton').style.fontWeight = '';
        }

    },

    highlightBarUserSelection:function() {
        // Summary:
        //    This function highlights setting to bold the button of user mode (self/selection) and unbolding the other

        if (!this._usersSelectionMode) {
            dojo.byId('selfBarButton').style.fontWeight      = 'bold';
            dojo.byId('selectionBarButton').style.fontWeight = '';
        } else {
            dojo.byId('selfBarButton').style.fontWeight      = '';
            dojo.byId('selectionBarButton').style.fontWeight = 'bold';
        }
    },

    userSelfClick:function() {
        // Summary:
        //    This function loads the corresponding view in 'self' mode
        if (this._usersSelectionMode) {
            this._usersSelectionMode = false;
            this.highlightBarUserSelection();
            if (dijit.byId('selectDate').attr('value') != null) {
               this.loadDayListSelf(dijit.byId('selectDate').attr('value'));
            }
        }
    },

    userSelectionClick:function() {
        // Summary:
        //    This function loads the corresponding view in 'selection' mode (group view)
        this._usersSelectionMode = true;
        this.highlightBarUserSelection();
        this.showSelector();
    },

    showSelector:function() {
        // Summary:
        //    First function of the user selection window process, for the group view.
        // Description:
        //    Request the user list to the DB and then calls the next function of the process to show the selection
        // window.
        this._urlUserList = phpr.webpath + "index.php/" + phpr.module + "/index/jsonGetAllUsers/";
        phpr.DataStore.addStore({url: this._urlUserList});
        phpr.DataStore.requestData({url: this._urlUserList, processData: dojo.hitch(this, "selectorRender")});
    },

    selectorRender:function() {
        // Summary:
        //    Called after receiving the users list from the DB. Shows the user selection window for the group view.

        var userList = phpr.DataStore.getData({url: this._urlUserList});

        phpr.destroyWidget('selectorContent');
        dojo.byId('selectorTitle').innerHTML = phpr.nls.get('User selection');
        dijit.byId('selectorDialog').attr('title', phpr.nls.get('Calendar'));

        this.render(["phpr.Calendar.template", "usersSelector.html"], dojo.byId('selectorContainer'), {
            label           : phpr.nls.get('Select users for the group view'),
            userList        : userList,
            done            : phpr.nls.get('Done'),
            noUsersSelected : phpr.nls.get('You have to select at least one user!')
        });
        if (this._usersSelected.length > 0) {
            dijit.byId('userList').setValue(this._usersSelected.join(','));
        }
        dijit.byId('selectorDialog').show();
    },

    usersSelectionDoneClick:function() {
        // Summary:
        //    Called once the users of the selection window have been selected.

        var userList = dijit.byId('userList').attr('value');
        if (userList.length == 0) {
            dojo.byId("usersSelectorError").style.visibility='visible';
            return;
        }
        dojo.byId("usersSelectorError").style.visibility='hidden';
        this._usersSelected = new Array();
        dijit.byId('selectorDialog').hide();

        // The userList array comes with lots and lots of string indexes apart from the number indexes that are the
        // correct ones, that seems to be a Dojo bug. So, here it will be picked up the only the ones that matter.
        for (var i = 0; i < userList.length; i ++) {
            this._usersSelected[i] = userList[i];
        }
        this.loadDayListSelect(dijit.byId('selectDate').attr('value'));
    }
});
