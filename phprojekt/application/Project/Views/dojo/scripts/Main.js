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
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Project.Main");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = 'Project';
        this.loadFunctions(this.module);

        dojo.subscribe("Project.basicData", this, "basicData");

        this.gridWidget = phpr.Project.Grid;
        this.formWidget = phpr.Project.Form;
        this.treeWidget = phpr.Project.Tree;
    },

    loadResult:function(id, module, projectId) {
        this.cleanPage();
        phpr.currentProjectId = id;
        this.basicData();
    },

    basicData:function() {
        phpr.module = this.module;
        this.render(["phpr.Project.template", "BasicData.html"], dojo.byId('centerMainContent'));
        this.setSubmoduleNavigation('BasicData');
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.openForm(phpr.currentProjectId, phpr.module);
        // Remove delete button
        if (dijit.byId("deleteButton")) {
            dijit.byId("deleteButton").destroy();
        }
    },

    updateCacheData:function() {
        if (this.tree) {
            this.tree.updateData();
        }
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    }
});
