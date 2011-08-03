/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.gfx.arc"]||(dojo._hasResource["dojox.gfx.arc"]=!0,dojo.provide("dojox.gfx.arc"),dojo.require("dojox.gfx.matrix"),function(){function l(b){var e=Math.cos(b),b=Math.sin(b),c={x:e+4/3*(1-e),y:b-4/3*e*(1-e)/b};return{s:{x:e,y:-b},c1:{x:c.x,y:-c.y},c2:c,e:{x:e,y:b}}}var b=dojox.gfx.matrix,m=2*Math.PI,p=Math.PI/4,n=Math.PI/8,t=p+n,q=l(n);dojox.gfx.arc={unitArcAsBezier:l,curvePI4:q,arcAsBezier:function(g,e,c,a,h,i,j,f){var h=Boolean(h),i=Boolean(i),k=b._degToRad(a),a=e*e,o=c*c,d=
b.multiplyPoint(b.rotate(-k),{x:(g.x-j)/2,y:(g.y-f)/2}),r=d.x*d.x,s=d.y*d.y,a=Math.sqrt((a*o-a*s-o*r)/(a*s+o*r));isNaN(a)&&(a=0);a={x:a*e*d.y/c,y:-a*c*d.x/e};h==i&&(a={x:-a.x,y:-a.y});a=b.multiplyPoint([b.translate((g.x+j)/2,(g.y+f)/2),b.rotate(k)],a);e=b.normalize([b.translate(a.x,a.y),b.rotate(k),b.scale(e,c)]);a=b.invert(e);g=b.multiplyPoint(a,g);f=b.multiplyPoint(a,j,f);j=Math.atan2(g.y,g.x);a=j-Math.atan2(f.y,f.x);i&&(a=-a);a<0?a+=m:a>m&&(a-=m);c=n;f=q;c=i?c:-c;g=[];for(h=a;h>0;h-=p)h<t&&(c=
h/2,f=l(c),c=i?c:-c,h=0),d=b.normalize([e,b.rotate(j+c)]),i?(a=b.multiplyPoint(d,f.c1),k=b.multiplyPoint(d,f.c2),d=b.multiplyPoint(d,f.e)):(a=b.multiplyPoint(d,f.c2),k=b.multiplyPoint(d,f.c1),d=b.multiplyPoint(d,f.s)),g.push([a.x,a.y,k.x,k.y,d.x,d.y]),j+=2*c;return g}}}());