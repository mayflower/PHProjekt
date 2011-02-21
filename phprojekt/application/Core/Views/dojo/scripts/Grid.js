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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Core.Grid");

dojo.declare("phpr.Core.Grid", phpr.Default.Grid, {
    _setGetExtraActionsUrl:function() {
        // Summary:
        //    Sets the url where to get the grid actions data from.
        this._getActionsUrl = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonGetExtraActions';
    },

    _setUpdateUrl:function() {
        // Summary:
        //    Sets the url for save the changes.
        this._updateUrl = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonSaveMultiple/nodeId/1';
    },

    _setUrl:function() {
        // Summary:
        //    Set the url for getting the data.
        this._url = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonList/nodeId/1';
    },

    _useCheckbox:function() {
        // Summary:
        //    Whether to show or not the checkbox in the grid list.
        return false;
    },

    _setExportButton:function(meta) {
        // Summary:
        //    If there is any row, render an export Button.
    },

    _showTags:function() {
        // Summary:
        //    Draw the tags.
    },

    _getDoActionUrl:function(action, idUrl, ids) {
        // Summary:
        //    Custom getDoActionUrl for Core
        return phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/' + action + '/' + idUrl + '/' + ids;
    },

    _getLinkForEdit:function(id) {
        // Summary:
        //    Return the link for open the form.
        dojo.publish(phpr.module + '.setUrlHash', [phpr.parentmodule, id, [phpr.module]]);
    },

    _canEdit:function(inRowIndex) {
        // Summary:
        //    Check the user access on the item.
        return true;
    }
});
