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

dojo.provide("phpr.Contact.Main");

dojo.declare("phpr.Contact.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Contact";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Contact.Grid;
        this.formWidget = phpr.Contact.Form;
        this.treeWidget = phpr.Contact.Tree;
    },

    updateCacheData:function() {
        phpr.DataStore.deleteAllCache();
    }
});
