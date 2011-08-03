/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.rotator.Slide"]||(dojo._hasResource["dojox.widget.rotator.Slide"]=!0,dojo.provide("dojox.widget.rotator.Slide"),function(d){function c(a,b){var c=b.node=b.next.node,e=b.rotatorBox,f=a%2,e=(f?e.w:e.h)*(a<2?-1:1);d.style(c,{display:"",zIndex:(d.style(b.current.node,"zIndex")||1)+1});if(!b.properties)b.properties={};b.properties[f?"left":"top"]={start:e,end:0};return d.animateProperty(b)}d.mixin(dojox.widget.rotator,{slideDown:function(a){return c(0,a)},slideRight:function(a){return c(1,
a)},slideUp:function(a){return c(2,a)},slideLeft:function(a){return c(3,a)}})}(dojo));