/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.encoding.crypto.RSAKey"]||(dojo._hasResource["dojox.encoding.crypto.RSAKey"]=!0,dojo.provide("dojox.encoding.crypto.RSAKey"),dojo.require("dojox.math.BigInteger"),dojo.require("dojox.math.random.Simple"),dojo.experimental("dojox.encoding.crypto.RSAKey"),function(){var e=dojox.math,f=e.BigInteger,g=e.random.Simple,h=function(){return new g};dojo.declare("dojox.encoding.crypto.RSAKey",null,{constructor:function(b){this.rngf=b||h;this.e=0;this.n=this.d=this.p=this.q=this.dmp1=
this.dmq1=this.coeff=null},setPublic:function(b,a){if(b&&a&&b.length&&a.length)this.n=new f(b,16),this.e=parseInt(a,16);else throw Error("Invalid RSA public key");},encrypt:function(b){var a;a=this.n.bitLength()+7>>3;var c=this.rngf;if(a<b.length+11)throw Error("Message too long for RSA");for(var d=Array(a),e=b.length;e&&a;)d[--a]=b.charCodeAt(--e);d[--a]=0;b=c();for(c=[0];a>2;){for(c[0]=0;c[0]==0;)b.nextBytes(c);d[--a]=c[0]}d[--a]=2;d[--a]=0;b.destroy();a=new f(d);if(!a)return null;a=a.modPowInt(this.e,
this.n);if(!a)return null;a=a.toString(16);return a.length%2?"0"+a:a}})}());