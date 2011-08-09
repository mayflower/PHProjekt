/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.Toolbar"]||(dojo._hasResource["dijit.Toolbar"]=!0,dojo.provide("dijit.Toolbar"),dojo.require("dijit._Widget"),dojo.require("dijit._KeyNavContainer"),dojo.require("dijit._Templated"),dojo.require("dijit.ToolbarSeparator"),dojo.declare("dijit.Toolbar",[dijit._Widget,dijit._Templated,dijit._KeyNavContainer],{templateString:'<div class="dijit" role="toolbar" tabIndex="${tabIndex}" dojoAttachPoint="containerNode"></div>',baseClass:"dijitToolbar",postCreate:function(){this.inherited(arguments);
this.connectKeyNavHandlers(this.isLeftToRight()?[dojo.keys.LEFT_ARROW]:[dojo.keys.RIGHT_ARROW],this.isLeftToRight()?[dojo.keys.RIGHT_ARROW]:[dojo.keys.LEFT_ARROW])},startup:function(){this._started||(this.startupKeyNavChildren(),this.inherited(arguments))}}));