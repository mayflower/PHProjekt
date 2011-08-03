/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.numrec"]||(dojo._hasResource["dojox.lang.functional.numrec"]=!0,dojo.provide("dojox.lang.functional.numrec"),dojo.require("dojox.lang.functional.lambda"),dojo.require("dojox.lang.functional.util"),function(){var b=dojox.lang.functional,g=b.inlineLambda,h=["_r","_i"];b.numrec=function(i,d){var c,a,e={};a=function(a){e[a]=1};typeof d=="string"?a=g(d,h,a):(c=b.lambda(d),a="_a.call(this, _r, _i)");var f=b.keys(e);a=new Function(["_x"],"var _t=arguments.callee,_r=_t.t,_i".concat(f.length?
","+f.join(","):"",c?",_a=_t.a":"",";for(_i=1;_i<=_x;++_i){_r=",a,"}return _r"));a.t=i;if(c)a.a=c;return a}}());