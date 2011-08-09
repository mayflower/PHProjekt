/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.tailrec"]||(dojo._hasResource["dojox.lang.functional.tailrec"]=!0,dojo.provide("dojox.lang.functional.tailrec"),dojo.require("dojox.lang.functional.lambda"),dojo.require("dojox.lang.functional.util"),function(){var b=dojox.lang.functional,h=b.inlineLambda;b.tailrec=function(a,c,d){var i,g,j,k={},e={},f=function(a){k[a]=1};typeof a=="string"?a=h(a,"_x",f):(i=b.lambda(a),a="_c.apply(this, _x)",e["_c=_t.c"]=1);typeof c=="string"?c=h(c,"_x",f):(g=b.lambda(c),c=
"_t.t.apply(this, _x)");typeof d=="string"?d=h(d,"_x",f):(j=b.lambda(d),d="_b.apply(this, _x)",e["_b=_t.b"]=1);f=b.keys(k);e=b.keys(e);a=new Function([],"var _x=arguments,_t=_x.callee,_c=_t.c,_b=_t.b".concat(f.length?","+f.join(","):"",e.length?",_t=_x.callee,"+e.join(","):g?",_t=_x.callee":"",";for(;!",a,";_x=",d,");return ",c));if(i)a.c=i;if(g)a.t=g;if(j)a.b=j;return a}}());