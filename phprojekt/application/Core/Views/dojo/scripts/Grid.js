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

dojo.provide("phpr.Core.Grid");

dojo.declare("phpr.Core.Grid", phpr.Default.LegacyGrid, {
    setUrl: function() {
        this.url = 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonList/nodeId/1';
    },

    setGetExtraActionsUrl: function() {
        this.getActionsUrl = 'index.php/Core/' + this.main.module.toLowerCase() + '/jsonGetExtraActions';
    },

    useCheckbox: function() {
        return false;
    },

    editItemWithId: function(id) {
        phpr.pageManager.modifyCurrentState({
            moduleName: this.main.module,
            id: id,
            action: this.main.action ? this.main.action : undefined
        });
    },

    canEdit: function(inRowIndex) {
        return true;
    },

    setExportButton: function(meta) {
    },

    getDoActionUrl: function(action, idUrl, ids) {
        // Summary:
        //    Custom getDoActionUrl for Core
        return 'index.php/Core/' + phpr.module.toLowerCase() + '/' + action + '/' + idUrl + '/' + ids;
    }
});
