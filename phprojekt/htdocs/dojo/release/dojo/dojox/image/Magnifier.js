/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.image.Magnifier"]||(dojo._hasResource["dojox.image.Magnifier"]=!0,dojo.provide("dojox.image.Magnifier"),dojo.require("dojox.gfx"),dojo.require("dojox.image.MagnifierLite"),dojo.declare("dojox.image.Magnifier",dojox.image.MagnifierLite,{_createGlass:function(){this.glassNode=dojo.create("div",{style:{height:this.glassSize+"px",width:this.glassSize+"px"},className:"glassNode"},dojo.body());this.surfaceNode=dojo.create("div",null,this.glassNode);this.surface=dojox.gfx.createSurface(this.surfaceNode,
this.glassSize,this.glassSize);this.img=this.surface.createImage({src:this.domNode.src,width:this._zoomSize.w,height:this._zoomSize.h})},_placeGlass:function(a){var b=a.pageX-2,c=a.pageY-2,d=this.offset.x+this.offset.w+2,e=this.offset.y+this.offset.h+2;b<this.offset.x||c<this.offset.y||b>d||c>e?this._hideGlass():this.inherited(arguments)},_setImage:function(a){var b=(a.pageX-this.offset.l)/this.offset.w,a=(a.pageY-this.offset.t)/this.offset.h;this.img.setShape({x:this._zoomSize.w*b*-1+this.glassSize*
b,y:this._zoomSize.h*a*-1+this.glassSize*a})}}));