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

dojo.provide("phpr.Gantt.Main");

dojo.declare("phpr.Gantt.Main", phpr.Default.Main, {
    gantt: null,
    scale: 1,

    constructor:function() {
        this.module = 'Gantt';
        this.loadFunctions(this.module);

        this.treeWidget = phpr.Gantt.Tree;

        dojo.subscribe("Gantt.dialogCallback", this, "dialogCallback");
        dojo.subscribe("Gantt.toggle", this, "toggle");
        dojo.subscribe("Gantt.revertSlider", this, "revertSlider");
    },

    reload:function() {
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
        this.render(["phpr.Gantt.template", "mainContent.html"], dojo.byId('centerMainContent'), {
            webpath:                     phpr.webpath,
            selectedProjectTimelineText: phpr.nls.get("Selected Project Timeline"),
            projectPeriodHelp:           phpr.nls.get("Project Period Help")
        });
        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            phpr.TreeContent.fadeOut();
            this.setSubGlobalModulesNavigation();
        } else {
            phpr.TreeContent.fadeIn();
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree  = new this.treeWidget(this);

        this.gantt = new phpr.Project.GanttBase(this);

        this._url = phpr.webpath + "index.php/Gantt/index/jsonGetProjects/nodeId/" + phpr.currentProjectId;
        phpr.DataStore.addStore({'url': this._url, 'noCache': true});
        phpr.DataStore.requestData({'url': this._url, 'processData': dojo.hitch(this, 'prepareData')});
    },

    prepareData:function(items, request) {
        // summary:
        //    Render all the views
        // description:
        //    Get all the data and render all the views
        var data = phpr.DataStore.getData({url: this._url});

        // Keep the project data
        this.gantt.projectDataBuffer = data["projects"] || Array();

        // assign global constants required for calculations with boundaries provided by data provider
        this.gantt.STEPPING = data["step"];
        // convert second in mseconds (JS supported format)
        this.gantt.MIN_DATE = 1000 * data["min"];
        this.gantt.MAX_DATE = 1000 * data["max"];

        if (this.gantt.projectDataBuffer.length > 0) {
            // set timeline
            var width = this.setTimeline();

            // Render the projects information
            dojo.byId('projectList').innerHTML = '';
            for (var j in this.gantt.projectDataBuffer) {
                var caption = this.gantt.projectDataBuffer[j].caption;
                if (caption.length > 25) {
                    caption = caption.substr(0, 25) + '...';
                }
                dojo.byId('projectList').innerHTML += this.render(["phpr.Gantt.template", "inner.html"], null, {
                    name:     this.buildProjectName(j),
                    level:    this.gantt.projectDataBuffer[j].level,
                    caption:  caption,
                    STEPPING: this.gantt.STEPPING,
                    width:    width + 4,
                    webpath:  phpr.webpath
                });
            }
            dojo.parser.parse(dojo.byId('projectList'));

            // Insert 2 date widgets dynamically
            this.installCalendars();
            // Insert projects dynamically
            this.installProjects();
            this.setToggle();
            this.setHeight();
        } else {
            dojo.byId('projectList').innerHTML = phpr.drawEmptyMessage('There are no valid projects');
        }

        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"), {
            writePermissions:  data["rights"]["currentUser"]["write"],
            deletePermissions: false,
            saveText:          phpr.nls.get('Save'),
            deleteText:        phpr.nls.get('Delete')
        });

        // Action buttons for the form
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
    },

    installCalendars:function() {
        // summary:
        //    Render the calendar widgets
        // description:
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
        // the project end date widget (calendar view)
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

    installProjects:function() {
        // summary:
        //    Render the projects
        // description:
        //    calculation hog on the User side: recalculate date indexes,
        //    reassign values, wire correct onfocus and on change events
        var listLength = this.gantt.projectDataBuffer.length;
        var listIndex  = -1;

        while (++listIndex < listLength) {
            var projectValues = new Array(
                this.gantt.convertStampToIndex(1000 * this.gantt.projectDataBuffer[listIndex].start),
                this.gantt.convertStampToIndex(1000 * this.gantt.projectDataBuffer[listIndex].end)
            );

            var projectName   = new String(this.buildProjectName(listIndex));
            var ProjectChilds = new String(this.gantt.projectDataBuffer[listIndex].childs)
            dijit.byId(projectName).container = this.gantt;
            dijit.byId(projectName).attr('value', projectValues);
            this.gantt.projectDataBuffer[listIndex] = new Array(projectName, projectValues[0], projectValues[1],
                ProjectChilds);
        }
    },

    buildProjectName:function(index) {
        var name = "p:" + this.gantt.projectDataBuffer[index].parent + "|own:" + this.gantt.projectDataBuffer[index].id;
        return name;
    },

    decodeName:function(element) {
        // summary:
        //    Decode the name and return the parent and id
        // description:
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
        // summary:
        //    This function sets the height of the vertical lines
        // description:
        //    This function sets the height of the vertical lines
        var count = this.getProjectCount(phpr.currentProjectId) + 1;
        if (phpr.currentProjectId == 1) {
            count = this.getProjectCount(phpr.currentProjectId);
        }
        var height = 45 + (count * 23.44);
        dojo.query('#gantt_timeline .slider .splitter').forEach(function(ele) {
            dojo.style(ele, 'height', (height)+'px');
        });
    },

    getProjectCount:function(parent) {
        // summary:
        //    Return the number of sub projects included the project itself
        // description:
        //    Return the number of sub projects included the project itself
        var self  = this;
        var count = 0;
        dojo.query(".project_list .sub_project").forEach(function(element) {
            var info = self.decodeName(element);
            if (info.id > 0 && info.parent == parent) {
                if (element.style.display == 'block') {
                    count = count + 1 + self.getProjectCount(info.id);
                }
            }
        });
        return count;
    },

    setToggle:function() {
        // summary:
        //    This function assigns the function to toggle the lines.
        // description:
        //    The assignment is not flexible due to the 'ProjectChart' instance.
        this.gantt.toggleElement = new Object();
        var self                 = this;
        var i                    = 0;
        dojo.query('.project_list .sub_project').forEach(function(element) {
            var a = element.getElementsByTagName('a')[0];
            if (self.gantt.projectDataBuffer[i][3] > 0) {
                a.innerHTML = "[-] " + a.innerHTML;
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
        // summary:
        //    This function checks whether the element is expanded or not to replace the +/-
        // description:
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
        // summary:
        //    This function is triggerd by the a-element to toggle the childs.
        // description:
        //    This function is triggerd by the a-element to toggle the childs.
        var myHandler = this.switchController(element);

        this.toggleProject(myHandler, id);

        this.setHeight();
    },

    toggleProject:function(show, parentId) {
        // summary:
        //    Show or hide all the projects under the parentId
        // description:
        //    Show or hide all the projects under the parentId
        var i     = 0;
        var self  = this;
        dojo.query(".project_list .sub_project").forEach(function(element) {
            var info = self.decodeName(element);
            if (info.id > 0 && (info.parent == parentId)) {
                element.style.display = (show == true) ? 'block' : 'none';
                if (self.gantt.projectDataBuffer[i][3] > 0) {
                    element.style.display = (show == true) ? 'block' : 'none';
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
        // summary:
        //    This function creates the timeline on top of the gantt
        // description:
        //    The months are saved in an array and should be loaded in the selected language.
        //    The time spread is dynamically created by the given time scope.
        var element   =  dojo.byId('gantt_timeline');
        var startDate = new Date(this.gantt.MIN_DATE);
        var endDate   = new Date(this.gantt.MAX_DATE);
        var months    = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
            'October', 'November', 'December'];
        var surface   = dojox.gfx.createSurface("timeLine", 1024, 100);
        var m         = dojox.gfx.matrix;

        var html = '<ul class="sub_project">';
        html += '<li style="border-left:none; border-right:none; width:250px; float: left;">&nbsp;</li>';
        html += '<li style="width:12px; float: left;">&nbsp;</li>';
        html += '<li class="slider" style="float:left; margin-top:0px; left:0px;">';
        html += '<ul style="margin-top:0px; width:100%; left:0px;">';
        html += '<li class="splitter" style="float:left; width:1px; height:5px; ';
        html += 'border-left:1px dotted #3d3d3d; margin-left: -1px;"></li>';

        // Get how many years there are
        var years = 1;
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
        this.scale = (2 / years);

        // Draw the timeline with the correct scale
        var totalWidth = 0;
        for (var i = 0 ; true ; i++) {
            startDate = dojo.date.add(startDate, 'month', 1);
            var check = dojo.date.compare(startDate, endDate);
            if (check == 1) {
                break;
            }
            var year   = startDate.getFullYear();
            var width  = Math.round(dojo.date.getDaysInMonth(startDate) * this.scale);
            totalWidth = totalWidth + width;

            if (i > 11) {
                i = 0;
            }
            var month = months[i];

            html += '<li style="width:' + width + 'px; float:left;">&nbsp;</li>';
            html += '<li class="splitter" style="float:left; width:1px; height:5px; ';
            html += 'border-left:1px dotted #3d3d3d;margin-left: -2px;"></li>';

            var x = 260 + (totalWidth -(width / 2));
            if (years > 3) {
                var size = 7 * (this.scale * 2);
            } else {
                var size = 7;
            }
            phpr.Gfx.makeText(surface, {x: x, y: 85, text: phpr.nls.get(month) + " " + year, align: "start"},
            {family: "Verdana", size: size + "pt"}, "black", "black")
            .setTransform(m.rotategAt(-75, x, 85));
        }
        html += '</ul></li></ul>';

        element.innerHTML = html;
        this.gantt.FIX_VALUE = this.scale;

        return totalWidth;
    },

    dialogCallback:function(posMin, posMax, nodeToChange, currentNode, dialogType) {
        // summary:
        //    The callback function is executed by the dialog box continue the onChange event
        // description:
        //    The callback function is executed by the dialog box continue the onChange event
        this.gantt.dialogCallback(posMin, posMax, nodeToChange, currentNode, dialogType);
    },

    revertSlider:function(currentNode) {
        // summary:
        //    Function executed on cancel
        // description:
        //    Function executed on cancel
        this.gantt.revertSlider(currentNode);
    },

    submitForm:function() {
        // summary:
        //    Collect all the project values and save it
        // description:
        //    Collect all the project values and save it
        var sendData   = new Array();
        var projects   = new Array();
        var listIndex  = -1;
        var listLength = this.gantt.projectDataBuffer.length;
        var ids        = new Array();
        while (++listIndex < listLength) {
            var name  = this.gantt.projectDataBuffer[listIndex][0];
            var value = this.gantt.normalizeValues(dijit.byId(name).attr('value'));
            var id    = name.split(':')[2];
            projects[listIndex] = id + "," + this.gantt.convertIndex2DateString(value[0]) + ","
                + this.gantt.convertIndex2DateString(value[1]);
            ids.push(id);
        }
        sendData['projects[]'] = projects;
        phpr.send({
            url:       phpr.webpath + 'index.php/Gantt/index/jsonSave/',
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
        // summary:
        //    Update all the caches
        // description:
        //    Update list, parent and form cached for the changed projects
        for (var i in ids) {
            // List
            var listUrl = phpr.webpath + "index.php/Project/index/jsonList/nodeId/" + ids[i];
            phpr.DataStore.deleteData({url: listUrl});

            // Delete parent cache
            var parentId = this.tree.getParentId(ids[i]);
            var listUrl  = phpr.webpath + "index.php/Project/index/jsonList/nodeId/" + parentId;
            phpr.DataStore.deleteData({url: listUrl});

            // Form
            var formUrl = phpr.webpath + "index.php/" + phpr.module + "/index/jsonDetail/id/" + ids[i];
            phpr.DataStore.deleteData({url: formUrl});
        }
    }
});
