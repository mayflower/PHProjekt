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
 * @version    Release: @package_version@
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 */

dojo.provide("phpr.FrontendMessage");

dojo.declare("phpr.FrontendMessage", null, {
    url: null,

    constructor:function() {
        this.url = phpr.webpath + 'index.php/Default/index/jsonGetFrontendMessage';
    },

    getFrontendMessage:function() {
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
                if (false != response.data) {
                    that.showToaster(response.data);
                }
            },
            handleAs: 'json'
        });
    },

    disableFrontendMessages:function() {
        // Summary:
        //    Disables all the frontend messages
        // Description:
        //    Disables all the frontend messages by calling the disableFrontendMessages action
        //    from the indexController
        var url = phpr.webpath + 'index.php/Default/index/jsonDisableFrontendMessages';
        phpr.send({
            url:       url,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
            })
        });
    },

    showToaster:function(data) {
        // Summary:
        //    Publishes the dojo toaster widget
        // Description:
        //    Publishes the dojo toaster widget and clears the existing interval loop.
        //    In addition, starts a new interval loop.
        clearInterval(window.interval);
        window.getFrontendMessage();

        var moduleUrl  = '#' + data.module + ',' + data.projectId + ',id,' + data.itemId;
        var currentUrl = window.location.hash;

        // Be fault tolerant and catch any error if the highlighting of a field does not run.
        try {
            if ('edit' == data.process) {
                // Check urls
                if (moduleUrl == currentUrl) {
                    dojo.publish(phpr.module + '.formProxy', ['highlightChanges', data]);
                }
            }
        } catch (err) {
            phpr.handleError(this.url, 'silence', 'Can not highlight changes. ' + err);
        }

        // Delete caches
        if (data.process == 'add' || data.process == 'delete' || data.process == 'edit') {
            // Delete all links of this module
            var url = phpr.webpath + 'index.php/' + data.module;
            phpr.DataStore.deleteDataPartialString({url: url});

            // Delete all links related of this module
            var url = 'moduleName/' + data.module;
            phpr.DataStore.deleteDataPartialString({url: url});

            // Delete general tags
            var url = phpr.webpath + 'index.php/Default/Tag/jsonGetTags';
            phpr.DataStore.deleteData({url: url});

            if (data.module == 'Project') {
                // Update for projects
                var url = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + data.project;
                phpr.DataStore.deleteData({url: url});

                var url = phpr.webpath + 'index.php/Timecard';
                phpr.DataStore.deleteDataPartialString({url: url});

                phpr.Tree.updateData();
                phpr.Tree.loadTree();
            }

            // Restore the views
            if (data.module == phpr.module) {
                dojo.publish(phpr.module + '.setWidgets');
            }
        }

        var template = this._templateString(data);
        dojo.publish('FrontendMessage', [{
            message:  template,
            type:     'warning',
            duration: 0
        }]);

        return;
    },

    _templateString:function(data) {
        // Summary:
        //    Process the data into an string
        // Description:
        //    Returns a string with the given data and html tags for the toaster.
        var template = '';
        var project  = phpr.nls.get('in Project');

        if (phpr.isGlobalModule(data.module)) {
            data.projectId = '';
        } else {
            data.projectId = data.projectId + ',';
        }

        switch (data.process) {
            case 'add':
                template = "<br /><a href='" + phpr.webpath + "index.php#" + data.module + "," + data.projectId
                    + "id," + data.itemId + "'><i>" + phpr.nls.get(data.module, data.module) + "</i>: <b>"
                    + data.user + "</b> " + data.description + " <i>" + data.item + "</i> " + project + " <i><b>"
                    + data.project +"</b></i>.</a><br />&nbsp;"
                break;
            case 'delete':
                template = "<br /><i>" + phpr.nls.get(data.module, data.module) + "</i>: <b>" + data.user + "</b> "
                    + data.description + " <i>" + data.item + "</i> " + project + " <i><b>"
                    + data.project +"</b></i>.<br />&nbsp;";
                break;
            case 'edit':
                template = "<br /><a href='" + phpr.webpath + "index.php#" + data.module + "," + data.projectId
                    + "id," + data.itemId + "'><i>" + phpr.nls.get(data.module, data.module) + "</i>: <b>"
                    + data.user + "</b> " + data.description + " <i>" + data.item + "</i> " + project + " <i><b>"
                    + data.project +"</b></i>.</a><br />&nbsp;";
                break;
            case 'login':
                template = "<br /><b>" + data.user + "</b> " + data.description + "<br />&nbsp;";
                break;
            case 'logout':
                template = "<br /><b>" + data.user + "</b> " + data.description + "<br />&nbsp;";
                break;
            case 'remind':
                template = "<br />" + data.description + " " + data.time + ": <br /><i><b>"
                    + data.item + "</b></i>";
                break;
            default:
                template = "<br />" + data.user + " " + data.description + "<br />&nbsp;";
                break;
        }
        return template;
    }
});
