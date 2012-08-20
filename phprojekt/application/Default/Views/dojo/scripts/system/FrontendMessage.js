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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 */

dojo.provide("phpr.Default.System.FrontendMessage");

dojo.declare("phpr.Default.System.FrontendMessage", null, {
    url: null,
    _disabled: false,
    _lastRun: new Date(),
    _interval: 30000,
    _timeout: null,

    constructor: function() {
        this.url = 'index.php/Default/index/jsonGetFrontendMessage';
    },

    getFrontendMessage: function() {
        // Summary:
        //    Performs an AJAX call to the given URL
        // Description:
        //    Performs an AJAX call to the given URL
        phpr.loading.hide();
        var that = this;
        dojo.xhrPost({
            url:     this.url,
            content: {'csrfToken': phpr.csrfToken},
            error: function(error, handle) {
                phpr.handleError(this.url, 'silence', error);
            },
            load: function(response) {
                if (false !== response.data) {
                    that.showToaster(response.data);
                }
                that._scheduleNextRun();
            },
            handleAs: 'json'
        });
    },

    _scheduleNextRun: function() {
        if (this._disabled) {
            return;
        }

        var waittime = Math.max(0, this._interval - (new Date() - this._lastRun));
        var that = this;

        if (this._timeout !== null) {
            clearTimeout(this._timeout);
        }

        this._timeout = setTimeout(
            function() {
                that._nextRun();
                that._timeout = null;
            },
            waittime
        );

        this._lastRun = new Date();
    },

    _nextRun: function() {
        this.getFrontendMessage();
    },

    startLoop: function(interval) {
        this._interval = interval;
        this._scheduleNextRun();
    },

    disableFrontendMessages: function() {
        // Summary:
        //    Disables all the frontend messages
        // Description:
        //    Disables all the frontend messages by calling the disableFrontendMessages action
        //    from the indexController
        this._disabled = true;
        var url = 'index.php/Default/index/jsonDisableFrontendMessages';
        phpr.send({
            url:       url
        }).then(dojo.hitch(this, function(data) {
            if (data) {
                new phpr.handleResponse('serverFeedback', data);
            }
        }));
    },

    showToaster: function(data) {
        // Summary:
        //    Publishes the dojo toaster widget
        // Description:
        //    Publishes the dojo toaster widget and clears the existing interval loop.
        //    In addition, starts a new interval loop.

        var moduleUrl  = "#" + data.module + "," + data.projectId + ",id," + data.itemId;
        var currentUrl = window.location.hash;

        // Be fault tolerant and catch any error if the highlighting of a field does not run.
        try {
            if ('edit' == data.process) {
                // Check urls
                if (moduleUrl == currentUrl) {
                    dojo.publish(phpr.module + '.highlightChanges', [data]);
                }
            }
        } catch (err) {
            phpr.handleError(this.url, 'silence', "Can not highlight changes. " + err);
        }

        // Delete caches
        if (data.process == 'add' || data.process == 'delete' || data.process == 'edit') {
            // Delete all links of this module
            var url = 'index.php/' + data.module;
            phpr.DataStore.deleteDataPartialString({url: url});

            // Delete all links related of this module
            var url = 'moduleName/' + data.module;
            phpr.DataStore.deleteDataPartialString({url: url});

            // Delete general tags
            var url = 'index.php/Default/Tag/jsonGetTags';
            phpr.DataStore.deleteData({url: url});

            if (data.module == 'Project') {
                // Update for projects
                var url = 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + data.project;
                phpr.DataStore.deleteData({url: url});

                var url = 'index.php/Timecard';
                phpr.DataStore.deleteDataPartialString({url: url});

                phpr.tree.updateData();
            }

            // Restore the views
            if (data.module == phpr.module) {
                dojo.publish(phpr.module + '.setNavigations');
                dojo.publish(phpr.module + '.setWidgets');
            }
        }

        var template = this._templateString(data);
        dojo.publish("FrontendMessage", [{
            message:  template,
            type:     'warning',
            duration: 0
        }]);

        return;
    },

    _templateString: function(data) {
        // Summary:
        //    Process the data into an string
        // Description:
        //    Returns a string with the given data and html tags for the toaster.
        var template = '';
        var project  = phpr.nls.get("in Project");

        if (phpr.isGlobalModule(data.module)) {
            data.projectId = "";
        } else {
            data.projectId = data.projectId + ",";
        }

        switch (data.process) {
            case 'add':
                template = "<br /><a href='" + "index.php#" +
                    data.module + "," + data.projectId + "id," + data.itemId +
                    "'><i>" + phpr.nls.get(data.module, data.module) +
                    "</i>: <b>" + data.user + "</b> " + data.description +
                    " <i>" + data.item + "</i> " + project + " <i><b>" +
                    data.project + "</b></i>.</a><br />&nbsp;";
                break;
            case 'delete':
                template = "<br /><i>" + phpr.nls.get(data.module, data.module) +
                    "</i>: <b>" + data.user + "</b> " + data.description +
                    " <i>" + data.item + "</i> " + project + " <i><b>" +
                    data.project + "</b></i>.<br />&nbsp;";
                break;
            case 'edit':
                template = "<br /><a href='" + "index.php#" +
                    data.module + "," + data.projectId + "id," + data.itemId +
                    "'><i>" + phpr.nls.get(data.module, data.module) +
                    "</i>: <b>" + data.user + "</b> " + data.description +
                    " <i>" + data.item + "</i> " + project + " <i><b>" +
                    data.project + "</b></i>.</a><br />&nbsp;";
                break;
            case 'login':
                template = "<br /><b>" + data.user + "</b> " + data.description + "<br />&nbsp;";
                break;
            case 'logout':
                template = "<br /><b>" + data.user + "</b> " + data.description + "<br />&nbsp;";
                break;
            case 'remind':
                template = "<br />" + data.description + " " + data.time +
                    ": <br /><i><b>" + data.item + "</b></i>";
                break;
            default:
                template = "<br />" + data.user + " " + data.description + "<br />&nbsp;";
                break;
        }
        return template;
    }
});
