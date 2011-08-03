/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.lang.functional.lambda"]||(dojo._hasResource["dojox.lang.functional.lambda"]=!0,dojo.provide("dojox.lang.functional.lambda"),function(){var e=dojox.lang.functional,d={},g="ab".split(/a*/).length>1?String.prototype.split:function(a){var b=this.split.call(this,a);(a=a.exec(this))&&a.index==0&&b.unshift("");return b},f=function(a){var b=[],c=g.call(a,/\s*->\s*/m);if(c.length>1)for(;c.length;)a=c.pop(),b=c.pop().split(/\s*,\s*|\s+/m),c.length&&c.push("(function("+b+"){return ("+
a+")})");else if(a.match(/\b_\b/))b=["_"];else{var c=a.match(/^\s*(?:[+*\/%&|\^\.=<>]|!=)/m),d=a.match(/[+\-*\/%&|\^\.=<>!]\s*$/m);if(c||d)c&&(b.push("$1"),a="$1"+a),d&&(b.push("$2"),a+="$2");else{var c=a.replace(/(?:\b[A-Z]|\.[a-zA-Z_$])[a-zA-Z_$\d]*|[a-zA-Z_$][a-zA-Z_$\d]*:|this|true|false|null|undefined|typeof|instanceof|in|delete|new|void|arguments|decodeURI|decodeURIComponent|encodeURI|encodeURIComponent|escape|eval|isFinite|isNaN|parseFloat|parseInt|unescape|dojo|dijit|dojox|window|document|'(?:[^'\\]|\\.)*'|"(?:[^"\\]|\\.)*"/g,
"").match(/([a-z_$][a-z_$\d]*)/gi)||[],e={};dojo.forEach(c,function(a){a in e||(b.push(a),e[a]=1)})}}return{args:b,body:a}},h=function(a){return a.length?function(){var b=a.length-1,c=e.lambda(a[b]).apply(this,arguments);for(--b;b>=0;--b)c=e.lambda(a[b]).call(this,c);return c}:function(a){return a}};dojo.mixin(e,{rawLambda:function(a){return f(a)},buildLambda:function(a){a=f(a);return"function("+a.args.join(",")+"){return ("+a.body+");}"},lambda:function(a){if(typeof a=="function")return a;if(a instanceof
Array)return h(a);if(a in d)return d[a];a=f(a);return d[a]=new Function(a.args,"return ("+a.body+");")},clearLambdaCache:function(){d={}}})}());