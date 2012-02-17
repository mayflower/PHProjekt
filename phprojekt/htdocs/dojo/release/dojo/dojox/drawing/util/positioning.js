/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.drawing.util.positioning"]||(dojo._hasResource["dojox.drawing.util.positioning"]=!0,dojo.provide("dojox.drawing.util.positioning"),function(){dojox.drawing.util.positioning.label=function(a,b){var f=0.5*(a.x+b.x),e=0.5*(a.y+b.y),c=dojox.drawing.util.common.slope(a,b),d=4/Math.sqrt(1+c*c);if(b.y>a.y&&b.x>a.x||b.y<a.y&&b.x<a.x)d=-d,e-=20;f+=-d*c;e+=d;return{x:f,y:e,foo:"bar",align:b.x<a.x?"end":"start"}};dojox.drawing.util.positioning.angle=function(a,b){var f=0.7*a.x+0.3*b.x,
e=0.7*a.y+0.3*b.y,c=dojox.drawing.util.common.slope(a,b),d=4/Math.sqrt(1+c*c);b.x<a.x&&(d=-d);f+=-d*c;e+=d;c=b.y>a.y?"end":"start";e+=b.x>a.x?10:-10;return{x:f,y:e,align:c}}}());