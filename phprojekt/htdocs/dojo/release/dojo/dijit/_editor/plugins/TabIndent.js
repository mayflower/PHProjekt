/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit._editor.plugins.TabIndent"]||(dojo._hasResource["dijit._editor.plugins.TabIndent"]=!0,dojo.provide("dijit._editor.plugins.TabIndent"),dojo.require("dijit._editor._Plugin"),dojo.require("dijit.form.ToggleButton"),dojo.experimental("dijit._editor.plugins.TabIndent"),dojo.declare("dijit._editor.plugins.TabIndent",dijit._editor._Plugin,{useDefaultCommand:!1,buttonClass:dijit.form.ToggleButton,command:"tabIndent",_initButton:function(){this.inherited(arguments);var a=this.editor;
this.connect(this.button,"onChange",function(b){a.set("isTabIndent",b)});this.updateState()},updateState:function(){var a=this.get("disabled");this.button.set("disabled",a);a||this.button.set("checked",this.editor.isTabIndent,!1)}}),dojo.subscribe(dijit._scopeName+".Editor.getPlugin",null,function(a){if(!a.plugin)switch(a.args.name){case "tabIndent":a.plugin=new dijit._editor.plugins.TabIndent({command:a.args.name})}}));