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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

dojo.provide("phpr.Calendar.ViewDayListSelf");

dojo.declare("phpr.Calendar.ViewDayListSelf", phpr.Calendar.DefaultView, {
    // Summary:
    //    Class for displaying a Calendar Day List for the logged user (self).
    // Description:
    //    This Class takes care of displaying the list information we receive from our Server in a HTML table.
    updateData:function(id, startDate, endDate, newItem) {
        // Summary:
        //    Delete the cache for the current id/date and the url.
        this.inherited(arguments);

        var url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayListSelf/date/';
        phpr.DataStore.deleteDataPartialString({url: url});
    },

    /************* Private functions *************/

    _constructor:function() {
        // Summary:
        //    Define the current view name.
        this._view = 'daySelf';
    },

    _initStructure:function() {
        // Summary:
        //    Fills the weekDays array with all the dates of the selected week in string format.
        if (!this._internalCacheDates[this._cacheIndex]) {
            this._internalCacheDates[this._cacheIndex] = [];

            if (!this._internalCacheDates[this._cacheIndex]['schedule']) {
                for (var hour = 8; hour < 20; hour++) {
                    for (var half = 0; half < 2; half++) {
                        var minute = (half == 0) ? '00' : '30';
                        var row    = ((hour - 8) * 2) + half;

                        this._schedule[row] = [];

                        for (var column = 0; column < 2; column ++) {
                            this._schedule[row][column] = [];
                        }

                        this._schedule[row]['hour'] = phpr.Date.getIsoTime(hour + ':' + minute);

                        var tmp = (row / 2);
                        if (Math.floor(tmp) == tmp) {
                            // Even row
                            this._schedule[row]['even'] = true;
                        } else {
                            // Odd row
                            this._schedule[row]['even'] = false;
                        }
                    }
                }
                this._internalCacheDates[this._cacheIndex]['schedule'] = this._schedule;
            }
        } else {
            this._schedule = this._internalCacheDates[this._cacheIndex]['schedule'];
        }
    },

    _setUrl:function() {
        // Summary:
        //    Sets the url to get the data from.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayListSelf/date/' + this._date;
    },

    _getScheduleBarContent:function() {
        // Summary:
        //    Returns the string for show in the bar.
        var date        = phpr.Date.isoDateTojsDate(this._date);
        var days        = dojo.date.locale.getNames('days', 'wide');
        var description = days[date.getDay()];

        return description.slice(0, 1).toUpperCase() + description.slice(1) + ', ' + this._date;
    },

    _renderStructure:function() {
        // Summary:
        //    Create and show the main table for the daySelf view.
        if (dijit.byId(this._view + 'Box-Calendar').getChildren().length == 0) {
            var contentView       = document.createElement('div');
            contentView.id        = this._view + 'CalendarSchedule-Calendar';
            contentView.className = 'calendarSchedule';

            var table         = document.createElement('table');
            table.id          = this._view + 'table-Calendar';
            table.style.width = this._widthTable + '%';

            var furtherEvents = document.createElement('div');
            furtherEvents.id  = this._view + 'furtherEvents-Calendar';

            contentView.appendChild(table);
            contentView.appendChild(furtherEvents);
            dijit.byId(this._view + 'Box-Calendar').set('content', contentView);

            // Create basic structure
            for (var i in this._schedule) {
                var tr         = table.insertRow(table.rows.length);
                var td         = tr.insertCell(0);
                td.className   = 'hours';
                td.style.width = this._widthHourColumn + '%';
                td.innerHTML   = this._schedule[i].hour;

                var button = new dijit.form.Button({
                    id:        'addDaySelf_' + i + '-Calendar',
                    showLabel: false,
                    iconClass: 'add',
                    baseClass: 'addButton',
                    onClick:   dojo.hitch(this, '_openForm', this._schedule[i].hour)
                });
                td.appendChild(button.domNode);

                // Fix buttons for do not change the height of the row
                if (dojo.isIE) {
                    button.domNode.style.lineHeight                      =  0;
                    button.domNode.children[0].children[0].style.padding = '0px';
                    dojo.style(button.domNode.children[0].children[0].children[0], {
                        height:    '15px',
                        marginTop: (dojo.isIE == 8) ? '-4px' : '-2px'
                    });
                } else {
                    button.domNode.style.height    = '13px';
                    button.domNode.style.marginTop = '-8px';
                }

                var td = tr.insertCell(1);
                td.className = (this._schedule[i].even) ? 'emptyCellEven' : 'emptyCellOdd';
            }

            // Line
            var tr = table.insertRow(table.rows.length);
            var td = tr.insertCell(0);
            td.setAttribute('colspan', 2);
        } else {
            var contentView = dojo.byId(this._view + 'CalendarSchedule-Calendar');
            var table       = dojo.byId('tableDaySelf-Calendar');
        }

        // Hide all the other events area
        dojo.query('.eventsArea', contentView).forEach(function(ele) {
            ele.style.display = 'none';
        });

        // Create / show the events area for this date
        var eventArea = dojo.byId(this._view + '_eventAreaFor_' + this._date + '-Calendar');
        if (!eventArea) {
            var eventArea       = document.createElement('div');
            eventArea.id        = this._view + '_eventAreaFor_' + this._date + '-Calendar';
            eventArea.className = 'eventsArea';
            dojo.style(eventArea, {'float': 'left', position: 'absolute'});
            contentView.appendChild(eventArea);
            this._resizeStructure();
        } else {
            eventArea.style.display = 'inline';
        }

        // Create events if do not exists yet
        for (var i in this.events) {
            if (!dojo.byId(this._view + '_containerPlainDivFor_' + i + '_' + this._date + '-Calendar')) {
                var event            = document.createElement('div');
                event.id             = this._view + '_containerPlainDivFor_' + i + '_' + this._date + '-Calendar',
                event.className      = 'eventsDivMain';
                event.style.position = 'absolute';
                event.style.overflow = 'hidden';
                eventArea.appendChild(event);

                var plainDiv               = document.createElement('div');
                plainDiv.id                = this._view + '_plainDivFor_' + i + '_' + this._date + '-Calendar',
                plainDiv.className         = 'eventsDivSecond';
                plainDiv.style.borderWidth = this.EVENTS_BORDER_WIDTH + 'px';
                plainDiv.style.cursor      = 'pointer';

                var resize = new phpr.Calendar.ResizeHandle({
                    id:           this._view + '_eventResizeFor_' + i + '_' + this._date + '-Calendar',
                    resizeAxis:   'y',
                    activeResize: true,
                    targetId:     this._view + '_plainDivFor_' + i + '_' + this._date + '-Calendar',
                    style:        'bottom: 0; width: 100%; position: absolute;'
                });
                event.appendChild(plainDiv);
                event.appendChild(resize.domNode);
            }
        }

        // Further events
        var furtherEvents = dojo.byId(this._view + 'furtherEvents-Calendar');
        if (this._furtherEvents.show) {
            furtherEvents.style.display = 'inline';

            var html = phpr.nls.get('Further events') + ':<br />';
            for (var i in this._furtherEvents['events']) {
                html += this._furtherEvents['events'][i].time + ':&nbsp;'
                    + '<a href="javascript: dojo.publish(\'Calendar.setUrlHash\', [\'Calendar\', '
                    + this._furtherEvents['events'][i].id + ']);">' + this._furtherEvents['events'][i].title + '</a>'
                    + '<br />';
            }
            html += '<br />';
            dojo.empty(furtherEvents);
            furtherEvents.innerHTML = html;
        } else {
            furtherEvents.style.display = 'none';
        }
    },

    _setCellTimeAndColumnSize:function() {
        // Summary:
        //    Updates internal class variables with current sizes of schedule.
        var scheduleBkg      = dojo.byId(this.getDivId('table')).getElementsByTagName('td');
        this._cellTimeWidth  = scheduleBkg[0].offsetWidth;
        this.cellColumnWidth = scheduleBkg[1].offsetWidth;

        this._cellHeaderHeight = 0;
        this.cellTimeHeight    = scheduleBkg[0].offsetHeight;
    },

    _setStepValues:function() {
        // Summary:
        //     Updates internal class variables with current sizes of schedule.
        this.stepH             = dojo.byId(this.getDivId('table')).offsetWidth - this._cellTimeWidth;
        this.stepH             = dojo.number.round(this.stepH, 1);
        this.stepY             = this.cellTimeHeight;
        this.posHMax           = parseInt(dojo.byId(this.getDivId('area')).style.width) - this.stepH;
        this.posYMaxComplement = parseInt(dojo.byId(this.getDivId('area')).style.height);
    },

    _getColumn:function(date) {
        // Summary:
        //    Return the column of one event.
        return 0;
    },

    _openForm:function(hour) {
        // Summary:
        //    Get a hour and open a form with these value.
        // Call the form
        dojo.publish('Calendar.openForm', [null, 'Calendar', this._date, hour]);
    },

    _exportData:function() {
        // Summary:
        //    Open a new window in CSV mode
        window.open(phpr.webpath + 'index.php/' + phpr.module + '/index/csvDayListSelf/nodeId/1/date/' + this._date
            + '/csrfToken/' + phpr.csrfToken);

        return false;
    }
});
