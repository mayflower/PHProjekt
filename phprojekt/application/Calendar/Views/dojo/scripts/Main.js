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

dojo.provide("phpr.Calendar.Main");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
    _date:                null,
    _dayListSelf:         null,
    _dayListSelfWidget:   null,
    _dayListSelect:       null,
    _dayListSelectWidget: null,
    _monthList:           null,
    _monthListWidget:     null,
    _userStore:           null,
    _weekList:            null,
    _weekListWidget:      null,
    _usersSelected:       [],
    _viewToUse:           'week',
    _needResize:          false,

    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Calendar';

        this._loadFunctions();
        dojo.subscribe('Calendar.changeView', this, 'changeView');
        dojo.subscribe('Calendar.setDate', this, 'setDate');
        dojo.subscribe('Calendar.openDayView', this, 'openDayView');
        dojo.subscribe('Calendar.updateCacheDataFromView', this, 'updateCacheDataFromView');

        this._gridWidget          = phpr.Calendar.Grid;
        this._dayListSelfWidget   = phpr.Calendar.ViewDayListSelf;
        this._dayListSelectWidget = phpr.Calendar.ViewDayListSelect;
        this._weekListWidget      = phpr.Calendar.ViewWeekList;
        this._monthListWidget     = phpr.Calendar.ViewMonthList;
        this._formWidget          = phpr.Calendar.Form;
        this._userStore           = new phpr.Store.User();

        this._needResize              = [];
        this._needResize['list']      = false;
        this._needResize['daySelf']   = false;
        this._needResize['daySelect'] = false;
        this._needResize['week']      = false;
        this._needResize['month']     = false;
    },

    setWidgets:function() {
        // Summary:
        //   Custom setWidgets for calendar
        phpr.Tree.loadTree();

        if (!this._date) {
            this._date = new Date();
        }

        // Hide buttons of other views
        if (dijit.byId('exportCsvButton')) {
            dijit.byId('exportCsvButton').domNode.style.display = 'none';
        }
        if (dijit.byId('gridFiltersButton')) {
            dijit.byId('gridFiltersButton').domNode.style.display = 'none';
        }
        if (dijit.byId('exportCsvButtondaySelf-Calendar')) {
            dijit.byId('exportCsvButtondaySelf-Calendar').domNode.style.display = 'none';
        }
        if (dijit.byId('exportCsvButtondaySelect-Calendar')) {
            dijit.byId('exportCsvButtondaySelect-Calendar').domNode.style.display = 'none';
        }
        if (dijit.byId('exportCsvButtonweek-Calendar')) {
            dijit.byId('exportCsvButtonweek-Calendar').domNode.style.display = 'none';
        }
        if (dijit.byId('exportCsvButtonmonth-Calendar')) {
            dijit.byId('exportCsvButtonmonth-Calendar').domNode.style.display = 'none';
        }

        // Hide other views
        dojo.byId('listContent-Calendar').style.display = 'none';
        dojo.place('listContent-Calendar', 'garbage');
        dojo.byId('daySelfContent-Calendar').style.display = 'none';
        dojo.place('daySelfContent-Calendar', 'garbage');
        dojo.byId('daySelectContent-Calendar').style.display = 'none';
        dojo.place('daySelectContent-Calendar', 'garbage');
        dojo.byId('weekContent-Calendar').style.display = 'none';
        dojo.place('weekContent-Calendar', 'garbage');
        dojo.byId('monthContent-Calendar').style.display = 'none';
        dojo.place('monthContent-Calendar', 'garbage');

        // Show and render the current view
        switch(this._viewToUse) {
            case 'daySelf':
                dojo.place('daySelfContent-Calendar', 'content-Calendar', 'fisrt');
                dojo.style(dojo.byId('daySelfContent-Calendar'), 'display', 'block');
                this._loadDayListSelf();
                break;
            case 'daySelect':
                dojo.place('daySelectContent-Calendar', 'content-Calendar', 'fisrt');
                dojo.style(dojo.byId('daySelectContent-Calendar'), 'display', 'block');
                this._userStore.fetch(dojo.hitch(this, '_displayDialogForSelectUsers'));
                break;
            case 'week':
                dojo.place('weekContent-Calendar', 'content-Calendar');
                dojo.style(dojo.byId('weekContent-Calendar'), 'display', 'block');
                this._loadWeekList();
                break;
            case 'list':
                dojo.place('listContent-Calendar', 'content-Calendar');
                dojo.style(dojo.byId('listContent-Calendar'), 'display', 'block');
                this._loadGrid();
                break;
            case 'month':
            default:
                dojo.place('monthContent-Calendar', 'content-Calendar');
                dojo.style(dojo.byId('monthContent-Calendar'), 'display', 'block');
                this._loadMonthList();
                break;
        }
    },

    changeView:function(view) {
        // Summary:
        //     Change the view and reload it.
        this._viewToUse = view;
        this._setNavigationButtons();
        this.setWidgets();
    },

    setDate:function(day) {
        // Summary:
        //    Called by the buttons '<< Today >>' to load a specific date into the views.
        var PREVIOUS = 0;
        var TODAY    = 1;
        var NEXT     = 2;

        if (this._isViewActive('day')) {
            var interval = 'day';
        } else if (this._isViewActive('week')) {
            var interval = 'week';
        } else if (this._isViewActive('month')) {
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

        if (this._viewToUse == 'daySelf') {
            this._loadDayListSelf();
        } else if (this._viewToUse == 'daySelect') {
            this._loadDayListSelect();
        } else if (this._isViewActive('week')) {
            this._loadWeekList();
        } else if (this._isViewActive('month')) {
            this._loadMonthList();
        }
    },

    openForm:function(id, module, startDate, startTime) {
        // Summary:
        //     Open a new form.
        if (!dojo.byId('detailsBox-' + phpr.module)) {
            this.reload();
        }

        if (id == undefined || id == 0) {
            var params           = [];
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

        if (!this._form) {
            this._form = new this._formWidget(module, this._subModules);
        }
        this._form.init(id, params);
    },

    openDayView:function(date) {
        // Summary:
        //    The header of every day in the week view and every cell of the month view
        //    have a link to this function to load the day list of a specific day.
        var temp  = date.split('-');
        var year  = temp[0];
        var month = temp[1];
        var day   = temp[2];

        this._date.setFullYear(year);
        this._date.setMonth(month - 1);
        this._date.setDate(day);

        this.changeView('daySelf');
    },

    updateCacheData:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        var keepView    = this._viewToUse;
        this._viewToUse = 'form'; // Update also the current view but not the form.
        this.updateCacheDataFromView(id, startDate, endDate, newItem);
        // Restore the view
        this._viewToUse = keepView;

        // Remove tags
        var tagUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
        phpr.DataStore.deleteData({url: tagUrl});

        // Update the form
        if (this._form) {
            this._form.updateData();
        }
    },

    updateCacheDataFromView:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Update the cache of the views using the id and dates.
        // Description:
        //    If is a newItem, remove all the cache, if not, just the id/date.
        if (this._grid) {
            this._grid.updateData();
        }
        if (this._dayListSelf && this._viewToUse != 'daySelf') {
            this._dayListSelf.updateData(id, startDate, endDate, newItem);
        }
        if (this._dayListSelect && this._viewToUse != 'daySelect') {
            this._dayListSelect.updateData(id, startDate, endDate, newItem);
        }
        if (this._weekList && this._viewToUse != 'week') {
            this._weekList.updateData(id, startDate, endDate, newItem);
        }
        if (this._monthList && this._viewToUse != 'month') {
            this._monthList.updateData(id, startDate, endDate, newItem);
        }
        if (this._form && this._viewToUse != 'form') {
            this._form.showLayout('none');
            // Update urls for this Id
            var url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/' + phpr.currentProjectId
                + '/id/' + id;
            phpr.DataStore.deleteData({url: url});
            var url = phpr.webpath + 'index.php/Default/Tag/jsonGetTagsByModule/moduleName/' + phpr.module
                + '/id/' + id;
            phpr.DataStore.deleteData({url: url});
            var url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetUsersRights'
                + '/nodeId/' + phpr.currentProjectId + '/id/' + id;
            phpr.DataStore.deleteData({url: url});

            var url = phpr.webpath + 'index.php/Calendar/index/jsonGetRelatedData/id/' + id;
            phpr.DataStore.deleteData({url: url});
        }
    },

    /************* Private functions *************/

    _renderTemplate:function() {
        // Summary:
        //    Render the module layout only one time.
        // Description:
        //    Try to create the layout if not exists, or recover it from the garbage.
        if (!dojo.byId('defaultMainContent-' + phpr.module)) {
            phpr.Render.render(['phpr.Calendar.template', 'mainContent.html'], dojo.byId('centerMainContent'), {
                today: phpr.nls.get('Today')
            });

            var view = dijit.byId('content-Calendar');
            dojo.connect(view, 'resize',  dojo.hitch(this, '_resizeLayout'));
        } else {
            dojo.place('defaultMainContent-' + phpr.module, 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContent-' + phpr.module), 'display', 'block');
        }
    },

    _resizeLayout:function() {
        // Summary:
        //    Add/Remove the flag on each view for resize it the next time that the view is showm.
        if (this._needResize[this._viewToUse]) {
            // The content was resizes by the view
            this._needResize[this._viewToUse] = false;
        } else {
            // The content was resized by the user => add flag to all the views
            this._needResize['list']      = true;
            this._needResize['daySelf']   = true;
            this._needResize['daySelect'] = true;
            this._needResize['week']      = true;
            this._needResize['month']     = true;
            this.changeView(this._viewToUse);
        }
    },

    _setNavigationButtons:function(currentModule) {
        // Summary:
        //    Display the navigation tabs of the current module.
        var modules = [];
        this._addModuleView(modules, 'List', 'list', this._isViewActive('list'));
        this._addModuleView(modules, 'Day', 'daySelf', this._isViewActive('day'));
        this._addModuleView(modules, 'Week', 'week', this._isViewActive('week'));
        this._addModuleView(modules, 'Month', 'month', this._isViewActive('month'));

        // Create the buttons for the modules (only if not exists)
        var activeTab = false;
        for (var i = 0; i < modules.length; i++) {
            var liclass        = '';
            if (modules[i].activeTab) {
                liclass = 'class = active';
            }

            var td = dojo.byId('navigation_' + modules[i].name);
            if (!td) {
                var buttonHtml = phpr.Render.render(['phpr.Default.template', 'navigation.html'], null, {
                    id:             modules[i].name,
                    moduleName :    'Calendar',
                    moduleLabel:    modules[i].label,
                    liclass:        liclass,
                    moduleFunction: 'changeView',
                    functionParams: '"' + modules[i].functionParams + '"'});
                dojo.place(buttonHtml, 'tr_nav_main');
            } else {
                dojo.removeClass(td, 'hidden active');
                if (liclass == 'class = active') {
                    dojo.addClass(td, 'active');
                }
            }
        }

        // Add spaces
        for (var i = 0; i < 5; i++) {
            var td = dojo.byId('navigation_empty_' + i);
            if (!td) {
                var buttonHtml = phpr.Render.render(['phpr.Default.template', 'navigationEmpty.html'], null, {
                    id: i
                });
                dojo.place(buttonHtml, 'tr_nav_main');
            } else {
                dojo.removeClass(td, 'hidden active');
            }
        }

        var modules = [];
        if (!this._isViewActive('day')) {
            if (dojo.byId('navigation_Self')) {
                dojo.addClass(dojo.byId('navigation_Self'), 'hidden');
            }
            if (dojo.byId('navigation_Selection')) {
                dojo.addClass(dojo.byId('navigation_Selection'), 'hidden');
            }
        } else {
            this._addModuleView(modules, 'Self', 'daySelf', this._isViewActive('daySelf'));
            this._addModuleView(modules, 'Selection', 'daySelect', this._isViewActive('daySelect'));
        }

        for (var i = 0; i < modules.length; i++) {
            var liclass = '';
            if (modules[i].activeTab) {
                liclass = 'class = active';
            }

            var td = dojo.byId('navigation_' + modules[i].name);
            if (!td) {
                var buttonHtml = phpr.Render.render(['phpr.Default.template', 'navigation.html'], null, {
                    id:             modules[i].name,
                    moduleName :    'Calendar',
                    moduleLabel:    modules[i].label,
                    liclass:        liclass,
                    moduleFunction: 'changeView',
                    functionParams: '"' + modules[i].functionParams + '"'});
                dojo.place(buttonHtml, 'tr_nav_main');
            } else {
                dojo.removeClass(td, 'hidden active');
                if (liclass == 'class = active') {
                    dojo.addClass(td, 'active');
                }
            }
        }

        // Resize for the changes
        dijit.byId('subModuleNavigation').layout();

        this._customSetNavigationButtons();
    },

    _addModuleView:function(moduleViews, name, functionParams, activeTab) {
        // Summary:
        //    Adds a specific view to the moduleViews array.
        var i                            = moduleViews.length;
        moduleViews[i]                   = [];
        moduleViews[i]['name']           = name;
        moduleViews[i]['label']          = phpr.nls.get(name);
        moduleViews[i]['functionParams'] = functionParams;
        moduleViews[i]['activeTab']      = activeTab;
    },

    _isViewActive:function(view) {
        // Summary:
        //    Returns whether a specific view type is active or not.
        switch (view) {
            case 'list':
                return (this._viewToUse == 'list');
                break;
            case 'day':
                return (this._viewToUse == 'daySelf' || this._viewToUse == 'daySelect');
                break;
            case 'daySelf':
                return (this._viewToUse == 'daySelf');
                break;
            case 'daySelect':
                return (this._viewToUse == 'daySelect');
                break;
            case 'week':
                return (this._viewToUse == 'week');
                break;
            case 'month':
            default:
                return (this._viewToUse == 'month');
                break;
        }
    },

    _loadGrid:function() {
        // Summary:
        //   Loads the Dojo Grid.
        if (!this._grid) {
            this._grid = new this._gridWidget(phpr.module);
        }
        this._grid.init(phpr.currentProjectId, this._needResize[this._viewToUse]);
    },

    _loadDayListSelf:function() {
        // Summary:
        //    Loads the Day List in Self mode.
        var dateString = phpr.Date.getIsoDate(this._date);
        if (!this._dayListSelf) {
            this._dayListSelf = new this._dayListSelfWidget();
        }
        this._dayListSelf.init(dateString, this._needResize[this._viewToUse]);
    },

    _loadDayListSelect:function() {
        // Summary:
        //    Loads the Day List in a Selection mode.
        var dateString = phpr.Date.getIsoDate(this._date);
        if (!this._dayListSelect) {
            this._dayListSelect = new this._dayListSelectWidget();
        }
        var userList = dijit.byId('select_users[]-Calendar').get('value');
        if (userList.length == 0) {
            dojo.byId('usersSelectorError-Calendar').style.visibility = 'visible';
            return;
        } else {
            dojo.byId('usersSelectorError-Calendar').style.visibility = 'hidden';
        }

        this._usersSelected = [];
        dijit.byId('selectorDialog-Calendar').hide();

        // Get only the ids of the users.
        for (var i = 0; i < userList.length; i ++) {
            this._usersSelected[i] = userList[i];
        }

        dojo.byId('daySelectContent-Calendar').style.display = 'block';
        this._dayListSelect.init(dateString, this._usersSelected, this._needResize[this._viewToUse]);
    },

    _displayDialogForSelectUsers:function() {
        // Summary:
        //    Show a dialog to select the users for the day select view.
        // Mark as select the selected users
        var userList = phpr.clone(this._userStore.getList());
        // Fix => Change display for name
        for (var i in userList) {
            userList[i]['name'] = userList[i]['display'];
        }
        var fieldValues = {
            type:     'multipleselectbox',
            id:       'select_users',
            label:    '',
            disabled: false,
            required: false,
            value:    this._usersSelected.join(','),
            range:    userList,
            tab:      1,
            hint:     ''
        };

        var dialog = dijit.byId('selectorDialog-Calendar');
        if (!dialog) {
            // Content
            var container   = document.createElement('div');
            var title       = document.createElement('h1');
            title.innerHTML = phpr.nls.get('User selection');
            var content = new dijit.layout.ContentPane({
                style: 'width: 330px; height: 310px; border: 2px solid #294064; padding-left: 7px;'
            }, document.createElement('div'));
            container.appendChild(title);
            container.appendChild(content.domNode);

            // Table
            var table             = document.createElement('table');
            table.style.width     = '100%';
            table.style.textAlign = 'center';

            // Title
            var row      = table.insertRow(table.rows.length);
            var td       = row.insertCell(0);
            td.clasName  = 'label';
            dojo.style(td, {textAlign: 'center', fontWeight: 'bold'});
            td.innerHTML = '<br />' + phpr.nls.get('Select users for the group view') + ':<br /><br />';

            // Select
            var row            = table.insertRow(table.rows.length);
            var td             = row.insertCell(0);
            td.style.textAlign = 'center';
            var widgetClass    = new phpr.Field.MultipleselectField(fieldValues, 'Calendar');
            td.appendChild(dijit.byId(widgetClass.fieldId).domNode);
            dijit.byId(widgetClass.fieldId).domNode.style.textAlign = 'left';

            // Error
            var row             = table.insertRow(table.rows.length);
            row.style.height    = '27px';
            var td              = row.insertCell(0);
            td.id               = 'usersSelectorError-Calendar';
            td.className        = 'label';
            dojo.style(td, {visibility: 'hidden', textAlign: 'center', fontWeight: 'bold', color: 'red'});
            td.innerHTML = phpr.nls.get('You have to select at least one user!');

            // Button
            var row            = table.insertRow(table.rows.length);
            var td             = row.insertCell(0);
            td.style.textAlign = 'center';

            var button = new dijit.form.Button({
                label:     phpr.nls.get('Done'),
                baseClass: 'positive',
                onClick:   dojo.hitch(this, '_loadDayListSelect')
            });
            td.appendChild(button.domNode);

            content.set('content', table);

            var dialog = new dijit.Dialog({
                id:        'selectorDialog-Calendar',
                title:     phpr.nls.get('Calendar'),
                draggable: false,
                style:     'background-color: white; width: auto; height: auto; text-align: center;',
                content:   container
            });
        } else {
            // Update values
            var widgetClass = new phpr.Field.MultipleselectField(fieldValues, 'Calendar');
        }

        dialog.show();
    },

    _loadWeekList:function() {
        // Summary:
        //    Loads the Week List.
        var dateString = phpr.Date.getIsoDate(this._date);
        if (!this._weekList) {
            this._weekList = new this._weekListWidget();
        }
        this._weekList.init(dateString, this._needResize[this._viewToUse]);
    },

    _loadMonthList:function() {
        // Summary:
        //    Loads the Month List.
        var dateString  = phpr.Date.getIsoDate(this._date);
        if (!this._monthList) {
            this._monthList = new this._monthListWidget();
        }
        this._monthList.init(dateString, this._needResize[this._viewToUse]);
    }
});
