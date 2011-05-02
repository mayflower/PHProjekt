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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Gantt.Main");

dojo.declare("phpr.Gantt.Main", phpr.Default.Main, {
    DAY_MSEC:                86400000,
    MIN_DATE:                1213187363000,
    MAX_DATE:                1229804778000,
    STEPPING:                192,
    RESIZE_PARENT_START:     0,
    RESIZE_PARENT_END:       1,
    RESIZE_SUBPROJECT_END:   2,
    RESIZE_SUBPROJECT_START: 3,
    _activeDialog:           false,
    _activeSlider:           null,
    _url:                    null,
    _scale:                  1.8,
    _visible:                0,
    _width:                  0,
    _projects:               [],

    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Gantt';

        this._loadFunctions();
        dojo.subscribe('Gantt.setActiveSlider', this, 'setActiveSlider');
        dojo.subscribe('Gantt.processUpdate', this, 'processUpdate');

        this._gridWidget = phpr.Gantt.Grid;
        this._formWidget = phpr.Gantt.Form;
    },

    setWidgets:function() {
        // Summary:
        //    Set and start the widgets of the module.
        phpr.Tree.loadTree();
        this._url = phpr.webpath + 'index.php/Gantt/index/jsonGetProjects/nodeId/' + phpr.currentProjectId;
        phpr.DataStore.addStore({'url': this._url, 'noCache': true});
        phpr.DataStore.requestData({'url': this._url, 'processData': dojo.hitch(this, '_getGanttData')});
    },

    setActiveSlider:function(sliderId) {
        // Summary:
        //    Buffer the name of the clicked slider and store its current values.
        // Description:
        //    Also show the dates widgets with the current date values and
        //    mark as selected the project name.
        if (sliderId) {
            var id = sliderId.substr('horizontalRangeSlider_'.length);
            // Important hack:
            // this function is called once on active slider selection with the mouse.
            // this event also represent the logical begin of the adjustment sequence.
            dojo.fadeIn({
                node:        'selectDates-Gantt',
                duration:    1000,
                beforeBegin: function() {
                    var pos = dojo.byId('centerMainContent').scrollLeft;
                    dojo.style('selectDates-Gantt', 'margin', '10px ' + pos + 'px');
                    dojo.style('selectDates-Gantt', 'opacity', 0);
                    dojo.style('selectDates-Gantt', 'display', 'block');
                }
            }).play();
            if (this._activeSlider && this._activeSlider == sliderId) {
                return;
            }

            if (dojo.byId(this._activeSlider)) {
                dojo.removeClass(dojo.byId(this._activeSlider), 'dijitSliderPhprFocused');
            }
            this._activeSlider = sliderId;
            dojo.addClass(dojo.byId(this._activeSlider), 'dijitSliderPhprFocused');

            // Try to find the named project element in the list and store last values
            var values                   = this._normalizeValues(dijit.byId(this._activeSlider).get('value'));
            this._projects[id].oldValues = {
                start: this._projects[id].values.start,
                end:   this._projects[id].values.end
            };
            this._projects[id].values  = {start: values[0], end: values[1]};
            this._projects[id].updated = true;
            this._selectActiveTile(id);
        }
    },

    processUpdate:function(values, sliderName) {
        // summary:
        //    Initiates conflict check.
        if (!this._activeSlider) {
            this.setActiveSlider(sliderName);
        }
        if (sliderName) {
            var id                       = sliderName.substr('horizontalRangeSlider_'.length);
            values                       = this._normalizeValues(values);
            this._projects[id].oldValues = {
                start: this._projects[id].values.start,
                end:   this._projects[id].values.end
            };
            this._projects[id].values  = {start: values[0], end: values[1]};
            this._projects[id].updated = true;

            this._assertUpdate(values[0], values[1], id);
            if (this._activeSlider == sliderName) {
                dojo.byId('minDate-Gantt').value = this._convertIndex2DateString(values[0]);
                dojo.byId('maxDate-Gantt').value = this._convertIndex2DateString(values[1]);
            }
        }
    },

    openForm:function(id, module) {
        // Summary:
        //     Open a new form.
        // Description:
        //     Disable for this module.
    },

    /************* Private functions *************/

    _renderTemplate:function() {
        // Summary:
        //    Render the module layout only one time.
        if (!dojo.byId('defaultMainContent-' + phpr.module)) {
            phpr.Render.render(['phpr.Gantt.template', 'mainContent.html'], dojo.byId('centerMainContent'), {
                selectedProjectTimelineText: phpr.nls.get('Selected Project Timeline'),
                projectPeriodHelp:           phpr.nls.get('Click on a Project timeline and see and/or change here the '
                    + 'Start and End dates.'),
                saveText: phpr.nls.get('Save')
            });
            // Action buttons for the form
            dojo.connect(dijit.byId('submitButton-Gantt'), 'onClick', dojo.hitch(this, '_submitForm'));
        } else {
            dojo.place('defaultMainContent-' + phpr.module, 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContent-' + phpr.module), 'display', 'block');
        }
    },

    _setNewEntry:function() {
        // Summary:
        //    Create the Add button.
        // Description:
        //    Disable for this module.
    },

    _getGanttData:function() {
        // Summary:
        //    Get all the data and render all the views.

        // Destroy the old items
        this._removeAllChilds();

        var data = phpr.DataStore.getData({url: this._url});

        // Assign global constants required for calculations with boundaries provided by data provider
        this.STEPPING = data['step'];

        // Convert second in mseconds (JS supported format)
        this.MIN_DATE = 1000 * data['min'];
        this.MAX_DATE = 1000 * data['max'];

        // Set the project data
        if ((phpr.currentProjectId == 1 && data['projects'] && data['projects'].length > 0) ||
            (phpr.currentProjectId != 1 && data['projects'] && data['projects'].length > 1)) {

            // Hide empty div and show the view
            dojo.byId('container-Gantt').style.display    = 'block';
            dojo.byId('emptyResults-Gantt').style.display = 'none';

            // Set timeline
            this._width = this._setTimeline();

            // Collect the projects information
            for (var j in data['projects']) {
                var id             = data['projects'][j].id;
                this._projects[id] = data['projects'][j];

                if (this._projects[id].caption.length > 25) {
                    this._projects[id].caption = this._projects[id].caption.substr(0, 25) + '...';
                }

                this._projects[id].values = {
                    start: this._convertStampToIndex(1000 * this._projects[id].start),
                    end:   this._convertStampToIndex(1000 * this._projects[id].end)
                };
                this._projects[id].oldValues = {
                    start: this._projects[id].values.start,
                    end:   this._projects[id].values.end
                };
                this._projects[id].access  = data['rights']['currentUser'][id] || false;
                this._projects[id].updated = false;
            }

            // Show the main projects
            this._visible = 0;
            this._showProjectsUnder(phpr.currentProjectId);

            // Insert two date widgets dynamically
            this._installCalendars();

            // Set the toggle button
            this._setToggle();

            // Set the height
            this._setHeight();
        } else {
            // Hide view and show the empty div
            if (dojo.byId('emptyResults-Gantt').innerHTML == "") {
                dojo.byId('emptyResults-Gantt').innerHTML = phpr.drawEmptyMessage('There are no valid projects');
            }
            dojo.byId('defaultMainContent-Gantt').style.width = '95%';
            dojo.byId('container-Gantt').style.display    = 'none';
            dojo.byId('emptyResults-Gantt').style.display = 'inline';
        }

        // Action buttons for the form
        if (data['rights']['currentUser']['write']) {
            dojo.byId('bottomContent-Gantt').style.display = 'inline';
        } else {
            dojo.byId('bottomContent-Gantt').style.display = 'none';
        }
    },

    _removeAllChilds:function() {
        // Summary:
        //    Destroy all the old nodes.
        dojo.query('.sub_project', dojo.byId('projectList-Gantt')).forEach(function(ul) {
            var ulId = ul.id.substr(4);
            dijit.byId('horizontalRangeSlider_' + ulId).destroyRecursive();
            dojo.destroy(ul);
        });
    },

    _setTimeline:function() {
        // Summary:
        //    Creates the timeline on top of the gantt.
        // Description:
        //    The months are saved in an array and should be loaded in the selected language.
        //    The time spread is dynamically created by the given time scope.
        var element   = dijit.byId('timelineDates-Gantt');
        var startDate = new Date(this.MIN_DATE);
        var endDate   = new Date(this.MAX_DATE);
        var height    = 45;

        // Ul for months/year
        var row       = document.createElement('ul');
        row.className = 'sub_project';

        // Space
        var space1       = document.createElement('li');
        space1.innerHTML = '&nbsp;';
        dojo.style(space1, {borderLeft: 'none', borderRight: 'none', width: '250px', 'float': 'left'});
        row.appendChild(space1);

        // Space
        var space2       = document.createElement('li');
        space2.innerHTML = '&nbsp;';
        dojo.style(space2, {width: '12px', 'float': 'left'});
        row.appendChild(space2);

        // Slider that contain the months
        var slider       = document.createElement('li');
        slider.className = 'slider';
        dojo.style(slider, {'float': 'left', marginTop: '0px', left: '0px'});
        row.appendChild(slider);

        // ul for the months
        var dates = document.createElement('ul');
        dojo.style(dates, {marginTop: '0px', width: '100%', left: '0px'});
        slider.appendChild(dates);

        // Separating line
        var line       = document.createElement('li');
        line.className = 'splitter';
        dojo.style(line, {'float': 'left', width: '1px', height: height + 'px', borderLeft: '1px dotted #3d3d3d',
            marginLeft: '-1px'});
        dates.appendChild(line);

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

        var maxWidth = 268 + (365 * 2 * years);

        // Change the width to the maxWidth
        dojo.style(dojo.byId('defaultMainContent-Gantt'), 'width', maxWidth + 'px');

        // Draw the timeline with the correct scale
        var totalWidth = 0;
        for (var i = 0 ; true ; i++) {
            startDate = dojo.date.add(startDate, 'month', 1);
            var check = dojo.date.compare(startDate, endDate);
            if (check == 1) {
                break;
            }
            var year   = startDate.getFullYear().toString().substr(2,2);
            var width  = Math.round(dojo.date.getDaysInMonth(startDate) * this._scale);
            totalWidth = totalWidth + width;

            if (i > 11) {
                i = 0;
            }

            var monthNumber = (i < 9) ? '0' + Math.round(i+1) : Math.round(i+1);
            var monthString = monthNumber + '.' + year;

            // Month string
            var month       = document.createElement('li');
            month.innerHTML = monthString;
            dojo.style(month, {'float': 'left', width: width + 'px'});
            dates.appendChild(month);

            // Separating line
            var line       = document.createElement('li');
            line.className = 'splitter';
            dojo.style(line, {'float': 'left', width: '1px', height: height + 'px', borderLeft: '1px dotted #3d3d3d',
                marginLeft: '-2px'});
            dates.appendChild(line);
        }

        // Remove old content and replace it with the new one
        element.set('content', row);

        return totalWidth;
    },

    _convertStampToIndex:function(stamp) {
        // Summary:
        //    Convert unix time stamp in microseconds to position number.
        return Math.round((stamp - this.MIN_DATE) / this.DAY_MSEC) + 1;
    },

    _showProjectsUnder:function(parentId) {
        // Summary:
        //    Create and show the projecs under parentId.
        for (var j in this._projects) {
            if (this._projects[j].parent == parentId) {
                // Create the slider row
                var row       = document.createElement('ul');
                row.className = 'sub_project';
                row.id        = 'lbl_' + this._projects[j].id;
                dojo.style(row, {height: '24px', display: 'inline'});

                // Expand and label
                var title       = document.createElement('li');
                title.className = 'title';
                row.appendChild(title);
                var div = document.createElement('div');
                dojo.style(div, {width: '100%', left: '0px', top: '0px'});
                title.appendChild(div);
                var img = document.createElement('div');
                dojo.style(img, {display: 'inline'});
                img.innerHTML = '<img src="' + phpr.webpath + 'img/spacer.gif" height="1" width="'
                    + this._projects[j].level + '" />';
                div.appendChild(img);
                var expander       = document.createElement('div');
                expander.className = 'expander';
                dojo.style(expander, {display: 'inline', width: '15px'});
                div.appendChild(expander);
                var link = document.createElement('div');
                dojo.style(link, {display: 'inline'});
                div.appendChild(link);
                var a = document.createElement('a');
                a.setAttribute('href', 'javascript:void(0)');
                a.style.textDecoration = 'none';
                a.innerHTML            = '<strong>' + this._projects[j].caption + '</strong>';
                link.appendChild(a);

                // Separator
                var separator       = document.createElement('li');
                separator.className = 'separator';
                separator.innerHTML = '&nbsp;';
                row.appendChild(separator);

                // Slider
                var slider       = document.createElement('li');
                slider.className = 'slider';
                row.appendChild(slider);
                var bar = new phpr.Form.HorizontalRangeSlider({
                    id:             'horizontalRangeSlider_' + this._projects[j].id,
                    secondary:      33,
                    name:           'horizontalRangeSlider_' + this._projects[j].id,
                    clickSelect:    false,
                    minimum:        1,
                    maximum:        this.STEPPING,
                    value:          [this._projects[j].values.start, this._projects[j].values.end],
                    discreteValues: this.STEPPING,
                    style:          'margin-top: 0px; width: ' + (this._width + 4) + 'px;',
                    disabled:       (!this._projects[j].access) ? true : false
                });
                slider.appendChild(bar.domNode);

                // Add the project bar
                if (dojo.byId('lbl_' + this._projects[j].parent)) {
                    dojo.place(row, 'lbl_' + this._projects[j].parent, 'after');
                } else {
                    dojo.byId('projectList-Gantt').appendChild(row);
                }

                this._visible++;
            }
        }
    },

    _installCalendars:function() {
        // Summary:
        //    Render the calendar widgets.
        // the project begin date widget (calendar view)
        if (!dojo.byId('minDate-Gantt')) {
            new phpr.Form.DateTextBox({
                name:          'minDate-Gantt',
                id:            'minDate-Gantt',
                constraints:   {datePattern: 'yyyy-MM-dd', strict: true},
                promptMessage: 'yyyy-MM-dd',
                style:         'width:150px;',
                required:      true,
                onChange:      dojo.hitch(this, '_setRangeSelect', 'min')
            }, dojo.byId('TgtMin-Gantt'));
        }

        // The project end date widget (calendar view)
        if (!dojo.byId('maxDate-Gantt')) {
            new phpr.Form.DateTextBox({
                name:          'maxDate-Gantt',
                id:            'maxDate-Gantt',
                constraints:   {datePattern: 'yyyy-MM-dd', strict: true},
                promptMessage: 'yyyy-MM-dd',
                style:         'width:150px;',
                required:      true,
                onChange:      dojo.hitch(this, '_setRangeSelect', 'max')
            }, dojo.byId('TgtMax-Gantt'));
        }
    },

    _setToggle:function() {
        // Summary:
        //    Assigns the function to toggle the lines.
        // Description:
        //    The assignment is not flexible due to the 'ProjectChart' instance.
        var self = this;
        dojo.query('.sub_project', dojo.byId('projectList-Gantt')).forEach(function(element) {
            var a  = element.getElementsByTagName('a')[0];
            var id = a.parentNode.parentNode.parentNode.parentNode.id.substr(4);
            if (self._projects[id].childs > 0) {
                if (a.innerHTML.substr(0, 3) != '[+]' && a.innerHTML.substr(0, 3) != '[-]') {
                    dojo.connect(a, 'onclick', dojo.hitch(self, '_toggle', id));
                    a.innerHTML = '[+] ' + a.innerHTML;
                }
            }
        });
        self = null;
    },

    _toggle:function(id) {
        // Summary:
        //    Triggerd by the a-element to toggle the childs.
        var myHandler = this._switchController(dojo.byId('lbl_' + id).getElementsByTagName('a')[0]);
        if (myHandler) {
            // Add childs
            this._showProjectsUnder(id);
            this._setToggle();
        } else {
            // Destroy childs
            this._removeChilds(id);
        }
        this._setHeight();
    },

    _removeChilds:function(id) {
        // Summary:
        //    Destroy all the childs of the id node.
        var self  = this;
        var nodes = [];
        dojo.query('.sub_project', dojo.byId('projectList-Gantt')).forEach(function(ul) {
            var ulId     = ul.id.substr(4);
            var parentId = self._projects[ulId].parent;
            if (parentId == id) {
                // Remove node
                self._visible--;
                dijit.byId('horizontalRangeSlider_' + ulId).destroyRecursive();
                dojo.destroy(ul);
                nodes.push(ulId);
            }
        });
        // Remove the childs too
        dojo.forEach(nodes, function(childId) {
            self._removeChilds(childId);
        });
        self = null;
    },

    _switchController:function(element, close) {
        // Summary:
        //    Checks whether the element is expanded or not to replace the +/-.
        if (element.innerHTML.indexOf('+') > 0 || close == false) {
            element.innerHTML = element.innerHTML.replace('[+]', '[-]');
            return true;
        } else {
            element.innerHTML = element.innerHTML.replace('[-]', '[+]');
            return false;
        }
    },

    _setHeight:function() {
        // Summary:
        //    Sets the height of the vertical lines.
        var height = this._getProjectsHeight();
        dojo.query('#timelineDates-Gantt .slider .splitter').forEach(function(ele) {
            dojo.style(ele, 'height', (height) + 'px');
        });
        // Set the position of the button too
        dojo.byId('bottomContent-Gantt').style.top = (height + 180) + 'px';
    },

    _getProjectsHeight:function() {
        // Summary:
        //    Return the height depends on the number of visible projects.
        var count = this._visible
        if (count < 0) {
            count = 1;
        }

        var height = (dojo.isIE) ? 29 : 24;

        return (45 + (count * height));
    },

    _normalizeValues:function(rawData) {
        // Summary:
        //    Values inbound might be integer, float or even string,
        //    for comparisons and assignments we need clean integer values.
        if (rawData && 2 == rawData.length) {
            return [Math.floor(1 * rawData[0]), Math.floor(1 * rawData[1])];
        }
        return new [0, 0]
    },

    _selectActiveTile:function(sliderId) {
        // Summary:
        //    Change the color of the current project.
        // Description:
        //    Put white all the titles except the current one
        dojo.query('.sub_project', dojo.byId('projectList-Gantt')).forEach(function(ul) {
            var titleElement = ul.getElementsByTagName('li')[0];
            if (ul.id == 'lbl_' + sliderId) {
                titleElement.style.background = '#c0c2c5';
            } else {
                titleElement.style.background = '#ffffff';
            }
        });
    },

    _convertIndex2DateString:function(position) {
        // Summary:
        //    Calculates date string from numeric day offset.
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

    _assertUpdate:function(posMin, posMax, indexId, pDialogCallback) {
        // Summary:
        //    Checks current update values for date collisions in dependent projects:
        //    checks are performed against parents and child nodes.
        var parent        = this._projects[indexId].parent;
        var dialogsToShow = [];

        // Parent
        if (this._projects[parent]) {
            var parentValues = this._projects[parent].values;
            if (posMin < parentValues.start) {
                dialogsToShow.push([this.RESIZE_PARENT_START, parent, indexId, posMin, parentValues.start]);
            }
            if (posMax > parentValues.end) {
                dialogsToShow.push([this.RESIZE_PARENT_END, parent, indexId, parentValues.end, posMax]);
            }
        }

        // Childs
        for (var j in this._projects) {
            if (this._projects[j].parent == indexId) {
                var childValues = this._projects[j].values;
                if (posMax < childValues.end) {
                    dialogsToShow.push([this.RESIZE_SUBPROJECT_END, j, indexId, childValues.start, posMax]);
                }
                if (posMin > childValues.start) {
                    dialogsToShow.push([this.RESIZE_SUBPROJECT_START, j, indexId, posMin, childValues.end]);
                }
            }
        }

        this._runDialogs(dialogsToShow);
    },

    _runDialogs:function(dialogsToShow) {
        // Summary:
        //    Show a dialog and wait until the user answer it.
        //    After that, show other dialog if there is any.
        if (dialogsToShow.length > 0) {
            if (this._activeDialog == false) {
                this._activeDialog = true;
                var dialogData     = dialogsToShow.pop();
                this._processDialog(dialogData[0], dialogData[1], dialogData[2], dialogData[3], dialogData[4]);
            }
            setTimeout(dojo.hitch(this, '_runDialogs', dialogsToShow), 1000);
        }
    },

    _processDialog:function(dialogType, nodeIdToChange, currentNodeId, posMin, posMax) {
        // Summary:
        //    Delivers the error text and executes showDialog().
        if (!this._projects[nodeIdToChange].access) {
            return;
        }

        var toChange = this._projects[nodeIdToChange].caption;
        var current  = this._projects[currentNodeId].caption;

        switch(dialogType) {
            case this.RESIZE_PARENT_START:
                var text = phpr.nls.get('Attention: parent project')
                    + ' "' + toChange + '" '
                    + phpr.nls.get('starts after sub-project')
                    + ' "' + current + '"!<br /><br />'
                    + phpr.nls.get('Click "OK" to adjust parent project to new start date') + '<br />';
                break;
            case this.RESIZE_PARENT_END:
                var text = phpr.nls.get('Attention: parent project')
                    + ' "' + toChange + '" '
                    + phpr.nls.get('ends before sub-project')
                    + ' "' + current + '"!<br /><br />'
                    + phpr.nls.get('Click "OK" to adjust parent project to new end date') + '<br />'
                break;
            case this.RESIZE_SUBPROJECT_END:
                var text = phpr.nls.get('Attention: sub-project')
                    + ' "' + toChange + '" '
                    + phpr.nls.get('ends after parent project')
                    + ' "' + current + '"!<br /><br />'
                    + phpr.nls.get('Click "OK" to adjust sub-project to new end date') + '<br />'
                break;
            case this.RESIZE_SUBPROJECT_START:
                var text = phpr.nls.get('Attention: sub-project')
                    + ' "' + toChange + '" '
                    + phpr.nls.get('starts before parent project')
                    + ' "' + current + '"!<br /><br />'
                    + phpr.nls.get('Click "OK" to adjust sub-project to new start date') + '<br />'
                break;
        }

        text += phpr.nls.get('Click "Reset" to reset current project') + '<br />';
        text += phpr.nls.get('Click "x" or "ESC" to do nothing');

        this._showDialog(text, dialogType, nodeIdToChange, currentNodeId, posMin, posMax);
    },

    _showDialog:function(text, dialogType, nodeIdToChange, currentNodeId, posMin, posMax) {
        // Summary:
        //    Shows the dialog box to alert a conflict.
        var dialog = dijit.byId('errorDialog-Gantt');
        if (!dialog) {
            var content = phpr.Render.render(['phpr.Gantt.template', 'dialog.html'], null, {
                text:          text,
                ok:            phpr.nls.get('OK'),
                reset:         phpr.nls.get('Reset')
            });

            var dialog = new dijit.Dialog({
                id:        'errorDialog-Gantt',
                title:     phpr.nls.get('Warning'),
                draggable: false,
                content:   content
            });

            // Connect button
            dojo.connect(dojo.byId('buttonDialog-Gantt'), 'onclick', dojo.hitch(this, function() {
                dialog.hide();
                this._revertSlider(dojo.byId('hiddenDialogId-Gantt').value);
            }));
            // On Hide, process the next dialog
            dojo.connect(dialog, 'hide',  dojo.hitch(this, function() {
                this._activeDialog = false;
            }));
            dojo.disconnect(dialog, 'onExecute');
        } else {
            dojo.byId('contentForDialog-Gantt').innerHTML = text;
        }
        dojo.byId('hiddenDialogId-Gantt').value = currentNodeId;
        dialog.execute = dojo.hitch(this, '_dialogCallback', posMin, posMax, nodeIdToChange, dialogType);
        dialog.show();
    },

    _dialogCallback:function(posMin, posMax, nodeIdToChange, dialogType) {
        // Summary:
        //    The callback function is executed by the dialog box continue the onChange event.
        //    Change the value of the node.
        var values = this._projects[nodeIdToChange].values;
        switch (dialogType) {
            case this.RESIZE_PARENT_END:
            case this.RESIZE_SUBPROJECT_END:
                values.end = posMax;
                break;
            case this.RESIZE_PARENT_START:
            case this.RESIZE_SUBPROJECT_START:
                values.start = posMin;
                break;
        }
        this._updateSlider(nodeIdToChange, values);

        // Finish this dialog
        this._activeDialog = false;
    },

    _updateSlider:function(projectId, sliderValues) {
        // Summary:
        //    Update the value of the slider.
        // Update the widget
        if (dijit.byId('horizontalRangeSlider_' + projectId)) {
            dijit.byId('horizontalRangeSlider_' + projectId).set('value', [sliderValues.start, sliderValues.end]);
        }
        // Update the values in the array
        this._projects[projectId].oldValues = {
            start: this._projects[projectId].values.start,
            end:   this._projects[projectId].values.end
        };
        this._projects[projectId].values  = sliderValues;
        this._projects[projectId].updated = true;
    },

    _revertSlider:function(projectId) {
        // Summary:
        //    Revert the value of the slider.
        var sliderValues = this._projects[projectId].oldValues;
        if (dijit.byId('horizontalRangeSlider_' + projectId)) {
            dijit.byId('horizontalRangeSlider_' + projectId).set('value', [sliderValues.start, sliderValues.end]);
        }
        // Update the values in the array
        this._projects[projectId].values = {
            start: this._projects[projectId].oldValues.start,
            end:   this._projects[projectId].oldValues.end
        };
        this._projects[projectId].updated = true;
    },

    _setRangeSelect:function(side) {
        // Summary:
        //    Assign range slider value from calendar.
        if (this._activeSlider && this._activeSlider.length > 1) {
            var dateId  = (side == 'min') ? 'minDate-Gantt' : 'maxDate-Gantt';
            var newDate = dijit.byId(dateId).get('value');
            var current = this._normalizeValues(dijit.byId(this._activeSlider).get('value'));
            var id      = this._activeSlider.substr('horizontalRangeSlider_'.length);

            // Min value has array index 0, max := 1
            var index      = (side == 'min') ? 0 : 1;
            current[index] = this._convertStampToIndex(newDate.getTime());

            this._projects[id].oldValues = {
                start: this._projects[id].values.start,
                end:   this._projects[id].values.end
            };
            this._projects[id].values  = {start: current[0], end: current[1]};
            this._projects[id].updated = true;

            dijit.byId(this._activeSlider).set('value', current);
            dijit.byId(this._activeSlider).focus();
        }
    },

    _submitForm:function() {
        // Summary:
        //    Collect all the project values and save it.
        var sendData   = [];
        var projects   = [];
        var ids        = [];

        for (var id in this._projects) {
            // Only send the changed values
            if (this._projects[id].updated) {
                projects.push({
                    id:    id,
                    start: this._convertIndex2DateString(this._projects[id].values.start),
                    end:   this._convertIndex2DateString(this._projects[id].values.end)
                });
                ids.push(id);
            }
        }

        sendData['projects'] = dojo.toJson(projects);
        phpr.send({
            url:       phpr.webpath + 'index.php/Gantt/index/jsonSave/nodeId/' + phpr.currentProjectId,
            content:   sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
                    this._updateCacheData(ids);
                    this.reload();
                }
            })
        });
    },

    _updateCacheData:function(ids) {
        // Summary:
        //    Update list, parent and form cached for the changed projects.
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
                + '/id/' + ids[i];
            phpr.DataStore.deleteData({url: formUrl});
        }
    }
});
