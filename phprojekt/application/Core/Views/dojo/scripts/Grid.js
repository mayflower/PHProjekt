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

dojo.provide("phpr.Core.Grid");

dojo.declare("phpr.Core.Grid", phpr.Default.Grid, {

    setUrl:function() {
        this.url = phpr.webpath + "index.php/Core/" + phpr.module.toLowerCase() + "/jsonList";
    },

    setGetExtraActionsUrl:function() {
        this.getActionsUrl = phpr.webpath + "index.php/Core/" + phpr.module.toLowerCase() + "/jsonGetExtraActions";
    },

    useCheckbox:function() {
        return false;
    },

    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    },

    setExportButton:function(meta) {
        // Summary:
        //    This empty function is here to override the Default one and make it not show export button
        // in Administration module
    }
});
