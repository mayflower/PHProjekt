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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Timecard.Form");

dojo.declare("phpr.Timecard.Form", null, {
    _id:                      0,
    _bookUrl:                 null,
    _contentBar:              null,
    _date:                    null,
    _dateObject:              null,
    _favoritesUrl:            null,
    _url:                     null,
    _sendData:                [],
    _needFavoriteRangeUpdate: true,
    _needRangeUpdate:         true,

    constructor:function() {
        // Summary:
        //    Construct the form only one time.
        this._needRangeUpdate = true;
        this._id           = 0;
        this._url          = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this._id;
        this._favoritesUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetFavoritesProjects';

        // Fixed hours 0-24
        var hours    = [];
        var show     = '';
        var rowClass = 'dayViewWhite';
        for (var i = 0; i < 24; i++) {
            for (j = 0; j < 2; j++) {
                show = '';
                if (rowClass == 'dayViewCelestial') {
                    rowClass = 'dayViewWhite';
                } else {
                    rowClass = 'dayViewCelestial';
                }
                if (i < 10) {
                    show = '0';
                }
                if (j == 0) {
                    hour = i + ':00';
                    show = show + i + ':00';
                } else {
                    hour = i + ':30';
                    show = show + i + ':30';
                }
                hours.push({
                    hour:    hour,
                    display: show,
                    'class': rowClass
                });
            }
        }

        phpr.Render.render(['phpr.Timecard.template', 'dayView.html'], dojo.byId('dayView-Timecard'), {
            hours:          hours,
            tooltipHelpTxt: phpr.nls.get('Click for open the form')
        });

        this._contentBar = new phpr.Timecard.ContentBar('projectBookingContainer-Timecard');
    },

    init:function(date) {
        // Summary:
        //    Init the form for a new render.
        this._setFavoriteButton();
        this.setDate(date);
        this.drawDayView();
        dojo.byId('dayView-Timecard').scrollTop = 320;
    },

    setDate:function(date) {
        // Summary:
        //    Set the date for use in the form.
        if (undefined == date) {
            this._dateObject = new Date();
        } else {
            this._dateObject = date;
        }
        this._date = phpr.Date.getIsoDate(this._dateObject);
    },

    drawDayView:function() {
        // Summary:
        //    Render the Day View.
        this._bookUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDayList/date/' + this._date
        phpr.DataStore.addStore({url: this._bookUrl});
        phpr.DataStore.requestData({url: this._bookUrl, processData: dojo.hitch(this, function() {
            var data       = phpr.DataStore.getData({url: this._bookUrl});
            var hourHeight = 40;

            // Clean "Day View"
            dojo.forEach(dojo.byId('projectBookingContainer-Timecard').children, function(ele) {
                ele.style.display = 'none';
            });

            // Draw hours block
            for (i in data) {
                var dndClass = 'dndTarget';
                // Open period
                var endTime = data[i].endTime;
                if (null === data[i].endTime) {
                    endTime      = data[i].startTime;
                    var dndClass = 'dndTargetOpen';
                } else if (data[i].endTime == '00:00' || data[i].endTime == '00:00:00') {
                    endTime = '24:00';
                }

                var start = this._contentBar.convertHourToPixels(hourHeight, data[i].startTime);
                var end   = this._contentBar.convertHourToPixels(hourHeight, endTime);
                var top   = start + 'px';
                if ((end - start) - 6 < 0) {
                    var height = (end - start) + 'px';
                } else {
                    var height = (end - start) - 6 + 'px';
                }

                var booking = dojo.byId('targetBooking' + data[i].id);
                if (!booking) {
                    var booking = dojo.doc.createElement('div');
                    booking.id  = 'targetBooking' + data[i].id;
                    dijit.byId('projectBookingContainer-Timecard').domNode.appendChild(booking);
                    dojo.connect(booking, 'onclick',  dojo.hitch(this, 'fillForm', data[i].id));
                }

                // Set/update values
                var lineHeight = (dojo.isIE) ? '' : 'inherit';
                if (parseInt(height) <= 4) {
                    if (dojo.isIE && dojo.isIE < 8) {
                        lineHeight = 1;
                    } else {
                        lineHeight = 0;
                    }
                } else if (parseInt(height) < 14) {
                    if (dojo.isIE && dojo.isIE < 8) {
                        lineHeight = 1;
                    } else {
                        lineHeight = 0.5;
                    }
                }
                booking.innerHTML = data[i].display;
                dojo.style(booking, {
                    display:    'block',
                    top:        top,
                    height:     height,
                    lineHeight: lineHeight
                });
                booking.className = dndClass;
            }
        })});
    },

    fillForm:function(id) {
        // Summary:
        //    Fill the form with the data from a saved item.
        this._id  = id;
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this._id;
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
            var data      = phpr.DataStore.getData({url: this._url});
            var endTime   = data[0]['endTime'].substr(0, 5);
            var startTime = data[0]['startDatetime'].substr(11, 5);
            if (endTime == 0 || endTime == null) {
                var hour    = parseInt(startTime) + 1;
                var endTime = phpr.Date.getIsoTime(hour + '00');
            }

            var temp  = startTime.split(':');
            var start = parseInt(temp[0]);
            if (start == 0) {
                var start = parseInt(temp[0].substr(1, 1));
            }
            var end = parseInt(temp[1]);

            if (end > 0 && end < 30) {
                var index = start + ':00';
            } else if (end > 30) {
                var index = start + ':30';
            } else {
                var index = start + ':' + temp[1];
            }

            this._drawFormView(dojo.byId('buttonHours' + index + '-Timecard'), this._dateObject, startTime, endTime,
                data[0]['projectId'], data[0]['notes']);

            this._focusNote();
        })});
    },

    fillFormTime:function(index) {
        // Summary:
        //    Fill the form with the start and end time.
        this._id  = 0;
        var temp  = index.split(':');
        var start = parseInt(temp[0], 10);
        var end   = temp[1];

        var hour = start + 1;
        var end  = phpr.Date.getIsoTime(hour + ':' + end);

        this._drawFormView(dojo.byId('buttonHours' + index + '-Timecard'), this._dateObject, index, end, '', "\n");
    },

    forceUpdate:function() {
        // Summary:
        //    Force to update the project list in the form and favorite list.
        this._needRangeUpdate         = true;
        this._needFavoriteRangeUpdate = true;
    },

    /************* Private functions *************/

    _setFavoriteButton:function() {
        // Summary:
        //    Set the favorites button.
        var button = dijit.byId('favoriteButton-Timecard');
        if (!button) {
            var params = {
                id:        'favoriteButton-Timecard',
                label:     phpr.nls.get('Manage project list'),
                showLabel: true,
                baseClass: 'positive',
                disabled:  false,
                onClick:   dojo.hitch(this, '_openManageFavorites')
            };
            var button = new dijit.form.Button(params);
            dojo.byId('buttonRow').appendChild(button.domNode);
        } else {
            dojo.style(button.domNode, 'display', 'inline');
        }
    },

    _drawFormView:function(node, date, start, end, project, notes) {
        // Summary:
        //    Render the form and the favorites.
        this._url = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDetail/nodeId/1/id/' + this._id;
        phpr.DataStore.addStore({url: this._favoritesUrl});
        phpr.DataStore.requestData({url: this._favoritesUrl, processData: dojo.hitch(this, function() {
            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                if (!dijit.byId('tooltipDialog-Timecard')) {
                    // New form
                    var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
                    var meta      = phpr.DataStore.getMetaData({url: this._url});

                    var fieldTemplate = new phpr.TableForm('Timecard');
                    fieldTemplate.createTable(1);

                    // startDatetime
                    var fieldValues = {
                        type:     'datetime',
                        id:       meta[0].key,
                        label:    meta[0].label,
                        disabled: false,
                        required: meta[0].required,
                        value:    '',
                        tab:      1,
                        hint:     meta[0].hint
                    };
                    fieldTemplate.addRow(fieldValues);

                    // endTime
                    var fieldValues = {
                        type:     'time',
                        id:       meta[1].key,
                        label:    meta[1].label,
                        disabled: false,
                        required: meta[1].required,
                        value:    '',
                        tab:      1,
                        hint:     meta[1].hint
                    };
                    fieldTemplate.addRow(fieldValues);

                    // projectId
                    var range = [];
                    for (var i in favorites) {
                        var id = parseInt(favorites[i].id);
                        if (id > 0) {
                            range.push({id: id, name: favorites[i].name});
                        }
                    }
                    range.push({id: -1, name: '----'});

                    for (var i in meta[3].range) {
                        if (!favorites[meta[3].range[i].id]) {
                            range.push(meta[3].range[i]);
                        }
                    }
                    this._needRangeUpdate = false;
                    var fieldValues = {
                        type:     'selectbox',
                        id:       meta[3].key,
                        label:    meta[3].label,
                        disabled: false,
                        required: meta[3].required,
                        value:    '',
                        range:    range,
                        tab:      1,
                        hint:     meta[3].hint
                    };
                    fieldTemplate.addRow(fieldValues);

                    // notes
                    var fieldValues = {
                        type:     'simpletextarea',
                        id:       meta[4].key,
                        label:    meta[4].label,
                        disabled: false,
                        required: meta[4].required,
                        value:    '',
                        tab:      1,
                        hint:     meta[4].hint
                    };
                    fieldTemplate.addRow(fieldValues);

                    // timecardId
                    var fieldValues = {
                        type:     'hidden',
                        id:       'timecardId',
                        label:    '',
                        disabled: false,
                        required: true,
                        value:    this._id,
                        tab:      1,
                        hint:     ''
                    };
                    fieldTemplate.addRow(fieldValues);

                    var fieldValues = {
                        type:              'formButtons',
                        id:                'buttons',
                        label:             '',
                        disabled:          false,
                        required:          false,
                        value:             '',
                        tab:               1,
                        hint:              '',
                        writePermissions:  true,
                        deletePermissions: true
                    };
                    fieldTemplate.addRow(fieldValues);

                    // New form
                    var formWidget = new dijit.form.Form({
                        id:       'bookingForm',
                        name:     'bookingForm',
                        style:    'display: inline;',
                        onSubmit: function() {
                            return false;
                        }
                    });
                    var table = fieldTemplate.getTable(1);
                    table.className = 'form formContainer';
                    formWidget.domNode.appendChild(table);

                    var content = document.createElement('div');
                    content.id  = 'projectBookingForm';
                    content.appendChild(formWidget.domNode);

                    var tooltipDialog = new dijit.TooltipDialog({
                        id:      'tooltipDialog-Timecard',
                        content: content,
                        orient:  function() {
                            this.domNode.className = this['class'] + ' dijitTooltipABLeft dijitTooltipRight';
                        },
                        onBlur: function() {
                            dijit.popup.close(this);
                        },
                        onCancel: function() {
                            dijit.popup.close(this);
                        }
                    });
                    tooltipDialog.startup();

                    dojo.connect(dijit.byId('submitButton-Timecard'), 'onClick', dojo.hitch(this, '_submitForm'));
                    dojo.connect(dijit.byId('deleteButton-Timecard'), 'onClick', dojo.hitch(this, function() {
                        phpr.confirmDialog(dojo.hitch(this, '_deleteForm'),
                        phpr.nls.get('Are you sure you want to delete?'))
                    }));
                } else {
                    if (this._needRangeUpdate) {
                        // Update the store
                        var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
                        var meta      = phpr.DataStore.getMetaData({url: this._url});
                        var range     = [];
                        for (var i in favorites) {
                            var id = parseInt(favorites[i].id);
                            if (id > 0) {
                                range.push({id: id, name: favorites[i].name});
                            }
                        }
                        range.push({id: -1, name: '----'});

                        for (var i in meta[3].range) {
                            if (!favorites[meta[3].range[i].id]) {
                                range.push(meta[3].range[i]);
                            }
                        }
                        this._needRangeUpdate = false;

                        dijit.byId('projectId-Timecard').store = new dojo.data.ItemFileWriteStore({data: {
                            identifier: 'id',
			                 label:     'name',
			                 items:     range
                        }});
                    }
                    tooltipDialog = dijit.byId('tooltipDialog-Timecard');
                    dijit.byId('timecardId-Timecard').set('value', this._id);
                }

                dijit.popup.open({
                    parent: node,
                    popup:  tooltipDialog,
                    around: node,
                    orient: {'TL': 'BL', 'TR': 'BR'}
                });
                dojo.byId('projectId-Timecard').focus();
                this._updateForm(date, start, end, project, notes);
            })});
        })});
    },

    _updateForm:function(date, start, end, project, notes) {
        // Summary:
        //    Fill the form with some data.
        dijit.byId('startDatetime_forDate-Timecard').set('displayedValue', phpr.Date.getIsoDate(date));
        dijit.byId('startDatetime_forTime-Timecard').set('displayedValue', start);
        dijit.byId('endTime-Timecard').set('displayedValue', end);
        dijit.byId('projectId-Timecard').set('value', project);
        dijit.byId('notes-Timecard').set('value', notes);
        if (parseInt(this._id) > 0) {
            dojo.style(dojo.byId('deleteButton-Timecard'), 'display', 'inline');
        } else {
            dojo.style(dojo.byId('deleteButton-Timecard'), 'display', 'none')
        }
    },

    _focusNote:function() {
        // Summary:
        //    Wait that the widget exists to focus it.
        if (!dojo.byId('notes-Timecard')) {
            setTimeout(dojo.hitch(this, '_focusNote'), 500);
        } else {
            dojo.byId('notes-Timecard').focus();
        }
    },

    _submitForm:function(event) {
        // Summary:
        //    Save the booking form and reload the views.
        if (!this._prepareSubmission()) {
            return false;
        }

        this._id  = dijit.byId('timecardId-Timecard').get('value');
        phpr.send({
            url: phpr.webpath + 'index.php/Timecard/index/jsonSave/nodeId/' + phpr.currentProjectId
                + '/id/' + this._id,
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dijit.popup.close(dijit.byId('tooltipDialog-Timecard'));
                    this._updateData();
                }
            })
        });
    },

    _prepareSubmission:function() {
        // Summary:
        //    Correct some data before send it to the server
        this._sendData = [];
        var formWidget = dijit.byId('bookingForm');
        if (!formWidget.isValid()) {
            formWidget.validate();
            return false;
        }
        var formData = formWidget.get('value');

        // Add the fields without the module string
        for (var index in formData) {
            var newIndex             = index.substr(0, index.length - 9);
            this._sendData[newIndex] = formData[index];
        }

        if (this._sendData.endTime) {
            this._sendData.endTime = phpr.Date.getIsoTime(this._sendData.endTime);
        }
        if (this._sendData.startTime) {
            this._sendData.startTime = phpr.Date.getIsoTime(this._sendData.startTime);
        }
        if (this._sendData.projectId < 0) {
            this._sendData.projectId = 0;
        }
        if (this._sendData.notes == "\n") {
            this._sendData.notes = '';
        }

        delete formData;

        return true;
    },

    _updateData:function() {
        // Summary:
        //    Delete the cache for all the views
        // Description:
        //    Delete the cache and reload the views
        this._id = 0;
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._bookUrl});
        dojo.publish('Timecard.reloadGrid', [this._dateObject]);
        this.drawDayView();
    },

    _deleteForm:function(id, event) {
        // Summary:
        //    Delete a booking and reload the views.
        this._id = dijit.byId('timecardId-Timecard').get('value');
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this._id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    dijit.popup.close(dijit.byId('tooltipDialog-Timecard'));
                    this._updateData();
               }
            })
        });
    },

    _openManageFavorites:function() {
        // Summary:
        //    Function called on manageFavorites button click to regulate the popup project's boxes height,
        //    and then open the Manage Favorites dialog
        if (this._needFavoriteRangeUpdate) {
            phpr.DataStore.addStore({url: this._favoritesUrl});
            phpr.DataStore.requestData({url: this._favoritesUrl, processData: dojo.hitch(this, function() {
                phpr.DataStore.addStore({url: this._url});
                phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                    var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
                    var meta      = phpr.DataStore.getMetaData({url: this._url});
                    var range     = meta[3].range || [];

                    // Get Favorites
                    var favoritesList = new Array();
                    for (var k in favorites) {
                         for (var j in range) {
                            if (range[j].id == favorites[k].id) {
                                favoritesList.push(range[j]);
                            }
                        }
                    }

                    // Get All Projects
                    var allProjects   = new Array();
                    for (var j in range) {
                        var found = false;
                        for (var k in favorites) {
                            if (range[j].id == favorites[k].id) {
                                found = true;
                            }
                        }
                        if (!found) {
                            allProjects.push(range[j]);
                        }
                    }

                    // Make Dialog
                    this._createFavoritesDialog(allProjects, favoritesList);
                    this._needFavoriteRangeUpdate = false;
                })});
            })});
        } else {
            this._finishDialog();
        }
    },

    _createFavoritesDialog:function(allProjects, favoritesList) {
        // Summary:
        //    Render the dialog for manage favorites.
        if (!dojo.byId('manageFavorites-Timecard')) {
            // Create the dialog
            var html = phpr.Render.render(['phpr.Timecard.template', 'favoritesDialog.html'], null, {
                titleTxt:      phpr.nls.get('Drag the projects from left to right'),
                helpTxt:       phpr.nls.get('Favorite projects appear first in the select box of the form')
            });

            var dialog = new dijit.Dialog({
                id:        'manageFavorites-Timecard',
                title:     phpr.nls.get('Manage project list'),
                draggable: false,
                style:     'width: 650px;',
                content:   html
            });

            this._finishDialog();

            // Fill the source
            var source = new phpr.Timecard.Favorites('projectFavoritesSource-Timecard', {
                creator:    phpr.Timecard.FavoritesCreator,
                selfAccept: false
            });
            var nodes = [];
            for (var i in allProjects) {
                nodes.push({data: allProjects[i].name, id: 'favoritesTarget-' + allProjects[i].id });
            }
            source.insertNodes(false, nodes);

            // Fill the target
            var target = new phpr.Timecard.Favorites('projectFavoritesTarget-Timecard', {
                creator:    phpr.Timecard.FavoritesCreator,
                selfAccept: false
            });
            var nodes = [];
            for (var i in favoritesList) {
                nodes.push({data: favoritesList[i].name, id: 'favoritesSoruce-' + favoritesList[i].id });
            }
            target.insertNodes(false, nodes);

            delete nodes;

            dojo.connect(dijit.byId('manageFavorites-Timecard'), 'hide',  dojo.hitch(this, '_submitFavoritesForm'));
        } else {
            // Just update project names
            for (var k in allProjects) {
                var id = allProjects[k].id;
                if (dojo.byId('favoritesTarget-' + id)) {
                    dojo.byId('favoritesTarget-' + id).innerHTML = allProjects[k].name;
                }
                if (dojo.byId('favoritesSoruce-' + id)) {
                    dojo.byId('favoritesSoruce-' + id).innerHTML = allProjects[k].name;
                }
            }

            for (var k in favoritesList) {
                var id = favoritesList[k].id;
                if (dojo.byId('favoritesTarget-' + id)) {
                    dojo.byId('favoritesTarget-' + id).innerHTML = favoritesList[k].name;
                }
                if (dojo.byId('favoritesSoruce-' + id)) {
                    dojo.byId('favoritesSoruce-' + id).innerHTML = favoritesList[k].name;
                }
            }
        }
    },

    _finishDialog:function() {
        // Summary:
        //    Show the dialog.
        if (dojo.byId('tooltipDialog-Timecard')) {
            dijit.popup.close(dijit.byId('tooltipDialog-Timecard'));
        }
        dijit.byId('manageFavorites-Timecard').show();
    },

    _submitFavoritesForm:function() {
        // Summary:
        //    Save the favorites projects.
        this._sendData                = []
        this._sendData['favorites[]'] = []
        var _this                     = this;

        dojo.forEach(dojo.byId('projectFavoritesTarget-Timecard').children, function(node) {
            var id = node.id.replace(/favoritesTarget-/, '').replace(/favoritesSoruce-/, '');
            _this._sendData['favorites[]'].push(id);
        });

        if (this._sendData['favorites[]'].length == 0) {
            this._sendData['favorites[]'].push(0);
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonFavoritesSave',
            content:   this._sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this._needRangeUpdate = true;
                    phpr.DataStore.deleteData({url: this._favoritesUrl});
                    phpr.DataStore.requestData({url: this._favoritesUrl});
               }
            })
        });
    }
});
