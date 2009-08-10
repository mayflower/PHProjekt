/*
	Copyright (c) 2004-2009, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit.form.VerticalRuleLabels"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dijit.form.VerticalRuleLabels"] = true;
dojo.provide("dijit.form.VerticalRuleLabels");

dojo.require("dijit.form.HorizontalRuleLabels");

dojo.declare("dijit.form.VerticalRuleLabels", dijit.form.HorizontalRuleLabels,
{
	// summary:
	//		Labels for the `dijit.form.VerticalSlider`

	templateString: '<div class="dijitRuleContainer dijitRuleContainerV dijitRuleLabelsContainer dijitRuleLabelsContainerV"></div>',

	_positionPrefix: '<div class="dijitRuleLabelContainer dijitRuleLabelContainerV" style="top:',
	_labelPrefix: '"><span class="dijitRuleLabel dijitRuleLabelV">',

	_calcPosition: function(pos){
		// Overrides HorizontalRuleLabel._calcPosition()
		return 100-pos;
	},

	// TODO: remove this.   Apparently it's not used.
	_isHorizontal: false
});

}
