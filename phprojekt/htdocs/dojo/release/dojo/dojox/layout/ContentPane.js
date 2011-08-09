/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.layout.ContentPane"]||(dojo._hasResource["dojox.layout.ContentPane"]=!0,dojo.provide("dojox.layout.ContentPane"),dojo.require("dijit.layout.ContentPane"),dojo.require("dojox.html._base"),dojo.declare("dojox.layout.ContentPane",dijit.layout.ContentPane,{adjustPaths:!1,cleanContent:!1,renderStyles:!1,executeScripts:!0,scriptHasHooks:!1,constructor:function(){this.ioArgs={};this.ioMethod=dojo.xhrGet},onExecError:function(){},_setContent:function(c){var a=this._contentSetter;
if(!(a&&a instanceof dojox.html._ContentSetter))a=this._contentSetter=new dojox.html._ContentSetter({node:this.containerNode,_onError:dojo.hitch(this,this._onError),onContentError:dojo.hitch(this,function(a){a=this.onContentError(a);try{this.containerNode.innerHTML=a}catch(b){console.error("Fatal "+this.id+" could not change content due to "+b.message,b)}})});this._contentSetterParams={adjustPaths:Boolean(this.adjustPaths&&(this.href||this.referencePath)),referencePath:this.href||this.referencePath,
renderStyles:this.renderStyles,executeScripts:this.executeScripts,scriptHasHooks:this.scriptHasHooks,scriptHookReplacement:"dijit.byId('"+this.id+"')"};this.inherited("_setContent",arguments)}}));