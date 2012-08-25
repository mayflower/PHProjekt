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

dojo.provide("phpr.Calendar2.Main");
dojo.provide("phpr.Calendar2.CalendarViewMixin");
dojo.require("dijit.Dialog");
dojo.require("phpr.Calendar2.Selector");
dojo.require("phpr.Default.System.ProxyableStore");

// This ist our view mixin, it provides us with a border container without the overview box
// this is only needed because the pagestyle is relative to the bordercontainer
// TODO: make all stylings work without the bordercontainer if no overview is required
dojo.declare("phpr.Calendar2.CalendarViewMixin", phpr.Default.System.ViewContentMixin, {
    mainContent: null,
    mixin: function() {
        this.inherited(arguments);
        this.view.clear = dojo.hitch(this, "clear");
    },
    update: function() {
        this.inherited(arguments);
        this._renderMainContainer();
    },
    destroyMixin: function() {
        this.clear();
        this._clearCenterMainContent();

        for (var id in this.mainContent._attachPoints) {
            var name = this.mainContent._attachPoints[id];
            if (this.view[name] && dojo.isFunction(this.view[name].destroyDescendants)) {
                this.view[name].destroyDescendants();
            }
            delete this.view[name];
        }

        delete this.view.clear;
        this.mainContent.destroyRecursive();
        this.mainContent = null;
    },
    clear: function() {
        return this.view;
    },
    _clearCenterMainContent: function() {
        this.view.centerMainContent.destroyDescendants();
    },
    _renderMainContainer: function() {
        this._clearCenterMainContent();

        var mainContent = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Calendar2.template.mainContent.html"
        });

        this.mainContent = mainContent;

        this.view.centerMainContent.set('content', mainContent);
        this.mainContent.startup();

        // mix in all template attach points, we don't need to be picky here
        for (var id in mainContent._attachPoints) {
            var name = mainContent._attachPoints[id];
            this.view[name] = mainContent[name];
        }
    }
});

