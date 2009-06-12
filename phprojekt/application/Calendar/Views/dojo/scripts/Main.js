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
    _usersSelectionMode: false,
    _usersSelected:      Array(),

    constructor:function() {
        this.module = "Calendar";
        this.loadFunctions(this.module);
        dojo.subscribe(this.module + ".showFormFromList", this, "showFormFromList");
        dojo.subscribe(this.module + ".listViewClick", this, "listViewClick");
        dojo.subscribe(this.module + ".dayViewClick", this, "dayViewClick");
        dojo.subscribe(this.module + ".weekViewClick", this, "weekViewClick");
        dojo.subscribe(this.module + ".monthViewClick", this, "monthViewClick");
        dojo.subscribe(this.module + ".setDate", this, "setDate");
        dojo.subscribe(this.module + ".userSelfClick", this, "userSelfClick");
        dojo.subscribe(this.module + ".userSelectionClick", this, "userSelectionClick");
        dojo.subscribe(this.module + ".usersSelectionDoneClick", this, "usersSelectionDoneClick");
        dojo.subscribe(this.module + ".anotherViewDayClick", this, "anotherViewDayClick");
        dojo.subscribe(this.module + ".loadAppropriateList", this, "loadAppropriateList");

        this.gridWidget          = phpr.Calendar.Grid;
        this.dayListSelfWidget   = phpr.Calendar.ViewDayListSelf;
        this.dayListSelectWidget = phpr.Calendar.ViewDayListSelect;
        this.weekListWidget      = phpr.Calendar.ViewWeekList;
        this.monthListWidget     = phpr.Calendar.ViewMonthList;
        this.formWidget          = phpr.Calendar.Form;
        this.treeWidget          = phpr.Calendar.Tree;
    },

    reload:function() {
        // Summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session
        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        this.render(["phpr.Calendar.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            changeDate: phpr.nls.get('Change date'),
            today:      phpr.nls.get('Today'),
            user:       phpr.nls.get('User'),
            self:       phpr.nls.get('Self'),
            selection:  phpr.nls.get('Selection')
        });

        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            phpr.TreeContent.fadeOut();
            this.setSubGlobalModulesNavigation();
        } else {
            phpr.TreeContent.fadeIn();
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.loadAppropriateList();
    },

    loadAppropriateList:function() {
        // Summary:
        //    Loads the appropriate list of events
        if (this.dayListSelf) {
            this.loadDayListSelf();
        } else if (this.dayListSelect) {
            this.loadDayListSelect();
        } else if (this.weekList) {
            this.loadWeekList();
        } else if (this.monthList) {
            this.loadMonthList();
        } else {
            // Nothing else loaded? Then loads the default one
            this.loadGrid();
        }
    },

    loadGrid:function() {
        // Summary:
        //   This function loads the Dojo Grid
        this.destroyOtherLists('grid');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        this.setSubmoduleNavigation();
        this.setScheduleBar(false, false);
    },

    loadDayListSelf:function() {
        // Summary:
        //    This function loads the Day List in Self mode
        this.destroyOtherLists('dayListSelf');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dateString       = this.dateToString();
        this.dayListSelf = new this.dayListSelfWidget(this, phpr.currentProjectId, dateString);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, true);
    },

    loadDayListSelect:function() {
        // Summary:
        //    This function loads the Day List in a Selection mode
        this.destroyOtherLists('dayListSelect');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dateString         = this.dateToString();
        this.dayListSelect = new this.dayListSelectWidget(this, phpr.currentProjectId, dateString, this._usersSelected);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, true);
    },

    loadWeekList:function() {
        // Summary:
        //    This function loads the Week List
        this.destroyOtherLists('weekList');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dateString    = this.dateToString();
        this.weekList = new this.weekListWidget(this, phpr.currentProjectId, dateString);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, false);
    },

    loadMonthList:function() {
        // Summary:
        //    This function loads the Month List
        this.destroyOtherLists('monthList');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        dateString     = this.dateToString();
        this.monthList = new this.monthListWidget(this, phpr.currentProjectId, dateString);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, false);
    },

    showFormFromList:function(rowID) {
        // Summary:
        //    This function opens an specific item clicked from the views
        this.publish("openForm", [rowID]);
    },

    listViewClick:function() {
        // Summary:
        //    List button clicked, loads the regular grid
        this.loadGrid();
    },

    dayViewClick:function() {
        // Summary:
        //    This function loads the Day List with the entered date, if any.
        if (!this._usersSelectionMode) {
            this.loadDayListSelf();
        } else {
            this.loadDayListSelect();
        }
    },

    weekViewClick:function() {
        // Summary:
        //    This function loads the Week List with the entered date, if any.
        this.loadWeekList();
    },

    monthViewClick:function() {
        // Summary:
        //    This function loads the Month List with the entered date, if any.
        this.loadMonthList();
    },

    setDate:function(day) {
        // Summary
        //    This function is called by the buttons '<< Today >>' to load a specific date into the Day or Week List
        var PREVIOUS = 0;
        var TODAY    = 1;
        var NEXT     = 2;

        if (this.dayListSelf || this.dayListSelect) {
            var interval = 'day';
        } else if (this.weekList) {
            var interval = 'week';
        } else if (this.monthList) {
            var interval = 'month';
        }

        switch (day) {
            case PREVIOUS:
                this._date = dojo.date.add(this._date, interval, -1);
                break;
            case TODAY:
            default:
                this._date = new Date();
                break;
            case NEXT:
                this._date = dojo.date.add(this._date, interval, 1);
                break;
        }
        if (this.dayListSelf) {
            this.loadDayListSelf();
        } else if (this.dayListSelect) {
            this.loadDayListSelect();
        } else if (this.weekList) {
            this.loadWeekList();
        } else if (this.monthList) {
            this.loadMonthList();
        }
    },

    openForm:function(/*int*/ id, /*String*/ module, /*String*/ startDate, /*String*/ startTime) {
        // Summary:
        //    This function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        if (id == undefined || id == 0) {
            var params           = new Array();
            var today            = new Date();
            var addDay           = false;
            var startDateIsToday = false;

            if (startTime == undefined) {
                if (startDate == undefined) {
                    startDateIsToday = true;
                } else {
                    // The selected day is today?
                    todayStart = new Date();
                    todayStart.setHours(0, 0, 0, 0);
                    startDate_Date = dojo.date.stamp.fromISOString(startDate);
                    if (dojo.date.compare(todayStart, startDate_Date) == 0) {
                        startDateIsToday = true;
                    }
                }

                if (startDateIsToday) {
                    var startHour = today.getHours();
                    if (today.getMinutes() != 0) {
                        startHour ++;
                    }
                    if (startHour < 8) {
                        startHour = 8;
                    }
                    if (startHour > 17) {
                        startHour = 8;
                        addDay    = true;
                    }
                } else {
                    startHour = 8;
                }

                params['startTime'] = dojo.number.format(startHour, {pattern: '00'}) + ':' + '00';
                params['endTime']   = dojo.number.format(startHour + 1, {pattern: '00'}) + ':' + '00';

            } else {
                params['startTime'] = startTime;
                // Generate the End Time, 1 hour after Start Time
                var temp          = startTime.split(':');
                var startHour     = parseInt(temp[0], 10);
                var startMinutes  = parseInt(temp[1], 10);
                startHour        += 1;
                params['endTime'] = dojo.number.format(startHour, {pattern: '00'}) + ':'
                                  + dojo.number.format(startMinutes, {pattern: '00'});
            }

            if (startDate != undefined) {
                if (addDay) {
                    startDate           = dojo.date.stamp.fromISOString(startDate);
                    startDate           = dojo.date.add(startDate, 'day', 1);
                    params['startDate'] = dojo.date.stamp.toISOString(startDate);
                    params['startDate'] = params['startDate'].substr(0, 10);
                } else {
                    params['startDate'] = startDate;
                }
            } else {
                if (addDay) {
                    startDate           = dojo.date.add(today, 'day', 1);
                    params['startDate'] = dojo.date.stamp.toISOString(startDate);
                } else {
                    params['startDate'] = dojo.date.stamp.toISOString(today);
                }
                params['startDate'] = params['startDate'].substr(0, 10);
            }
            params['endDate'] = params['startDate'];
        }

        this.form = new this.formWidget(this, id, module, params);
    },

    userSelfClick:function() {
        // Summary:
        //    This function loads the corresponding view in 'self' mode
        if (this._usersSelectionMode) {
            this._usersSelectionMode = false;
            this.loadDayListSelf();
        }
    },

    userSelectionClick:function() {
        // Summary:
        //    First function of the user selection window process, for the group view.
        // Description:
        //    Request the user list to the DB and then calls the next function of the process to show the selection
        // window.
        this.userStore = new phpr.Store.User();
        this.userStore.fetch(dojo.hitch(this, "selectorRender"));
    },

    selectorRender:function() {
        // Summary:
        //    Called after receiving the users list from the DB. Shows the user selection window for the group view.
        var userList = this.userStore.getList();

        phpr.destroyWidget('selectorContent');
        dojo.byId('selectorTitle').innerHTML = phpr.nls.get('User selection');
        dijit.byId('selectorDialog').attr('title', phpr.nls.get('Calendar'));

        // Mark as select the selected users
        for (var i = 0; i < userList.length; i++) {
            userList[i].selected = '';
            if (this._usersSelected.length > 0) {
                for (var j = 0; j < this._usersSelected.length; j++) {
                    if (this._usersSelected[j] == userList[i].id) {
                        userList[i].selected = 'selected="selected"';
                    }
                }
            }
        }

        this.render(["phpr.Calendar.template", "usersSelector.html"], dojo.byId('selectorContainer'), {
            label:           phpr.nls.get('Select users for the group view'),
            userList:        userList,
            done:            phpr.nls.get('Done'),
            noUsersSelected: phpr.nls.get('You have to select at least one user!')
        });

        dijit.byId('selectorDialog').show();
    },

    usersSelectionDoneClick:function() {
        // Summary:
        //    Called once the users of the selection window have been selected.
        var userList = dijit.byId('userList').attr('value');
        if (userList.length == 0) {
            dojo.byId("usersSelectorError").style.visibility = 'visible';
            return;
        }
        this._usersSelectionMode = true;
        dojo.byId("usersSelectorError").style.visibility = 'hidden';
        this._usersSelected = new Array();
        dijit.byId('selectorDialog').hide();

        // The userList array comes with lots and lots of string indexes apart from the number indexes (these last ones
        // are the correct ones). This seems to be a Dojo bug. So, here it will be picked up the only the ones that
        // matter.
        for (var i = 0; i < userList.length; i ++) {
            this._usersSelected[i] = userList[i];
        }
        this.loadDayListSelect();
    },

    anotherViewDayClick:function(date) {
        // Summary:
        //    The header of every day in the week view and every cell of the month view have a link to this function to
        // load the day list of a specific day.
        var temp  = date.split('-');
        var year  = temp[0];
        var month = temp[1];
        var day   = temp[2];

        this._date.setFullYear(year);
        this._date.setMonth(month - 1);
        this._date.setDate(day);

        this._usersSelectionMode = false;
        this.loadDayListSelf();
    },

    destroyOtherLists:function(mode) {
        // Summary:
        //    Destroys the objects of the lists not being used
        if (mode != 'grid') {
            this.grid = null;
        }
        if (mode != 'dayListSelf') {
            this.dayListSelf = null;
        }
        if (mode != 'dayListSelect') {
            this.dayListSelect = null;
        }
        if (mode != 'weekList') {
            this.weekList = null;
        }
        if (mode != 'monthList') {
            this.monthList = null;
        }
    },

    setSubmoduleNavigation:function() {
        // Description:
        //    This function is responsible for displaying the Navigation top bar of the Calendar
        //    Current submodules are: List, Day and Week.
        var moduleViews = new Array();
        this.addModuleView(moduleViews, phpr.nls.get('List'), 'listViewClick', this.isListActive(this.grid));
        this.addModuleView(moduleViews, phpr.nls.get('Day'), 'dayViewClick', this.isListActive('dayList'));
        this.addModuleView(moduleViews, phpr.nls.get('Week'), 'weekViewClick', this.isListActive(this.weekList));
        this.addModuleView(moduleViews, phpr.nls.get('Month'), 'monthViewClick', this.isListActive(this.monthList));

        navigation = '<div id="nav_main" class="left" style="width: 300px;">'
                       + '<ul>';

        for (var i = 0; i < moduleViews.length; i++) {
            var liclass = '';
            if (moduleViews[i].activeTab) {
                liclass = 'class = active';
            }
            navigation += this.render(["phpr.Default.template", "navigation.html"], null, {
                moduleName :    'Calendar',
                moduleLabel:    moduleViews[i].label,
                liclass:        liclass,
                moduleFunction: moduleViews[i].functionName,
                functionParams: ""
            });
        }

        navigation += '   </ul>'
                   + '</div>'
                   + '<div id="nav_sub">'
                       + '<ul>';

        var moduleViews = new Array();
        if (!this.isListActive('dayList')) {
            this.addModuleView(moduleViews, phpr.nls.get('Self'), 'userSelfClick', true);
        } else {
            this.addModuleView(moduleViews, phpr.nls.get('Self'), 'userSelfClick', !this._usersSelectionMode);
            this.addModuleView(moduleViews, phpr.nls.get('Selection'), 'userSelectionClick', this._usersSelectionMode);
        }

        for (var i = 0; i < moduleViews.length; i++) {
            var liclass = '';
            if (moduleViews[i].activeTab) {
                liclass = 'class = active';
            }
            navigation += this.render(["phpr.Default.template", "navigation.html"], null, {
                moduleName :    'Calendar',
                moduleLabel:    moduleViews[i].label,
                liclass:        liclass,
                moduleFunction: moduleViews[i].functionName,
                functionParams: ""
            });
        }

        navigation += '   </ul>'
                   + '</div>';

        dojo.byId("subModuleNavigation").innerHTML = navigation;
        phpr.initWidgets(dojo.byId("subModuleNavigation"));
    },

    addModuleView:function(moduleViews, label, functionName, activeTab) {
        // Summary:
        //    Adds a specific view to the moduleViews array
        var i                          = moduleViews.length;
        moduleViews[i]                 = new Array();
        moduleViews[i]['label']        = label;
        moduleViews[i]['functionName'] = functionName;
        moduleViews[i]['activeTab']    = activeTab;
    },

    isListActive:function(list) {
        // Summary
        //    Returns whether a specific list type is active or not
        var answer = false;
        if (list == 'dayList') {
            if (this.dayListSelf != undefined || this.dayListSelect != undefined) {
                answer = true;
            }
        } else if (list != undefined) {
            answer = true;
        }

        return answer;
    },

    setScheduleBar:function(mainBar, selectionTab) {
        // Summary
        //    Shows / hide and configures the Buttons bar
        if (mainBar) {
            if (!dijit.byId('scheduleBar')) {
                var scheduleBar = new dijit.layout.ContentPane({id: 'scheduleBar', region:'top',
                                                                style:'height: 6%; overflow: hidden;'});
                // This should be here, and not in the scheduleBar definition, to avoid a bug on IE
                scheduleBar.attr('class', 'prepend-0 append-0');
            } else {
                var scheduleBar = dijit.byId('scheduleBar');
            }

            if (this.isListActive('dayList')) {
                var dateString  = this.dateToString();
                var dateDescrip = this.dateDescripDay() + ', ' + this.formatDate(dateString);
            } else if (this.isListActive(this.weekList)) {
                var dateDescrip = this.getWeek() + ' . ' + phpr.nls.get('Calendar week');
            } else if (this.isListActive(this.monthList)) {
                var dateDescrip = this.dateDescripMonth() + ', ' + this._date.getFullYear();
            }

            content = this.render(["phpr.Calendar.template", "scheduleBar.html"], null, {
                date:  dateDescrip,
                today: phpr.nls.get('Today')
            });
            scheduleBar.attr('content', content);
            dijit.byId('calendarMain').addChild(scheduleBar);
            dijit.byId('calendarMain').resize();
        } else {
            if (dojo.byId('scheduleBar')) {
                dijit.byId('calendarMain').removeChild(dijit.byId('scheduleBar'));
                dijit.byId('calendarMain').resize();
            }
        }
    },

    dateToString:function() {
        // Summary
        //    Returns the date we are working with, in string format
        return this._date.getFullYear() + '-' + (this._date.getMonth() + 1) + '-' + this._date.getDate();
    },

    dateDescripDay:function() {
        // Summary:
        //    Returns the day of the week we are working with, in a descriptive string of the current language
        days       = dojo.date.locale.getNames('days', 'wide');
        dayDescrip = days[this._date.getDay()];
        dayDescrip = this.capitalizeFirstLetter(dayDescrip);
        return dayDescrip;
    },

    dateDescripMonth:function() {
        // Summary
        //    Returns the month we are working with, in a descriptive string of the current language
        months       = dojo.date.locale.getNames('months', 'wide');
        monthDescrip = months[this._date.getMonth()];
        monthDescrip = this.capitalizeFirstLetter(monthDescrip);
        return monthDescrip;
    },

    formatDate:function(date) {
        // Summary:
        //    Formats a date string. E.g. receives '2009-5-4' and returns '2009-05-04'
        var temp   = date.split('-');
        var year   = temp[0];
        var month  = temp[1];
        var day    = temp[2];
        var result = year + '-' + dojo.number.format(month, {pattern: '00'}) + '-'
            + dojo.number.format(day, {pattern: '00'});

        return result;
    },

    getWeek:function() {
        // Summary
        //    Returns the position in the year for the week we are working with
        var firstDayYear = new Date(this._date.getFullYear(),0,1);
        var week         = Math.ceil((((this._date - firstDayYear) / 86400000) + firstDayYear.getDay())/7);
        return week;
    },

    capitalizeFirstLetter:function(str) {
        // Summary
        //    Capitalizes the first letter of a string
        result = str.slice(0,1).toUpperCase() + str.slice(1);
        return result;
    },

    updateCacheData:function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.

        // As the 'grid' object may not exist, it is not called updateData function but deleted the cache manually
        var gridUrl = phpr.webpath + "index.php/" + phpr.module + "/index/jsonList/nodeId/" + phpr.currentProjectId;
        var tagUrl  = phpr.webpath + "index.php/Default/Tag/jsonGetTags";
        phpr.DataStore.deleteData({url: gridUrl});
        phpr.DataStore.deleteData({url: tagUrl});

        if (this.form) {
            this.form.updateData();
        }
    }
});
