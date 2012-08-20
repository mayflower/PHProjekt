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
 * @subpackage Todo
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Todo.Form");

dojo.declare("phpr.Todo.Form", phpr.Default.DialogForm, {

    postRenderForm: function() {
        this.inherited(arguments);
        var data = phpr.DataStore.getData({url: this._url});
        if (data.length > 0 && data[0]['id'] == 0) {
            dojo.connect(dijit.byId('projectId'), 'onChange', null, function() {
                var url = 'index.php/Project/index/jsonDetail/'
                        + 'nodeId/1/id/' + dijit.byId('projectId').value;
                phpr.DataStore.addStore({url: url});
                start = dijit.byId('startDate');
                end   = dijit.byId('endDate');
                start.setValue('');
                end.setValue('');

                phpr.DataStore.requestData({
                    url:         url,
                    processData: function() {
                        var data = phpr.DataStore.getData({url: url});
                        if (data.length > 0) {
                            start.setValue(new Date(data[0]['startDate']));
                            end.setValue(new Date(data[0]['endDate']));
                        } else {
                            start.setValue(new Date());
                        }
                    }
                });
            });
        }
    }
});
