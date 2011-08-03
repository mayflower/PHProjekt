/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.form.manager._FormMixin"]||(dojo._hasResource["dojox.form.manager._FormMixin"]=!0,dojo.provide("dojox.form.manager._FormMixin"),dojo.require("dojox.form.manager._Mixin"),function(){var e=dojox.form.manager.actionAdapter;dojo.declare("dojox.form.manager._FormMixin",null,{name:"",action:"",method:"",encType:"","accept-charset":"",accept:"",target:"",startup:function(){if(this.isForm=this.domNode.tagName.toLowerCase()=="form")this.connect(this.domNode,"onreset","_onReset"),this.connect(this.domNode,
"onsubmit","_onSubmit");this.inherited(arguments)},_onReset:function(a){var b={returnValue:!0,preventDefault:function(){this.returnValue=!1},stopPropagation:function(){},currentTarget:a.currentTarget,target:a.target};this.onReset(b)!==!1&&b.returnValue&&this.reset();dojo.stopEvent(a);return!1},onReset:function(){return!0},reset:function(){this.inspectFormWidgets(e(function(a,b){b.reset&&b.reset()}));this.isForm&&this.domNode.reset();return this},_onSubmit:function(a){this.onSubmit(a)===!1&&dojo.stopEvent(a)},
onSubmit:function(){return this.isValid()},submit:function(){this.isForm&&this.onSubmit()!==!1&&this.domNode.submit()},isValid:function(){for(var a in this.formWidgets){var b=!1;e(function(a,c){!c.get("disabled")&&c.isValid&&!c.isValid()&&(b=!0)}).call(this,null,this.formWidgets[a].widget);if(b)return!1}return!0},validate:function(){var a=!0,b=this.formWidgets,f=!1,c;for(c in b)e(function(b,d){d._hasBeenBlurred=!0;var c=d.disabled||!d.validate||d.validate();!c&&!f&&(dojo.window.scrollIntoView(d.containerNode||
d.domNode),d.focus(),f=!0);a=a&&c}).call(this,null,b[c].widget);return a}})}());