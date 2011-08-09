/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.rotator.Pan"]||(dojo._hasResource["dojox.widget.rotator.Pan"]=!0,dojo.provide("dojox.widget.rotator.Pan"),dojo.require("dojo.fx"),function(d){function k(a,b){var i=b.next.node,e=b.rotatorBox,g=a%2,c=g?"left":"top",e=(g?e.w:e.h)*(a<2?-1:1),g={},f={};d.style(i,"display","");g[c]={start:0,end:-e};f[c]={start:e,end:0};return d.fx.combine([d.animateProperty({node:b.current.node,duration:b.duration,properties:g,easing:b.easing}),d.animateProperty({node:i,duration:b.duration,
properties:f,easing:b.easing})])}function o(a,b){d.style(a,"zIndex",b)}d.mixin(dojox.widget.rotator,{pan:function(a){var b=a.wrap,i=a.rotator.panes,e=i.length,g=e,c=a.current.idx,f=a.next.idx,h=Math.abs(f-c),j=Math.abs(e-Math.max(c,f)+Math.min(c,f))%e,m=c<f,l=3,p=[],q=[],n=a.duration;if(!b&&!m||b&&(m&&h>j||!m&&h<j))l=1;if(a.continuous){a.quick&&(n=Math.round(n/(b?Math.min(j,h):h)));o(i[c].node,g--);for(b=l==3;;){h=c;b?++c>=e&&(c=0):--c<0&&(c=e-1);h=i[h];j=i[c];o(j.node,g--);p.push(k(l,d.mixin({easing:function(a){return a}},
a,{current:h,next:j,duration:n})));if(b&&c==f||!b&&c==f)break;q.push(j.node)}var a=d.fx.chain(p),r=d.connect(a,"onEnd",function(){d.disconnect(r);d.forEach(q,function(a){d.style(a,{display:"none",left:0,opacity:1,top:0,zIndex:0})})});return a}return k(l,a)},panDown:function(a){return k(0,a)},panRight:function(a){return k(1,a)},panUp:function(a){return k(2,a)},panLeft:function(a){return k(3,a)}})}(dojo));