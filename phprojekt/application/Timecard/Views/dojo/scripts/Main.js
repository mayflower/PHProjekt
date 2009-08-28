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
    _date: new Date(),

    constructor:function() {
        this.module = 'Timecard';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Timecard.Grid;
        this.formWidget = phpr.Timecard.Form;
        this.treeWidget = phpr.Timecard.Tree;

        dojo.subscribe("Timecard.changeDate", this, "changeDate");
    },

    reload:function() {
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
        this.render(["phpr.Timecard.template", "mainContent.html"], dojo.byId('centerMainContent'));
        this.cleanPage();
        phpr.TreeContent.fadeOut();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.grid = new this.gridWidget(this, this._date);
        this.form = new this.formWidget(this, this._date);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
    },

    changeDate:function(date) {
        // summary:
        //    Update the date and reload the views
        // description:
        //    Update the date and reload the views
        this._date = date;

        this.form.setDate(date);
        this.form.drawDayView();
        this.form.resetForm();

        this.grid.reload(date);
    }
});
