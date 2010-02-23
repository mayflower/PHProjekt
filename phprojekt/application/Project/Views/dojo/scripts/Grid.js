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

dojo.provide("phpr.Project.Grid");

dojo.declare("phpr.Project.Grid", phpr.Default.Grid, {
    updateData:function() {
        this.inherited(arguments);

        // Delete parent cache
        var parentId = phpr.Tree.getParentId(phpr.currentProjectId);
        var url      = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonList/nodeId/' + parentId;
        phpr.DataStore.deleteData({url: url});

        // Delete cache for Timecard on places where Projects are shown
        phpr.destroyWidget('timecardTooltipDialog');
        phpr.DataStore.deleteData({url: phpr.webpath + 'index.php/Timecard/index/jsonGetFavoritesProjects'});
        phpr.DataStore.deleteDataPartialString({url: phpr.webpath + 'index.php/Timecard/index/jsonDetail/'});
    },

    updateAfterSaveChanges:function() {
        // Summary:
        //    Actions after the saveChanges call returns success
        this.inherited(arguments);
        phpr.Tree.loadTree();
    }
});
