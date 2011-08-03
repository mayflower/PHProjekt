/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.Declaration"]||(dojo._hasResource["dijit.Declaration"]=!0,dojo.provide("dijit.Declaration"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.declare("dijit.Declaration",dijit._Widget,{_noScript:!0,stopParser:!0,widgetClass:"",defaults:null,mixins:[],buildRendering:function(){var b=this.srcNodeRef.parentNode.removeChild(this.srcNodeRef),f=dojo.query("> script[type^='dojo/method']",b).orphan(),e=dojo.query("> script[type^='dojo/connect']",b).orphan(),d=b.nodeName,
c=this.defaults||{};dojo.forEach(f,function(a){var b=a.getAttribute("event")||a.getAttribute("data-dojo-event"),d=dojo.parser._functionFromScript(a);b?c[b]=d:e.push(a)});this.mixins=this.mixins.length?dojo.map(this.mixins,function(a){return dojo.getObject(a)}):[dijit._Widget,dijit._Templated];c.widgetsInTemplate=!0;c._skipNodeCache=!0;c.templateString="<"+d+" class='"+b.className+"' dojoAttachPoint='"+(b.getAttribute("dojoAttachPoint")||"")+"' dojoAttachEvent='"+(b.getAttribute("dojoAttachEvent")||
"")+"' >"+b.innerHTML.replace(/\%7B/g,"{").replace(/\%7D/g,"}")+"</"+d+">";dojo.query("[dojoType]",b).forEach(function(a){a.removeAttribute("dojoType")});var g=dojo.declare(this.widgetClass,this.mixins,c);dojo.forEach(e,function(a){var b=a.getAttribute("event")||a.getAttribute("data-dojo-event")||"postscript",a=dojo.parser._functionFromScript(a);dojo.connect(g.prototype,b,a)})}}));