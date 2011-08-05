/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dijit._base.wai"])dojo._hasResource["dijit._base.wai"]=!0,dojo.provide("dijit._base.wai"),dijit.wai={onload:function(){var a=dojo.create("div",{id:"a11yTestNode",style:{cssText:'border: 1px solid;border-color:red green;position: absolute;height: 5px;top: -999px;background-image: url("'+(dojo.config.blankGif||dojo.moduleUrl("dojo","resources/blank.gif"))+'");'}},dojo.body()),b=dojo.getComputedStyle(a);if(b){var c=b.backgroundImage;dojo[b.borderTopColor==b.borderRightColor||c!=
null&&(c=="none"||c=="url(invalid-url:)")?"addClass":"removeClass"](dojo.body(),"dijit_a11y");dojo.isIE?a.outerHTML="":dojo.body().removeChild(a)}}},(dojo.isIE||dojo.isMoz)&&dojo._loaders.unshift(dijit.wai.onload),dojo.mixin(dijit,{hasWaiRole:function(a,b){var c=this.getWaiRole(a);return b?c.indexOf(b)>-1:c.length>0},getWaiRole:function(a){return dojo.trim((dojo.attr(a,"role")||"").replace("wairole:",""))},setWaiRole:function(a,b){dojo.attr(a,"role",b)},removeWaiRole:function(a,b){var c=dojo.attr(a,
"role");c&&(b?(c=dojo.trim((" "+c+" ").replace(" "+b+" "," ")),dojo.attr(a,"role",c)):a.removeAttribute("role"))},hasWaiState:function(a,b){return a.hasAttribute?a.hasAttribute("aria-"+b):!!a.getAttribute("aria-"+b)},getWaiState:function(a,b){return a.getAttribute("aria-"+b)||""},setWaiState:function(a,b,c){a.setAttribute("aria-"+b,c)},removeWaiState:function(a,b){a.removeAttribute("aria-"+b)}});