/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.binrec"]||(dojo._hasResource["dojox.lang.functional.binrec"]=!0,dojo.provide("dojox.lang.functional.binrec"),dojo.require("dojox.lang.functional.lambda"),dojo.require("dojox.lang.functional.util"),function(){var b=dojox.lang.functional,h=b.inlineLambda,n=["_z.r","_r","_z.a"];b.binrec=function(a,f,d,g){var j,i,k,l,m={},c={},e=function(a){m[a]=1};typeof a=="string"?a=h(a,"_x",e):(j=b.lambda(a),a="_c.apply(this, _x)",c["_c=_t.c"]=1);typeof f=="string"?f=h(f,"_x",
e):(i=b.lambda(f),f="_t.apply(this, _x)");typeof d=="string"?d=h(d,"_x",e):(k=b.lambda(d),d="_b.apply(this, _x)",c["_b=_t.b"]=1);typeof g=="string"?g=h(g,n,e):(l=b.lambda(g),g="_a.call(this, _z.r, _r, _z.a)",c["_a=_t.a"]=1);e=b.keys(m);c=b.keys(c);a=new Function([],"var _x=arguments,_y,_z,_r".concat(e.length?","+e.join(","):"",c.length?",_t=_x.callee,"+c.join(","):"",i?c.length?",_t=_t.t":"_t=_x.callee.t":"",";while(!",a,"){_r=",d,";_y={p:_y,a:_r[1]};_z={p:_z,a:_x};_x=_r[0]}for(;;){do{_r=",f,';if(!_z)return _r;while("r" in _z){_r=',
g,";if(!(_z=_z.p))return _r}_z.r=_r;_x=_y.a;_y=_y.p}while(",a,");do{_r=",d,";_y={p:_y,a:_r[1]};_z={p:_z,a:_x};_x=_r[0]}while(!",a,")}"));if(j)a.c=j;if(i)a.t=i;if(k)a.b=k;if(l)a.a=l;return a}}());