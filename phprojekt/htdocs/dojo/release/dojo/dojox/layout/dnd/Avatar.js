/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.layout.dnd.Avatar"]||(dojo._hasResource["dojox.layout.dnd.Avatar"]=!0,dojo.provide("dojox.layout.dnd.Avatar"),dojo.require("dojo.dnd.Avatar"),dojo.require("dojo.dnd.common"),dojo.declare("dojox.layout.dnd.Avatar",dojo.dnd.Avatar,{constructor:function(b,a){this.opacity=a||0.9},construct:function(){var b=this.manager.source,a=b.creator?b._normalizedCreator(b.getItem(this.manager.nodes[0].id).data,"avatar").node:this.manager.nodes[0].cloneNode(!0);dojo.addClass(a,"dojoDndAvatar");
a.id=dojo.dnd.getUniqueId();a.style.position="absolute";a.style.zIndex=1999;a.style.margin="0px";a.style.width=dojo.marginBox(b.node).w+"px";dojo.style(a,"opacity",this.opacity);this.node=a},update:function(){dojo.toggleClass(this.node,"dojoDndAvatarCanDrop",this.manager.canDropFlag)},_generateText:function(){}}));