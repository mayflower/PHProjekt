/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx.gradient"]||(dojo._hasResource["dojox.gfx.gradient"]=!0,dojo.provide("dojox.gfx.gradient"),dojo.require("dojox.gfx.matrix"),function(){function l(a,c,h,e,f,b){a=i.multiplyPoint(h,a,c);e=i.multiplyPoint(e,a);return{r:a,p:e,o:i.multiplyPoint(f,e).x/b}}function n(a,c){return a.o-c.o}var m=dojo,i=dojox.gfx.matrix,j=m.Color;dojox.gfx.gradient.rescale=function(a,c,h){var e=a.length,f=h<c,b;f&&(b=c,c=h,h=b);if(!e)return[];if(h<=a[0].offset)b=[{offset:0,color:a[0].color},{offset:1,
color:a[0].color}];else if(c>=a[e-1].offset)b=[{offset:0,color:a[e-1].color},{offset:1,color:a[e-1].color}];else{var i=h-c,d,k,g;b=[];c<0&&b.push({offset:0,color:new j(a[0].color)});for(g=0;g<e;++g)if(d=a[g],d.offset>=c)break;g?(k=a[g-1],b.push({offset:0,color:m.blendColors(new j(k.color),new j(d.color),(c-k.offset)/(d.offset-k.offset))})):b.push({offset:0,color:new j(d.color)});for(;g<e;++g){d=a[g];if(d.offset>=h)break;b.push({offset:(d.offset-c)/i,color:new j(d.color)})}g<e?(k=a[g-1],b.push({offset:1,
color:m.blendColors(new j(k.color),new j(d.color),(h-k.offset)/(d.offset-k.offset))})):b.push({offset:1,color:new j(a[e-1].color)})}if(f){b.reverse();g=0;for(e=b.length;g<e;++g)d=b[g],d.offset=1-d.offset}return b};dojox.gfx.gradient.project=function(a,c,h,e){var a=a||i.identity,f=i.multiplyPoint(a,c.x1,c.y1),b=i.multiplyPoint(a,c.x2,c.y2),j=Math.atan2(b.y-f.y,b.x-f.x),d=i.project(b.x-f.x,b.y-f.y),f=i.multiplyPoint(d,f),b=i.multiplyPoint(d,b),f=new i.Matrix2D([i.rotate(-j),{dx:-f.x,dy:-f.y}]),b=i.multiplyPoint(f,
b).x,a=[l(h.x,h.y,a,d,f,b),l(e.x,e.y,a,d,f,b),l(h.x,e.y,a,d,f,b),l(e.x,h.y,a,d,f,b)].sort(n),c=dojox.gfx.gradient.rescale(c.colors,a[0].o,a[3].o);return{type:"linear",x1:a[0].p.x,y1:a[0].p.y,x2:a[3].p.x,y2:a[3].p.y,colors:c,angle:j}}}());