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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Main");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
    _date: new Date(),

    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Timecard';

        this._loadFunctions();
        dojo.subscribe('Timecard.changeDate', this, 'changeDate');
        dojo.subscribe('Timecard.reloadGrid', this, 'reloadGrid');

        this._gridWidget = phpr.Timecard.Grid;
        this._formWidget = phpr.Timecard.Form;
    },

    setWidgets:function() {
        // Summary:
        //   Custom setWidgets for timecard
        phpr.Tree.loadTree();
        if (!this._grid) {
            this._grid = new this._gridWidget();
        }
        this._grid.init(this._date, false);

        if (!this._form) {
            this._form = new this._formWidget();
        }
        this._form.init(this._date);
    },

    changeDate:function(date) {
        // Summary:
        //    Update the date and reload the views.
        this._date = date;

        this._grid.init(date, false);

        this._form.setDate(date);
        this._form.drawDayView();
    },

    reloadGrid:function(date) {
        // Summary:
        //    Call reload grid form the Form.
        this._grid.init(date, true);
    },

    /************* Private functions *************/

    _renderTemplate:function() {
        // Summary:
        //    Render the module layout only one time.
        // Description:
        //    Try to create the layout if not exists, or recover it from the garbage.
        if (!dojo.byId('defaultMainContent-' + phpr.module)) {
            phpr.Render.render(['phpr.Timecard.template', 'mainContent.html'], dojo.byId('centerMainContent'));
        } else {
            dojo.place('defaultMainContent-' + phpr.module, 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContent-' + phpr.module), 'display', 'block');
        }
    },

    _customSetNavigationButtons:function() {
        // Summary:
        //     Called after the submodules are created.
        //     Is used for extend the navigation routine.
        // Description:
        //     Do not add a new entry button.
    }
});
