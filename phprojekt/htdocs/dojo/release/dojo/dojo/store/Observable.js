/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.store.Observable"])dojo._hasResource["dojo.store.Observable"]=!0,dojo.provide("dojo.store.Observable"),dojo.getObject("store",!0,dojo),dojo.store.Observable=function(c){function n(b,k){var a=c[b];a&&(c[b]=function(c){if(e)return a.apply(this,arguments);e=!0;try{return dojo.when(a.apply(this,arguments),function(b){k(typeof b=="object"&&b||c);return b})}finally{e=!1}})}var m=[],p=0;c.notify=function(b,c){p++;for(var a=m.slice(),d=0,f=a.length;d<f;d++)a[d](b,c)};var s=c.query;
c.query=function(b,k){var k=k||{},a=s.apply(this,arguments);if(a&&a.forEach){var d=dojo.mixin({},k);delete d.start;delete d.count;var f=c.queryEngine&&c.queryEngine(b,d),n=p,o=[],e;a.observe=function(b,d){o.push(b)==1&&m.push(e=function(g,e){dojo.when(a,function(a){var m=a.length!=k.count,h;if(++n!=p)throw Error("Query is out of date, you must observe() the query prior to any data modifications");var q,i=-1,j=-1;if(e){h=0;for(l=a.length;h<l;h++){var r=a[h];if(c.getIdentity(r)==e){q=r;i=h;(f||!g)&&
a.splice(h,1);break}}}if(f){if(g&&(f.matches?f.matches(g):f([g]).length))if(i>-1?a.splice(i,0,g):a.push(g),j=dojo.indexOf(f(a),g),k.start&&j==0||!m&&j==a.length-1)j=-1}else g&&(j=i>=0?i:c.defaultIndex||0);if((i>-1||j>-1)&&(d||!f||i!=j)){a=o.slice();for(h=0;b=a[h];h++)b(g||q,i,j)}})});return{cancel:function(){o.splice(dojo.indexOf(o,b),1);o.length||m.splice(dojo.indexOf(m,e),1)}}}}return a};var e;n("put",function(b){c.notify(b,c.getIdentity(b))});n("add",function(b){c.notify(b)});n("remove",function(b){c.notify(void 0,
b)});return c};