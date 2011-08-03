/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.sketch._Plugin"]||(dojo._hasResource["dojox.sketch._Plugin"]=!0,dojo.provide("dojox.sketch._Plugin"),dojo.require("dijit.form.Button"),dojo.declare("dojox.sketch._Plugin",null,{constructor:function(a){a&&dojo.mixin(this,a);this._connects=[]},figure:null,iconClassPrefix:"dojoxSketchIcon",itemGroup:"toolsGroup",button:null,queryCommand:null,shape:"",useDefaultCommand:!0,buttonClass:dijit.form.ToggleButton,_initButton:function(){if(this.shape.length){var a=this.iconClassPrefix+
" "+this.iconClassPrefix+this.shape.charAt(0).toUpperCase()+this.shape.substr(1);if(!this.button)this.button=new this.buttonClass({label:this.shape,showLabel:!1,iconClass:a,dropDown:this.dropDown,tabIndex:"-1"}),this.connect(this.button,"onClick","activate")}},attr:function(a,b){return this.button.attr(a,b)},onActivate:function(){},activate:function(){this.onActivate();this.figure.setTool(this);this.attr("checked",!0)},onMouseDown:function(){},onMouseMove:function(){},onMouseUp:function(){},destroy:function(){dojo.forEach(this._connects,
dojo.disconnect)},connect:function(a,b,c){this._connects.push(dojo.connect(a,b,this,c))},setFigure:function(a){this.figure=a},setToolbar:function(a){this._initButton();this.button&&a.addChild(this.button);this.itemGroup&&a.addGroupItem(this,this.itemGroup)}}));