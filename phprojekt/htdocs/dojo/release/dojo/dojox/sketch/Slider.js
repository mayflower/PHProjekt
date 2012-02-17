/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.sketch.Slider"]||(dojo._hasResource["dojox.sketch.Slider"]=!0,dojo.provide("dojox.sketch.Slider"),dojo.require("dijit.form.HorizontalSlider"),dojo.declare("dojox.sketch.Slider",dojox.sketch._Plugin,{_initButton:function(){this.slider=new dijit.form.HorizontalSlider({minimum:5,maximum:100,style:"width:100px;",baseClass:"dijitInline dijitSlider"});this.slider._movable.node.title='Double Click to "Zoom to Fit"';this.connect(this.slider,"onChange","_setZoom");this.connect(this.slider.sliderHandle,
"ondblclick","_zoomToFit")},_zoomToFit:function(){var a=this.figure.getFit();this.slider.attr("value",this.slider.maximum<a?this.slider.maximum:this.slider.minimum>a?this.slider.minimum:a)},_setZoom:function(a){a&&this.figure&&this.figure.zoom(a)},reset:function(){this.slider.attr("value",this.slider.maximum);this._zoomToFit()},setToolbar:function(a){this._initButton();a.addChild(this.slider);if(!a._reset2Zoom)a._reset2Zoom=!0,this.connect(a,"reset","reset")}}),dojox.sketch.registerTool("Slider",
dojox.sketch.Slider));