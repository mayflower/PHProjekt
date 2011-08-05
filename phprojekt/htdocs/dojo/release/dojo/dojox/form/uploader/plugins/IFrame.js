/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.uploader.plugins.IFrame"]||(dojo._hasResource["dojox.form.uploader.plugins.IFrame"]=!0,dojo.provide("dojox.form.uploader.plugins.IFrame"),dojo.require("dojox.form.uploader.plugins.HTML5"),dojo.require("dojo.io.iframe"),dojo.declare("dojox.form.uploader.plugins.IFrame",[],{force:"",postMixInProperties:function(){this.inherited(arguments);if(!this.supports("multiple"))this.uploadType="iframe"},upload:function(a){if(!this.supports("multiple")||this.force=="iframe")this.uploadIFrame(a),
dojo.stopEvent(a)},uploadIFrame:function(){this.getUrl();dojo.io.iframe.send({url:this.getUrl(),form:this.form,handleAs:"json",error:dojo.hitch(this,function(a){console.error("HTML Upload Error:"+a.message)}),load:dojo.hitch(this,function(a){this.onComplete(a)})})}}),dojox.form.addUploaderPlugin(dojox.form.uploader.plugins.IFrame));