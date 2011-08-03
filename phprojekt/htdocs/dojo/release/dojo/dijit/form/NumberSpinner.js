/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.NumberSpinner"]||(dojo._hasResource["dijit.form.NumberSpinner"]=!0,dojo.provide("dijit.form.NumberSpinner"),dojo.require("dijit.form._Spinner"),dojo.require("dijit.form.NumberTextBox"),dojo.declare("dijit.form.NumberSpinner",[dijit.form._Spinner,dijit.form.NumberTextBoxMixin],{adjust:function(a,c){var b=this.constraints,g=isNaN(a),e=!isNaN(b.max),f=!isNaN(b.min);g&&c!=0&&(a=c>0?f?b.min:e?b.max:0:e?this.constraints.max:f?b.min:0);var d=a+c;if(g||isNaN(d))return a;if(e&&
d>b.max)d=b.max;if(f&&d<b.min)d=b.min;return d},_onKeyPress:function(a){if((a.charOrCode==dojo.keys.HOME||a.charOrCode==dojo.keys.END)&&!a.ctrlKey&&!a.altKey&&!a.metaKey&&typeof this.get("value")!="undefined"){var c=this.constraints[a.charOrCode==dojo.keys.HOME?"min":"max"];typeof c=="number"&&this._setValueAttr(c,!1);dojo.stopEvent(a)}}}));