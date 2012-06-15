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
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Tab.Main");

dojo.declare("phpr.Tab.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "Tab";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Tab.Grid;
        this.formWidget = phpr.Tab.Form;
    },

    customSetSubmoduleNavigation:function() {
        this.setNewEntry();
    },

    updateCacheData:function() {
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
        var tabStore = new phpr.Default.System.Store.Tab();
        tabStore.update();
    }
});
