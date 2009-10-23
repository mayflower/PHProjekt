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

dojo.provide("phpr.Administration.Main");

dojo.declare("phpr.Administration.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Administration";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Administration.Grid;
        this.formWidget = phpr.Administration.Form;
        this.treeWidget = phpr.Administration.Tree;

        dojo.subscribe("Administration.loadSubModule", this, "loadSubModule");
        dojo.subscribe("Administration.setSubGlobalModulesNavigation", this, "setSubGlobalModulesNavigation");
        dojo.subscribe("Administration.customSetSubmoduleNavigation", this, "customSetSubmoduleNavigation");
    },

    reload:function() {
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
        var summaryTxt = '<b>' + phpr.nls.get('Administration') + '</b>'
            + '<br /><br />'
            + phpr.nls.get('Here can be configured general settings of the site that affects all the users.')
            + '<br /><br />'
            + phpr.nls.get('Please choose one of the tabs of above.');
        this.render(["phpr.Administration.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            summaryTxt: summaryTxt
        });
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
        phpr.module       = this.module;
        phpr.submodule    = module;
        phpr.parentmodule = '';
        this.render(["phpr.Administration.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            summaryTxt: ''
        });
        this.cleanPage();
        phpr.TreeContent.fadeOut();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree = new this.treeWidget(this);
        this.form = new this.formWidget(this, 0, this.module);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        var subModuleUrl = phpr.webpath + 'index.php/Administration/index/jsonGetModules';
        var self = this;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this, function() {
                var modules = new Array();
                modules.push({
                    "name":           "Module",
                    "label":          phpr.nls.get('Module'),
                    "moduleFunction": "setUrlHash",
                    "functionParams": "'Administration', null, ['Module']"});
                modules.push({
                    "name":           "Tab",
                    "label":          phpr.nls.get('Tab'),
                    "moduleFunction": "setUrlHash",
                    "functionParams": "'Administration', null, ['Tab']"});
                modules.push({
                    "name":           "User",
                    "label":          phpr.nls.get('User'),
                    "moduleFunction": "setUrlHash",
                    "functionParams": "'Administration', null, ['User']"});
                modules.push({
                    "name":           "Role",
                    "label":          phpr.nls.get('Role'),
                    "moduleFunction": "setUrlHash",
                    "functionParams": "'Administration', null, ['Role']"});
                tmp = phpr.DataStore.getData({url: subModuleUrl});
                for (var i = 0; i < tmp.length; i++) {
                    modules.push({
                        "name":           tmp[i].name,
                        "label":          tmp[i].label,
                        "moduleFunction": "setUrlHash",
                        "functionParams": "'Administration', null, ['" + tmp[i].name + "']"});
                }
                var navigation ='<ul id="nav_main">';
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = modules[i].moduleFunction;
                    var functionParams = modules[i].functionParams;
                    if (moduleName == phpr.submodule) {
                        liclass   = 'class = active';
                    }
                    navigation += self.render(["phpr.Administration.template", "navigation.html"], null, {
                        moduleName :    'Administration',
                        moduleLabel:    moduleLabel,
                        liclass:        liclass,
                        moduleFunction: moduleFunction,
                        functionParams: functionParams
                    });
                }
                navigation += "</ul>";
                dojo.byId("subModuleNavigation").innerHTML = navigation;
                phpr.initWidgets(dojo.byId("subModuleNavigation"));
                if (phpr.submodule == 'Module' || phpr.submodule == 'Tab' ||
                    phpr.submodule == 'User' || phpr.submodule == 'Role') {
                    dojo.publish(phpr.submodule + ".customSetSubmoduleNavigation");
                } else {
                    dojo.publish("Administration.customSetSubmoduleNavigation");
                }
            })
        })
    },

    updateCacheData:function() {
        phpr.DataStore.deleteAllCache();
        if (this.tree) {
            this.tree.updateData();
        }
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    },

    processActionFromUrlHash:function(data) {
        if (data[0]) {
            if (data[0] == 'Module' ||
                data[0] == 'Tab' ||
                data[0] == 'User' ||
                data[0] == 'Role') {
                dojo.publish(data[0] + ".reload");
            } else {
                this.loadSubModule(data[0]);
            }
        }
    },

    customSetSubmoduleNavigation:function() {
    }
});
