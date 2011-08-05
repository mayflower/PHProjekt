/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.store.Cache"])dojo._hasResource["dojo.store.Cache"]=!0,dojo.provide("dojo.store.Cache"),dojo.getObject("store",!0,dojo),dojo.store.Cache=function(e,d,f){f=f||{};return dojo.delegate(e,{query:function(a,b){var c=e.query(a,b);c.forEach(function(a){(!f.isLoaded||f.isLoaded(a))&&d.put(a)});return c},queryEngine:e.queryEngine||d.queryEngine,get:function(a,b){return dojo.when(d.get(a),function(c){return c||dojo.when(e.get(a,b),function(b){b&&d.put(b,{id:a});return b})})},add:function(a,
b){return dojo.when(e.add(a,b),function(c){return d.add(typeof c=="object"?c:a,b)})},put:function(a,b){d.remove(b&&b.id||this.getIdentity(a));return dojo.when(e.put(a,b),function(c){return d.put(typeof c=="object"?c:a,b)})},remove:function(a,b){return dojo.when(e.remove(a,b),function(){return d.remove(a,b)})},evict:function(a){return d.remove(a)}})};