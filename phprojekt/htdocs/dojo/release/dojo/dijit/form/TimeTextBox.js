/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.TimeTextBox"]||(dojo._hasResource["dijit.form.TimeTextBox"]=!0,dojo.provide("dijit.form.TimeTextBox"),dojo.require("dijit._TimePicker"),dojo.require("dijit.form._DateTimeTextBox"),dojo.declare("dijit.form.TimeTextBox",dijit.form._DateTimeTextBox,{baseClass:"dijitTextBox dijitComboBox dijitTimeTextBox",popupClass:"dijit._TimePicker",_selector:"time",value:new Date(""),_onKey:function(b){this.inherited(arguments);switch(b.keyCode){case dojo.keys.ENTER:case dojo.keys.TAB:case dojo.keys.ESCAPE:case dojo.keys.DOWN_ARROW:case dojo.keys.UP_ARROW:break;
default:setTimeout(dojo.hitch(this,function(){var a=this.get("displayedValue");this.filterString=a&&!this.parse(a,this.constraints)?a.toLowerCase():"";this._opened&&this.closeDropDown();this.openDropDown()}),0)}}}));