/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.editor.plugins.Save"]||(dojo._hasResource["dojox.editor.plugins.Save"]=!0,dojo.provide("dojox.editor.plugins.Save"),dojo.require("dijit.form.Button"),dojo.require("dijit._editor._Plugin"),dojo.require("dojo.i18n"),dojo.requireLocalization("dojox.editor.plugins","Save",null,"ROOT,ar,ca,cs,da,de,el,es,fi,fr,he,hu,it,ja,kk,ko,nb,nl,pl,pt,pt-pt,ro,ru,sk,sl,sv,th,tr,zh,zh-tw"),dojo.declare("dojox.editor.plugins.Save",dijit._editor._Plugin,{iconClassPrefix:"dijitAdditionalEditorIcon",
url:"",logResults:!0,_initButton:function(){var a=dojo.i18n.getLocalization("dojox.editor.plugins","Save");this.button=new dijit.form.Button({label:a.save,showLabel:!1,iconClass:this.iconClassPrefix+" "+this.iconClassPrefix+"Save",tabIndex:"-1",onClick:dojo.hitch(this,"_save")})},updateState:function(){this.button.set("disabled",this.get("disabled"))},setEditor:function(a){this.editor=a;this._initButton()},_save:function(){this.save(this.editor.get("value"))},save:function(a){var b={"Content-Type":"text/html"};
this.url?(a={url:this.url,postData:a,headers:b,handleAs:"text"},this.button.set("disabled",!0),a=dojo.xhrPost(a),a.addCallback(dojo.hitch(this,this.onSuccess)),a.addErrback(dojo.hitch(this,this.onError))):console.log("No URL provided, no post-back of content: "+a)},onSuccess:function(a){this.button.set("disabled",!1);this.logResults&&console.log(a)},onError:function(a){this.button.set("disabled",!1);this.logResults&&console.log(a)}}),dojo.subscribe(dijit._scopeName+".Editor.getPlugin",null,function(a){if(!a.plugin&&
a.args.name.toLowerCase()==="save")a.plugin=new dojox.editor.plugins.Save({url:"url"in a.args?a.args.url:"",logResults:"logResults"in a.args?a.args.logResults:!0})}));