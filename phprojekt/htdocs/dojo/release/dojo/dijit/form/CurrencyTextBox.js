/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.CurrencyTextBox"]||(dojo._hasResource["dijit.form.CurrencyTextBox"]=!0,dojo.provide("dijit.form.CurrencyTextBox"),dojo.require("dojo.currency"),dojo.require("dijit.form.NumberTextBox"),dojo.declare("dijit.form.CurrencyTextBox",dijit.form.NumberTextBox,{currency:"",baseClass:"dijitTextBox dijitCurrencyTextBox",regExpGen:function(a){return"("+(this._focused?this.inherited(arguments,[dojo.mixin({},a,this.editOptions)])+"|":"")+dojo.currency.regexp(a)+")"},_formatter:dojo.currency.format,
_parser:dojo.currency.parse,parse:function(a,c){var b=this.inherited(arguments);isNaN(b)&&/\d+/.test(a)&&(b=dojo.hitch(dojo.mixin({},this,{_parser:dijit.form.NumberTextBox.prototype._parser}),"inherited")(arguments));return b},_setConstraintsAttr:function(a){if(!a.currency&&this.currency)a.currency=this.currency;this.inherited(arguments,[dojo.currency._mixInDefaults(dojo.mixin(a,{exponent:!1}))])}}));