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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
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
        var range = dojo.clone(metaData[3].range);

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
        if (this._metaData && this._metaData[3].range && this._favoritesData) {
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
    },

    startWorking: function(projectId, notes) {
    },

    stopWorking: function(projectId, notes) {
    },

    setFavoriteProjects: function(projects) {
    },

    getLastProjectId: function() {
    },

    getMergedFavoriteProjects: function() {
    },

    getFavoriteProjects: function() {
    },

    dataChanged: function() {
    },

    setDate: function(date) {
    },

    isLoading: function() {
    },

    onWorkingStart: function() { },
    onWorkingStop: function() { },
    onLoadingStart: function() { },
    onLoadingStop: function() { },
    onChange: function() { }
});

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
    _date: new Date(),
    _contentWidget: null,
    _menuCollector: null,
    _store: null,

    constructor: function() {
        this.module = 'Timecard';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Timecard.Grid;
        this.formWidget = phpr.Timecard.Form;

        this._menuCollector = new phpr.Default.System.GarbageCollector();
    },

    _systemDateChanged: function() {
    },

    renderTemplate: function() {
    },

    setWidgets: function() {
    },

    _dataChanged: function() {
    },

    _updateMenuButton: function() {
    },

    formDataChanged: function(newDate, forceReload) {
    },

    setSubGlobalModulesNavigation: function(currentModule) {
    },

    getGlobalModuleNavigationButton: function(label) {
    },

    changeDate: function(date) {
    },

    setTimecardCaldavClientButton: function() {
    },

    showTimecardCaldavClientData: function(url, iosUrl) {
    }
});
