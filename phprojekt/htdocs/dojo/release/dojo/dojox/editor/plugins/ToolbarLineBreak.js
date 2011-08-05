/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.editor.plugins.ToolbarLineBreak"]||(dojo._hasResource["dojox.editor.plugins.ToolbarLineBreak"]=!0,dojo.provide("dojox.editor.plugins.ToolbarLineBreak"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.require("dijit._editor._Plugin"),dojo.require("dijit._Widget"),dojo.require("dijit._Templated"),dojo.require("dijit._editor._Plugin"),dojo.declare("dojox.editor.plugins.ToolbarLineBreak",[dijit._Widget,dijit._Templated],{templateString:"<span class='dijit dijitReset'><br></span>",
postCreate:function(){dojo.setSelectable(this.domNode,!1)},isFocusable:function(){return!1}}),dojo.subscribe(dijit._scopeName+".Editor.getPlugin",null,function(a){if(!a.plugin){var b=a.args.name.toLowerCase();if(b==="||"||b==="toolbarlinebreak")a.plugin=new dijit._editor._Plugin({button:new dojox.editor.plugins.ToolbarLineBreak,setEditor:function(a){this.editor=a}})}}));