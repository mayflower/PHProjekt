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

dojo.declare('phpr.Project.GanttBase', null, {
    constructor:function(Stepping, MinDate, MaxDate) {
        // array of project names and its current min/max values
        this.projectDataBuffer = 1;
        // name of the last activated slider
        this.activeSlider = new String();
        // is true if the user confirms the first update
        this.skipConfirmation = false;
        this.STEPPING = 192;
        this.MIN_DATE = 1213187363000;
        this.MAX_DATE = 1229804778000;
        this.DAY_MSEC = 86400000;
    },

    findArrayIndex:function(sliderName) {
        // summary:
        //    walks the projectDataBuffer array, returns array index on name match
        // description:
        //    walks the projectDataBuffer array, returns array index on name match
        var pipeIndexSearch = sliderName.lastIndexOf('|');

        // try to find the named project element in the list and store last values
        var listIndex = this.projectDataBuffer.length;
        while(--listIndex > -1 ) {
            // hash element given ()is not the case while initialization phase
            if(!this.projectDataBuffer[listIndex][0]) {
                return -1;
            }
            var pipeIndexBuffer = this.projectDataBuffer[listIndex][0].lastIndexOf('|');
            if(this.projectDataBuffer[listIndex][0].substring(pipeIndexBuffer) !=
                sliderName.substring(pipeIndexSearch)) {
                continue;
            }
            return listIndex;
        }
        return listIndex;
    },

    getProjectCaption:function(nodeName) {
        // summary:
        //    implicates for existance of DOM element lbl_*projectid*
        //    which contains project textual name
        // description:
        //    implicates for existance of DOM element lbl_*projectid*
        //    which contains project textual name
        var element = document.getElementById('lbl_'+ nodeName);
        if(element) {
            var caption = element.getElementsByTagName('a')[0].getElementsByTagName('strong')[0].innerHTML;
            return caption.substr(caption.lastIndexOf('.')+1);
        }
        return nodeName;
    },

    showDialog:function(pText, pChildName, pPosMin, pPosMax) {
        // summary:
        //    Shows the dialog box to alert a conflict
        // description:
        //    Shows the dialog box to alert a conflict
        var element       = dijit.byId('ganttDialog');
        this.callbackOpts = new Array(pPosMin,pPosMax,pChildName);
        dojo.byId('message_text').innerHTML = pText;
        element.show();
    },

    dialogCallback:function(pValues) {
        // summary:
        //    The callback function is executed by the dialog box continue the onChange event
        // description:
        //    The callback function is executed by the dialog box continue the onChange event
        this.assertUpdate(this.callbackOpts[0],this.callbackOpts[1], this.callbackOpts[2], true);
    },

    processDialog:function (dialogType, parentName, childName, posMin, posMax) {
        // summary:
        //    This function delivers the error text and executes showDialog()
        // description:
        //    This function delivers the error text and executes showDialog()
        switch(dialogType) {
            case 0:
                this.showDialog('Attention: parent project "' + this.getProjectCaption(parentName) + '"' +
                               ' starts after subproject "' + this.getProjectCaption(childName) + '"!<br />' +
                               'Click "OK" to adjust parent project to new start date<br />' +
                               'Click "x" to reset current project', childName, posMin, posMax);
            case 1:
                this.showDialog('Attention: parent project "'+this.getProjectCaption(parentName)+ '"' +
                               ' ends before subproject "'+this.getProjectCaption(childName)+'"!<br />' +
                               'Click "OK" to adjust parent project to new end date<br />' +
                               'Click "x" to reset current project', childName, posMin, posMax);
            case 2:
                this.showDialog('Attention: subproject "' + this.getProjectCaption(parentName) + '"' +
                               ' ends after parent project "' + this.getProjectCaption(childName) + '"!<br />' +
                               'Click "OK" to adjust subproject to new end date<br />' +
                               'Click "x" to reset current project', childName, posMin, posMax);
            case 3:
                this.showDialog('Attention: subproject "'+this.getProjectCaption(parentName) + '"' +
                               ' starts before parent project "'+this.getProjectCaption(childName)+'"!<br />' +
                               'Click "OK" to adjust subproject to new start date<br />' +
                               'Click "x" to reset current project', childName, posMin, posMax);
            return false;
        }
    },

    getParentName:function(ownName) {
        // summary:
        //    estimate parent object name scan in the list for match.
        // description:
        //    estimate parent object name scan in the list for match.
        var nameTokens = ownName.split('|');
        var parentId   = nameTokens[0].split(':');
        if(parseInt(parentId[1]) != 0) {
            parentId = new String('|own:'+parentId[1]);
            // serach the name in the list
            var listIndex = this.findArrayIndex(parentId);
            // return name if found, otherwise false
            return (listIndex > -1) ? this.projectDataBuffer[listIndex][0] : false;
        }
        return false;
    },

    convertIndex2DateString:function(position) {
        // summary:
        //    calculates date string from numeric day offset
        // description:
        //    calculates date string from numeric day offset
        var date = new Date(this.MIN_DATE + Math.floor(position * this.DAY_MSEC));
        var year = date.getYear();
        // FF returns 103 for year 2003, IE returns 2003
        if(year < 1900) {
            year += 1900;
        }

        var day = date.getDate();
        if (day < 10) {
            day = '0'+day;
        }
        var month = (date.getMonth()+1);
        if (month < 10) {
            month = '0'+month
        }
        return year + '-' + month + '-' + day;
    },

    processUpdate:function(values, sliderName) {
        // summary:
        //    initiates conflict check
        // description:
        //    initiates conflict check
        this.sliderValues = values;
        this.sliderName   = sliderName;
        if(sliderName && this.activeSlider.length > 1) {
            values = this.normalizeValues(values, sliderName);
            this.assertUpdate(values[0], values[1], sliderName);
            dojo.byId('minDate').value = this.convertIndex2DateString(values[0]);
            dojo.byId('maxDate').value = this.convertIndex2DateString(values[1]);
        }
    },

    setRangeSelect:function(new_date, side) {
        // summary:
        //    assign range slider value from calendar
        // description:
        //    assign range slider value from calendar
        if(this.activeSlider.length > 1) {
            var current =  this.normalizeValues(dijit.byId(this.activeSlider).attr('value'));
            // min value has array index 0, max := 1
            var index = (side == 'min')? 0 : 1;
            current[index] = this.convertStampToIndex(new_date.getTime());
            this.assertUpdate(current[0], current[1], this.activeSlider);
            dijit.byId(this.activeSlider).attr('value', current);
        }
    },

    convertStampToIndex:function (stamp) {
        // summary:
        //    incoming stamp is unix time stamp in microseconds
        // description:
        //    incoming stamp is unix time stamp in microseconds
        //    on js side we have a calculation error
        return 1 + Math.floor((stamp - this.MIN_DATE) / this.DAY_MSEC);
    },

    setActiveSlider:function(sliderName) {
        // summary:
        //    buffer the name of the clicked slider and store its current values
        // description:
        //    buffer the name of the clicked slider and store its current values
        //    in the projects. This function also resetes previous confirmation
        //    dialogs.
        if(sliderName) {
            this.skipConfirmation = false;
            /* important hack:
              this function is called once on active slider selection with the mouse.
              this event also represent the logical begin of the adjustment sequence.
              by the sequence begin we reset the previous skipConfirmation value to false.
              */
            if(this.activeSlider && this.activeSlider == sliderName) {
                return;
            }
            this.activeSlider = sliderName;
            //now, move focus away to trig this event again as soon as user moves a slider
            document.getElementById('projectList').focus();

            // try to find the named project element in the list and store last values
            var listIndex = this.findArrayIndex(sliderName);
            if(listIndex > -1) {
                var values = this.normalizeValues(dijit.byId(this.activeSlider).attr('value'));
                this.projectDataBuffer[listIndex][1] = values[0];
                this.projectDataBuffer[listIndex][2] = values[1];
            }
        }
    },

    normalizeValues:function(rawData) {
        // summary:
        //    values inbound might be integer, float or even string,
        //    for comparisons and assignments we need clean integer values
        // description:
        //    values inbound might be integer, float or even string,
        //    for comparisons and assignments we need clean integer values
        if(rawData && 2 == rawData.length) {
            rawData[0] = Math.floor(1 * rawData[0]);
            rawData[1] = Math.floor(1 * rawData[1])
            return rawData;
        }
        return new Array(0, 0);
    },

    assertUpdate:function(posMin, posMax, nodeName, pDialogCallback) {
        // summary:
        //    checks current update values for date collisions in dependent projects:
        //    checks are performed against parents and child nodes. The _recursion_ is
        //    triggered by the update event of the adjusted slider
        // description:
        //    checks current update values for date collisions in dependent projects:
        //    checks are performed against parents and child nodes. The _recursion_ is
        //    triggered by the update event of the adjusted slider
        var ownListIndex     = this.findArrayIndex(nodeName);
        if(ownListIndex < 0) {
            return 0;
        }
        this.skipConfirmation= pDialogCallback;

        var dependencyUpdate  = false;
        var projectReverse    = false;
        // widening current time line, parent processing req.
        if(this.projectDataBuffer[ownListIndex][1] > posMin || this.projectDataBuffer[ownListIndex][2] < posMax) {
            var parent = this.getParentName(nodeName);

            // parent exists
            if(parent != false) {
                var parentValues = this.normalizeValues(dijit.byId(parent).attr('value'));
                if(posMin < parentValues[0]) {
                    this.skipConfirmation = (this.skipConfirmation==true) ? this.skipConfirmation : this.processDialog(0, parent, nodeName, posMin, posMax);
                    if(this.skipConfirmation) {
                        parentValues[0] = posMin;
                    }
                    else {
                     //   projectReverse = true;
                    }
                    dependencyUpdate = true;
                }
                if(posMax > parentValues[1]) {
                    this.skipConfirmation = (this.skipConfirmation==true) ? this.skipConfirmation : this.processDialog(1, parent, nodeName, posMin, posMax);
                    if(this.skipConfirmation) {
                        parentValues[1] = posMax;
                    }
                    else {
                      //  projectReverse = true;
                    }
                    dependencyUpdate = true;
                }
                if(this.projectReverse) {
                  this.revertSlider(projectReverse, ownListIndex, nodeName);
                  return;
                }
                this.updateSlider(dependencyUpdate, parent, parentValues);
            }
        }

        // narrowing selected time line, child processing req.
        if(this.projectDataBuffer[ownListIndex][1] < posMin || this.projectDataBuffer[ownListIndex][2] > posMax) {
            // variate id to expected parentid for search
            var owner      = nodeName.split(':');
            owner          = 'p:' + owner[2];
            var listIndex  = -1;
            var listLength = this.projectDataBuffer.length;

            while(++listIndex < listLength) {
                dependencyUpdate = false;
                projectReverse   = false;
                var pipeIndex = this.projectDataBuffer[listIndex][0].lastIndexOf('|');
                if(this.projectDataBuffer[listIndex][0].substring(0, pipeIndex) != owner) {
                    continue;
                }
                var childName   = this.projectDataBuffer[listIndex][0];
                var childValues = this.normalizeValues(dijit.byId(childName).attr('value'));
                if(posMax < childValues[1]) {
                    this.skipConfirmation = (this.skipConfirmation==true) ? this.skipConfirmation : this.processDialog(2, childName, nodeName, posMin, posMax);
                    if(this.skipConfirmation) {
                        childValues[1] = posMax;
                        dependencyUpdate = true;
                    }
                    else {
                    //    projectReverse = true;
                    }
                }
                if(posMin > childValues[0]) {
                    this.skipConfirmation = (this.skipConfirmation==true) ? this.skipConfirmation : this.processDialog(3, childName, nodeName, posMin, posMax);
                    if(this.skipConfirmation) {
                        childValues[0] = posMin;
                        dependencyUpdate = true;
                    }
                    else {
                      //  projectReverse = true;
                    }
                }

               if(this.projectReverse) {
                    this.revertSlider(projectReverse, ownListIndex, nodeName);
                    return;
                }
                this.updateSlider(dependencyUpdate, childName, childValues);
            }
        }
    },

    revertSlider:function(shouldReverse, projectIndex, projectId) {
        // summary:
        //    Revert the value of the slider
        // description:
        //    Revert the value of the slider
        if(shouldReverse) {
            dijit.byId(projectId).attr('value', new Array(this.projectDataBuffer[projectIndex][1],
                                                     this.projectDataBuffer[projectIndex][2]));
        }
    },

    updateSlider:function(shouldUpdate, projectId, sliderValues) {
        // summary:
        //    Update the value of the slider
        // description:
        //    Update the value of the slider
        if(shouldUpdate) {
            dijit.byId(projectId).attr('value', sliderValues);
            var projectIndex = this.findArrayIndex(projectId);
            if(projectIndex > -1) {
                // set the 'projects' values to the current state
                this.projectDataBuffer[projectIndex][1] = sliderValues[0];
                this.projectDataBuffer[projectIndex][2] = sliderValues[1];
            }
        }
    }
});
