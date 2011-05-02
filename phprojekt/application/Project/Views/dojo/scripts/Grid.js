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
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Project.Grid");

dojo.declare("phpr.Project.Grid", phpr.Default.Grid, {
    updateData:function() {
        // Summary:
        //    Delete the cache for this grid.
        this.inherited(arguments);

        // Delete parent cache
        var parentId = phpr.Tree.getParentId(phpr.currentProjectId);
        var url      = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonList/nodeId/' + parentId;
        phpr.DataStore.deleteData({url: url});

        // Delete cache for Timecard on places where Projects are shown
        dojo.publish('Timecard.formProxy', ['forceUpdate']);
        phpr.DataStore.deleteData({url: phpr.webpath + 'index.php/Timecard/index/jsonGetFavoritesProjects'});
        phpr.DataStore.deleteDataPartialString({url: phpr.webpath + 'index.php/Timecard/index/jsonDetail/'});
    },

    _setUpdateUrl:function() {
        // Summary:
        //    Sets the url for save the changes.
        var parentId    = phpr.Tree.getParentId(phpr.currentProjectId);
        this._updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + parentId;
    },

    _updateAfterSaveChanges:function() {
        // Summary:
        //    Actions after the saveChanges call returns success.
        this.inherited(arguments);
        phpr.Tree.loadTree();
    }
});
