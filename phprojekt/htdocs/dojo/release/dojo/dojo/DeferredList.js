/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojo.DeferredList"])dojo._hasResource["dojo.DeferredList"]=!0,dojo.provide("dojo.DeferredList"),dojo.DeferredList=function(a,b,c,i){var e=[];dojo.Deferred.call(this);var d=this;a.length===0&&!b&&this.resolve([0,[]]);var f=0;dojo.forEach(a,function(j,g){function h(k,b){e[g]=[k,b];f++;f===a.length&&d.resolve(e)}j.then(function(a){b?d.resolve([g,a]):h(!0,a)},function(a){c?d.reject(a):h(!1,a);if(i)return null;throw a;})})},dojo.DeferredList.prototype=new dojo.Deferred,dojo.DeferredList.prototype.gatherResults=
function(a){a=new dojo.DeferredList(a,!1,!0,!1);a.addCallback(function(a){var c=[];dojo.forEach(a,function(a){c.push(a[1])});return c});return a};