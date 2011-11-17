/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.encoding.compression.lzw"]||(dojo._hasResource["dojox.encoding.compression.lzw"]=!0,dojo.provide("dojox.encoding.compression.lzw"),dojo.require("dojox.encoding.bits"),dojo.getObject("encoding.compression.lzw",!0,dojox),function(){var f=function(a){for(var b=1,c=2;a>=c;c<<=1,++b);return b};dojox.encoding.compression.lzw.Encoder=function(a){this.size=a;this.init()};dojo.extend(dojox.encoding.compression.lzw.Encoder,{init:function(){this.dict={};for(var a=0;a<this.size;++a)this.dict[String.fromCharCode(a)]=
a;this.width=f(this.code=this.size);this.p=""},encode:function(a,b){var c=String.fromCharCode(a),d=this.p+c,e=0;if(d in this.dict)return this.p=d,e;b.putBits(this.dict[this.p],this.width);(this.code&this.code+1)==0&&b.putBits(this.code++,e=this.width++);this.dict[d]=this.code++;this.p=c;return e+this.width},flush:function(a){if(this.p.length==0)return 0;a.putBits(this.dict[this.p],this.width);this.p="";return this.width}});dojox.encoding.compression.lzw.Decoder=function(a){this.size=a;this.init()};
dojo.extend(dojox.encoding.compression.lzw.Decoder,{init:function(){this.codes=Array(this.size);for(var a=0;a<this.size;++a)this.codes[a]=String.fromCharCode(a);this.width=f(this.size);this.p=-1},decode:function(a){var a=a.getBits(this.width),b;if(a<this.codes.length)b=this.codes[a],this.p>=0&&this.codes.push(this.codes[this.p]+b.substr(0,1));else{if((a&a+1)==0)return this.codes.push(""),++this.width,"";b=this.codes[this.p];b+=b.substr(0,1);this.codes.push(b)}this.p=a;return b}})}());