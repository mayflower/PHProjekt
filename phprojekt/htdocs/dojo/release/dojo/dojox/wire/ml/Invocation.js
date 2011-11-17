/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.wire.ml.Invocation"]||(dojo._hasResource["dojox.wire.ml.Invocation"]=!0,dojo.provide("dojox.wire.ml.Invocation"),dojo.require("dojox.wire.ml.Action"),dojo.declare("dojox.wire.ml.Invocation",dojox.wire.ml.Action,{object:"",method:"",topic:"",parameters:"",result:"",error:"",_run:function(){if(this.topic){var a=this._getParameters(arguments);try{dojo.publish(this.topic,a),this.onComplete()}catch(c){this.onError(c)}}else if(this.method){var b=this.object?dojox.wire.ml._getValue(this.object):
dojo.global;if(b){var a=this._getParameters(arguments),d=b[this.method];if(!d){d=b.callMethod;if(!d)return;a=[this.method,a]}try{var g=!1;if(b.getFeatures){var h=b.getFeatures();if(this.method=="fetch"&&h["dojo.data.api.Read"]||this.method=="save"&&h["dojo.data.api.Write"]){var e=a[0];if(!e.onComplete)e.onComplete=function(){};this.connect(e,"onComplete","onComplete");if(!e.onError)e.onError=function(){};this.connect(e,"onError","onError");g=!0}}var f=d.apply(b,a);if(!g)if(f&&f instanceof dojo.Deferred){var i=
this;f.addCallbacks(function(a){i.onComplete(a)},function(a){i.onError(a)})}else this.onComplete(f)}catch(j){this.onError(j)}}}},onComplete:function(a){this.result&&dojox.wire.ml._setValue(this.result,a);this.error&&dojox.wire.ml._setValue(this.error,"")},onError:function(a){if(this.error){if(a&&a.message)a=a.message;dojox.wire.ml._setValue(this.error,a)}},_getParameters:function(a){if(!this.parameters)return a;var c=[],b=this.parameters.split(",");if(b.length==1)a=dojox.wire.ml._getValue(dojo.trim(b[0]),
a),dojo.isArray(a)?c=a:c.push(a);else for(var d in b)c.push(dojox.wire.ml._getValue(dojo.trim(b[d]),a));return c}}));