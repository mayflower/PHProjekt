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
 * @subpackage Todo
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Todo.Main");

dojo.declare("phpr.Todo.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Todo";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Todo.Grid;
        this.formWidget = phpr.Todo.Form;
    },

    openForm:function(id, module) {
        // Summary:
        //    This function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }

        if (id == undefined || id == 0) {
            var params          = new Array();
            params['startDate'] = phpr.Date.getIsoDate(new Date());
        }

        this.form = new this.formWidget(this, id, module, params);
    }
});
