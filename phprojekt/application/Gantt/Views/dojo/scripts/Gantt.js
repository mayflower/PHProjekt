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

dojo.declare('phpr.Project.GanttBase', null, {
    constructor:function(main) {
        // Array of project names and its current min/max values
        this.projectDataBuffer = 1;

        // Name of the last activated slider
        this.activeSlider = new String();

        this.STEPPING = 192;
        this.MIN_DATE = 1213187363000;
        this.MAX_DATE = 1229804778000;
        this.DAY_MSEC = 86400000;
        this.main     = main;

        this.RESIZE_PARENT_START     = 0;
        this.RESIZE_PARENT_END       = 1;
        this.RESIZE_SUBPROJECT_END   = 2;
        this.RESIZE_SUBPROJECT_START = 3;
    },

    findArrayIndex:function(sliderName) {
        // summary:
        //    Walks the projectDataBuffer array, returns array index on name match
        // description:
        //    Walks the projectDataBuffer array, returns array index on name match
        var pipeIndexSearch = sliderName.lastIndexOf('|');

        // Try to find the named project element in the list and store last values
        var listIndex = this.projectDataBuffer.length;
        while (--listIndex > -1 ) {
            // Hash element given ()is not the case while initialization phase
            if (!this.projectDataBuffer[listIndex][0]) {
                return -1;
            }
            var pipeIndexBuffer = this.projectDataBuffer[listIndex][0].lastIndexOf('|');
            if (this.projectDataBuffer[listIndex][0].substring(pipeIndexBuffer) !=
                sliderName.substring(pipeIndexSearch)) {
                continue;
            }
            return listIndex;
        }
        return listIndex;
    },

    getProjectCaption:function(nodeName) {
        // summary:
        //    Implicates for existance of DOM element lbl_*projectid*
        //    which contains project textual name
        // description:
        //    Implicates for existance of DOM element lbl_*projectid*
        //    which contains project textual name
        var element = document.getElementById('lbl_'+ nodeName);
        if (element) {
            var caption = element.getElementsByTagName('a')[0].getElementsByTagName('strong')[0].innerHTML;
            return caption.substr(caption.lastIndexOf('.')+1);
        }
        return nodeName;
    },

    showDialog:function(text, dialogType, nodeToChange, currentNode, posMin, posMax) {
        // summary:
        //    Shows the dialog box to alert a conflict
        // description:
        //    Shows the dialog box to alert a conflict
        var content = this.main.render(["phpr.Gantt.template", "dialog.html"], null, {
            text:        text,
            ok:          phpr.nls.get('OK'),
            reset:       phpr.nls.get('Reset'),
            currentNode: currentNode
        });

        var dialog = new dijit.Dialog({
            title:     phpr.nls.get('Warning'),
            draggable: false,
            execute:   function(formContents){
                dojo.publish('Gantt.dialogCallback', [posMin, posMax, nodeToChange, dialogType])
            }
        });

        dialog.set('content', content);
        dojo.body().appendChild(dialog.domNode);
        dojo.disconnect(dialog, "onExecute");
        dialog.show();
    },

    dialogCallback:function(posMin, posMax, nodeToChange, dialogType) {
        // summary:
        //    The callback function is executed by the dialog box continue the onChange event
        // description:
        //    The callback function is executed by the dialog box continue the onChange event
        this.acceptUpdate(posMin, posMax, nodeToChange, dialogType);
    },

    processDialog:function(dialogType, nodeToChange, currentNode, posMin, posMax) {
        // summary:
        //    This function delivers the error text and executes showDialog()
        // description:
        //    This function delivers the error text and executes showDialog()

        if (dijit.byId(nodeToChange).get('disabled')) {
            return;
        }

        var toChange = this.getProjectCaption(nodeToChange);
        var current  = this.getProjectCaption(currentNode);

        switch(dialogType) {
            case this.RESIZE_PARENT_START:
                var text = phpr.nls.get('Attention: parent project');
                text += ' "' + toChange + '" ';
                text += phpr.nls.get('starts after sub-project');
                text += ' "' + current + '"!<br /><br />';
                text += phpr.nls.get('Click "OK" to adjust parent project to new start date') + '<br />';
                break;
            case this.RESIZE_PARENT_END:
                var text = phpr.nls.get('Attention: parent project');
                text += ' "' + toChange + '" ';
                text += phpr.nls.get('ends before sub-project');
                text += ' "' + current + '"!<br /><br />';
                text += phpr.nls.get('Click "OK" to adjust parent project to new end date') + '<br />';
                break;
            case this.RESIZE_SUBPROJECT_END:
                var text = phpr.nls.get('Attention: sub-project');
                text += ' "' + toChange + '" ';
                text += phpr.nls.get('ends after parent project');
                text += ' "' + current + '"!<br /><br />';
                text += phpr.nls.get('Click "OK" to adjust sub-project to new end date') + '<br />';
                break;
            case this.RESIZE_SUBPROJECT_START:
                var text = phpr.nls.get('Attention: sub-project');
                text += ' "' + toChange + '" ';
                text += phpr.nls.get('starts before parent project');
                text += ' "' + current + '"!<br /><br />';
                text += phpr.nls.get('Click "OK" to adjust sub-project to new start date') + '<br />';
                break;
        }

        text += phpr.nls.get('Click "Reset" to reset current project') + '<br />';
        text += phpr.nls.get('Click "x" or "ESC" to do nothing');
        this.showDialog(text, dialogType, nodeToChange, currentNode, posMin, posMax);
    },

    getParentName:function(ownName) {
        // summary:
        //    Estimate parent object name scan in the list for match.
        // description:
        //    Estimate parent object name scan in the list for match.
        var nameTokens = ownName.split('|');
        var parentId   = nameTokens[0].split(':');
        if (parseInt(parentId[1]) != 0) {
            parentId = new String('|own:'+parentId[1]);
            // Serach the name in the list
            var listIndex = this.findArrayIndex(parentId);
            // Return name if found, otherwise false
            return (listIndex > -1) ? this.projectDataBuffer[listIndex][0] : false;
        }
        return false;
    },

    convertIndex2DateString:function(position) {
        // summary:
        //    Calculates date string from numeric day offset
        // description:
        //    Calculates date string from numeric day offset
        var date = new Date(this.MIN_DATE + Math.floor(position * this.DAY_MSEC));
        var year = date.getYear();
        // FF returns 103 for year 2003, IE returns 2003
        if (year < 1900) {
            year += 1900;
        }

        var day = date.getDate();
        if (day < 10) {
            day = '0' + day;
        }
        var month = (date.getMonth()+1);
        if (month < 10) {
            month = '0' + month
        }

        return year + '-' + month + '-' + day;
    },

    processUpdate:function(values, sliderName) {
        // summary:
        //    Initiates conflict check
        // description:
        //    Initiates conflict check
        this.sliderValues = values;
        this.sliderName   = sliderName;
        if (sliderName && this.activeSlider.length > 1) {
            values = this.normalizeValues(values, sliderName);
            this.assertUpdate(values[0], values[1], sliderName);
            if (this.activeSlider == sliderName) {
                dojo.byId('minDate').value = this.convertIndex2DateString(values[0]);
                dojo.byId('maxDate').value = this.convertIndex2DateString(values[1]);
            }
        }
    },

    setRangeSelect:function(new_date, side) {
        // summary:
        //    Assign range slider value from calendar
        // description:
        //    Assign range slider value from calendar
        if (this.activeSlider.length > 1) {
            var current =  this.normalizeValues(dijit.byId(this.activeSlider).get('value'));
            // Min value has array index 0, max := 1
            var index = (side == 'min')? 0 : 1;
            current[index] = this.convertStampToIndex(new_date.getTime());
            this.assertUpdate(current[0], current[1], this.activeSlider);
            dijit.byId(this.activeSlider).set('value', current);

            // Try to find the named project element in the list and store the state
            var listIndex = this.findArrayIndex(this.activeSlider);
            if (listIndex > -1) {
                this.projectDataBuffer[listIndex][4] = 1;
            }
        }
    },

    convertStampToIndex:function(stamp) {
        // summary:
        //    Incoming stamp is unix time stamp in microseconds
        // description:
        //    Incoming stamp is unix time stamp in microseconds
        //    on js side we have a calculation error
        return Math.round((stamp - this.MIN_DATE) / this.DAY_MSEC) + 1;
    },

    setActiveSlider:function(sliderName) {
        // summary:
        //    Buffer the name of the clicked slider and store its current values
        // description:
        //    Buffer the name of the clicked slider and store its current values
        //    in the projects. This function also resetes previous confirmation
        //    dialogs.
        if (sliderName) {
            // Important hack:
            // this function is called once on active slider selection with the mouse.
            // this event also represent the logical begin of the adjustment sequence.
            dojo.fadeIn({
                node:        "gantSelectDates",
                duration:    1000,
                beforeBegin: function() {
                    var pos = dojo.byId('centerMainContent').scrollLeft;
                    dojo.style("gantSelectDates", "margin", "10px " + pos + "px");
                    dojo.style("gantSelectDates", "opacity", 0);
                    dojo.style("gantSelectDates", "display", "block");
                }
            }).play();
            if (this.activeSlider && this.activeSlider == sliderName) {
                return;
            }

            if (dojo.byId(this.activeSlider)) {
                dojo.removeClass(dojo.byId(this.activeSlider), "dijitSliderPhprFocused");
            }
            this.activeSlider = sliderName;
            dojo.addClass(dojo.byId(this.activeSlider), "dijitSliderPhprFocused");

            // Try to find the named project element in the list and store last values
            var listIndex = this.findArrayIndex(sliderName);
            if (listIndex > -1) {
                var values = this.normalizeValues(dijit.byId(this.activeSlider).get('value'));
                this.projectDataBuffer[listIndex][1] = values[0];
                this.projectDataBuffer[listIndex][2] = values[1];
                this.selectActiveTile(this.activeSlider);
            }
        }
    },

    selectActiveTile:function(nodeName) {
        // summary:
        //    Change the color of the current project
        // description:
        //    Put white all the titles except the current one
        var projects = document.getElementById('projectList');
        if (projects) {
            var rows = projects.getElementsByTagName('ul');
            for (i in rows) {
                if (rows[i].id) {
                    var titleElement = rows[i].getElementsByTagName('li')[0];
                    if (rows[i].id == 'lbl_' + nodeName) {
                        titleElement.style.background = '#c0c2c5';
                    } else {
                        titleElement.style.background = '#ffffff';
                    }
                }
            }
        }
    },

    normalizeValues:function(rawData) {
        // summary:
        //    Values inbound might be integer, float or even string,
        //    for comparisons and assignments we need clean integer values
        // description:
        //    Values inbound might be integer, float or even string,
        //    for comparisons and assignments we need clean integer values
        if (rawData && 2 == rawData.length) {
            rawData[0] = Math.floor(1 * rawData[0]);
            rawData[1] = Math.floor(1 * rawData[1])
            return rawData;
        }
        return new Array(0, 0);
    },

    assertUpdate:function(posMin, posMax, nodeName, pDialogCallback) {
        // summary:
        //    Checks current update values for date collisions in dependent projects:
        //    checks are performed against parents and child nodes.
        // description:
        //    Checks current update values for date collisions in dependent projects:
        //    checks are performed against parents and child nodes.
        var ownListIndex = this.findArrayIndex(nodeName);
        if (ownListIndex < 0) {
            return 0;
        }

        // Widening current time line, parent processing req.
        if (this.projectDataBuffer[ownListIndex][1] > posMin || this.projectDataBuffer[ownListIndex][2] < posMax) {

            var parent = this.getParentName(nodeName);

            // Parent exists
            if (parent != false) {
                var parentValues = this.normalizeValues(dijit.byId(parent).get('value'));
                if (posMin < parentValues[0]) {
                    this.processDialog(this.RESIZE_PARENT_START, parent, nodeName, posMin, parentValues[1]);
                }
                if (posMax > parentValues[1]) {
                    this.processDialog(this.RESIZE_PARENT_END, parent, nodeName, parentValues[0], posMax);
                }
            }
        }

        // Narrowing selected time line, child processing req.
        if (this.projectDataBuffer[ownListIndex][1] < posMin || this.projectDataBuffer[ownListIndex][2] > posMax) {

            // Variate id to expected parentid for search
            var owner      = nodeName.split(':');
            owner          = 'p:' + owner[2];
            var listIndex  = -1;
            var listLength = this.projectDataBuffer.length;

            while (++listIndex < listLength) {
                var pipeIndex = this.projectDataBuffer[listIndex][0].lastIndexOf('|');
                if (this.projectDataBuffer[listIndex][0].substring(0, pipeIndex) != owner) {
                    continue;
                }
                var childName   = this.projectDataBuffer[listIndex][0];
                var childValues = this.normalizeValues(dijit.byId(childName).get('value'));
                if (posMax < childValues[1]) {
                    this.processDialog(this.RESIZE_SUBPROJECT_END, childName, nodeName, childValues[0], posMax);
                }
                if (posMin > childValues[0]) {
                    this.processDialog(this.RESIZE_SUBPROJECT_START, childName, nodeName, posMin, childValues[1]);
                }
            }
        }
    },

    acceptUpdate:function(posMin, posMax, nodeName, dialogType) {
        // summary:
        //    Change the value of the node
        // description:
        //    Change the value of the node
        var values = this.normalizeValues(dijit.byId(nodeName).get('value'));
        switch (dialogType) {
            case this.RESIZE_PARENT_END:
            case this.RESIZE_SUBPROJECT_END:
                values[1] = posMax;
                break;
            case this.RESIZE_PARENT_START:
            case this.RESIZE_SUBPROJECT_START:
                values[0] = posMin;
                break;
        }
        this.updateSlider(nodeName, values);
    },

    revertSlider:function(projectId) {
        // summary:
        //    Revert the value of the slider
        // description:
        //    Revert the value of the slider
        var projectIndex = this.findArrayIndex(projectId);
        if (projectIndex > -1) {
            // Set the 'projects' values to the current state
            dijit.byId(projectId).set('value', new Array(this.projectDataBuffer[projectIndex][1],
                                                         this.projectDataBuffer[projectIndex][2]));
            this.projectDataBuffer[projectIndex][4] = 1;
        }
    },

    updateSlider:function(projectId, sliderValues) {
        // summary:
        //    Update the value of the slider
        // description:
        //    Update the value of the slider
        dijit.byId(projectId).set('value', sliderValues);
        var projectIndex = this.findArrayIndex(projectId);
        if (projectIndex > -1) {
            // Set the 'projects' values to the current state
            this.projectDataBuffer[projectIndex][1] = sliderValues[0];
            this.projectDataBuffer[projectIndex][2] = sliderValues[1];
            this.projectDataBuffer[projectIndex][4] = 1;
        }
    }
});
