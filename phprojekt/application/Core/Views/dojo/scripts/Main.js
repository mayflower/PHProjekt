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

dojo.provide("phpr.Core.Main");

dojo.declare("phpr.Core.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Core";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Core.Grid;
        this.formWidget = phpr.Core.Form;
        this.treeWidget = phpr.Core.Tree;
    },

    reload:function() {
        phpr.module       = this.module;
        phpr.submodule    = this.module;
        phpr.parentmodule = 'Administration';
        this.render(["phpr.Default.template", "mainContent.html"], dojo.byId('centerMainContent'));
        this.cleanPage();
        phpr.TreeContent.fadeOut();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        dojo.publish("Administration.setSubGlobalModulesNavigation", [currentModule]);
    },

    processActionFromUrlHash:function(data) {
        dojo.publish("Administration.processActionFromUrlHash", [data]);
    }
});
