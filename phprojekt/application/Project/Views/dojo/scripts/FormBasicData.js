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

dojo.provide("phpr.Project.FormBasicData");

dojo.declare("phpr.Project.FormBasicData", phpr.Project.Form, {
    postRenderForm:function() {
        if (dijit.byId("deleteButton")) {
            dijit.byId("deleteButton").destroy();
        }
    },

    submitForm:function() {
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonSaveTags/moduleName/' + phpr.module
                            + '/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: dojo.hitch(this, function(data) {
                            if (this.sendData['string']) {
                                new phpr.handleResponse('serverFeedback', data);
                            }
                            if (data.type =='success') {
                                this.publish("updateCacheData");
                                this.publish("changeProject", [this.id]);
                            }
                        })
                    });
                }
            })
        });
    }
});
