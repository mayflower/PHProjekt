/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mobile._ScrollableMixin"]||(dojo._hasResource["dojox.mobile._ScrollableMixin"]=!0,dojo.provide("dojox.mobile._ScrollableMixin"),dojo.require("dijit._WidgetBase"),dojo.require("dojox.mobile.scrollable"),dojo.declare("dojox.mobile._ScrollableMixin",null,{fixedHeader:"",fixedFooter:"",destroy:function(){this.cleanup();this.inherited(arguments)},startup:function(){var a={};if(this.fixedHeader)a.fixedHeaderHeight=dojo.byId(this.fixedHeader).offsetHeight;if(this.fixedFooter){var b=
dojo.byId(this.fixedFooter);if(b.parentNode==this.domNode)this.isLocalFooter=!0,b.style.bottom="0px";a.fixedFooterHeight=b.offsetHeight}this.init(a);this.inherited(arguments)}}),function(){var a=new dojox.mobile.scrollable;dojo.extend(dojox.mobile._ScrollableMixin,a);dojo.version.major==1&&dojo.version.minor==4&&dojo.mixin(dojox.mobile._ScrollableMixin._meta.hidden,a)}());