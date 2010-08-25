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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.form.CheckBox");
dojo.provide("phpr.form.HorizontalSlider");
dojo.provide("phpr.form.Rating");

dojo.declare("phpr.form.CheckBox", dijit.form.CheckBox, {
    // Summary:
    //    Re-write the widget for return 0 on no-checked

    _getValueAttr: function(){
        // Summary:
        //    Hook so attr('value') works.
        // Description:
        //    If the CheckBox is checked, returns the value attribute.
        //    Otherwise returns 0.
        return (this.checked ? this.value : 0);
    }
});

dojo.declare("phpr.form.HorizontalSlider", dijit.form.HorizontalSlider, {
    _layoutHackIE7: function() {
        // Summary:
        //    Disable work around table sizing bugs on IE7 by forcing redraw
    }
});

dojo.declare("phpr.form.Rating", [dojox.form.Rating], {
    // Summary:
    //    Re-write the widget for fix some issues
    constructor:function(params) {
        dojo.mixin(this, params);
        var tpl = '<div class="dojoxRating dijitInline">'
            + '<input type="hidden" value="0" dojoAttachPoint="focusNode" name="${name}" /><ul>${stars}</ul>'
            + '</div>';

        var starTpl = '<li class="dojoxRatingStar dijitInline" '
          + 'dojoAttachEvent="onclick:onStarClick,onmouseover:_onMouse,onmouseout:_onMouse" value="${value}"></li>';
        var rendered = "";
        for(var i = 0; i < this.numStars; i++) {
            rendered += dojo.string.substitute(starTpl, {value: i + 1});
        }
        this.templateString = dojo.string.substitute(tpl, {stars: rendered, name: params.name});
    },

    onStarClick:function(evt) {
        if (!this.disabled) {
            this.inherited("onStarClick", arguments);
        }
    },

    setAttribute:function(key, value){
        this.set('value', value);
        if (key == "value") {
            this._renderStars(this.value);
            this.onChange(this.value);
        }
    }
});
