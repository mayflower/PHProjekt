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

dojo.provide("phpr.User.Form");

dojo.declare("phpr.User.Form", phpr.Core.Form, {
    setPermissions:function(data) {
        this._writePermissions = true;

        // users can't be deleted
        this._deletePermissions = false;
        this._accessPermissions = true;
    },

    addModuleTabs:function(data) {
    },

    useCache:function() {
        return false;
    },

    submitForm:function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonSave/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    var result     = Array();
                    result.type    = 'success';
                    result.message = phpr.nls.get('You need reload the browser in order to let changes have effect');
                    new phpr.handleResponse('serverFeedback', result);
                    this.publish("updateCacheData");
                    this.publish("setUrlHash", [phpr.module]);
                }
            })
        });
    }
});
