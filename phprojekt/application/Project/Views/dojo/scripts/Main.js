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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Project.Main");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = 'Project';
        this.loadFunctions(this.module);

        dojo.subscribe("Project.basicData", this, "basicData");

        this.gridWidget          = phpr.Project.Grid;
        this.formWidget          = phpr.Project.DialogForm;
        this.formBasicDataWidget = phpr.Project.FormBasicData;
    },

    loadResult:function(id, module, projectId) {
        this.cleanPage();
        phpr.parentmodule = null;
        phpr.currentProjectId = id;
        phpr.tree.fadeIn();
        phpr.pageManager.modifyCurrentState({
            moduleName: this.module,
            action: 'basicData',
            projectId: projectId,
            id: id
        })
    },

    basicData:function() {
        var view = phpr.viewManager.useDefaultView({blank: true}).clear();

        phpr.module = this.module;
        this.destroy();

        this.cleanPage();

        this.destroyForm();
        this.destroyGrid();

        this.setSubmoduleNavigation('BasicData');
        this.hideSuggest();
        this.setSearchForm();
        phpr.tree.fadeIn();
        phpr.tree.loadTree();

        this.form = new this.formBasicDataWidget(this, phpr.currentProjectId, phpr.module, {}, view.centerMainContent);
    },

    openForm:function(id, module) {
        // Summary:
        //    This function opens a new Detail View
        this.preOpenForm();

        if (id == undefined || id == 0) {
            var params          = new Array();
            params['startDate'] = phpr.date.getIsoDate(new Date());
        }

        var view = phpr.viewManager.useDefaultView({blank: true});

        if (!this.grid) {
            this.reload();
        }

        this.form = new this.formWidget(this, id, module, params, null);
    },

    updateCacheData:function() {
        phpr.tree.updateData();
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
        phpr.DataStore.deleteAllCache();
    },

    processActionFromUrlHash:function(data) {
        if (data.action == 'basicData') {
            this.basicData();
        } else {
            this.reload();
        }
    }
});
