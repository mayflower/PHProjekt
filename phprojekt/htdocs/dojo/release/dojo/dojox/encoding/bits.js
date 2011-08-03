/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.encoding.bits"])dojo._hasResource["dojox.encoding.bits"]=!0,dojo.provide("dojox.encoding.bits"),dojo.getObject("encoding.bits",!0,dojox),dojox.encoding.bits.OutputStream=function(){this.reset()},dojo.extend(dojox.encoding.bits.OutputStream,{reset:function(){this.buffer=[];this.accumulator=0;this.available=8},putBits:function(a,b){for(;b;){var c=Math.min(b,this.available);this.accumulator|=(c<=b?a>>>b-c:a)<<this.available-c&255>>>8-this.available;this.available-=c;if(!this.available)this.buffer.push(this.accumulator),
this.accumulator=0,this.available=8;b-=c}},getWidth:function(){return this.buffer.length*8+(8-this.available)},getBuffer:function(){var a=this.buffer;this.available<8&&a.push(this.accumulator&255<<this.available);this.reset();return a}}),dojox.encoding.bits.InputStream=function(a,b){this.buffer=a;this.width=b;this.bbyte=this.bit=0},dojo.extend(dojox.encoding.bits.InputStream,{getBits:function(a){for(var b=0;a;){var c=Math.min(a,8-this.bit),d=this.buffer[this.bbyte]>>>8-this.bit-c;b<<=c;b|=d&~(-1<<
c);this.bit+=c;if(this.bit==8)++this.bbyte,this.bit=0;a-=c}return b},getWidth:function(){return this.width-this.bbyte*8-this.bit}});