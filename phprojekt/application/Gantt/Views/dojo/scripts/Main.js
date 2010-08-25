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
 * @subpackage Gantt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Gantt.Main");

dojo.declare("phpr.Gantt.Main", phpr.Default.Main, {
    gantt:   null,
    scale:   1.8,
    toggled: 0,

    constructor:function() {
        this.module = 'Gantt';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Gantt.Grid;
        this.formWidget = phpr.Gantt.Form;

        dojo.subscribe("Gantt.dialogCallback", this, "dialogCallback");
        dojo.subscribe("Gantt.toggle", this, "toggle");
        dojo.subscribe("Gantt.revertSlider", this, "revertSlider");
    },

    renderTemplate:function() {
        // Summary:
        //   Custom renderTemplate for gantt
        var projectPeriodHelp = phpr.nls.get('Click on a Project timeline and see and/or change here the Start and End '
            + 'dates.');
        this.render(["phpr.Gantt.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            webpath:                     phpr.webpath,
            selectedProjectTimelineText: phpr.nls.get('Selected Project Timeline'),
            projectPeriodHelp:           projectPeriodHelp
        });
    },

    setWidgets:function() {
        // Summary:
        //   Custom setWidgets for gantt
        phpr.Tree.loadTree();
        this.gantt = new phpr.Project.GanttBase(this);
        this._url  = phpr.webpath + 'index.php/Gantt/index/jsonGetProjects/nodeId/' + phpr.currentProjectId;
        phpr.DataStore.addStore({'url': this._url, 'noCache': true});
        phpr.DataStore.requestData({'url': this._url, 'processData': dojo.hitch(this, 'prepareData')});
    },

    setNewEntry:function() {
    },

    prepareData:function(items, request) {
        // Summary:
        //    Render all the views
        // Description:
        //    Get all the data and render all the views
        var data = phpr.DataStore.getData({url: this._url});

        // Keep the project data
        this.gantt.projectDataBuffer = data["projects"] || Array();

        // Assign global constants required for calculations with boundaries provided by data provider
        this.gantt.STEPPING = data["step"];
        // Convert second in mseconds (JS supported format)
        this.gantt.MIN_DATE = 1000 * data["min"];
        this.gantt.MAX_DATE = 1000 * data["max"];

        if ((phpr.currentProjectId == 1 && this.gantt.projectDataBuffer.length > 0) ||
            (phpr.currentProjectId != 1 && this.gantt.projectDataBuffer.length > 1)) {
            // Find how many hidden projects will be
            this.toggled = 0;
            for (var j in this.gantt.projectDataBuffer) {
                var projectLevel = this.gantt.projectDataBuffer[j].level;
                if (projectLevel != 10) {
                    this.toggled++;
                }
            }
            // Set timeline
            var width = this.setTimeline();

            // Collect the projects information
            var dataForRender = new Array();
            for (var j in this.gantt.projectDataBuffer) {
                var caption = this.gantt.projectDataBuffer[j].caption;
                if (caption.length > 25) {
                    caption = caption.substr(0, 25) + '...';
                }

                var projectValues = new Array(
                    this.gantt.convertStampToIndex(1000 * this.gantt.projectDataBuffer[j].start),
                    this.gantt.convertStampToIndex(1000 * this.gantt.projectDataBuffer[j].end)
                );
                var projectName   = this.buildProjectName(j).toString();
                var projectChilds = this.gantt.projectDataBuffer[j].childs.toString();
                var projectLevel  = this.gantt.projectDataBuffer[j].level;
                var display       = (projectLevel == 10) ? 'block' : 'none';
                var writeAccess   = data["rights"]["currentUser"][this.gantt.projectDataBuffer[j].id] || false;

                this.gantt.projectDataBuffer[j] = new Array(projectName, projectValues[0], projectValues[1],
                    projectChilds, 0);

                dataForRender.push({
                    name:     projectName,
                    level:    projectLevel,
                    caption:  caption,
                    value:    projectValues,
                    display:  display,
                    disabled: (!writeAccess) ? 'disabled="disabled"' : ''
                });
            }

            // Render the projects information
            this.render(["phpr.Gantt.template", "inner.html"], dojo.byId('projectList'), {
                dataForRender: dataForRender,
                gantt:         this.gantt,
                STEPPING:      this.gantt.STEPPING,
                width:         width + 4,
                webpath:       phpr.webpath
            });

            dijit.byId('ganttObject').container = this.gantt;

            // Insert 2 date widgets dynamically
            this.installCalendars();
            // Set the toggle button
            this.setToggle();
        } else {
            dojo.byId('projectList').innerHTML = phpr.drawEmptyMessage('There are no valid projects');
        }

        this.render(["phpr.Gantt.template", "formbuttons.html"], dojo.byId("bottomContent"), {
            writePermissions: data["rights"]["currentUser"]["write"],
            saveText:         phpr.nls.get('Save')
        });

        // Action buttons for the form
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
    },

    installCalendars:function() {
        // Summary:
        //    Render the calendar widgets
        // Description:
        //    Render the calendar widgets
        phpr.destroyWidget("minDate");
        phpr.destroyWidget("maxDate");
        // the project begin date widget (calendar view)
        this.gantt.DateMin = new dijit.form.DateTextBox({
            name:          'minDate',
            id:            'minDate',
            constraints:   {datePattern:'yyyy-MM-dd', strict:true},
            promptMessage: "yyyy-MM-dd",
            onChange:      dojo.hitch(this, function() {
                this.gantt.setRangeSelect(arguments[0], 'min');
            }),
            style:         'width:150px;',
            required:      true},
            dojo.byId('TgtMin')
        );

        // The project end date widget (calendar view)
        this.gantt.DateMax = new dijit.form.DateTextBox({
            name:          'maxDate',
            id:            'maxDate',
            constraints:   {datePattern:'yyyy-MM-dd', strict:true},
            promptMessage: "yyyy-MM-dd",
            onChange:      dojo.hitch(this, function() {
                this.gantt.setRangeSelect(arguments[0], 'max');
            }),
            style:         'width:150px;',
            required:      true},
            dojo.byId('TgtMax')
        );
    },

    buildProjectName:function(index) {
        // Summary:
        //    Return the name of the project in a "gantt" way
        // Description:
        //    Return the name of the project using p: parentId | own: id
        var name = "p:" + this.gantt.projectDataBuffer[index].parent + "|own:" + this.gantt.projectDataBuffer[index].id;
        return name;
    },

    decodeName:function(element) {
        // Summary:
        //    Decode the name and return the parent and id
        // Description:
        //    Decode the name and return the parent and id
        if (element.id) {
            var parent = Number(element.id.split(':')[1].split('|')[0]);
            var id     = Number(element.id.split(':')[2]);
        } else {
            var parent = 0;
            var id     = 0;
        }
        return new Object({'parent': parent, 'id': id});
    },

    setHeight:function() {
        // Summary:
        //    This function sets the height of the vertical lines
        // Description:
        //    This function sets the height of the vertical lines
        var height = this.getProjectsHeight(phpr.currentProjectId);
        dojo.query('#gantt_timeline .slider .splitter').forEach(function(ele) {
            dojo.style(ele, 'height', (height) + 'px');
        });
    },

    getProjectsHeight:function(parent) {
        // Summary:
        //    Return the height depends on the number of visible projects
        // Description:
        //    Return the height depends on the number of visible projects
        var count = this.gantt.projectDataBuffer.length - this.toggled
        if (count < 0) {
            count = 1;
        }

        return (45 + (count * 24));
    },

    setToggle:function() {
        // Summary:
        //    This function assigns the function to toggle the lines.
        // Description:
        //    The assignment is not flexible due to the 'ProjectChart' instance.
        this.gantt.toggleElement = new Object();
        var self                 = this;
        var i                    = 0;
        dojo.query('.project_list .sub_project').forEach(function(element) {
            var a = element.getElementsByTagName('a')[0];
            if (self.gantt.projectDataBuffer[i][3] > 0) {
                a.innerHTML = "[+] " + a.innerHTML;
            }
            var info = self.decodeName(element);
            if (info.id > 0) {
                a.onclick = dojo.hitch(this, function() {
                    dojo.publish('Gantt.toggle', [a, info.parent, info.id]);
                });
            }
            i++;
        });
    },

    switchController:function(element, close) {
        // Summary:
        //    This function checks whether the element is expanded or not to replace the +/-
        // Description:
        //    This function checks whether the element is expanded or not to replace the +/-
        if (element.innerHTML.indexOf('+') > 0 || close == false) {
            element.innerHTML = element.innerHTML.replace("[+]", "[-]");
            return true;
        } else {
            element.innerHTML = element.innerHTML.replace("[-]", "[+]");
            return false;
        }
    },

    toggle:function(element, parent, id) {
        // Summary:
        //    This function is triggerd by the a-element to toggle the childs.
        // Description:
        //    This function is triggerd by the a-element to toggle the childs.
        var myHandler = this.switchController(element);
        this.toggleProject(myHandler, id);
        this.setHeight();
    },

    toggleProject:function(show, parentId) {
        // Summary:
        //    Show or hide all the projects under the parentId
        // Description:
        //    Show or hide all the projects under the parentId
        var i     = 0;
        var self  = this;
        dojo.query(".project_list .sub_project").forEach(function(element) {
            var info = self.decodeName(element);
            if (info.id > 0 && (info.parent == parentId)) {
                if (show == true) {
                    if (element.style.display != 'block') {
                        element.style.display = 'block';
                        self.toggled--;
                    }
                } else {
                    if (element.style.display != 'none') {
                        element.style.display = 'none';
                        self.toggled++;
                    }
                }
                if (self.gantt.projectDataBuffer[i][3] > 0) {
                    if (show == true) {
                        if (element.style.display != 'block') {
                            element.style.display = 'block';
                            self.toggled--;
                        }
                    } else {
                        if (element.style.display != 'none') {
                            element.style.display = 'none';
                            self.toggled++;
                        }
                    }
                    var a = element.getElementsByTagName('a')[0];
                    if (a.innerHTML.indexOf('-') > 0) {
                        self.toggleProject(show, info.id);
                    }
                }
            }
            i++;
        });
    },

    setTimeline:function() {
        // Summary:
        //    This function creates the timeline on top of the gantt
        // Description:
        //    The months are saved in an array and should be loaded in the selected language.
        //    The time spread is dynamically created by the given time scope.
        var element   =  dojo.byId('gantt_timeline');
        var startDate = new Date(this.gantt.MIN_DATE);
        var endDate   = new Date(this.gantt.MAX_DATE);
        var height    = this.getProjectsHeight(phpr.currentProjectId);

        var html = '<ul class="sub_project">';
        html += '<li style="border-left: none; border-right: none; width: 250px; float: left;">&nbsp;</li>';
        html += '<li style="width: 12px; float: left;">&nbsp;</li>';
        html += '<li class="slider" style="float: left; margin-top: 0px; left: 0px;">';
        html += '<ul style="margin-top: 0px; width: 100%; left: 0px;">';
        html += '<li class="splitter" style="float: left; width: 1px; height: ' + height + 'px;';
        html += 'border-left: 1px dotted #3d3d3d; margin-left: -1px;"></li>';

        // Get how many years there are
        var years          = 1;
        var checkStartDate = startDate;
        for (var i = 0 ; true ; i++) {
            checkStartDate = dojo.date.add(checkStartDate, 'month', 1);
            var check = dojo.date.compare(checkStartDate, endDate);
            if (check == 1) {
                break;
            }
            if (i > 11) {
                i = 0;
                years++;
            }
        }

        var maxWidth  = 268 + (365 * 2 * years);

        // Change the width to the maxWidth
        dojo.style(dojo.byId('ganttChart'), "width", maxWidth + "px");

        // Draw the timeline with the correct scale
        var totalWidth = 0;
        for (var i = 0 ; true ; i++) {
            startDate = dojo.date.add(startDate, 'month', 1);
            var check = dojo.date.compare(startDate, endDate);
            if (check == 1) {
                break;
            }
            var year   = startDate.getFullYear().toString().substr(2,2);
            var width  = Math.round(dojo.date.getDaysInMonth(startDate) * this.scale);
            totalWidth = totalWidth + width;

            if (i > 11) {
                i = 0;
            }

            var monthNumber = (i < 9) ? '0' + Math.round(i+1) : Math.round(i+1);
            var monthString = monthNumber + '.' + year;

            html += '<li style="width:' + width + 'px; float: left;">' + monthString + '</li>';
            html += '<li class="splitter" style="float: left; width: 1px; height: ' + height + 'px;';
            html += 'border-left: 1px dotted #3d3d3d; margin-left: -2px;"></li>';
        }
        html += '</ul></li></ul>';

        element.innerHTML    = html;
        this.gantt.FIX_VALUE = this.scale;

        return totalWidth;
    },

    dialogCallback:function(posMin, posMax, nodeToChange, currentNode, dialogType) {
        // Summary:
        //    The callback function is executed by the dialog box continue the onChange event
        // Description:
        //    The callback function is executed by the dialog box continue the onChange event
        this.gantt.dialogCallback(posMin, posMax, nodeToChange, currentNode, dialogType);
    },

    revertSlider:function(currentNode) {
        // Summary:
        //    Function executed on cancel
        // Description:
        //    Function executed on cancel
        this.gantt.revertSlider(currentNode);
    },

    submitForm:function() {
        // Summary:
        //    Collect all the project values and save it
        // Description:
        //    Collect all the project values and save it
        var sendData   = new Array();
        var projects   = new Array();
        var listIndex  = -1;
        var listLength = this.gantt.projectDataBuffer.length;
        var ids        = new Array();
        while (++listIndex < listLength) {
            var name  = this.gantt.projectDataBuffer[listIndex][0];
            var value = this.gantt.normalizeValues(dijit.byId(name).get('value'));
            // Only send the changed values
            if ((value[0] != this.gantt.projectDataBuffer[listIndex][1]) ||
                (value[1] != this.gantt.projectDataBuffer[listIndex][2]) ||
                this.gantt.projectDataBuffer[listIndex][4]) {
                var id = name.split(':')[2];
                projects.push(id + "," + this.gantt.convertIndex2DateString(value[0]) + ","
                    + this.gantt.convertIndex2DateString(value[1]));
                ids.push(id);
            }
        }
        sendData['projects[]'] = projects;
        phpr.send({
            url:       phpr.webpath + 'index.php/Gantt/index/jsonSave/nodeId/' + phpr.currentProjectId,
            content:   sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this.updateCacheData(ids);
                    this.reload();
                }
            })
        });
    },

    updateCacheData:function(ids) {
        // Summary:
        //    Update all the caches
        // Description:
        //    Update list, parent and form cached for the changed projects
        for (var i in ids) {
            var parentId = phpr.Tree.getParentId(ids[i]);
            // List
            var listUrl = phpr.webpath + 'index.php/Project/index/jsonList/nodeId/' + ids[i];
            phpr.DataStore.deleteDataPartialString({url: listUrl});
            // Parent List
            var listUrl  = phpr.webpath + 'index.php/Project/index/jsonList/nodeId/' + parentId;
            phpr.DataStore.deleteDataPartialString({url: listUrl});
            // Form
            var formUrl = phpr.webpath + 'index.php/Project/index/jsonDetail/nodeId/' + parentId
                + "/id/" + ids[i];
            phpr.DataStore.deleteData({url: formUrl});
        }
    },

    openForm:function(id, module) {
    }
});
