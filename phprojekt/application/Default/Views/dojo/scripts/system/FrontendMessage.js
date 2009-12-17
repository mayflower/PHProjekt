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
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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
            url:   this.url,
            error: function(error, handle) {
                phpr.handleError(this.url, 'js', error);
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
        var template = this._templateString(data);
        dojo.publish("FrontendMessage", [{
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
        switch (data.process) {
            case 'add':
                template = "<br /><a href='" + phpr.webpath + "index.php#" + data.module + "," + data.projectId
                    + ",id," + data.itemId + "'>" + data.user + " " + data.description; + ".</a><br />&nbsp;"
                break;
            case 'delete':
                template = "<br />" + data.user + " " + data.description + ". <br />&nbsp;";
                break;
            case 'edit':
                template = "<br /><a href='" + phpr.webpath + "index.php#" + data.module + "," + data.projectId
                    + ",id," + data.itemId + "'>" + data.user + " " + data.description + ".</a><br />&nbsp;";
                break;
            case 'login':
                template = "<br />" + data.user + " " + data.description + "<br />&nbsp;";
                break;
            case 'logout':
                template = "<br />" + data.user + " " + data.description + "<br />&nbsp;";
                break;
            case 'remind':
                template = "<br />" + data.user + " " + data.description + "<br />&nbsp;";
                break;
            default:
                template = "<br />" + data.user + " " + data.description + "<br />&nbsp;";
                break;
        }

        return template;
    }
});
