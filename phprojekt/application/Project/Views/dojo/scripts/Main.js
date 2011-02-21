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

dojo.provide("phpr.Project.Main");

dojo.declare("phpr.Project.Main", phpr.Default.Main, {
    _formBasicData: null,

    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Project';

        this._loadFunctions();
        dojo.subscribe('Project.basicData', this, 'basicData');

        this._gridWidget          = phpr.Project.Grid;
        this._formWidget          = phpr.Project.Form;
        this._formBasicDataWidget = phpr.Project.FormBasicData;
    },

    updateCacheData:function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        phpr.Tree.updateData();
        if (this._grid) {
            this._grid.updateData();
        }
        if (this._form) {
            this._form.updateData();
        }
        if (this._formBasicData) {
            this._formBasicData.updateData();
        }
        phpr.DataStore.deleteAllCache();
    },

    processActionFromUrlHash:function(data) {
        // Summary:
        //    Check the action params and run the correct function.
        //    Reload is the default, but each function can redefine it.
        // Description:
        //    Open a current project form or a sub-project form (normal use).
        if (data[0] == 'basicData') {
            this.basicData();
        } else {
            this.reload();
        }
    },

    loadResult:function(id, module, projectId) {
        // Summary:
        //     Clean the result page and reload the selected module-item.
        this._cleanPage();
        phpr.parentmodule     = null;
        phpr.currentProjectId = id;
        phpr.Tree.fadeIn();
        dojo.publish(module + '.reload');
        this.setUrlHash(module, null, ['basicData']);
    },

    basicData:function() {
        // Summary:
        //     Open a new Project form.
        // Description:
        //     Open the form for the current project (Big form without grid view).
        this._setGlobalVars();
        this._cleanPage();

        // _setNavigations() for BasicData
        if (phpr.isGlobalModule(this._module)) {
            phpr.Tree.fadeOut();
        } else {
            phpr.Tree.fadeIn();
        }
        this._hideSuggest();
        this._setSearchForm();
        this._setNavigationButtons('BasicData');

        // _renderTemplate() for BasicData
        if (!dojo.byId('defaultMainContent-BasicData')) {
            phpr.Render.render(['phpr.Project.template', 'basicData.html'], dojo.byId('centerMainContent'));
        } else {
            dojo.place('defaultMainContent-BasicData', 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContent-BasicData'), 'display', 'block');
        }

        // openForm() for BasicData
        if (!this._formBasicData) {
            this._formBasicData = new this._formBasicDataWidget(module, this._subModules);
        }
        this._formBasicData.init(phpr.currentProjectId);
    }
});
