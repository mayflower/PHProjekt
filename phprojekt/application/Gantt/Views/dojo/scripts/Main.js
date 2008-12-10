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

dojo.provide("phpr.Gantt.Main");

dojo.declare("phpr.Gantt.Main", phpr.Default.Main, {
    gantt: null,

    constructor:function() {
        this.module = 'Gantt';
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Gantt.Grid;
        this.formWidget = phpr.Gantt.Form;
        this.treeWidget = phpr.Gantt.Tree;

        dojo.subscribe("Gantt.dialogCallback", this, "dialogCallback");
        dojo.subscribe("Gantt.toggle", this, "toggle");
    },

    reload:function() {
        phpr.module = this.module;
        this.render(["phpr.Gantt.template", "mainContent.html"], dojo.byId('centerMainContent') ,{
            webpath:                     phpr.webpath,
            selectedProjectTimelineText: phpr.nls.get("Selected Project Timeline")
        });
        this.cleanPage();
        if (this._isGlobalModule(this.module)) {
            this.setSubGlobalModulesNavigation();
        } else {
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
        this.tree  = new this.treeWidget(this);

        this.gantt = new phpr.Project.GanttBase();

        this._url = phpr.webpath + "index.php/Gantt/index/jsonGetProjects/nodeId/" + phpr.currentProjectId;
        phpr.DataStore.addStore({'url': this._url, 'noCache': true});
        phpr.DataStore.requestData({'url': this._url, 'processData': dojo.hitch(this, 'prepareData')});

        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"),{
            writePermissions:  true,
            deletePermissions: false,
            saveText:          phpr.nls.get('Save'),
            deleteText:        phpr.nls.get('Delete')
        });

        // Action buttons for the form
        dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
    },

    setNewEntry:function() {
    },

    prepareData:function(items, request) {
        // summary:
        //    Render all the views
        // description:
        //    Get all the data and render all the views
        var data = phpr.DataStore.getData({url: this._url});

        this.gantt.projectDataBuffer = data["projects"] || Array();

        // assign global constants required for calculations with boundaries provided by data provider
        this.gantt.STEPPING = data["step"];
        // convert second in mseconds (JS supported format)
        this.gantt.MIN_DATE = 1000 * data["min"];
        this.gantt.MAX_DATE = 1000 * data["max"];

        // Render the projects information
        dojo.byId('projectList').innerHTML = '';
        for (var j in this.gantt.projectDataBuffer) {
            dojo.byId('projectList').innerHTML += this.render(["phpr.Gantt.template", "inner.html"], null ,{
                name:     this.gantt.projectDataBuffer[j].name,
                level:    this.gantt.projectDataBuffer[j].level,
                caption:  this.gantt.projectDataBuffer[j].caption,
                STEPPING: this.gantt.STEPPING - 1,
                width:    (this.gantt.STEPPING *2) + 4,
                webpath:  phpr.webpath
            });
        }
        dojo.parser.parse(dojo.byId('projectList'));

        // set timeline
        this.setTimeline();
        // insert 2 date widgets dynamically
        this.installCalendars();
        // insert projects dynamically
        this.installProjects();
        //this.setToggle();
        this.setHeight();
    },

    installCalendars:function() {
        // summary:
        //    Render the calendar widgets
        // description:
        //    Render the calendar widgets
        phpr.destroyWidget("minDate");
        phpr.destroyWidget("maxDate");
        // the project begin date widget (calendar view)
        this.gantt.DateMin = new dijit.form.DateTextBox({name:          'minDate',
                                                         id:            'minDate',
                                                         constraints:   {datePattern:'yyyy-MM-dd', strict:true},
                                                         promptMessage: "yyyy-MM-dd",
                                                         onChange:      dojo.hitch(this,function() { this.gantt.setRangeSelect(arguments[0], 'min'); }),
                                                         style:         'width:150px;',
                                                         required:      true},
                                                         dojo.byId('TgtMin'));
        // the project end date widget (calendar view)
        this.gantt.DateMax = new dijit.form.DateTextBox({name:          'maxDate',
                                                         id:            'maxDate',
                                                         constraints:   {datePattern:'yyyy-MM-dd', strict:true},
                                                         promptMessage: "yyyy-MM-dd",
                                                         onChange:      dojo.hitch(this,function() { this.gantt.setRangeSelect(arguments[0], 'max'); }),
                                                         style:         'width:150px;',
                                                         required:      true},
                                                         dojo.byId('TgtMax'));
    },


    installProjects:function() {
        // summary:
        //    Render the projects
        // description:
        //    calculation hog on the User side: recalculate date indexes,
        //    reassign values, wire correct onfocus and on change events
        var listLength = this.gantt.projectDataBuffer.length;
        var listIndex  = -1;

        while(++listIndex < listLength) {
            var projectValues = new Array(
                this.gantt.convertStampToIndex(1000 * this.gantt.projectDataBuffer[listIndex].start),
                this.gantt.convertStampToIndex(1000 * this.gantt.projectDataBuffer[listIndex].end)
            );
            var projectName   = new String(this.gantt.projectDataBuffer[listIndex].name);
            var ProjectChilds = new String(this.gantt.projectDataBuffer[listIndex].childs)
            dijit.byId(projectName).container = this.gantt;
            dijit.byId(projectName).attr('value', projectValues);
            this.gantt.projectDataBuffer[listIndex] = new Array(projectName, projectValues[0], projectValues[1], ProjectChilds );
        }
    },

    setHeight:function() {
        // summary:
        //    This function sets the height of the vertical lines
        // description:
        //    This function sets the height of the vertical lines
        var height = (this.gantt.projectDataBuffer.length + 1) * 22;
        dojo.query('#gantt_timeline .slider .splitter').forEach(function(ele) {
            dojo.style(ele, 'height', (height)+'px');
        });
    },

    setToggle:function() {
        // summary:
        //    This function assigns the function to toggle the lines.
        // description:
        //    The assignment is not flexible due to the 'ProjectChart' instance.
        this.gantt.toggleElement = new Object();
        var i    = 0;
        var self = this;
        dojo.query('.project_list .sub_project').forEach(function(el){
            var par = Number(el.id.split(':')[1].split('|')[0]);
            var id  = Number(el.id.split(':')[2]);
            el.getElementsByTagName('a')[0].onclick = dojo.hitch(this,function() {dojo.publish('Gantt.toggle', [this, par, id]);});
            self.gantt.toggleElement[i] = par;
            i++;
        });

        for(var i = 0; i< this.gantt.projectDataBuffer.length; i++) {
            var value = this.gantt.toggleElement[i];
            if ((value - 1) >= 0) {
                var element = dojo.query(".project_list .sub_project .expander")[(value - 1)];
                if (element.innerHTML.indexOf('[-]') == -1) {
                    element.innerHTML = "[-] " + element.innerHTML;
                }
            }
        }
    },

    switchController:function(pEle, pDirect) {
        // summary:
        //    This function checks whether the element is expanded or not to replace the +/-
        // description:
        //    This function checks whether the element is expanded or not to replace the +/-
        if (element.innerHTML.indexOf('+') > 0 || pDirect == false) {
            element.innerHTML = element.innerHTML.replace("[+]", "[-]");
            return true;
        }
        else {
            element.innerHTML = element.innerHTML.replace("[-]", "[+]");
            return false;
        }
    },

    toggle:function(pEle, pPar, pId) {
        // summary:
        //    This function is triggerd by the a-element to toggle the childs.
        // description:
        //    This function is triggerd by the a-element to toggle the childs.
        var myEle     = dojo.query('.project_list .sub_project .expander')[(pId-1)];
        var myHandler = this.switchController(myEle);

        var childs = this.gantt.projectDataBuffer[(pId-1)][3];
        dojo.query(".project_list .sub_project").forEach(function(el){
            var myPar = el.id.split(':')[1].split('|')[0];
            var myId  = el.id.split(':')[2];
            var myMax =  Number(pId)+Number(childs);

            if (myHandler == true) {
                var myQu = myPar >= pId;
            }
            if (myHandler == false) {
                var myQu = myPar >= pId && myPar <= myMax;
            }

            if (myQu) {
                el.style.display = (myHandler == true) ? 'block' : 'none';
            }
        });

        this.setHeight();
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
        var months    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dec'];

        var html = '<ul class="sub_project">';
        html += '<li style="border-left:none; border-right:none; width:25%; float: left;">&nbsp;</li>';
        html += '<li style="width:12px; float: left;">&nbsp;</li>';
        html += '<li class="slider" style="float:left; margin-top:0px; left:0px;">';
        html += '<ul style="margin-top:0px; width:100%; left:0px;">';
        html += '<li class="splitter" style="float:left; width:1px; height:5px; border-left:1px dotted #3d3d3d; margin-left: -1px;"></li>';

        for (var i = 0 ; true ; i++) {
            startDate = dojo.date.add(startDate, 'month', 1);
            var check = dojo.date.compare(startDate, endDate);
            if (check == 1) {
                break;
            }
            var year   = startDate.getFullYear();
            var width  = (dojo.date.getDaysInMonth(startDate) * 2);

            html += '<li style="width:'+width+'px; border-top:1px solid #7d7d7d; float:left;">' + phpr.nls.get(months[i]) + " " + year + "</li>";
            html += '<li class="splitter" style="float:left; width:1px; height:5px; border-left:1px dotted #3d3d3d;margin-left: -2px;"></li>';
        }
        html += '</ul></li></ul>';
        element.innerHTML = html;
    },

    dialogCallback:function(values) {
        // summary:
        //    The callback function is executed by the dialog box continue the onChange event
        // description:
        //    The callback function is executed by the dialog box continue the onChange event
        this.gantt.dialogCallback(values);
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
        while(++listIndex < listLength) {
            var name  = this.gantt.projectDataBuffer[listIndex][0];
            var value = this.gantt.normalizeValues(dijit.byId(name).attr('value'));
            var id    = name.split(':')[2];
            projects[listIndex] = id + "," + this.gantt.convertIndex2DateString(value[0]) + "," + this.gantt.convertIndex2DateString(value[1]);
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
        //    Update list, parent and from cached for the changed projects
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
