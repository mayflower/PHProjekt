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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Calendar.Main");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    _date:                new Date(),
    _usersSelectionMode:  false,
    _usersSelected:       Array(),
    _scrollLastDirection: 0,
    _gridLastScrollTop:   0,
    _scrollDelayed:       0,
    _scrollConnection:    null,
    _resizeConnection:    null,
    _actionPending:       false,
    _dateWheelChanged:    false, // Whether the current date has just changed using the mouse wheel

    SCROLL_UP:    1,
    SCROLL_DOWN: -1,
    SCROLL_DELAY: 12,

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
        dojo.subscribe(this.module + ".connectMouseScroll", this, "connectMouseScroll");
        dojo.subscribe(this.module + ".scrollDone", this, "scrollDone");
        dojo.subscribe(this.module + ".connectViewResize", this, "connectViewResize");
        dojo.subscribe(this.module + ".saveChanges", this, "saveChanges");
        dojo.subscribe(this.module + ".enableEventDivClick", this, "enableEventDivClick");

        this.gridWidget          = phpr.Calendar.Grid;
        this.dayListSelfWidget   = phpr.Calendar.ViewDayListSelf;
        this.dayListSelectWidget = phpr.Calendar.ViewDayListSelect;
        this.weekListWidget      = phpr.Calendar.ViewWeekList;
        this.monthListWidget     = phpr.Calendar.ViewMonthList;
        this.formWidget          = phpr.Calendar.Form;
    },

    renderTemplate:function() {
        // Summary:
        //   Custom renderTemplate for calendar
        this.render(["phpr.Calendar.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            changeDate: phpr.nls.get('Change date'),
            today:      phpr.nls.get('Today'),
            user:       phpr.nls.get('User'),
            self:       phpr.nls.get('Self'),
            selection:  phpr.nls.get('Selection')
        });
    },

    setWidgets:function() {
        // Summary:
        //   Custom setWidgets for calendar
        phpr.Tree.loadTree();
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
        } else if (this.grid) {
            this.loadGrid();
        } else {
            // Nothing else loaded? Then loads the default one (Month)
            this.loadMonthList();
        }
    },

    loadGrid:function() {
        // Summary:
        //   This function loads the Dojo Grid
        this.scrollDisconnect();
        this.resizeDisconnect();
        this.destroyOtherLists('grid');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        this.setSubmoduleNavigation();
        this.setScheduleBar(false, false);
        this._actionPending = false;
        phpr.loading.hide();
    },

    loadDayListSelf:function() {
        // Summary:
        //    This function loads the Day List in Self mode
        this.scrollDisconnect();
        this.resizeDisconnect();
        this.destroyOtherLists('dayListSelf');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        var dateString = phpr.Date.getIsoDate(this._date);
        var updateUrl  = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.dayListSelf = new this.dayListSelfWidget(updateUrl, phpr.currentProjectId, dateString, null, this);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, true);
    },

    loadDayListSelect:function() {
        // Summary:
        //    This function loads the Day List in a Selection mode
        this.scrollDisconnect();
        this.resizeDisconnect();
        this.destroyOtherLists('dayListSelect');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        var dateString = phpr.Date.getIsoDate(this._date);
        var updateUrl  = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.dayListSelect = new this.dayListSelectWidget(updateUrl, phpr.currentProjectId, dateString,
            this._usersSelected, this);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, true);
    },

    loadWeekList:function() {
        // Summary:
        //    This function loads the Week List
        this.scrollDisconnect();
        this.resizeDisconnect();
        this.destroyOtherLists('weekList');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        var dateString = phpr.Date.getIsoDate(this._date);
        var updateUrl  = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.weekList = new this.weekListWidget(updateUrl, phpr.currentProjectId, dateString, null, this);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, false);
    },

    loadMonthList:function() {
        // Summary:
        //    This function loads the Month List
        this.scrollDisconnect();
        this.resizeDisconnect();
        this.destroyOtherLists('monthList');
        phpr.destroySubWidgets('buttonRow');
        this.setNewEntry();
        var dateString = phpr.Date.getIsoDate(this._date);
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
        if (this.actionRequested()) {
            return;
        }
        this.loadGrid();
    },

    dayViewClick:function() {
        // Summary:
        //    This function loads the Day List with the entered date, if any.
        if (dijit.byId('gridFiltersBox') && dojo.byId('gridFiltersBox').style.height != '0px') {
            dijit.byId('gridFiltersBox').toggle();
        }
        if (this.actionRequested()) {
            return;
        }
        if (!this._usersSelectionMode) {
            this.loadDayListSelf();
        } else {
            this.loadDayListSelect();
        }
    },

    weekViewClick:function() {
        // Summary:
        //    This function loads the Week List with the entered date, if any.
        if (dijit.byId('gridFiltersBox') && dojo.byId('gridFiltersBox').style.height != '0px') {
            dijit.byId('gridFiltersBox').toggle();
        }
        if (this.actionRequested()) {
            return;
        }
        this.loadWeekList();
    },

    monthViewClick:function() {
        // Summary:
        //    This function loads the Month List with the entered date, if any.
        if (dijit.byId('gridFiltersBox') && dojo.byId('gridFiltersBox').style.height != '0px') {
            dijit.byId('gridFiltersBox').toggle();
        }
        if (this.actionRequested()) {
            return;
        }
        this.loadMonthList();
    },

    setDate:function(day) {
        // Summary
        //    This function is called by the buttons '<< Today >>' to load a specific date into the Day or Week List
        var PREVIOUS = 0;
        var TODAY    = 1;
        var NEXT     = 2;

        if (this.actionRequested()) {
            return;
        }

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
            var endTime          = null;

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

                startTime = dojo.number.format(startHour, {pattern: '00'}) + ':' + '00';
                endTime   = dojo.number.format(startHour + 1, {pattern: '00'}) + ':' + '00';
            } else {
                // Generate the End Time, 1 hour after Start Time
                var temp          = startTime.split(':');
                var startHour     = parseInt(temp[0], 10);
                var startMinutes  = parseInt(temp[1], 10);
                startHour        += 1;
                endTime = dojo.number.format(startHour, {pattern: '00'}) + ':'
                    + dojo.number.format(startMinutes, {pattern: '00'});
            }

            if (startDate != undefined) {
                startDate = dojo.date.stamp.fromISOString(startDate);
                if (addDay) {
                    startDate = dojo.date.add(startDate, 'day', 1);
                }
            } else {
                if (addDay) {
                    startDate = dojo.date.add(today, 'day', 1);
                } else {
                    startDate = today;
                }
            }
            params['startDatetime'] = phpr.Date.getIsoDatetime(startDate, startTime);
            params['endDatetime']   = phpr.Date.getIsoDatetime(startDate, endTime);
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
        dijit.byId('selectorDialog').set('title', phpr.nls.get('Calendar'));

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
        var userList = dijit.byId('userList').get('value');
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
                       + '<table><tr>';

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

        navigation += '   </tr></table>'
                   + '</div>'
                   + '<div id="nav_sub">'
                       + '<table><tr>';

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

        navigation += '   </tr></table>'
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
                scheduleBar.set('class', 'prepend-0 append-0');
            } else {
                var scheduleBar = dijit.byId('scheduleBar');
            }

            if (this.isListActive('dayList')) {
                var dateString  = phpr.Date.getIsoDate(this._date);
                var dateDescrip = this.dateDescripDay() + ', ' + dateString;
            } else if (this.isListActive(this.weekList)) {
                var dateDescrip = this.getWeek() + ' . ' + phpr.nls.get('Calendar week');
            } else if (this.isListActive(this.monthList)) {
                var dateDescrip = this.dateDescripMonth() + ', ' + this._date.getFullYear();
            }

            content = this.render(["phpr.Calendar.template", "scheduleBar.html"], null, {
                date:  dateDescrip,
                today: phpr.nls.get('Today')
            });
            scheduleBar.set('content', content);
            dijit.byId('calendarMain').addChild(scheduleBar);
            dijit.byId('calendarMain').resize();
        } else {
            if (dojo.byId('scheduleBar')) {
                dijit.byId('calendarMain').removeChild(dijit.byId('scheduleBar'));
                dijit.byId('calendarMain').resize();
            }
        }
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

        // As the 'grid' object may not exist, it is not called always updateData function but deleted the cache
        // manually - Note: preUrl may be used later to make the url of other views
        var preUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/';
        if (this.grid) {
            this.grid.updateData();
        } else {
            var gridUrl = preUrl + 'jsonList/nodeId/' + phpr.currentProjectId + '/filters/';
            var tagUrl  = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
            phpr.DataStore.deleteDataPartialString({url: gridUrl});
            phpr.DataStore.deleteData({url: tagUrl});
        }

        if (this.form) {
            this.form.updateData();
            this.form = null;
        }
    },

    connectMouseScroll:function() {
        // Summary
        //    Makes the connection between the Grid event for Mouse Wheel Scroll, and the 'scrollDone' function
        var grid = dojo.byId("gridBox");

        this._scrollConnection = dojo.connect(grid, (!dojo.isMozilla ? "onmousewheel" : "DOMMouseScroll"), function(e){
           // except the direction is REVERSED, and the event isn't normalized! one more line to normalize that:
           var scrollValue = e[(!dojo.isMozilla ? "wheelDelta" : "detail")] * (!dojo.isMozilla ? 1 : -1);
           dojo.publish('Calendar.scrollDone', [scrollValue]);
        });
        if (this._dateWheelChanged) {
            this.highlightScheduleBarDate();
            this._dateWheelChanged         = false;
            dojo.byId('gridBox').scrollTop = 0;
        }
        this._actionPending = false;
    },

    connectViewResize:function() {
        // Summary:
        //    Connects the resize event of the Grid box to its appropriate function. Used in Day, Week and Month views
        var gridBox            = dijit.byId('gridBox');
        this._resizeConnection = dojo.connect(gridBox, 'resize',  dojo.hitch(this, "gridResized"));
    },

    gridResized:function() {
        // Summary:
        //    Receives the call of event of view resize and calls the appropriate function to update vars and divs.
        if (this.dayListSelf) {
            this.dayListSelf.setVarsAndDivs();
        } else if (this.dayListSelect) {
            this.dayListSelect.setVarsAndDivs();
        } else if (this.weekList) {
            this.weekList.setVarsAndDivs();
        }
    },

    scrollDone:function(scrollValue) {
        // Summary
        //    Called whenever the user scrolls the mouse wheel over the grid. Detects whether to interpret it as a
        // request for changing to previous or next day/week/month grid.
        var grid   = dojo.byId('gridBox');

        // Scrolled UP or DOWN?
        if (scrollValue > 0) {
            // UP - Is this at least the second time user scrolls up, and the grid scrolling space has reached its top?
            if (this._scrollLastDirection == this.SCROLL_UP && this._gridLastScrollTop == grid.scrollTop) {
                this._scrollDelayed ++;
                // Wait for a specific amount of scroll movements, so that day/week/month change doesn't happen without
                // intention.
                if (this._scrollDelayed >= this.SCROLL_DELAY) {
                    // Delayed 'time' reached, reset variables and go previous day/week/month
                    this._scrollLastDirection = 0;
                    this._scrollDelayed       = 0;
                    dojo.disconnect(this._scrollConnection);
                    this._dateWheelChanged = true;
                    dojo.publish('Calendar.saveChanges');
                    dojo.publish('Calendar.setDate', [0]);

                }
            } else {
                this._scrollLastDirection = this.SCROLL_UP;
                this._scrollDelayed       = 0;
            }
        } else {
            // DOWN - Is this at least the second time user scrolls up, and the grid scrolling space has reached its
            // bottom?
            if (this._scrollLastDirection == this.SCROLL_DOWN && this._gridLastScrollTop == grid.scrollTop) {
                this._scrollDelayed ++;
                // Wait for a specific amount of scroll movements, so that day/week/month change doesn't happen without
                // intention.
                if (this._scrollDelayed >= this.SCROLL_DELAY) {
                    // Delayed 'time' reached, reset variables and go next day/week/month
                    this._scrollLastDirection = 0;
                    this._scrollDelayed       = 0;
                    dojo.disconnect(this._scrollConnection);
                    this._dateWheelChanged = true;
                    dojo.publish('Calendar.saveChanges');
                    dojo.publish('Calendar.setDate', [2])
                }
            } else {
                this._scrollLastDirection = this.SCROLL_DOWN;
                this._scrollDelayed       = 0;
            }
        }
        this._gridLastScrollTop = grid.scrollTop;
    },

    scrollDisconnect:function() {
        // Summary
        //    Disconnects the event of mouse wheel scroll, of the gridBox
        if (this._scrollConnection != null) {
            dojo.disconnect(this._scrollConnection);
            this._scrollConnection = null;
        }
    },

    resizeDisconnect:function() {
        // Summary
        //    Disconnects the event of the gridBox resize
        if (this._resizeConnection != null) {
            dojo.disconnect(this._resizeConnection);
            this._resizeConnection = null;
        }
    },

    actionRequested:function() {
        // Summary
        //    The following lines are to avoid repetition of the Mouse Wheel scroll event connection, that could be
        // produced by clicking many times anxiously the same link or tab.
        //   If there is an action pending, it returns 'true' so that the caller function gets stopped.
        if (this._actionPending) {
            return true;
        } else {
            this._actionPending = true;
            phpr.loading.show();
            return false;
        }
    },

    highlightScheduleBarDate:function() {
        // Summary:
        //    Highlights the date after it has been changed using the mouse wheel
        text             = dojo.byId('scheduleBarDate');
        text.style.color = "white";
        dojox.fx.highlight({
            node:     'scheduleBarDate',
            color:    '#ffff99',
            duration: 1200
        }).play();
        setTimeout('text.style.color="black";', 1200);
    },

    saveChanges:function() {
        // Summary:
        //    Calls the appropriate function saveChanges depending on the class that triggered the event.
        if (this.weekList) {
            this.weekList.saveChanges();
        }
    },

    enableEventDivClick:function() {
        // Summary:
        //    Called using setTimeout to allow the events to be just clicked to open them in the form, but waiting a
        // while first, because an event has just been dragged...
        if (this.weekList) {
            this.weekList.eventClickDisabled = false;
        }
    }
});
