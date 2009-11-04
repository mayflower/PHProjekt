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

dojo.provide("phpr.Tab.Main");

dojo.declare("phpr.Tab.Main", phpr.Core.Main, {
    constructor:function() {
        this.module = "Tab";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Tab.Grid;
        this.formWidget = phpr.Tab.Form;
        this.treeWidget = phpr.Tab.Tree;
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
        var tabStore = new phpr.Store.Tab();
        tabStore.update();
    }
});
