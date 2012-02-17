/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx3d.vector"]||(dojo._hasResource["dojox.gfx3d.vector"]=!0,dojo.provide("dojox.gfx3d.vector"),dojo.mixin(dojox.gfx3d.vector,{sum:function(){var a={x:0,y:0,z:0};dojo.forEach(arguments,function(b){a.x+=b.x;a.y+=b.y;a.z+=b.z});return a},center:function(){var a=arguments.length;if(a==0)return{x:0,y:0,z:0};var b=dojox.gfx3d.vector.sum(arguments);return{x:b.x/a,y:b.y/a,z:b.z/a}},substract:function(a,b){return{x:a.x-b.x,y:a.y-b.y,z:a.z-b.z}},_crossProduct:function(a,b,d,c,e,f){return{x:b*
f-d*e,y:d*c-a*f,z:a*e-b*c}},crossProduct:function(a,b,d,c,e,f){return arguments.length==6&&dojo.every(arguments,function(a){return typeof a=="number"})?dojox.gfx3d.vector._crossProduct(a,b,d,c,e,f):dojox.gfx3d.vector._crossProduct(a.x,a.y,a.z,b.x,b.y,b.z)},_dotProduct:function(a,b,d,c,e,f){return a*c+b*e+d*f},dotProduct:function(a,b,d,c,e,f){return arguments.length==6&&dojo.every(arguments,function(a){return typeof a=="number"})?dojox.gfx3d.vector._dotProduct(a,b,d,c,e,f):dojox.gfx3d.vector._dotProduct(a.x,
a.y,a.z,b.x,b.y,b.z)},normalize:function(a,b,d){var c;a instanceof Array?(c=a[0],b=a[1],a=a[2]):(c=a,a=d);d=dojox.gfx3d.vector.substract(b,c);c=dojox.gfx3d.vector.substract(a,c);return dojox.gfx3d.vector.crossProduct(d,c)}}));