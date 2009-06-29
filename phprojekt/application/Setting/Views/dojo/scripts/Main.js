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

dojo.provide("phpr.Setting.Main");

dojo.declare("phpr.Setting.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Setting";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Setting.Grid;
        this.formWidget = phpr.Setting.Form;
        this.treeWidget = phpr.Setting.Tree;

        dojo.subscribe("Setting.loadSubModule", this, "loadSubModule");
    },

    reload:function() {
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
        this.render(["phpr.Setting.template", "mainContent.html"], dojo.byId('centerMainContent'));
        this.cleanPage();
        phpr.TreeContent.fadeOut();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
    },

    loadSubModule:function(/*String*/module) {
        //summary: this function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        phpr.submodule = module;
        this.setSubGlobalModulesNavigation();
        this.form = new this.formWidget(this, 0, this.module);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        var subModuleUrl = phpr.webpath + 'index.php/Setting/index/jsonGetModules';
        var self = this;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this, function() {
                modules = phpr.DataStore.getData({url: subModuleUrl});
                var navigation ='<ul id="nav_main">';
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = "setUrlHash";
                    var functionParams = "'Setting', null, ['" + modules[i].name + "']";
                    if (moduleName == phpr.submodule) {
                        liclass   = 'class = active';
                    }
                    navigation += self.render(["phpr.Setting.template", "navigation.html"], null, {
                        moduleName :    moduleName,
                        moduleLabel:    moduleLabel,
                        liclass:        liclass,
                        moduleFunction: moduleFunction,
                        functionParams: functionParams
                    });
                }
                navigation += "</ul>";
                dojo.byId("subModuleNavigation").innerHTML = navigation;
                phpr.initWidgets(dojo.byId("subModuleNavigation"));
                this.customSetSubmoduleNavigation();
            })
        })
    },

    updateCacheData:function() {
        if (this.form) {
            this.form.updateData();
        }
    },

    processActionFromUrlHash:function(data) {
        if (data[0]) {
            this.loadSubModule(data[0]);
        }
    }
});
