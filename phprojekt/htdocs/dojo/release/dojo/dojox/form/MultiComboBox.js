/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.MultiComboBox"]||(dojo._hasResource["dojox.form.MultiComboBox"]=!0,dojo.provide("dojox.form.MultiComboBox"),dojo.experimental("dojox.form.MultiComboBox"),dojo.require("dijit.form.ComboBox"),dojo.require("dijit.form.ValidationTextBox"),dojo.declare("dojox.form.MultiComboBox",[dijit.form.ValidationTextBox,dijit.form.ComboBoxMixin],{delimiter:",",_previousMatches:!1,_setValueAttr:function(a){this.delimiter&&a.length!=0&&(a=a+this.delimiter+" ",arguments[0]=this._addPreviousMatches(a));
this.inherited(arguments)},_addPreviousMatches:function(a){this._previousMatches&&(a.match(RegExp("^"+this._previousMatches))||(a=this._previousMatches+a),a=this._cleanupDelimiters(a));return a},_cleanupDelimiters:function(a){this.delimiter&&(a=a.replace(/  +/," "),a=a.replace(RegExp("^ *"+this.delimiter+"* *"),""),a=a.replace(RegExp(this.delimiter+" *"+this.delimiter),this.delimiter));return a},_autoCompleteText:function(a){arguments[0]=this._addPreviousMatches(a);this.inherited(arguments)},_startSearch:function(a){var a=
this._cleanupDelimiters(a),b=RegExp("^.*"+this.delimiter+" *");if(this._previousMatches=a.match(b))arguments[0]=a.replace(b,"");this.inherited(arguments)}}));