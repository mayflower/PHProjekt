/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit._editor.plugins.ToggleDir"]||(dojo._hasResource["dijit._editor.plugins.ToggleDir"]=!0,dojo.provide("dijit._editor.plugins.ToggleDir"),dojo.require("dijit._editor._Plugin"),dojo.require("dijit.form.ToggleButton"),dojo.experimental("dijit._editor.plugins.ToggleDir"),dojo.require("dijit._editor._Plugin"),dojo.require("dijit.form.ToggleButton"),dojo.declare("dijit._editor.plugins.ToggleDir",dijit._editor._Plugin,{useDefaultCommand:!1,command:"toggleDir",buttonClass:dijit.form.ToggleButton,
_initButton:function(){this.inherited(arguments);this.editor.onLoadDeferred.addCallback(dojo.hitch(this,function(){var a=this.editor.editorObject.contentWindow.document.documentElement,a=a.getElementsByTagName("body")[0];this.button.set("checked",dojo.getComputedStyle(a).direction!="ltr");this.connect(this.button,"onChange","_setRtl")}))},updateState:function(){this.button.set("disabled",this.get("disabled"))},_setRtl:function(a){var b="ltr";a&&(b="rtl");a=this.editor.editorObject.contentWindow.document.documentElement;
a=a.getElementsByTagName("body")[0];a.dir=b}}),dojo.subscribe(dijit._scopeName+".Editor.getPlugin",null,function(a){if(!a.plugin)switch(a.args.name){case "toggleDir":a.plugin=new dijit._editor.plugins.ToggleDir({command:a.args.name})}}));