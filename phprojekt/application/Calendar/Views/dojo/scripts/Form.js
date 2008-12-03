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

dojo.provide("phpr.Calendar.Form");

dojo.declare("phpr.Calendar.Form", phpr.Default.Form, {

    prepareSubmission:function() {
        this.sendData = new Array();
        for(var i = 0; i < this.formsWidget.length; i++) {
            if (!this.formsWidget[i].isValid()) {
                var parent = this.formsWidget[i].containerNode.parentNode.id;
                this.form.selectChild(parent);
                this.formsWidget[i].validate();
                return false;
            }
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].attr('value'));
        }
        // check if rule for reoccurence is set
        if (this.sendData.rrule_freq) {            
            // set frequence
            var rrule = 'FREQ='+this.sendData.rrule_freq;
            
            // set until value if available
            if (this.sendData.rrule_until) {
                until = this.sendData.rrule_until;
                until.setHours(this.sendData.startTime.getHours());
                until.setMinutes(this.sendData.startTime.getMinutes());
                until.setSeconds(this.sendData.startTime.getSeconds());
                until = dojo.date.add(until, 'minute', until.getTimezoneOffset());
                rrule += ';UNTIL='+dojo.date.locale.format(until, {datePattern: 'yyyyMMdd\'T\'HHmmss\'Z\'', selector: 'date'});
                this.sendData.rrule_until = null;
            }

            // set interval if available
            if (this.sendData.rrule_interval) {
                rrule += ';INTERVAL='+this.sendData.rrule_interval;
                this.sendData.rrule_interval = null;
            }

            // set weekdays if available
            if (this.sendData['rrule_byday[]']) {
                rrule += ';BYDAY='+this.sendData['rrule_byday[]'];
                this.sendData['rrule_byday[]'] = null;
            }
            this.sendData.rrule_freq = null;
            
            this.sendData.rrule = rrule;
        } else {
            this.sendData.rrule = null;
        }
        return true;
    },
    
    addModuleTabs:function(data) {
        // summary:
        //    Add all the tabs
        // description:
        //    Add all the tabs that are not the basic data
        this.addReoccurenceTab(data);
        this.addAccessTab(data);
        if (this.id > 0) {
            this.addTab(this.getHistoryData(), 'tabHistory', 'History');
        }
    },
    
    addReoccurenceTab:function(data) {
        // summary:
        //    Adds a tab for reoccurence
        // description:
        //    adds a tab to configure the rules if/when the event will reoccure
        
        var reoccurenceTab = '';
        
        // preset values
        values = {
            FREQ: '',
            INTERVAL: 1,
            UNTIL: '',
            BYDAY: ''
        };
        // parse data to fill the form
        if (data[0].rrule && data[0].rrule.length > 0) {
            rrule = data[0].rrule.split(';');
            for (i = 0; i < rrule.length; i++) {
                rule = rrule[i].split('=');
                name = rule[0];
                value = rule[1];
                switch (name) {
                    case 'UNTIL':
                        value = dojo.date.locale.parse(value, {datePattern: "yyyyMMdd'T'HHmmss'Z'", selector: 'date'});
                        value = dojo.date.add(value, 'minute', -value.getTimezoneOffset());
                        value = dojo.date.locale.format(value, {datePattern: 'yyyy-MM-dd', selector: 'date'});
                        break;
                }
                values[name] = value;
            }
        }
        
        // create ranges
        rangeFreq = new Array(
            {'id': '', 'name': 'Once'},
            {'id': 'DAILY', 'name': 'Daily'},
            {'id': 'WEEKLY', 'name': 'Weekly'},
            {'id': 'MONTHLY', 'name': 'Monthly'},
            {'id': 'YEARLY', 'name': 'Yearly'}
        );
        rangeByday = new Array(
            {'id': 'MO', 'name': 'Monday'},
            {'id': 'TU', 'name': 'Tuesday'},
            {'id': 'WE', 'name': 'Wendsday'},
            {'id': 'TH', 'name': 'Thursday'},
            {'id': 'FR', 'name': 'Friday'},
            {'id': 'SA', 'name': 'Saturday'},
            {'id': 'SU', 'name': 'Sunday'}
        );
        
        // Add fields
        reoccurenceTab += this.fieldTemplate.selectRender(rangeFreq ,'Repeats', 'rrule_freq', values.FREQ, false, false);
        reoccurenceTab += this.fieldTemplate.textFieldRender('Interval', 'rrule_interval', values.INTERVAL, false, false);
        reoccurenceTab += this.fieldTemplate.dateRender('Until', 'rrule_until', values.UNTIL, false, false);
        reoccurenceTab += this.fieldTemplate.multipleSelectRender(rangeByday ,'Weekdays', 'rrule_byday', values.BYDAY, false, false, 7, true);
        // Add the tab to the form
        this.addTab(reoccurenceTab, 'tabReoccurence', 'Reoccurence', 'accessReoccurenceTab');
    }

});
