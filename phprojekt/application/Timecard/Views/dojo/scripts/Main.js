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

dojo.provide("phpr.Timecard.Main");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {

    _view: 'month',
    _date: new Date(),

    constructor:function() {
        this.module = 'Timecard';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Timecard.Grid;
        this.formWidget = phpr.Timecard.Form;
        this.treeWidget = phpr.Timecard.Tree;
        this.updateUrl  = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;

        dojo.subscribe("Timecard.Workingtimes.start", this, "workingtimesStart");
        dojo.subscribe("Timecard.Workingtimes.stop", this, "workingtimesStop");
        dojo.subscribe("Timecard.changeListView", this, "changeListView");
        dojo.subscribe("Timecard.changeDate", this, "changeDate");
    },

    reload:function() {
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
        this.render(["phpr.Timecard.template", "mainContent.html"], dojo.byId('centerMainContent') ,{
            startStopButtonsHelp: phpr.nls.get('Start Stop Buttons Help'),
            startWorkingTimeText: phpr.nls.get('Start Working Time'),
            stopWorkingTimeText:  phpr.nls.get('Stop Working Time'),
            selectDate:           phpr.nls.get('Change date')
        });
        dijit.byId("selectDate").attr('value', new Date(this._date.getFullYear(), this._date.getMonth(), this._date.getDate()));
        this.cleanPage();
        phpr.TreeContent.fadeOut();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree     = new this.treeWidget(this);
        var updateUrl = null;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        this.form     = new this.formWidget(this,0,this.module, this._date);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        this.cleanPage();
    },

    changeListView:function(view) {
        // summary:
        //    Change the list view deppend on the view param
        // description:
        //    Change the list view deppend on the view param
        this._view = view;
        if (view == 'today') {
            this.grid.reloadView(this._view);
        } else {
            this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
        }
    },

    changeDate:function(date) {
        // summary:
        //    Update the date and reload the views
        // description:
        //    Update the date and reload the views
        this._date = date
        this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
        this.form.setDate(this._date);
        this.form.loadView(this._date);
        dijit.byId("selectDate").attr('value', new Date(this._date.getFullYear(), this._date.getMonth(), this._date.getDate()));
    },

    workingtimesStop:function() {
        // summary:
        //    This function deactivates the Timecard stopwatch
        // description:
        //    This function calls jsonStop
        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonStop',
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.grid.updateData();
                    phpr.DataStore.deleteData({url: this.form._hourUrl});
                    this.changeDate(new Date());
                }
            })
        });
    },

    workingtimesStart:function() {
        // summary:
        //    This function deactivates the Timecard startwatch
        // description:
        //    This function calls jsonStart
        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonStart',
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    phpr.DataStore.deleteData({url: this.form._hourUrl});
                    this.changeDate(new Date());
                }
            })
        });
    }
});
