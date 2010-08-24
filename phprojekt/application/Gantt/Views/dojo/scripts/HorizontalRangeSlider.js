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

dojo.provide("phpr.form.HorizontalRangeSlider");

dojo.declare("phpr.form._RangeSliderMixin", dojox.form._RangeSliderMixin, {
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
        dojo.byId('centerMainContent').focus();
        if (name) {
            dijit.byId('ganttObject').container.setActiveSlider(name);
        }
    }
});

dojo.declare("phpr.form.HorizontalRangeSlider", [dijit.form.HorizontalSlider, phpr.form._RangeSliderMixin], {
    templateString: "<table class=\"dijit dijitReset dijitSlider dojoxRangeSlider\" cellspacing=\"0\" "
        + "cellpadding=\"0\" border=\"0\" rules=\"none\"\r\n    ><tr class=\"dijitReset\"\r\n        >"
        + "<td class=\"dijitReset\" colspan=\"2\"></td\r\n        >"
        + "<td dojoAttachPoint=\"containerNode,topDecoration\" class=\"dijitReset\" "
        + "style=\"text-align:center;width:100%;\"></td\r\n        ><td class=\"dijitReset\" colspan=\"2\">"
        + "</td\r\n    ></tr\r\n    ><tr class=\"dijitReset\"\r\n        >"
        + "<td class=\"dijitReset dijitSliderButtonContainer dijitSliderButtonContainerH\"\r\n            >"
        + "<div class=\"dijitSliderDecrementIconH\" tabIndex=\"-1\" style=\"display:none\" "
        + "dojoAttachPoint=\"decrementButton\" dojoAttachEvent=\"onclick: decrement\">"
        + "<span class=\"dijitSliderButtonInner\">-</span></div\r\n        ></td\r\n        >"
        + "<td class=\"dijitReset\"\r\n            >"
        + "<div class=\"dijitSliderBar dijitSliderBumper dijitSliderBumperH dijitSliderLeftBumper "
        + "dijitSliderLeftBumperH\" dojoAttachEvent=\"onclick:_onClkDecBumper\"></div\r\n        >"
        + "</td\r\n        ><td class=\"dijitReset\"\r\n            >"
        + "<input dojoAttachPoint=\"valueNode\" type=\"hidden\" name=\"${name}\"\r\n            />"
        + "<div waiRole=\"presentation\" class=\"dojoxRangeSliderBarContainer\" "
        + "dojoAttachPoint=\"sliderBarContainer\"\r\n                ><div dojoAttachPoint=\"sliderHandle\" "
        + "tabIndex=\"${tabIndex}\" class=\"dijitSliderMoveable\" "
        + "dojoAttachEvent=\"onkeypress:_onKeyPress,onmousedown:_onHandleClick\" waiRole=\"slider\" "
        + "valuemin=\"${minimum}\" valuemax=\"${maximum}\"\r\n                    >"
        + "<div class=\"dijitSliderImageHandle dijitSliderImageHandleH\"></div\r\n                >"
        + "</div\r\n                ><div waiRole=\"presentation\" dojoAttachPoint=\"progressBar,focusNode\" "
        + "class=\"dijitSliderBar dijitSliderBarH dijitSliderProgressBar dijitSliderProgressBarH\" "
        + "dojoAttachEvent=\"onkeypress:_onKeyPress,onmousedown:_onBarClick\"></div\r\n                >"
        + "<div dojoAttachPoint=\"sliderHandleMax,focusNodeMax\" tabIndex=\"${tabIndex}\" "
        + "class=\"dijitSliderMoveable\" dojoAttachEvent=\"onkeypress:_onKeyPress,onmousedown:_onHandleClickMax\" "
        + "waiRole=\"sliderMax\" valuemin=\"${minimum}\" valuemax=\"${maximum}\"\r\n                    >"
        + "<div class=\"dijitSliderImageHandle dijitSliderImageHandleH\"></div\r\n                >"
        + "</div\r\n                ><div waiRole=\"presentation\" dojoAttachPoint=\"remainingBar\" "
        + "class=\"dijitSliderBar dijitSliderBarH dijitSliderRemainingBar dijitSliderRemainingBarH\" "
        + "dojoAttachEvent=\"onmousedown:_onRemainingBarClick\"></div\r\n            ></div\r\n        >"
        + "</td\r\n        ><td class=\"dijitReset\"\r\n            ><div class=\"dijitSliderBar "
        + "dijitSliderBumper dijitSliderBumperH dijitSliderRightBumper dijitSliderRightBumperH\" "
        + "dojoAttachEvent=\"onclick:_onClkIncBumper\"></div\r\n        ></td\r\n        >"
        + "<td class=\"dijitReset dijitSliderButtonContainer dijitSliderButtonContainerH\"\r\n            >"
        + "<div class=\"dijitSliderIncrementIconH\" tabIndex=\"-1\" style=\"display:none\" "
        + "dojoAttachPoint=\"incrementButton\" dojoAttachEvent=\"onclick: increment\">"
        + "<span class=\"dijitSliderButtonInner\">+</span></div\r\n        ></td\r\n    >"
        + "</tr\r\n    ><tr class=\"dijitReset\"\r\n        ><td class=\"dijitReset\" colspan=\"2\">"
        + "</td\r\n        ><td dojoAttachPoint=\"containerNode,bottomDecoration\" class=\"dijitReset\" "
        + "style=\"text-align:center;\"></td\r\n        ><td class=\"dijitReset\" colspan=\"2\"></td\r\n    >"
        + "</tr\r\n></table>\r\n"
    }
);
