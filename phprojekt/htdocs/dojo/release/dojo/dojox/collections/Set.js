/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.collections.Set"]||(dojo._hasResource["dojox.collections.Set"]=!0,dojo.provide("dojox.collections.Set"),dojo.require("dojox.collections.ArrayList"),function(){dojox.collections.Set=new function(){function d(a){return a.constructor==Array?new dojox.collections.ArrayList(a):a}this.union=function(a,b){for(var a=d(a),b=d(b),c=new dojox.collections.ArrayList(a.toArray()),e=b.getIterator();!e.atEnd();){var f=e.get();c.contains(f)||c.add(f)}return c};this.intersection=function(a,
b){for(var a=d(a),b=d(b),c=new dojox.collections.ArrayList,e=b.getIterator();!e.atEnd();){var f=e.get();a.contains(f)&&c.add(f)}return c};this.difference=function(a,b){for(var a=d(a),b=d(b),c=new dojox.collections.ArrayList,e=a.getIterator();!e.atEnd();){var f=e.get();b.contains(f)||c.add(f)}return c};this.isSubSet=function(a,b){for(var a=d(a),b=d(b),c=a.getIterator();!c.atEnd();)if(!b.contains(c.get()))return!1;return!0};this.isSuperSet=function(a,b){for(var a=d(a),b=d(b),c=b.getIterator();!c.atEnd();)if(!a.contains(c.get()))return!1;
return!0}}}());