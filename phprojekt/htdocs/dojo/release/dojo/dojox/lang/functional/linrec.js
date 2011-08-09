/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.linrec"]||(dojo._hasResource["dojox.lang.functional.linrec"]=!0,dojo.provide("dojox.lang.functional.linrec"),dojo.require("dojox.lang.functional.lambda"),dojo.require("dojox.lang.functional.util"),function(){var b=dojox.lang.functional,h=b.inlineLambda,n=["_r","_y.a"];b.linrec=function(a,e,f,g){var j,i,k,l,m={},c={},d=function(a){m[a]=1};typeof a=="string"?a=h(a,"_x",d):(j=b.lambda(a),a="_c.apply(this, _x)",c["_c=_t.c"]=1);typeof e=="string"?e=h(e,"_x",d):
(i=b.lambda(e),e="_t.t.apply(this, _x)");typeof f=="string"?f=h(f,"_x",d):(k=b.lambda(f),f="_b.apply(this, _x)",c["_b=_t.b"]=1);typeof g=="string"?g=h(g,n,d):(l=b.lambda(g),g="_a.call(this, _r, _y.a)",c["_a=_t.a"]=1);d=b.keys(m);c=b.keys(c);a=new Function([],"var _x=arguments,_y,_r".concat(d.length?","+d.join(","):"",c.length?",_t=_x.callee,"+c.join(","):i?",_t=_x.callee":"",";for(;!",a,";_x=",f,"){_y={p:_y,a:_x}}_r=",e,";for(;_y;_y=_y.p){_r=",g,"}return _r"));if(j)a.c=j;if(i)a.t=i;if(k)a.b=k;if(l)a.a=
l;return a}}());