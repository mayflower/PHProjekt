/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.TitleGroup"]||(dojo._hasResource["dojox.widget.TitleGroup"]=!0,dojo.provide("dojox.widget.TitleGroup"),dojo.require("dijit._Widget"),dojo.require("dijit.TitlePane"),function(c){var d=dijit.TitlePane.prototype,e=function(){var a=this._dxfindParent&&this._dxfindParent();a&&a.selectChild(this)};d._dxfindParent=function(){var a=this.domNode.parentNode;return a?(a=dijit.getEnclosingWidget(a))&&a instanceof dojox.widget.TitleGroup&&a:a};c.connect(d,"_onTitleClick",e);c.connect(d,
"_onTitleKey",function(a){(!a||!a.type||!(a.type=="keypress"&&a.charOrCode==c.keys.TAB))&&e.apply(this,arguments)});c.declare("dojox.widget.TitleGroup",dijit._Widget,{"class":"dojoxTitleGroup",addChild:function(a,b){return a.placeAt(this.domNode,b)},removeChild:function(a){this.domNode.removeChild(a.domNode);return a},selectChild:function(a){a&&dojo.query("> .dijitTitlePane",this.domNode).forEach(function(b){(b=dijit.getEnclosingWidget(b))&&b!==a&&b.open&&b.set("open",!1)});return a}})}(dojo));