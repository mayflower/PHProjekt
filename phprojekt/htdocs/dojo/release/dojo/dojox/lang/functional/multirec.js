/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.multirec"]||(dojo._hasResource["dojox.lang.functional.multirec"]=!0,dojo.provide("dojox.lang.functional.multirec"),dojo.require("dojox.lang.functional.lambda"),dojo.require("dojox.lang.functional.util"),function(){var b=dojox.lang.functional,h=b.inlineLambda,n=["_y.r","_y.o"];b.multirec=function(a,e,f,g){var j,i,k,l,m={},c={},d=function(a){m[a]=1};typeof a=="string"?a=h(a,"_x",d):(j=b.lambda(a),a="_c.apply(this, _x)",c["_c=_t.c"]=1);typeof e=="string"?e=h(e,
"_x",d):(i=b.lambda(e),e="_t.apply(this, _x)");typeof f=="string"?f=h(f,"_x",d):(k=b.lambda(f),f="_b.apply(this, _x)",c["_b=_t.b"]=1);typeof g=="string"?g=h(g,n,d):(l=b.lambda(g),g="_a.call(this, _y.r, _y.o)",c["_a=_t.a"]=1);d=b.keys(m);c=b.keys(c);a=new Function([],"var _y={a:arguments},_x,_r,_z,_i".concat(d.length?","+d.join(","):"",c.length?",_t=arguments.callee,"+c.join(","):"",i?c.length?",_t=_t.t":"_t=arguments.callee.t":"",";for(;;){for(;;){if(_y.o){_r=",g,";break}_x=_y.a;if(",a,"){_r=",e,
";break}_y.o=_x;_x=",f,";_y.r=[];_z=_y;for(_i=_x.length-1;_i>=0;--_i){_y={p:_y,a:_x[_i],z:_z}}}if(!(_z=_y.z)){return _r}_z.r.push(_r);_y=_y.p}"));if(j)a.c=j;if(i)a.t=i;if(k)a.b=k;if(l)a.a=l;return a}}());