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

dojo.provide("phpr.Statistic.Main");

dojo.declare("phpr.Statistic.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Statistic";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Statistic.Grid;
        this.formWidget = phpr.Statistic.Form;
        this.treeWidget = phpr.Statistic.Tree;

        dojo.subscribe("Statistic.changeDate", this, "changeDate");
    },

    reload:function() {
        // summary:
        //    Reload function for Statistics
        // description:
        //    Reload function for Statistics
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
        this.render(["phpr.Statistic.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            webpath:            phpr.webpath,
            selectedPeriodText: phpr.nls.get("Selected Statistic Period"),
            selectedPeriodHelp: phpr.nls.get("Selected Period Help")
        });
        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            phpr.TreeContent.fadeOut();
            this.setSubGlobalModulesNavigation();
        } else {
            phpr.TreeContent.fadeIn();
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree  = new this.treeWidget(this);

        var today = new Date();
        var start = new Date(today.getFullYear(), today.getMonth(), 1);
        var end   = new Date(today.getFullYear(), today.getMonth(), 31);
        while (end.getMonth() != start.getMonth()) {
            var end = new Date(start.getFullYear(), start.getMonth(), end.getDate() - 1);
        }
        this.changeDate(start, end);

        var params = {
            baseClass: "positive",
            iconClass: "export",
            alt:       "Export",
            disabled:  false
        };
        var exportButton = new dijit.form.Button(params);
        dojo.byId("buttonRow").appendChild(exportButton.domNode);
        dojo.connect(exportButton, "onClick", dojo.hitch(this, "exportData"));
    },

    changeDate:function(start, end) {
        // summary:
        //    Request a new data store for the dates
        // description:
        //    Request to the server the data for draw the statistics
        dijit.byId("startDate").attr('value', new Date(start.getFullYear(), start.getMonth(), start.getDate()));
        dijit.byId("endDate").attr('value', new Date(end.getFullYear(), end.getMonth(), end.getDate()));

        this._url = phpr.webpath + "index.php/Statistic/index/jsonGetStatistic"
            + "/nodeId/" + phpr.currentProjectId
            + "/startDate/" + phpr.Date.getIsoDate(start)
            + "/endDate/" + phpr.Date.getIsoDate(end);
        phpr.DataStore.addStore({'url': this._url, 'noCache': true});
        phpr.DataStore.requestData({'url': this._url, 'processData': dojo.hitch(this, 'prepareData')});
    },

   prepareData:function(items, request) {
        // summary:
        //    Process the data and draw the table
        // description:
        //    Process the data and draw the table
        var data       = phpr.DataStore.getData({url: this._url});
        var rows       = new Array();
        var sumPerUser = new Array();
        for (var p in data.projects) {
            if (!data.projects[p]) {
                continue;
            }
            var userData      = new Array();
            var sumPerProject = 0;
            for (var u in data.users) {
                if (!data.rows[p] || !data.rows[p][u]) {
                    userData.push(phpr.Date.convertTime(0));
                } else {
                    userData.push(phpr.Date.convertTime(data.rows[p][u]));
                    sumPerProject   = Math.abs(sumPerProject + data.rows[p][u]);
                    if (!sumPerUser[u]) {
                        sumPerUser[u] = 0;
                    }
                    sumPerUser[u] = Math.abs(sumPerUser[u] + data.rows[p][u]);
                }
            }
            rows.push({"project": data.projects[p], "userData": userData, "sum": phpr.Date.convertTime(sumPerProject)});
        }

        var total        = 0;
        var totalPerUser = new Array();
        for (var u in data.users) {
            if (!sumPerUser[u]) {
                totalPerUser.push(phpr.Date.convertTime(0));
            } else {
                totalPerUser.push(phpr.Date.convertTime(sumPerUser[u]));
                total = Math.abs(total + sumPerUser[u]);
            }
        }
        var totalRow = new Array();
        totalRow.push({"title": phpr.nls.get("Total"), "userData": totalPerUser, "sum": phpr.Date.convertTime(total)});

        this.render(["phpr.Statistic.template", "table.html"], dojo.byId('statisticContent'), {
            sumTxt: phpr.nls.get("Sum"),
            projectTxt: phpr.nls.get("Project"),
            rows:   rows,
            users:  data.users,
            total:  totalRow
        });
    },

    setNewEntry:function() {
    },

    exportData:function() {
        var start = dijit.byId("startDate").attr('value');
        var end   = dijit.byId("endDate").attr('value');

        window.open(phpr.webpath + "index.php/" + phpr.module + "/index/csvList"
            + "/nodeId/" + phpr.currentProjectId
            + "/startDate/" + phpr.Date.getIsoDate(start)
            + "/endDate/" + phpr.Date.getIsoDate(end));
        return false;
    }
});
