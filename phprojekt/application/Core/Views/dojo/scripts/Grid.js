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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Core.Grid");

dojo.declare("phpr.Core.Grid", phpr.Default.Grid, {
    setUrl:function() {
        this.url = phpr.webpath + 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonList/nodeId/1';
    },

    setGetExtraActionsUrl:function() {
        this.getActionsUrl = phpr.webpath + 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonGetExtraActions';
    },

    useCheckbox:function() {
        return false;
    },

    getLinkForEdit:function(id) {
        phpr.pageManager.modifyCurrentState({
            moduleName: this.main.module,
            id: id,
            action: this.main.action ? this.main.action : undefined
        });
    },

    canEdit:function(inRowIndex) {
        return true;
    },

    showTags:function() {
    },

    setExportButton:function(meta) {
    },

    getDoActionUrl:function(action, idUrl, ids) {
        // Summary:
        //    Custom getDoActionUrl for Core
        return phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/' + action + '/' + idUrl + '/' + ids;
    }
});
