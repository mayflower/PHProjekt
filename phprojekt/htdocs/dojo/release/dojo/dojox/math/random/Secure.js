/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.math.random.Secure"]||(dojo._hasResource["dojox.math.random.Secure"]=!0,dojo.provide("dojox.math.random.Secure"),dojo.declare("dojox.math.random.Secure",null,{constructor:function(b,c){this.prng=b;for(var a=this.pool=Array(b.size),d=this.pptr=0,e=b.size;d<e;){var f=Math.floor(65536*Math.random());a[d++]=f>>>8;a[d++]=f&255}this.seedTime();if(!c)this.h=[dojo.connect(dojo.body(),"onclick",this,"seedTime"),dojo.connect(dojo.body(),"onkeypress",this,"seedTime")]},destroy:function(){this.h&&
dojo.forEach(this.h,dojo.disconnect)},nextBytes:function(b){var c=this.state;if(!c){this.seedTime();c=this.state=this.prng();c.init(this.pool);for(var a=this.pool,d=0,e=a.length;d<e;a[d++]=0);this.pptr=0}d=0;for(e=b.length;d<e;++d)b[d]=c.next()},seedTime:function(){this._seed_int((new Date).getTime())},_seed_int:function(b){var c=this.pool,a=this.pptr;c[a++]^=b&255;c[a++]^=b>>8&255;c[a++]^=b>>16&255;c[a++]^=b>>24&255;a>=this.prng.size&&(a-=this.prng.size);this.pptr=a}}));