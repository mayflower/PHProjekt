/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.rotator.Wipe"]||(dojo._hasResource["dojox.widget.rotator.Wipe"]=!0,dojo.provide("dojox.widget.rotator.Wipe"),function(f){function m(a,b,c,e){var d=[0,b,0,0];a==i?d=[0,b,c,b]:a==j?d=[c,b,c,0]:a==h&&(d=[0,0,c,0]);e!=null&&(d[a]=a==k||a==h?e:(a%2?b:c)-e);return d}function l(a,b,c,e,d){f.style(a,"clip",b==null?"auto":"rect("+m(b,c,e,d).join("px,")+"px)")}function g(a,b){var c=b.next.node,e=b.rotatorBox.w,d=b.rotatorBox.h;f.style(c,{display:"",zIndex:(f.style(b.current.node,
"zIndex")||1)+1});l(c,a,e,d);return new f.Animation(f.mixin({node:c,curve:[0,a%2?e:d],onAnimate:function(b){l(c,a,e,d,parseInt(b))}},b))}var k=2,i=3,j=0,h=1;f.mixin(dojox.widget.rotator,{wipeDown:function(a){return g(k,a)},wipeRight:function(a){return g(i,a)},wipeUp:function(a){return g(j,a)},wipeLeft:function(a){return g(h,a)}})}(dojo));