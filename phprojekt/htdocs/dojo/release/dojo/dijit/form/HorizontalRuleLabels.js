/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.HorizontalRuleLabels"]||(dojo._hasResource["dijit.form.HorizontalRuleLabels"]=!0,dojo.provide("dijit.form.HorizontalRuleLabels"),dojo.require("dijit.form.HorizontalRule"),dojo.declare("dijit.form.HorizontalRuleLabels",dijit.form.HorizontalRule,{templateString:'<div class="dijitRuleContainer dijitRuleContainerH dijitRuleLabelsContainer dijitRuleLabelsContainerH"></div>',labelStyle:"",labels:[],numericMargin:0,minimum:0,maximum:1,constraints:{pattern:"#%"},_positionPrefix:'<div class="dijitRuleLabelContainer dijitRuleLabelContainerH" style="left:',
_labelPrefix:'"><div class="dijitRuleLabel dijitRuleLabelH">',_suffix:"</div></div>",_calcPosition:function(a){return a},_genHTML:function(a,b){return this._positionPrefix+this._calcPosition(a)+this._positionSuffix+this.labelStyle+this._labelPrefix+this.labels[b]+this._suffix},getLabels:function(){var a=this.labels;a.length||(a=dojo.query("> li",this.srcNodeRef).map(function(a){return String(a.innerHTML)}));this.srcNodeRef.innerHTML="";if(!a.length&&this.count>1)for(var b=this.minimum,d=(this.maximum-
b)/(this.count-1),c=0;c<this.count;c++)a.push(c<this.numericMargin||c>=this.count-this.numericMargin?"":dojo.number.format(b,this.constraints)),b+=d;return a},postMixInProperties:function(){this.inherited(arguments);this.labels=this.getLabels();this.count=this.labels.length}}));