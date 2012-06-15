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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Gantt.Form.HorizontalRangeSlider");

dojo.require("dojo.dnd.Moveable");
dojo.require("dijit.form.HorizontalSlider");

dojo.declare("phpr.Default.System.Form._RangeSliderMixin", dojox.form._RangeSliderMixin, {
    postCreate: function() {
        // Summary:
        //    Overwrite function for redefine the movers,
        //    since are wrong in the dojo distribution (1.5.0)
        this.inherited(arguments);

        // define a custom constructor for a SliderMoverMax that points back to me
        this._movableMax.destroy();
        var mover = dojo.declare(dijit.form._SliderMoverMax, {
            widget: this
        });
        this._movableMax = new dojo.dnd.Moveable(this.sliderHandleMax, {mover: mover});

        // a dnd for the bar!
        this._movableBar.destroy();
        var barMover = dojo.declare(dijit.form._SliderBarMover, {
            widget: this
        });
        this._movableBar = new dojo.dnd.Moveable(this.progressBar, {mover: barMover});
    },

    _onFocus: function() {
        // Summary:
        //    Overwrite function for do nothing
    },

    _onHandleClick: function(e) {
        // Summary:
        //    Overwrite function for do not focus
        if (this.disabled || this.readOnly) {
            return;
        }
        var name = e.target.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.id;
        this._selectBar(name);
        dojo.stopEvent(e);
    },

    _onHandleClickMax: function(e) {
        // Summary:
        //    Overwrite function for do not focus
        if (this.disabled || this.readOnly) {
            return;
        }
        var name = e.target.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.id;
        this._selectBar(name);
        dojo.stopEvent(e);
    },

    _onBarClick: function(e) {
        // Summary:
        //    Overwrite function for do not focus
        if (this.disabled || this.readOnly) {
            return;
        }
        var name = e.target.parentNode.parentNode.parentNode.parentNode.parentNode.id;
        this._selectBar(name);
        dojo.stopEvent(e);
    },

    _selectBar:function(name) {
        // Summary:
        //    Do not allow the focus
        // Description:
        //    Focus the main content for prevent the focus on IE
        //    Mark as select the current selected bar
        phpr.viewManager.getView().centerMainContent.domNode.focus();
        if (name) {
            dijit.byId('ganttObject').container.setActiveSlider(name);
        }
    }
});

dojo.declare("phpr.Gantt.Form.HorizontalRangeSlider", [dijit.form.HorizontalSlider, phpr.Default.System.Form._RangeSliderMixin], {
    templateString: __phpr_templateCache['phpr.Gantt.template.HorizontalRangeSlider.html']
    }
);