dojo.declare("phpr.Calendar2.Main", phpr.Default.Main, {
    _date:                new Date(),
    _usersSelectionMode:  false,
    _usersSelected:       [],
    _scrollLastDirection: 0,
    _gridLastScrollTop:   0,
    _scrollDelayed:       0,
    _scrollConnection:    null,
    _resizeConnection:    null,
    _actionPending:       false,
    _dateWheelChanged:    false, // Whether the current date has just changed using the mouse wheel
    _activeUser: null,
    weekList: null,
    monthList: null,
    cladavView: null,

    SCROLL_UP:    1,
    SCROLL_DOWN: -1,
    SCROLL_DELAY: 12,

    constructor: function() {
        this.module = "Calendar2";
        this.loadFunctions(this.module);
        dojo.subscribe(this.module + ".showFormFromList", this, "showFormFromList");
        dojo.subscribe(this.module + ".dayViewClick", this, "dayViewClick");
        dojo.subscribe(this.module + ".weekViewClick", this, "weekViewClick");
        dojo.subscribe(this.module + ".monthViewClick", this, "monthViewClick");
        dojo.subscribe(this.module + ".caldavViewClick", this, "caldavViewClick");
        dojo.subscribe(this.module + ".setDate", this, "setDate");
        dojo.subscribe(this.module + ".userSelectionClick", this, "userSelectionClick");
        dojo.subscribe(this.module + ".anotherViewDayClick", this, "anotherViewDayClick");
        dojo.subscribe(this.module + ".loadAppropriateList", this, "loadAppropriateList");
        dojo.subscribe(this.module + ".connectMouseScroll", this, "connectMouseScroll");
        dojo.subscribe(this.module + ".scrollDone", this, "scrollDone");
        dojo.subscribe(this.module + ".connectViewResize", this, "connectViewResize");
        dojo.subscribe(this.module + ".saveChanges", this, "saveChanges");
        dojo.subscribe(this.module + ".enableEventDivClick", this, "enableEventDivClick");
        dojo.subscribe(this.module + ".proxyChanged", this, "_changeProxyUser");
        dojo.subscribe(this.module + ".proxyLoad", this, "_loadProxyableUsers");

        this.gridWidget = phpr.Calendar2.Grid;
        this.dayListSelfWidget = phpr.Calendar2.ViewDayListSelf;
        this.dayListSelectWidget = phpr.Calendar2.ViewDayListSelect;
        this.weekListWidget = phpr.Calendar2.ViewWeekList;
        this.monthListWidget = phpr.Calendar2.ViewMonthList;
        this.caldavViewWidget = phpr.Calendar2.ViewCaldav;
        this.formWidget = phpr.Calendar2.Form;
        this.userStore = new phpr.Default.System.Store.User();
    },

    destroy: function() {
        this.inherited(arguments);
        var view = phpr.viewManager.getView().clear();
    },

    renderTemplate: function() {
        // Summary:
        //   Custom renderTemplate for calendar
        var view = phpr.viewManager.setView(phpr.Default.System.DefaultView,
                phpr.Calendar2.CalendarViewMixin, {}).clear();
    },

    setWidgets: function() {
        // Summary:
        //   Custom setWidgets for calendar
        this.userStore.fetch(
            dojo.hitch(this, function() {
                if (this.getActiveUser() === null) {
                    this.setActiveUser(this._getCurrentUser());
                }
                this.loadAppropriateList();
            }));
    },

    setActiveUser: function(user) {
        this._activeUser = user;
    },

    getActiveUser: function(user) {
        return this._activeUser;
    },

    _getCurrentUser: function() {
        var userList = this.userStore.getList();
        for (var i in userList) {
            if (userList[i].id == phpr.currentUserId) {
                return userList[i];
            }
        }
    },

    loadAppropriateList: function() {
        // Summary:
        //    Loads the appropriate list of events
        this.scrollDisconnect();
        this.resizeDisconnect();
        switch (this.state.action) {
            case "dayListSelf":
                this.loadDayListSelf();
                break;
            case "dayListSelect":
                this.loadDayListSelect();
                break;
            case "weekList":
                this.loadWeekList();
                break;
            case "monthList":
                this.loadMonthList();
                break;
            case "caldavView":
                this.loadCaldavView();
                break;
            default:
                this.loadMonthList();
        }
    },

    loadDayListSelf: function() {
        // Summary:
        //    This function loads the Day List in Self mode
        this.destroyOtherLists('dayListSelf');
        phpr.viewManager.getView().buttonRow.set('content', '');
        this.setNewEntry();
        var dateString = phpr.date.getIsoDate(this._date);
        var updateUrl  = 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' +
            phpr.currentProjectId + '/userId/' + this.getActiveUser().id;
        this.dayListSelf = new this.dayListSelfWidget(updateUrl, phpr.currentProjectId, dateString, null, this);
        this._addListToGarbageCollector(this.dayListSelf);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, true);
    },

    loadDayListSelect: function() {
        // Summary:
        //    This function loads the Day List in a Selection mode
        this.destroyOtherLists('dayListSelect');
        phpr.viewManager.getView().buttonRow.set('content', '');
        if (this._usersSelected.length > 0) {
            this.setNewEntry();
            var dateString = phpr.date.getIsoDate(this._date);
            var updateUrl  = 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' +
                phpr.currentProjectId + '/userId/' + this.getActiveUser().id;
            this.dayListSelect = new this.dayListSelectWidget(updateUrl, phpr.currentProjectId, dateString,
                    this._usersSelected, this);
            this._addListToGarbageCollector(this.dayListSelect);
            this.setSubmoduleNavigation();
            this.setScheduleBar(true, true);
        } else {
            this._changeStateWithNewAction("dayListSelf");
        }
    },

    loadWeekList: function() {
        // Summary:
        //    This function loads the Week List
        this.destroyOtherLists('weekList');
        phpr.viewManager.getView().buttonRow.set('content', '');
        this.setNewEntry();
        var dateString = phpr.date.getIsoDate(this._date);
        var updateUrl  = 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' +
            phpr.currentProjectId + '/userId/' + this.getActiveUser().id;
        this.weekList = new this.weekListWidget(updateUrl, phpr.currentProjectId, dateString, null, this);
        this._addListToGarbageCollector(this.weekList);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, false);
    },

    loadMonthList: function() {
        // Summary:
        //    This function loads the Month List
        this.destroyOtherLists('monthList');
        phpr.viewManager.getView().buttonRow.set('content', '');
        this.setNewEntry();
        var dateString = phpr.date.getIsoDate(this._date);
        this.monthList = new this.monthListWidget(this, phpr.currentProjectId, dateString, null, this);
        this._addListToGarbageCollector(this.monthList);
        this.setSubmoduleNavigation();
        this.setScheduleBar(true, false);
    },

    loadCaldavView: function() {
        // Summary:
        //    This function loads the Caldav view
        this.destroyOtherLists('caldavView');
        phpr.viewManager.getView().buttonRow.set('content', '');
        this.setNewEntry();
        this.caldavView = new this.caldavViewWidget();
        this.setSubmoduleNavigation();
    },

    _addListToGarbageCollector: function(list) {
        this.garbageCollector.addObject(list, 'lists');
    },

    showFormFromList: function(rowID) {
        // Summary:
        //    This function opens an specific item clicked from the views
        this.publish("openForm", [rowID]);
    },

    dayViewClick: function() {
        // Summary:
        //    This function loads the Day List with the entered date, if any.
        this._usersSelectionMode = false;

        this._changeStateWithNewAction("dayListSelf", true);
    },

    weekViewClick: function() {
        // Summary:
        //    This function loads the Week List with the entered date, if any.
        this._changeStateWithNewAction("weekList");
    },

    monthViewClick: function() {
        // Summary:
        //    This function loads the Month List with the entered date, if any.
        this._changeStateWithNewAction("monthList");
    },

    caldavViewClick: function() {
        // Summary:
        //    This function loads the caldav view.
        this._changeStateWithNewAction("caldavView");
    },

    _changeStateWithNewAction: function(actionName, force) {
        var options = {};

        if (force === true) {
            options.forceModuleReload = true;
        }

        phpr.pageManager.modifyCurrentState(
            {
                action: actionName
            },
            options
        );
    },

    setDate: function(day) {
        // Summary
        //    This function is called by the buttons '<< Today >>' to load a specific date into the Day or Week List
        var PREVIOUS = 0;
        var TODAY    = 1;
        var NEXT     = 2;

        var interval;
        if (this.dayListSelf || this.dayListSelect) {
            interval = 'day';
        } else if (this.weekList) {
            interval = 'week';
        } else if (this.monthList) {
            interval = 'month';
        }

        switch (day) {
            case PREVIOUS:
                this._date = dojo.date.add(this._date, interval, -1);
                break;
            case TODAY:
                /* falls through */
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

    openForm: function(/*int*/ id, /*String*/ module, /*String*/ startDate,
                      /*String*/ startTime, /*String*/ recurrenceId) {
        // Summary:
        //    This function opens a new Detail View
        this.preOpenForm();
        var view = phpr.viewManager.getView();

        var params = {};

        if (id === undefined || id === 0) {
            var today            = new Date();
            var addDay           = false;
            var startDateIsToday = false;
            var endTime          = null;

            var startDatetime = new Date();
            if (startDate !== undefined) {
                startDatetime = phpr.date.isoDatetimeTojsDate(startDate);
            }
            if (startTime === undefined) {
                startDatetime.setHours(8, 0, 0, 0);
            } else {
                var st = phpr.date.isoTimeTojsDate(startTime);
                startDatetime.setHours(st.getHours(), st.getMinutes());
            }

            var endDatetime = new Date(startDatetime);
            endDatetime.setHours(endDatetime.getHours() + 1);

            params.start = phpr.date.getIsoDatetime(startDatetime, startDatetime);
            params.end   = phpr.date.getIsoDatetime(endDatetime, endDatetime);
        }

        params.recurrenceId = recurrenceId || 0;

        this.form = new this.formWidget(this, id, module, params, null);
    },

    userSelectionClick: function() {
        // Summary:
        //    First function of the user selection window process, for the group view.
        // Description:
        //    Request the user list to the DB and then calls the next function of the process to show the selection
        // window.
        this._usersSelectionMode = true;
        var newstate = dojo.clone(this.state);
        newstate.action = "dayListSelect";
        phpr.pageManager.changeState(newstate, {noAction: true});
        this.userStore = new phpr.Default.System.Store.User();
        this.userStore.fetch(dojo.hitch(this, "selectorRender"));
    },

    selectorRender: function() {
        // Summary:
        //    Called after receiving the users list from the DB. Shows the user selection window for the group view.
        this._userList = this.userStore.getList();

        var view = phpr.viewManager.getView();

        view.selectorDialog.set('title', phpr.nls.get('Calendar2'));

        this._selectorContainerWidget = new phpr.Default.System.TemplateWrapper(
            { templateName: "phpr.Calendar2.template.selector.html" }
        );

        this.garbageCollector.addNode(this._selectorContainerWidget);

        view.selectorContainer.set('content', this._selectorContainerWidget);

        this._userSelector = new phpr.Calendar2.Selector({
            titleContainer: view.selectorTitle,
            labelContainer: this._selectorContainerWidget.label,
            errorContainer: this._selectorContainerWidget.error,
            doneButtonWidget: this._selectorContainerWidget.doneButton,
            selectionContainer: this._selectorContainerWidget.selection,
            selectorContainer: this._selectorContainerWidget.selector,
            itemList: this._userList,
            onComplete: dojo.hitch(this, this.usersSelectionDone),
            preSelection: this._usersSelected,
            labels: {
                title: phpr.nls.get('User selection'),
                label: phpr.nls.get('Select users for the group view'),
                done: phpr.nls.get('Done'),
                noSelection: phpr.nls.get('You have to select at least one user!')
            }
        });

        view.selectorDialog.show();
    },

    usersSelectionDone: function() {
        // Summary:
        //    Called once the users of the selection window have been selected.
        this._usersSelectionMode = true;
        phpr.viewManager.getView().selectorDialog.hide();

        this._usersSelected = this._userSelector.getSelection();

        this._userSelector.destroy();
        delete this._userSelector;

        this.loadDayListSelect();
    },

    anotherViewDayClick: function(date) {
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

    destroyOtherLists: function(mode) {
        // Summary:
        //    Destroys the objects of the lists not being used
        this.garbageCollector.collect('lists');
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

    setSubmoduleNavigation: function() {
        // Description:
        //    This function is responsible for displaying the Navigation top bar of the Calendar2
        //    Current submodules are: List, Day and Week.
        var activeUser = this.getActiveUser();
        var moduleViews = [];

        this.addModuleView(moduleViews, phpr.nls.get('Day'), 'dayViewClick', this.isListActive('dayList'));
        this.addModuleView(moduleViews, phpr.nls.get('Week'), 'weekViewClick', this.isListActive(this.weekList));
        this.addModuleView(moduleViews, phpr.nls.get('Month'), 'monthViewClick', this.isListActive(this.monthList));

        if (activeUser && activeUser.id === phpr.currentUserId) {
            this.addModuleView(moduleViews, phpr.nls.get('CalDav'), 'caldavViewClick', this.isListActive(this.caldavView));
        }

        if (this.isListActive('dayList')) {
            this.addModuleView(moduleViews, phpr.nls.get('Selection'), 'userSelectionClick', this._usersSelectionMode);
        }

        this._navigation = new phpr.Default.System.TabController({ });

        var selectedEntry;

        for (var i = 0; i < moduleViews.length; i++) {
            var entry = this._navigation.getEntryFromOptions({
                moduleLabel: moduleViews[i].label,
                callback: dojo.hitch(
                    this,
                    "_subModuleNavigationClick",
                    "Calendar2",
                    moduleViews[i].functionName,
                    "")
            });
            this._navigation.onAddChild(entry);

            if (moduleViews[i].activeTab && !selectedEntry) {
                selectedEntry = entry;
            }
        }

        phpr.viewManager.getView().subModuleNavigation.set('content', this._navigation);
        var dropDown = dojo.place(
            phpr.fillTemplate(
                "phpr.Calendar2.template.proxyDropDown.html",
                { label: phpr.nls.get("User") + ":" }
            ),
            this._navigation.containerNode,
            "last"
        );

        dojo.parser.parse(dropDown);

        this._navigation.onSelectChild(selectedEntry);
    },

    addModuleView: function(moduleViews, label, functionName, activeTab) {
        // Summary:
        //    Adds a specific view to the moduleViews array
        moduleViews.push({
            label: label,
            functionName: functionName,
            activeTab: activeTab
        });
    },

    isListActive: function(list) {
        // Summary
        //    Returns whether a specific list type is active or not
        var answer = false;
        if (list == 'dayList') {
            if (this.dayListSelf !== null || this.dayListSelect !== null) {
                answer = true;
            }
        } else if (list !== null) {
            answer = true;
        }

        return answer;
    },

    setScheduleBar: function(mainBar, selectionTab) {
        // Summary
        //    Shows / hide and configures the Buttons bar

        var view = phpr.viewManager.getView();
        if (mainBar) {
            var scheduleBar;
            if (!dijit.byId('scheduleBar')) {
                scheduleBar = new dijit.layout.ContentPane({id: 'scheduleBar', region: 'top',
                                                                style: 'height: 25px; overflow: hidden;'},
                                                                dojo.create('div'));
                // This should be here, and not in the scheduleBar definition, to avoid a bug on IE
                scheduleBar.set('class', 'prepend-0 append-0');
            } else {
                scheduleBar = dijit.byId('scheduleBar');
            }

            var dateDescrip;
            if (this.isListActive('dayList')) {
                var dateString  = phpr.date.getIsoDate(this._date);
                dateDescrip = this.dateDescripDay() + ', ' + dateString;
            } else if (this.isListActive(this.weekList)) {
                dateDescrip = this.getWeek() + ' . ' + phpr.nls.get('Calendar week');
            } else if (this.isListActive(this.monthList)) {
                dateDescrip = this.dateDescripMonth() + ', ' + this._date.getFullYear();
            }

            var content = this.render(["phpr.Calendar2.template", "scheduleBar.html"], null, {
                date:  dateDescrip,
                today: phpr.nls.get('Today')
            });
            scheduleBar.set('content', content);
            view.calendarMain.addChild(scheduleBar);
            view.calendarMain.resize();
        } else {
            if (dojo.byId('scheduleBar')) {
                view.calendarMain.removeChild(dijit.byId('scheduleBar'));
                view.calendarMain.resize();
            }
        }
    },

    dateDescripDay: function() {
        // Summary:
        //    Returns the day of the week we are working with, in a descriptive string of the current language
        days       = dojo.date.locale.getNames('days', 'wide');
        dayDescrip = days[this._date.getDay()];
        dayDescrip = this.capitalizeFirstLetter(dayDescrip);
        return dayDescrip;
    },

    dateDescripMonth: function() {
        // Summary
        //    Returns the month we are working with, in a descriptive string of the current language
        months       = dojo.date.locale.getNames('months', 'wide');
        monthDescrip = months[this._date.getMonth()];
        monthDescrip = this.capitalizeFirstLetter(monthDescrip);
        return monthDescrip;
    },

    getWeek: function() {
        // Summary
        //    Returns the position in the year for the week we are working with
        return dojo.date.locale.format(this._date, {datePattern: "w", selector: "date"});
    },

    capitalizeFirstLetter: function(str) {
        // Summary
        //    Capitalizes the first letter of a string
        result = str.slice(0, 1).toUpperCase() + str.slice(1);

        return result;
    },

    connectMouseScroll: function() {
        // Summary
        //    Makes the connection between the Grid event for Mouse Wheel Scroll, and the 'scrollDone' function
        var grid = phpr.viewManager.getView().gridContainer.domNode;

        this._scrollConnection = dojo.connect(grid, (!dojo.isMozilla ? "onmousewheel" : "DOMMouseScroll"), function(e) {
            // except the direction is REVERSED, and the event isn't normalized! one more line to normalize that:
            var scrollValue = e[(!dojo.isMozilla ? "wheelDelta" : "detail")] * (!dojo.isMozilla ? 1 : -1);
            dojo.publish('Calendar2.scrollDone', [scrollValue]);
        });
        if (this._dateWheelChanged) {
            this.highlightScheduleBarDate();
            this._dateWheelChanged         = false;
            grid.scrollTop = 0;
        }
    },

    connectViewResize: function() {
        // Summary:
        //    Connects the resize event of the Grid box to its appropriate function. Used in Day, Week and Month views
        var gridBox = phpr.viewManager.getView().gridContainer;
        this._resizeConnection = dojo.connect(gridBox, 'resize',  dojo.hitch(this, "gridResized"));
    },

    gridResized: function() {
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

    scrollDone: function(scrollValue) {
        // Summary
        //    Called whenever the user scrolls the mouse wheel over the grid. Detects whether to interpret it as a
        // request for changing to previous or next day/week/month grid.
        var grid   = phpr.viewManager.getView().gridContainer.domNode;

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
                    dojo.publish('Calendar2.saveChanges');
                    dojo.publish('Calendar2.setDate', [0]);

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
                    dojo.publish('Calendar2.saveChanges');
                    dojo.publish('Calendar2.setDate', [2]);
                }
            } else {
                this._scrollLastDirection = this.SCROLL_DOWN;
                this._scrollDelayed       = 0;
            }
        }
        this._gridLastScrollTop = grid.scrollTop;
    },

    scrollDisconnect: function() {
        // Summary
        //    Disconnects the event of mouse wheel scroll, of the gridBox
        if (this._scrollConnection !== null) {
            dojo.disconnect(this._scrollConnection);
            this._scrollConnection = null;
        }
    },

    resizeDisconnect: function() {
        // Summary
        //    Disconnects the event of the gridBox resize
        if (this._resizeConnection !== null) {
            dojo.disconnect(this._resizeConnection);
            this._resizeConnection = null;
        }
    },

    highlightScheduleBarDate: function() {
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

    saveChanges: function() {
        // Summary:
        //    Calls the appropriate function saveChanges depending on the class that triggered the event.
        if (this.weekList) {
            this.weekList.saveChanges();
        }
    },

    enableEventDivClick: function() {
        // Summary:
        //    Called using setTimeout to allow the events to be just clicked to open them in the form, but waiting a
        // while first, because an event has just been dragged...
        if (this.weekList) {
            this.weekList.eventClickDisabled = false;
        }
    },

    _loadProxyableUsers: function(widget) {
        this._proxySelectWidget = widget;
        this._proxyStore = new phpr.Default.System.ProxyableStore();
        this._proxyStore.fetch(dojo.hitch(this, this._proxyableUsersLoaded));
    },

    _proxyableUsersLoaded: function() {
        this._proxyableUserList = this._proxyStore.getList();
        this._populateProxySelect();
    },

    _populateProxySelect: function(users) {
        if (this._proxySelectWidget.getOptions().length === 0) {
            var options = [];
            options.push({
                label: this._getCurrentUser().display,
                value: String(this._getCurrentUser().id)
            });

            for (var index in this._proxyableUserList) {
                options.push({
                    label: this._proxyableUserList[index].display,
                    value: String(this._proxyableUserList[index].id)
                });
            }

            this._proxySelectWidget.addOption(options);
            this._ignoreFirstProxyChange = true;
        }
        this._proxySelectWidget.set('value', String(this.getActiveUser().id));
    },

    _changeProxyUser: function(widget) {
        if (this._ignoreFirstProxyChange) {
            this._ignoreFirstProxyChange = false;
        } else if (this.getActiveUser().id !== widget.get('value')) {
            this.setActiveUser(this._getUserById(widget.get('value')));
            this.reload(this.state);
        }
    },

    _getUserById: function(id) {
        var userList = this.userStore.getList();
        for (var i in userList) {
            if (userList[i].id == id) {
                return userList[i];
            }
        }
    },

    reload: function(state) {
        var activeUser = this.getActiveUser();
        if (state && (!state.action ||
                (state.action === "caldavView" && activeUser && activeUser.id != phpr.currentUserId))) {
            state.action = "monthList";
        }

        this.inherited(arguments);
    },

    processActionFromUrlHash: function(data) {
        if (!data.action) {
            data.action = "monthList";
        }
        if (this.state.action != data.action) {
            this.reload(data);
        }
    }
});
