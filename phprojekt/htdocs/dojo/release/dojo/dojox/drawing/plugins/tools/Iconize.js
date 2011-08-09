/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.drawing.plugins.tools.Iconize"])dojo._hasResource["dojox.drawing.plugins.tools.Iconize"]=!0,dojo.provide("dojox.drawing.plugins.tools.Iconize"),dojo.require("dojox.drawing.plugins._Plugin"),dojox.drawing.plugins.tools.Iconize=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(){},{onClick:function(){var c,b;for(b in this.stencils.stencils)if(console.log(" stanceil item:",this.stencils.stencils[b].id,this.stencils.stencils[b]),this.stencils.stencils[b].shortType==
"path"){c=this.stencils.stencils[b];break}c&&(console.log("click Iconize plugin",c.points),this.makeIcon(c.points))},makeIcon:function(c){var b=1E4,g=1E4;c.forEach(function(a){a.x!==void 0&&!isNaN(a.x)&&(b=Math.min(b,a.x),g=Math.min(g,a.y))});var e=0,f=0;c.forEach(function(a){if(a.x!==void 0&&!isNaN(a.x))a.x=Number((a.x-b).toFixed(1)),a.y=Number((a.y-g).toFixed(1)),e=Math.max(e,a.x),f=Math.max(f,a.y)});console.log("xmax:",e,"ymax:",f);c.forEach(function(a){a.x=Number((a.x/e).toFixed(1))*60+20;a.y=
Number((a.y/f).toFixed(1))*60+20});var d="[\n";dojo.forEach(c,function(a,b){d+="{\t";a.t&&(d+="t:'"+a.t+"'");a.x!==void 0&&!isNaN(a.x)&&(a.t&&(d+=", "),d+="x:"+a.x+",\t\ty:"+a.y);d+="\t}";b!=c.length-1&&(d+=",");d+="\n"});d+="]";console.log(d);var h=dojo.byId("data");if(h)h.value=d}}),dojox.drawing.plugins.tools.Iconize.setup={name:"dojox.drawing.plugins.tools.Iconize",tooltip:"Iconize Tool",iconClass:"iconPan"},dojox.drawing.register(dojox.drawing.plugins.tools.Iconize.setup,"plugin");