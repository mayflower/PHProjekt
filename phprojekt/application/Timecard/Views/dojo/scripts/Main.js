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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Main");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
    _date: new Date(),
    _contentWidget: null,
    startStopBar: null,

    constructor: function() {
        this.module = 'Timecard';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Timecard.Grid;
        this.formWidget = phpr.Timecard.Form;
    },

    renderTemplate: function() {
        // Summary:
        //   Custom renderTemplate for timecard
        var view = phpr.viewManager.useDefaultView({blank: true}).clear();
        this._contentWidget = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.mainContent.html",
            templateData: {
                manageFavoritesText: phpr.nls.get('Manage project list'),
                monthTxt:            phpr.date.getLongTranslateMonth(this._date.getMonth())
            }
        });
        view.centerMainContent.set('content', this._contentWidget);
        this.garbageCollector.addNode(this._contentWidget);

        // manageFavorites opens a dialog which places itself outside of the regular dom, so we need to clean it up
        // manually
        this.garbageCollector.addNode('manageFavorites');
    },

    setWidgets: function() {
        // Summary:
        //   Custom setWidgets for timecard
        phpr.tree.loadTree();
        this.grid = new this.gridWidget(this, this._date);
        this.form = new this.formWidget(this, this._date);
        this.startStopBar = new phpr.Timecard.StartStopBar({
            container: this._contentWidget.startStopButtonRow,
            date: this._date,
            onStartClick: dojo.hitch(this, "_onStartStopClick"),
            onStopClick: dojo.hitch(this, "_onStartStopClick")
        });
        this.garbageCollector.addNode(this.startStopBar);
    },

    _onStartStopClick: function() {
        this.form.updateData();
        this.grid.reload(this._date, true);
    },

    formDataChanged: function(newDate, forceReload) {
        this.grid.reload(newDate, forceReload);
        this.startStopBar.dateChanged(newDate);
    },

    setSubGlobalModulesNavigation: function(currentModule) {
    },

    changeDate: function(date) {
        // summary:
        //    Update the date and reload the views
        // description:
        //    Update the date and reload the views
        this._date = date;

        this.form.setDate(date);
        this.form.updateData();
        this.form.drawDayView();

        this.grid.reload(date);

        this.startStopBar.dateChanged(date);
    }
});
