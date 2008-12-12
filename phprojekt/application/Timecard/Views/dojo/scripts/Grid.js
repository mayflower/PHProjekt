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
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

dojo.provide("phpr.Timecard.Grid");

dojo.declare("phpr.Timecard.Grid", phpr.Default.Grid, {

    reloadView:function(/*String*/ view, /*int*/ year, /*int*/ month) {
        this.main.setSubGlobalModulesNavigation();
        this.gridLayout = new Array();
        this.setUrl(year, month, view);
        phpr.DataStore.addStore({url: this.url});
        phpr.DataStore.requestData({url: this.url, processData: dojo.hitch(this, "onLoaded")});
    },

    setUrl: function(year, month, view) {
        if (typeof year == "undefined") {
            date = new Date();
            year = date.getFullYear();
        }
        if (typeof month == "undefined") {
            date = new Date();
            month = date.getMonth() + 1;
        }
        if (typeof view == "undefined") {
            view = 'month';
        }
        this.url = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/year/"+year+"/month/"+month+"/view/"+view;
    },

    showTags: function() {
    },

    canEdit: function(inRowIndex) {
        return false;
    },

    useIdInGrid: function () {
        return false;
    },

    customGridLayout:function(meta) {
       this.gridLayout[0].styles = "cursor:pointer;"
    },

    setSaveChangesButton:function(meta) {
    },

    showForm:function(e) {
        if (e.cellIndex == 0) {
            var item  = this.grid.getItem(e.rowIndex);
            var date = this.grid.store.getValue(item, 'date');
            if (date) {
                var year = date.substr(0, 4);
                var month = date.substr(5, 2);
                var day = date.substr(8, 2);
                var date = new Date(year, (month - 1), day);
                this.main.form.setDate(date);
                this.main.form.reloadDateView();
                this.publish("changeDate", [date]);
            }
        }
    }
});
