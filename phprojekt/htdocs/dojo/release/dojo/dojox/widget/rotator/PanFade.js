/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.rotator.PanFade"]||(dojo._hasResource["dojox.widget.rotator.PanFade"]=!0,dojo.provide("dojox.widget.rotator.PanFade"),dojo.require("dojo.fx"),function(d){function k(a,c){var i={node:c.current.node,duration:c.duration,easing:c.easing},f={node:c.next.node,duration:c.duration,easing:c.easing},h=c.rotatorBox,b=a%2,g=b?"left":"top",h=(b?h.w:h.h)*(a<2?-1:1),b={},e={};d.style(f.node,{display:"",opacity:0});b[g]={start:0,end:-h};e[g]={start:h,end:0};return d.fx.combine([d.animateProperty(d.mixin({properties:b},
i)),d.fadeOut(i),d.animateProperty(d.mixin({properties:e},f)),d.fadeIn(f)])}function o(a,c){d.style(a,"zIndex",c)}d.mixin(dojox.widget.rotator,{panFade:function(a){var c=a.wrap,i=a.rotator.panes,f=i.length,h=f,b=a.current.idx,g=a.next.idx,e=Math.abs(g-b),j=Math.abs(f-Math.max(b,g)+Math.min(b,g))%f,m=b<g,l=3,p=[],q=[],n=a.duration;if(!c&&!m||c&&(m&&e>j||!m&&e<j))l=1;if(a.continuous){a.quick&&(n=Math.round(n/(c?Math.min(j,e):e)));o(i[b].node,h--);for(c=l==3;;){e=b;c?++b>=f&&(b=0):--b<0&&(b=f-1);e=i[e];
j=i[b];o(j.node,h--);p.push(k(l,d.mixin({easing:function(a){return a}},a,{current:e,next:j,duration:n})));if(c&&b==g||!c&&b==g)break;q.push(j.node)}var a=d.fx.chain(p),r=d.connect(a,"onEnd",function(){d.disconnect(r);d.forEach(q,function(a){d.style(a,{display:"none",left:0,opacity:1,top:0,zIndex:0})})});return a}return k(l,a)},panFadeDown:function(a){return k(0,a)},panFadeRight:function(a){return k(1,a)},panFadeUp:function(a){return k(2,a)},panFadeLeft:function(a){return k(3,a)}})}(dojo));