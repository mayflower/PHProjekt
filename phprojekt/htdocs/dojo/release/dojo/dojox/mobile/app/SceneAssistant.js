/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mobile.app.SceneAssistant"]||(dojo._hasResource["dojox.mobile.app.SceneAssistant"]=!0,dojo.provide("dojox.mobile.app.SceneAssistant"),dojo.experimental("dojox.mobile.app.SceneAssistant"),dojo.declare("dojox.mobile.app.SceneAssistant",null,{constructor:function(){},setup:function(){},activate:function(){},deactivate:function(){},destroy:function(){var a=dojo.query("> [widgetId]",this.containerNode).map(dijit.byNode);dojo.forEach(a,function(a){a.destroyRecursive()});this.disconnect()},
connect:function(a,b,c){if(!this._connects)this._connects=[];this._connects.push(dojo.connect(a,b,c))},disconnect:function(){dojo.forEach(this._connects,dojo.disconnect);this._connects=[]}}));