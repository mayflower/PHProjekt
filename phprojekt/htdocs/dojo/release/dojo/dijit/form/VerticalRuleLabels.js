/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.form.VerticalRuleLabels"]||(dojo._hasResource["dijit.form.VerticalRuleLabels"]=!0,dojo.provide("dijit.form.VerticalRuleLabels"),dojo.require("dijit.form.HorizontalRuleLabels"),dojo.declare("dijit.form.VerticalRuleLabels",dijit.form.HorizontalRuleLabels,{templateString:'<div class="dijitRuleContainer dijitRuleContainerV dijitRuleLabelsContainer dijitRuleLabelsContainerV"></div>',_positionPrefix:'<div class="dijitRuleLabelContainer dijitRuleLabelContainerV" style="top:',_labelPrefix:'"><span class="dijitRuleLabel dijitRuleLabelV">',
_calcPosition:function(a){return 100-a},_isHorizontal:!1}));