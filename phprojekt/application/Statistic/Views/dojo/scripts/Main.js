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
 * @subpackage Statistic
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Statistic.Main");

dojo.declare("phpr.Statistic.Main", phpr.Default.Main, {
    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Statistic';

        this._loadFunctions();
        dojo.subscribe('Statistic.changeDate', this, 'changeDate');

        this._gridWidget = phpr.Statistic.Grid;
        this._formWidget = phpr.Statistic.Form;
    },

    setWidgets:function() {
        // Summary:
        //    Set and start the widgets of the module.
        phpr.Tree.loadTree();
        var today = new Date();
        var start = new Date(today.getFullYear(), today.getMonth(), 1);
        var end   = new Date(today.getFullYear(), today.getMonth(), 31);
        while (end.getMonth() != start.getMonth()) {
            var end = new Date(start.getFullYear(), start.getMonth(), end.getDate() - 1);
        }

        // Change the date and render the table
        this.changeDate(start, end);

        // Add an export button
        var button = dijit.byId('exportCsvButton-Statistic');
        if (!button) {
            var params = {
                id:        'exportCsvButton-Statistic',
                label:     phpr.nls.get('Export to CSV'),
                showLabel: true,
                baseClass: 'positive',
                iconClass: 'export',
                disabled:  false,
                onClick:   dojo.hitch(this, '_exportData')
            };
            var button = new dijit.form.Button(params);
            dojo.byId('buttonRow').appendChild(button.domNode);
        } else {
            dojo.style(button.domNode, 'display', 'inline');
        }
    },

    changeDate:function(start, end) {
        // summary:
        //    Request a new data store for the dates and draw the statistics.
        dijit.byId('startDate-' + this._module).set('value',
            new Date(start.getFullYear(), start.getMonth(), start.getDate()));
        dijit.byId('endDate-' + this._module).set('value',
            new Date(end.getFullYear(), end.getMonth(), end.getDate()));

        // Clean table
        dijit.byId('content-' + this._module).set('content', '');

        this._url = phpr.webpath + 'index.php/Statistic/index/jsonGetStatistic'
            + '/nodeId/' + phpr.currentProjectId
            + '/startDate/' + phpr.Date.getIsoDate(start)
            + '/endDate/' + phpr.Date.getIsoDate(end);
        phpr.DataStore.addStore({'url': this._url, 'noCache': true});
        phpr.DataStore.requestData({'url': this._url, 'processData': dojo.hitch(this, '_prepareData')});
    },

    openForm:function(id, module) {
        // Summary:
        //     Open a new form.
        // Description:
        //     Disable for this module.
    },

    /************* Private functions *************/

    _renderTemplate:function() {
        // Summary:
        //    Render the module layout only one time.
        // Description:
        //    Try to create the layout if not exists, or recover it from the garbage.
        if (!dojo.byId('defaultMainContent-' + phpr.module)) {
            phpr.Render.render(['phpr.Statistic.template', 'mainContent.html'], dojo.byId('centerMainContent'), {
                module:             phpr.module,
                selectedPeriodText: phpr.nls.get('Selected Statistic Period'),
                selectedPeriodHelp: phpr.nls.get('Choose here the period for the statistics to be calculated.')
            });
        } else {
            dojo.place('defaultMainContent-' + phpr.module, 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContent-' + phpr.module), 'display', 'block');
        }
    },

   _prepareData:function(items, request) {
        // Summary:
        //    Process the data and draw the table.
        var data       = phpr.DataStore.getData({url: this._url});
        var rows       = [];
        var sumPerUser = [];
        for (var p in data.projects) {
            if (!data.projects[p]) {
                continue;
            }
            var userData      = new Array();
            var sumPerProject = 0;
            for (var u in data.users) {
                if (!data.rows[p] || !data.rows[p][u]) {
                    userData.push(phpr.Date.convertMinutesToTime(0));
                } else {
                    userData.push(phpr.Date.convertMinutesToTime(data.rows[p][u]));
                    sumPerProject   = Math.abs(sumPerProject + data.rows[p][u]);
                    if (!sumPerUser[u]) {
                        sumPerUser[u] = 0;
                    }
                    sumPerUser[u] = Math.abs(sumPerUser[u] + data.rows[p][u]);
                }
            }
            rows.push({
                project:  data.projects[p],
                userData: userData,
                sum:      phpr.Date.convertMinutesToTime(sumPerProject)
            });
        }

        var total        = 0;
        var totalPerUser = new Array();
        for (var u in data.users) {
            if (!sumPerUser[u]) {
                totalPerUser.push(phpr.Date.convertMinutesToTime(0));
            } else {
                totalPerUser.push(phpr.Date.convertMinutesToTime(sumPerUser[u]));
                total = Math.abs(total + sumPerUser[u]);
            }
        }
        var totalRow = {
            title:    phpr.nls.get('Total'),
            userData: totalPerUser,
            sum:      phpr.Date.convertMinutesToTime(total)
        };

        // Create the table
        var table              = dojo.doc.createElement('table');
        table.className        = 'statisticsTable';
        table.style.marginLeft = '10px';
        table.style.marginTop  = '10px';

        // Titles
        var row       = table.insertRow(table.rows.length);
        var cellIndex = 0;

        var cell       = row.insertCell(cellIndex);
        cell.innerHTML = '<b>' + phpr.nls.get('Project') + '</b>';
        cellIndex++;

        for (var i in data.users) {
            var cell       = row.insertCell(cellIndex);
            cell.innerHTML = '<b>' + users[i].users + '</b>';
            cellIndex++;
        }

        var cell       = row.insertCell(cellIndex);
        cell.innerHTML = '<b>' + phpr.nls.get('Sum') + '</b>';
        cellIndex++;

        var cell       = row.insertCell(cellIndex);
        cell.innerHTML = '<b>' + phpr.nls.get('Project') + '</b>';

        // Rows
        for (var r in rows) {
            var row       = table.insertRow(table.rows.length);
            var cellIndex = 0;

            var cell       = row.insertCell(cellIndex);
            cell.innerHTML = rows[r].project;
            cellIndex++;

            for (var i in rows[r].userData) {
                var cell       = row.insertCell(cellIndex);
                cell.innerHTML = rows[r].userData[i];
                cellIndex++;
            }

            var cell       = row.insertCell(cellIndex);
            cell.innerHTML = rows[r].sum;
            cellIndex++;

            var cell       = row.insertCell(cellIndex);
            cell.innerHTML = rows[r].project;
        }

        // Total
        var row       = table.insertRow(table.rows.length);
        var cellIndex = 0;

        var cell       = row.insertCell(cellIndex);
        cell.innerHTML = '<b>' + totalRow.title + '</b>';
        cellIndex++;

        for (var i in totalRow.userData) {
            var cell       = row.insertCell(cellIndex);
            cell.innerHTML = '<b>' + totalRow.userData[i] + '</b>';
            cellIndex++;
        }

        var cell       = row.insertCell(cellIndex);
        cell.innerHTML = '<b>' + totalRow.sum + '</b>';
        cellIndex++;

        var cell       = row.insertCell(cellIndex);
        cell.innerHTML = '<b>' + totalRow.title + '</b>';

        // Replace the content with the new table
        dijit.byId('content-' + this._module).set('content', table);
    },

    _setNewEntry:function() {
        // Summary:
        //    Create the Add button.
        // Description:
        //    Disable for this module.
    },

    _exportData:function() {
        // Summary:
        //    Export the table to an csv file.
        var start = dijit.byId('startDate-' + this._module).get('value');
        var end   = dijit.byId('endDate-' + this._module).get('value');

        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvList'
            + '/nodeId/' + phpr.currentProjectId
            + '/startDate/' + phpr.Date.getIsoDate(start)
            + '/endDate/' + phpr.Date.getIsoDate(end)
            + '/csrfToken/' + phpr.csrfToken);
        return false;
    }
});
