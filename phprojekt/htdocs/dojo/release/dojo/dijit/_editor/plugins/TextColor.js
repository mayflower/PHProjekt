/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit._editor.plugins.TextColor"]||(dojo._hasResource["dijit._editor.plugins.TextColor"]=!0,dojo.provide("dijit._editor.plugins.TextColor"),dojo.require("dijit._editor._Plugin"),dojo.require("dijit.ColorPalette"),dojo.declare("dijit._editor.plugins.TextColor",dijit._editor._Plugin,{buttonClass:dijit.form.DropDownButton,useDefaultCommand:!1,constructor:function(){this.dropDown=new dijit.ColorPalette;this.connect(this.dropDown,"onChange",function(b){this.editor.execCommand(this.command,
b)})},updateState:function(){var b=this.editor,c=this.command;if(b&&b.isLoaded&&c.length){if(this.button){var d=this.get("disabled");this.button.set("disabled",d);if(d)return;var a;try{a=b.queryCommandValue(c)||""}catch(e){a=""}}a==""&&(a="#000000");a=="transparent"&&(a="#ffffff");typeof a=="string"?a.indexOf("rgb")>-1&&(a=dojo.colorFromRgb(a).toHex()):(a=((a&255)<<16|a&65280|(a&16711680)>>>16).toString(16),a="#000000".slice(0,7-a.length)+a);a!==this.dropDown.get("value")&&this.dropDown.set("value",
a,!1)}}}),dojo.subscribe(dijit._scopeName+".Editor.getPlugin",null,function(b){if(!b.plugin)switch(b.args.name){case "foreColor":case "hiliteColor":b.plugin=new dijit._editor.plugins.TextColor({command:b.args.name})}}));