/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.HorizontalRule"]||(dojo._hasResource["dijit.form.HorizontalRule"]=!0,dojo.provide("dijit.form.HorizontalRule"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.declare("dijit.form.HorizontalRule",[dijit._Widget,dijit._Templated],{templateString:'<div class="dijitRuleContainer dijitRuleContainerH"></div>',count:3,container:"containerNode",ruleStyle:"",_positionPrefix:'<div class="dijitRuleMark dijitRuleMarkH" style="left:',_positionSuffix:"%;",_suffix:'"></div>',
_genHTML:function(a){return this._positionPrefix+a+this._positionSuffix+this.ruleStyle+this._suffix},_isHorizontal:!0,buildRendering:function(){this.inherited(arguments);var a;if(this.count==1)a=this._genHTML(50,0);else{var b,c=100/(this.count-1);if(!this._isHorizontal||this.isLeftToRight()){a=this._genHTML(0,0);for(b=1;b<this.count-1;b++)a+=this._genHTML(c*b,b);a+=this._genHTML(100,this.count-1)}else{a=this._genHTML(100,0);for(b=1;b<this.count-1;b++)a+=this._genHTML(100-c*b,b);a+=this._genHTML(0,
this.count-1)}}this.domNode.innerHTML=a}}));