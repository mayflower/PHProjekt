/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.dnd.Avatar"]||(dojo._hasResource["dojo.dnd.Avatar"]=!0,dojo.provide("dojo.dnd.Avatar"),dojo.require("dojo.dnd.common"),dojo.declare("dojo.dnd.Avatar",null,{constructor:function(d){this.manager=d;this.construct()},construct:function(){this.isA11y=dojo.hasClass(dojo.body(),"dijit_a11y");var d=dojo.create("table",{"class":"dojoDndAvatar",style:{position:"absolute",zIndex:"1999",margin:"0px"}}),a=this.manager.source,c,g=dojo.create("tbody",null,d),b=dojo.create("tr",null,g),f=
dojo.create("td",null,b);this.isA11y&&dojo.create("span",{id:"a11yIcon",innerHTML:this.manager.copy?"+":"<"},f);dojo.create("span",{innerHTML:a.generateText?this._generateText():""},f);var h=Math.min(5,this.manager.nodes.length),e=0;for(dojo.attr(b,{"class":"dojoDndAvatarHeader",style:{opacity:0.9}});e<h;++e)a.creator?c=a._normalizedCreator(a.getItem(this.manager.nodes[e].id).data,"avatar").node:(c=this.manager.nodes[e].cloneNode(!0),c.tagName.toLowerCase()=="tr"&&(b=dojo.create("table"),dojo.create("tbody",
null,b).appendChild(c),c=b)),c.id="",b=dojo.create("tr",null,g),f=dojo.create("td",null,b),f.appendChild(c),dojo.attr(b,{"class":"dojoDndAvatarItem",style:{opacity:(9-e)/10}});this.node=d},destroy:function(){dojo.destroy(this.node);this.node=!1},update:function(){dojo[(this.manager.canDropFlag?"add":"remove")+"Class"](this.node,"dojoDndAvatarCanDrop");if(this.isA11y){var d=dojo.byId("a11yIcon"),a="+";this.manager.canDropFlag&&!this.manager.copy?a="< ":!this.manager.canDropFlag&&!this.manager.copy?
a="o":this.manager.canDropFlag||(a="x");d.innerHTML=a}dojo.query("tr.dojoDndAvatarHeader td span"+(this.isA11y?" span":""),this.node).forEach(function(a){a.innerHTML=this._generateText()},this)},_generateText:function(){return this.manager.nodes.length.toString()}}));