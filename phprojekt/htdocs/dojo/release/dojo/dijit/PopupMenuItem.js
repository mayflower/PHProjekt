/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.PopupMenuItem"]||(dojo._hasResource["dijit.PopupMenuItem"]=!0,dojo.provide("dijit.PopupMenuItem"),dojo.require("dijit.MenuItem"),dojo.declare("dijit.PopupMenuItem",dijit.MenuItem,{_fillContent:function(){if(this.srcNodeRef){var a=dojo.query("*",this.srcNodeRef);dijit.PopupMenuItem.superclass._fillContent.call(this,a[0]);this.dropDownContainer=this.srcNodeRef}},startup:function(){if(!this._started){this.inherited(arguments);if(!this.popup){var a=dojo.query("[widgetId]",this.dropDownContainer)[0];
this.popup=dijit.byNode(a)}dojo.body().appendChild(this.popup.domNode);this.popup.startup();this.popup.domNode.style.display="none";this.arrowWrapper&&dojo.style(this.arrowWrapper,"visibility","");dijit.setWaiState(this.focusNode,"haspopup","true")}},destroyDescendants:function(){this.popup&&(this.popup._destroyed||this.popup.destroyRecursive(),delete this.popup);this.inherited(arguments)}}));