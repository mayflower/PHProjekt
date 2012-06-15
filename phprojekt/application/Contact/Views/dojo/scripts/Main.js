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
 * @subpackage Contact
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Contact.Main");

dojo.declare("phpr.Contact.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Contact";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Contact.Grid;
        this.formWidget = phpr.Contact.Form;
    },

    updateCacheData:function() {
        phpr.DataStore.deleteAllCache();
    }
});
