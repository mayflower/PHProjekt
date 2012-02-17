/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.dnd.autoscroll"])dojo._hasResource["dojo.dnd.autoscroll"]=!0,dojo.provide("dojo.dnd.autoscroll"),dojo.require("dojo.window"),dojo.getObject("dnd",!0,dojo),dojo.dnd.getViewport=dojo.window.getBox,dojo.dnd.V_TRIGGER_AUTOSCROLL=32,dojo.dnd.H_TRIGGER_AUTOSCROLL=32,dojo.dnd.V_AUTOSCROLL_VALUE=16,dojo.dnd.H_AUTOSCROLL_VALUE=16,dojo.dnd.autoScroll=function(d){var a=dojo.window.getBox(),b=0,c=0;if(d.clientX<dojo.dnd.H_TRIGGER_AUTOSCROLL)b=-dojo.dnd.H_AUTOSCROLL_VALUE;else if(d.clientX>
a.w-dojo.dnd.H_TRIGGER_AUTOSCROLL)b=dojo.dnd.H_AUTOSCROLL_VALUE;if(d.clientY<dojo.dnd.V_TRIGGER_AUTOSCROLL)c=-dojo.dnd.V_AUTOSCROLL_VALUE;else if(d.clientY>a.h-dojo.dnd.V_TRIGGER_AUTOSCROLL)c=dojo.dnd.V_AUTOSCROLL_VALUE;window.scrollBy(b,c)},dojo.dnd._validNodes={div:1,p:1,td:1},dojo.dnd._validOverflow={auto:1,scroll:1},dojo.dnd.autoScrollNodes=function(d){for(var a=d.target;a;){if(a.nodeType==1&&a.tagName.toLowerCase()in dojo.dnd._validNodes){var b=dojo.getComputedStyle(a);if(b.overflow.toLowerCase()in
dojo.dnd._validOverflow){var c=dojo._getContentBox(a,b),b=dojo.position(a,!0),e=Math.min(dojo.dnd.H_TRIGGER_AUTOSCROLL,c.w/2),h=Math.min(dojo.dnd.V_TRIGGER_AUTOSCROLL,c.h/2),f=d.pageX-b.x,g=d.pageY-b.y,i=b=0;if(dojo.isWebKit||dojo.isOpera)f+=dojo.body().scrollLeft,g+=dojo.body().scrollTop;f>0&&f<c.w&&(f<e?b=-e:f>c.w-e&&(b=e));g>0&&g<c.h&&(g<h?i=-h:g>c.h-h&&(i=h));c=a.scrollLeft;e=a.scrollTop;a.scrollLeft+=b;a.scrollTop+=i;if(c!=a.scrollLeft||e!=a.scrollTop)return}}try{a=a.parentNode}catch(j){a=null}}dojo.dnd.autoScroll(d)};