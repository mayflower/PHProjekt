/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.aspect.cflow"]||(dojo._hasResource["dojox.lang.aspect.cflow"]=!0,dojo.provide("dojox.lang.aspect.cflow"),function(){var f=dojox.lang.aspect;f.cflow=function(g,a){arguments.length>1&&!(a instanceof Array)&&(a=[a]);for(var h=f.getContextStack(),c=h.length-1;c>=0;--c){var b=h[c];if(!(g&&b.instance!=g)){if(!a)return!0;for(var b=b.joinPoint.targetName,d=a.length-1;d>=0;--d){var e=a[d];if(e instanceof RegExp){if(e.test(b))return!0}else if(b==e)return!0}}}return!1}}());