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

dojo.provide("phpr.Timecard.Form");

dojo.declare("phpr.Timecard.Form", phpr.Component, {
    sendData:           new Array(),
    formdata:           new Array(),
    dateObject:         null,
    id:                 0,
    _url:               null,
    _bookUrl:           null,
    _favoritesUrl:      null,
    _date:              null,
    _contentBar:        null,
    _manFavBoxesHeight: 18,

    constructor:function(main, date) {
        // Summary:
        //    Render the form on construction
        // Description:
        //    This function receives the form data from the server and renders the corresponding form
        this.main = main;
        this.id   = 0;

        // Fixed hours 0-24
        var hours = new Array();
        var pair  = 0;
        var show  = '';
        for (var i = 0; i < 24; i++) {
            show = '';
            if ((i % 2) == 0) {
                pair = 1;
            } else {
                pair = 0;
            }
            if (i < 10) {
                show = '0';
            }
            show = show + i + ':00';
            hours.push({
                "hour"   : i,
                "display": show,
                "pair":    pair
            });
        }
        this.render(["phpr.Timecard.template", "dayView.html"], dojo.byId('dayView'), {
            hours: hours
        });
        dojo.byId('dayView').scrollTop = dojo.byId('dayView').scrollHeight;

        this._contentBar = new phpr.Timecard.ContentBar("projectBookingContainer");

        this.setDate(date);
        this.loadView();
        dijit.byId("selectDate").attr('value', new Date(this.dateObject.getFullYear(), this.dateObject.getMonth(),
            this.dateObject.getDate()));
    },

    setDate:function(date) {
        // Summary:
        //    Set the date for use in the form
        // Description:
        //    Set the date for use in the form
        if (undefined == date) {
            this.dateObject = new Date();
        } else {
            this.dateObject = date;
        }
        this._date = phpr.Date.getIsoDate(this.dateObject);
    },

    loadView:function() {
        // Summary:
        //    Load all the form views
        // Description:
        //    Load all the form views
        this._favoritesUrl = phpr.webpath + "index.php/" + phpr.module + "/index/jsonGetFavoritesProjects";
        this._url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDetail/id/" + this.id;
        phpr.DataStore.addStore({url: this._favoritesUrl});
        phpr.DataStore.requestData({url: this._favoritesUrl, processData: dojo.hitch(this, function() {
            phpr.DataStore.addStore({url: this._url});
            phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
                this.darwFormView();
            })});
        })});

        this.drawDayView();
    },

    drawDayView:function() {
        // Summary:
        //    Render the Day View
        // Description:
        //    Render the Day View
        this._bookUrl = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDayList/date/" + this._date
        phpr.DataStore.addStore({url: this._bookUrl});
        phpr.DataStore.requestData({url: this._bookUrl, processData: dojo.hitch(this, function() {
            var data       = phpr.DataStore.getData({url: this._bookUrl});
            var hourHeight = 20;

            // Clean "Day View"
            dijit.byId("projectBookingContainer").destroyDescendants();

            // Draw hours block
            for (i in data) {
                var dndClass = 'dndTarget';
                // Open period
                var endTime  = data[i].endTime;
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

                var tmp       = dojo.doc.createElement("div");
                tmp.id        = 'targetBooking' + data[i].id;
                tmp.innerHTML = data[i].display;
                dojo.addClass(tmp, dndClass);
                dojo.style(tmp, "top", top);
                dojo.style(tmp, "height", height);
                if (parseInt(height) <= 4) {
                    if (dojo.isIE && dojo.isIE < 8) {
                        lineHeight = 0.5;
                    } else {
                        lineHeight = 0;
                    }
                    dojo.style(tmp, "lineHeight", lineHeight);
                } else if (parseInt(height) < 14) {
                    if (dojo.isIE && dojo.isIE < 8) {
                        lineHeight = 1;
                    } else {
                        lineHeight = 0.5;
                    }
                    dojo.style(tmp, "lineHeight", lineHeight);
                }
                dijit.byId("projectBookingContainer").domNode.appendChild(tmp);
                dojo.connect(tmp, "onclick",  dojo.hitch(this, "fillForm", data[i].id));
            }
        })});
    },

    darwFormView:function(values) {
        // Summary:
        //    Render the form and the favorites
        // Description:
        //    Render the form and the favorites
        var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
        var meta      = phpr.DataStore.getMetaData({url: this._url});
        var range     = meta[4]['range'];

        for (i in favorites) {
            phpr.destroyWidget('projectSource' + favorites[i].id);
        }

        this.fieldTemplate = new phpr.Default.Field();

        // Init formdata
        var formData = '';
        // date
        formData += this.fieldTemplate.dateRender(meta[0]["label"], meta[0]["key"], this._date, meta[0]["required"],
            false, meta[0]["hint"]);
        // startTime
        formData += this.fieldTemplate.textFieldRender(meta[1]["label"], meta[1]["key"], '', 5, meta[1]["required"],
            false, meta[1]["hint"]);
        // endTime
        formData += this.fieldTemplate.textFieldRender(phpr.nls.get('End Time'), 'endTime', '', 5, meta[2]["required"],
            false, meta[2]["hint"]);
        // projectId
        meta[4]['range'].push({'id': -1, 'name': ''});
        formData += this.fieldTemplate.selectRender(meta[4]['range'], meta[4]["label"], meta[4]["key"], -1,
            meta[4]["required"], false, meta[4]["hint"]);
        // notes
        formData += this.fieldTemplate.textAreaRender(meta[5]["label"], meta[5]["key"], '', meta[5]["required"],
            false, meta[5]["hint"]);

        this.render(["phpr.Timecard.template", "formView.html"], dojo.byId('formView'), {
            formData:            formData,
            saveText:            phpr.nls.get('Save'),
            deleteText:          phpr.nls.get('Delete'),
            manageFavoritesText: phpr.nls.get('Manage project list'),
            favorites:           favorites
        });
        // Fix layout
        projectBookingSource.domNode.style.height = 'auto';

        // Event buttons
        dojo.connect(dijit.byId('manageFavorites'), "hide",  dojo.hitch(this, "submitFavoritesForm"));
        dojo.connect(dojo.byId('buttonManageFavorites'), "click",  dojo.hitch(this, "openManageFavorites"));
        dojo.connect(dijit.byId("deleteBookingButton"), "onClick", dojo.hitch(this, "deleteForm"));
        dojo.connect(dijit.byId("saveBookingButton"), "onClick", dojo.hitch(this, "submitForm"));
    },

    createFavoritesDialog:function(allProjects, favoritesList) {
        // Summary:
        //    Render the dialog for manage favorites
        // Description:
        //    Render the dialog for manage favorites
        for (i in allProjects) {
            phpr.destroyWidget('favoritesTarget-' + allProjects[i].id);
        }
        for (i in favoritesList) {
            phpr.destroyWidget('favoritesSoruce-' + favoritesList[i].id);
        }
        var html = this.render(["phpr.Timecard.template", "favoritesDialog.html"], dojo.byId('dialogContent'), {
            allProjects:   allProjects,
            favoritesList: favoritesList
        });
    },

    prepareSubmission:function() {
        // Summary:
        //    Correct some data before send it to the server
        // Description:
        //    Correct some data before send it to the server
        if (this.sendData.endTime) {
            this.sendData.endTime = phpr.Date.getIsoTime(this.sendData.endTime);
        }
        if (this.sendData.startTime) {
            this.sendData.startTime = phpr.Date.getIsoTime(this.sendData.startTime);
        }
        if (this.sendData.projectId < 0) {
            this.sendData.projectId = 0;
        }
        if (this.sendData.notes == "\n") {
            this.sendData.notes = "";
        }
        return true;
    },

    submitForm:function() {
        // Summary:
        //    Save the booking form
        // Description:
        //    Save the booking form and reload the views
        this.sendData = new Array();
        this.sendData = dojo.mixin(this.sendData, dijit.byId('bookingForm').attr('value'));
        if (!this.prepareSubmission()) {
            return false;
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonSave/id/' + this.id,
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.updateData();
                }
            })
        });
    },

    deleteForm:function(id) {
        // Summary:
        //    Delete a booking
        // Description:
        //    Delete a bookinh and reload the views
        phpr.send({
            url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.updateData();
               }
            })
        });
    },

    submitFavoritesForm:function() {
        // Summary:
        //    Save the favorites projects
        // Description:
        //    Save the favorites projects
        this.sendData                = new Array();
        this.sendData['favorites[]'] = new Array();
        var favorites = dojo.byId('selectedProjectFavorites').value.split(",");
        for (var i in favorites) {
            var value = parseInt(favorites[i]);
            if (value > 0) {
                this.sendData['favorites[]'].push(value);
            }
        }

        phpr.send({
            url:       phpr.webpath + 'index.php/Timecard/index/jsonFavoritesSave',
            content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    phpr.DataStore.deleteData({url: this._favoritesUrl});
                    phpr.DataStore.requestData({url: this._favoritesUrl});
               }
            })
        });
    },

    updateData:function() {
        // Summary:
        //    Delete the cache for all the views
        // Description:
        //    Delete the cache and reload the views
        this.id = 0;
        phpr.DataStore.deleteData({url: this._url});
        phpr.DataStore.deleteData({url: this._bookUrl});
        this.main.grid.reload(this.dateObject, true);;
        this.drawDayView();
        this.resetForm();
    },

    openManageFavorites:function() {
        // Summary:
        //    Function called on manageFavorites button click, to regulate the popup project's boxes height, and then
        //    open the Manage Favorites dialog
        if (dojo.byId("dialogContent").innerHTML.replace(/\s/g, "") == "") {
            var favorites = phpr.DataStore.getData({url: this._favoritesUrl});
            var meta      = phpr.DataStore.getMetaData({url: this._url});
            var range     = meta[4]['range'];

            // Get Favorites
            var favoritesList = new Array();
            for (var k in favorites) {
                 for (var j in range) {
                    if (range[j]['id'] == favorites[k]['id']) {
                        favoritesList.push(range[j]);
                    }
                }
            }

            // Get All Projects
            var allProjects   = new Array();
            for (var j in range) {
                var found = false;
                for (var k in favorites) {
                    if (range[j]['id'] == favorites[k]['id']) {
                        found = true;
                    }
                }
                if (!found) {
                    allProjects.push(range[j]);
                }
            }

            // Make Dialog
            this.createFavoritesDialog(allProjects, favoritesList);
        }
        dijit.byId('manageFavorites').show();

        // If there are no projects in any of the boxes, don't let it reduce its height so much
        if (projectFavoritesSource && projectFavoritesSource.getAllNodes().length == 0) {
            dojo.style('projectFavoritesSource', 'height', this._manFavBoxesHeight + 'px');
        } else {
            dojo.style('projectFavoritesSource', 'height', '');
        }
        if (projectFavoritesTarget && projectFavoritesTarget.getAllNodes().length == 0) {
            dojo.style('projectFavoritesTarget', 'height', this._manFavBoxesHeight + 'px');
        } else {
            dojo.style('projectFavoritesTarget', 'height', '');
        }
    },

    updateForm:function(date, start, end, project, notes) {
        // Summary:
        //    Fill the form with some data
        // Description:
        //    Fill the form with some data
        dijit.byId("date").attr('value', new Date(date.getFullYear(), date.getMonth(), date.getDate()));
        dijit.byId('startTime').attr('value', start);
        dijit.byId('endTime').attr('value', end);
        dijit.byId('projectId').attr('value', project);
        dijit.byId('notes').attr('value', notes);
        if (parseInt(this.id) > 0) {
            dojo.style(dojo.byId('deleteBookingButtonDiv'), 'display', 'inline');
        } else {
            dojo.style(dojo.byId('deleteBookingButtonDiv'), 'display', 'none')
        }
    },

    fillForm:function(id) {
        // Summary:
        //    Fill the form with the data from a saved item
        // Description:
        //    Fill the form with the data from a saved item
        this.id   = id;
        this._url = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDetail/id/" + this.id;
        phpr.DataStore.addStore({url: this._url});
        phpr.DataStore.requestData({url: this._url, processData: dojo.hitch(this, function() {
            var data      = phpr.DataStore.getData({url: this._url});
            var endTime   = data[0]['endTime'].substr(0,5);
            var startTime = data[0]['startTime'].substr(0,5);
            if (endTime == 0 || endTime == null) {
                var hour = parseInt(startTime) + 1;
                if (hour < 10) {
                    hour = '0' + hour;
                }
                var endTime = hour + ':00';
            }
            this.updateForm(this.dateObject, startTime, endTime, data[0]['projectId'],
                data[0]['notes']);
            dojo.byId('notes').focus();
        })});
    },

    fillFormTime:function(start) {
        // Summary:
        //    Fill the form with the start and end time
        // Description:
        //    Fill the form with the start and end time
        this.id = 0;
        start   = parseInt(start);
        var hour = start + 1;
        if (hour < 10) {
            hour = '0' + hour;
        }
        var end  = hour + ':00';
        if (start < 10) {
            start = '0' + start;
        }
        start = start + ':00';
        this.updateForm(this.dateObject, start, end, '', "\n");
        dojo.byId('projectId').focus();
    },

    fillFormProject:function(projectId) {
        // Summary:
        //    Fill the form with one project
        // Description:
        //    Fill the form with one project
        this.id = 0;
        if (dijit.byId('startTime').attr('value') != '') {
            var start = dijit.byId('startTime').attr('value');
        } else {
            var start = this._getNow();
        }
        if (dijit.byId('endTime').attr('value') != '') {
            var end = dijit.byId('endTime').attr('value');
        } else {
            var end = '';
        }
        this.updateForm(this.dateObject, start, end, projectId, "\n");
        dojo.byId('notes').focus();
    },

    resetForm:function() {
        // Summary:
        //    Remove all the values of the form
        // Description:
        //    Remove all the values of the form
        this.id = 0;
        this.updateForm(this.dateObject, '', '', '', "\n");
    },

    _getNow:function() {
        // Summary:
        //    Return the current HH:mm
        // Description:
        //    Return the current HH:mm
        var now    = new Date();
        var hour   = now.getHours();
        var minute = now.getMinutes();
        if (hour < 10) {
            hour   = "0" + hour;
        }
        if (minute < 10) {
            minute = "0" + minute;
        }
        return phpr.Date.getIsoTime(hour + ':' + minute);
    }
});
