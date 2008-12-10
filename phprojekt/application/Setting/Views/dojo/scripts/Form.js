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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

dojo.provide("phpr.Setting.Form");

dojo.declare("phpr.Setting.Form", phpr.Default.Form, {

    setUrl:function() {
        this._url = phpr.webpath+"index.php/" + phpr.module + "/index/jsonDetail/moduleName/" + phpr.submodule;
    },

    initData:function() {
    },

    setPermissions:function (data) {
        this._writePermissions  = true;
        this._deletePermissions = false;
        this._accessPermissions = true;
    },

    addBasicFields:function() {
    },

    addAccessTab:function(data) {
    },

    addModuleTabs:function(data) {
    },

    useCache:function() {
        return false;
    },

    submitForm: function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/moduleName/' + phpr.submodule,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type =='success') {
                    this.publish("updateCacheData");
                    this.publish("reload");
                }
            })
        });
    }
});
