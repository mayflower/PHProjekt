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
 * @category  PHProjekt
 * @package   Template
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.phprojekt.com
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Main");


dojo.declare("phpr.Timecard.Store", null, {
    _hasData: false,
    _date: null,
    _url: null,
    _detailsUrl: null,
    _projectRange: null,
    _loading: false,
    _unassignedProjectId: 1,
    _favoritesUrl: null,
    _data: null,
    _metaData: null,
    _favoritesData: null,
    _mergedFavorites: null,
    _dlist: null,
    _updateListener: null,

    constructor: function(date) {
        this._date = date;

        this._updateListener = dojo.subscribe("Project.updateCacheData", this, "dataChanged");
        this._updateListener = dojo.subscribe("phpr.moduleSettingsChanged", this, "_settingsChanged");
    },

    destory: function() {
        dojo.unsubscribe(this._updateListener);
    },

    _settingsChanged: function(module) {
        if (module === "Timecard") {
            this.dataChanged();
        }
    },

    _setUrls: function() {
        this._favoritesUrl = 'index.php/Timecard/index/jsonGetFavoritesProjects';
        this._url = 'index.php/Timecard/index/jsonGetRunningBookings/' +
                    'year/' + this._date.getFullYear() +
                    '/month/' + (this._date.getMonth() + 1) +
                    '/date/' + this._date.getDate()
                                ;
        this._detailsUrl = 'index.php/Timecard/index/jsonDetail/nodeId/1/id/0';
    },

    _onDataLoaded: function(data) {
        this._data = data[0][1].data;
        this._metaData = data[1][1].metaData;
        this._favoritesData = data[2][1].data;
        this._runningBooking = null;

        this._projectRange = this._getProjectRange(this._metaData);
        this._computeMergedFavorites();

        if (this._data.length !== 0) {
            this._runningBooking = this._data;
        }

        this._dlist = null;
        this._hasData = true;
        this._stopLoading();
        this.onChange();
    },

    _getProjectRange: function(metaData) {
        var range;
        dojo.some(metaData, function(d) {
            if (d.key === "projectId") {
                range = d.range;
                return true;
            }
        });

        var l = range.length;
        for (var i = 0; i < l; i++) {
            if (range[i].id == this._unassignedProjectId) {
                range[i].name = phpr.nls.get("Unassigned", "Timecard");
                break;
            }
        }

        return range;
    },

    /*
     * This function merges the project list with the favorites.
     * the resulting list will be the same as the project list, but the favorites will be placed at the top of the list
     */
    _computeMergedFavorites: function() {
        if (this._metaData && this._projectRange && this._favoritesData) {
            // projectId
            var favorites = this._favoritesData;
            var range = dojo.clone(this._projectRange);
            for (var i in favorites) {
                var id = parseInt(favorites[i].id, 10);
                if (id > 0) {
                    for (var j in range) {
                        if (range[j].id == id) {
                            range.slice(j, 1);
                            break;
                        }
                    }
                    range.unshift({'id': parseInt(favorites[i].id, 10), 'name': favorites[i].name});
                }
            }
            this._mergedFavorites = range;
            return range;
        } else {
            return null;
        }
    },

    _updateData: function() {
        if (!this.isLoading()) {
            this._setUrls();
            this._cleanFavoritesData();

            phpr.DataStore.deleteData({url: this._url});

            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.addStore({url: this._detailsUrl});
            return this._requestData();
        }
    },

    _requestData: function() {
        if (!this.isLoading()) {
            this._startLoading();
            this._dlist = new dojo.DeferredList([
                phpr.DataStore.requestData({url: this._url}),
                phpr.DataStore.requestData({url: this._detailsUrl}),
                phpr.DataStore.requestData({url: this._favoritesUrl})
            ]);

            this._dlist.addCallback(dojo.hitch(this, "_onDataLoaded"));
            return this._dlist;
        }
    },

    _cleanFavoritesData: function() {
        if (!this.isLoading()) {
            phpr.DataStore.deleteData({url: this._favoritesUrl});
            phpr.DataStore.addStore({url: this._favoritesUrl});
        }
    },

    _updateFavoritesData: function() {
        this._cleanFavoritesData();
        this._requestData();
    },

    _startLoading: function() {
        this._loading = true;
        this.onLoadingStart();
    },

    _stopLoading: function() {
        this._loading = false;
        this.onLoadingStop();
    },

    hasRunningBooking: function() {
        return this._runningBooking !== null;
    },

    getRunningBooking: function() {
        return this._runningBooking;
    },

    getProjectRange: function() {
        var cb = new dojo.Deferred();
        if (this._hasData === true) {
            cb.callback(this._projectRange);
        } else {
            this._dlist.addCallback(dojo.hitch(this, function() {
                cb.callback(this._projectRange);
            }));
        }
        return cb;
    },

    startWorking: function(projectId, notes) {
        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(new Date()),
            projectId: projectId || this._unassignedProjectId,
            notes: notes || ""
        };

        phpr.send({
            url: 'index.php/Timecard/index/jsonSave/nodeId/1/id/0',
            content: data
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.dataChanged();
                    this.onWorkingStart();
                }
            }
        }));
    },

    stopWorking: function(projectId, notes) {
        if (!this.hasRunningBooking()) {
            throw new Error("no running booking");
        }

        var data = {
            startDatetime: phpr.date.getIsoDate(this._date) + " " + phpr.date.getIsoTime(this._runningBooking.startTime),
            endTime: phpr.date.getIsoTime(new Date()),
            projectId: projectId || this.getLastProjectId(),
            notes: notes || this._runningBooking.note || ""
        };

        phpr.send({
            url: 'index.php/Timecard/index/jsonSave/nodeId/1/id/' + this._runningBooking.id,
            content: data
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.dataChanged();
                    this.onWorkingStop();
                }
            }
        }));
    },

    setFavoriteProjects: function(projects) {
        if (!dojo.isArray(projects)) {
            throw new Error("Invalid project list");
        }

        if (projects.length === 0) {
            projects.push(0);
        }

        var sendData = {
            'favorites[]': projects
        };

        phpr.send({
            url: 'index.php/Timecard/index/jsonFavoritesSave',
            content: sendData
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this._updateFavoritesData();
                }
            }
        }));
    },

    getLastProjectId: function() {
        if (this.hasRunningBooking()) {
            return parseInt(this._runningBooking.projectId, 10);
        } else {
            return this._unassignedProjectId;
        }
    },

    getMergedFavoriteProjects: function() {
        var cb = new dojo.Deferred();

        if (this._hasData === true) {
            cb.callback(this._mergedFavorites);
        } else {
            this._dlist.addCallback(dojo.hitch(this, function() {
                cb.callback(this._mergedFavorites);
            }));
        }
        return cb;
    },

    getFavoriteProjects: function() {
        var cb = new dojo.Deferred();
        if (this._hasData === true) {
            cb.callback(this._favoritesData);
        } else {
            this._dlist.addCallback(dojo.hitch(this, function() {
                cb.callback(this._favoritesData);
            }));
        }
        return cb;
    },

    dataChanged: function() {
        this._updateData();
    },

    setDate: function(date) {
        this._date = date;
        this.dataChanged();
    },

    isLoading: function() {
        return this._loading === true;
    },

    onWorkingStart: function() { },
    onWorkingStop: function() { },
    onLoadingStart: function() { },
    onLoadingStop: function() { },
    onChange: function() { }
});

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {

    _store: null,
    _menuCollector: null,

    constructor: function() {
        this.module = "Timecard";
        this.loadFunctions(this.module);
        this._store = new phpr.Timecard.Store(new Date());

        this._menuCollector = new phpr.Default.System.GarbageCollector();

        this.formWidget = phpr.Timecard.Form;
        dojo.connect(this._store, "onChange", this, "_updateMenuButton");
    },

    renderTemplate: function() {
        phpr.viewManager.setView(
            phpr.Default.System.DefaultView,
            phpr.Timecard.ViewContentMixin
        );

        // manageFavorites opens a dialog which places itself outside of the regular dom, so we need to clean it up
        // manually
        this.garbageCollector.addNode('manageFavorites');
    },

    _updateMenuButton: function() {
        var that = this;
        if (this._menuButton && this._menuButton.dropDown) {
            this._menuCollector.collect();
            this._menuButton.dropDown.destroyDescendants();
            var button;

            if (this._store.hasRunningBooking()) {
                this._store.getMergedFavoriteProjects().then(dojo.hitch(this, function(data) {
                    var range = data;
                    var l = range.length;
                    var lastProjectName = "";
                    var lastProjectId = this._store.getLastProjectId();

                    for (var i = 0; i < l; i++) {
                        button = new dijit.MenuItem({
                            label: range[i].name,
                            onClick: dojo.hitch(this._store, this._store.stopWorking, range[i].id)
                        });

                        if (range[i].id === lastProjectId) {
                            lastProjectName = range[i].name;
                        }

                        this._menuCollector.addNode(button);
                        this._menuButton.dropDown.addChild(button);
                    }

                    button = new dijit.MenuItem({
                        label: "Stop (" + lastProjectName + ")",
                        onClick: dojo.hitch(this._store, this._store.stopWorking)
                    });

                    this._menuCollector.addNode(button);
                    this._menuButton.dropDown.addChild(button, 0);

                    dojo.addClass(this._menuButton.focusNode, "runningBooking");
                }));
            } else {
                this._menuCollector.addEvent(
                    dojo.connect(this._menuButton.dropDown, "onOpen", this._store,
                        function (evt) {
                            if (!this.hasRunningBooking()) {
                                dijit.popup.close(that._menuButton.dropDown.currentPopup);
                                this.startWorking();
                            }
                        }));
                dojo.removeClass(this._menuButton.focusNode, "runningBooking");
            }
            button = null;
        }
    },

    setWidgets: function() {
        this._store.dataChanged();
        this.grid = new phpr.Timecard.GridWidget({
            store: new dojo.store.JsonRest({target: 'index.php/Timecard/Timecard/'})
        });
        phpr.viewManager.getView().gridBox.set('content', this.grid);
        this.addExportButton();
    },

    addExportButton: function() {
        var params = {
            label:     phpr.nls.get('Export to CSV'),
            showLabel: true,
            baseClass: "positive",
            iconClass: "export",
            disabled:  false
        };
        this._exportButton = new dijit.form.Button(params);

        this.garbageCollector.addNode(this._exportButton);

        phpr.viewManager.getView().buttonRow.domNode.appendChild(this._exportButton.domNode);

        this._exportButton.subscribe(
            "timecard/yearMonthChanged",
            dojo.hitch(this, function(year, month) {
                if (this._exportButtonFunction) {
                    dojo.disconnect(this._exportButtonFunction);
                }
                this._exportButtonFunction = dojo.connect(
                    this._exportButton,
                    "onClick",
                    dojo.hitch(this, "exportData", year, month)
                );
            })
        );
    },

    exportData: function(year, month) {
        var start = new Date(year, month, 1),
            end = new Date(year, month + 1, 1);

        var params = {
            csrfToken: phpr.csrfToken,
            format: 'csv',
            filter: dojo.toJson({
                startDatetime: {
                    "!ge": start.toString(),
                    "!lt": end.toString()
                }
            })
        };
        window.open('index.php/Timecard/Timecard/?' + dojo.objectToQuery(params), '_blank');
    },

    getGlobalModuleNavigationButton: function(label) {
        var moduleName = this.module;
        var buttonContainer = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Timecard.template.menuButton.html",
            templateData: { label: label }
        });

        var button = buttonContainer.menuButton;
        this._menuButton = button;

        dojo.connect(button, "onClick", function() {
            phpr.currentProjectId = phpr.rootProjectId;
            phpr.pageManager.modifyCurrentState(
                dojo.mixin(dojo.clone(this._emptyState), { moduleName: moduleName }));
        });

        setTimeout(
            dojo.hitch(this, function() {
                this._store.dataChanged();
            }),
            15
        );
        return button;
    }
});
