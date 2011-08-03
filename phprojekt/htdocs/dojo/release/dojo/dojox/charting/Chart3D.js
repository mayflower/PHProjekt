/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.Chart3D"]||(dojo._hasResource["dojox.charting.Chart3D"]=!0,dojo.provide("dojox.charting.Chart3D"),dojo.require("dojox.gfx3d"),function(){var f={x:0,y:0,z:1},g=dojox.gfx3d.vector,e=dojox.gfx.normalizedLength;dojo.declare("dojox.charting.Chart3D",null,{constructor:function(a,c,b,d){this.node=dojo.byId(a);this.surface=dojox.gfx.createSurface(this.node,e(this.node.style.width),e(this.node.style.height));this.view=this.surface.createViewport();this.view.setLights(c.lights,
c.ambient,c.specular);this.view.setCameraTransform(b);this.theme=d;this.walls=[];this.plots=[]},generate:function(){return this._generateWalls()._generatePlots()},invalidate:function(){this.view.invalidate();return this},render:function(){this.view.render();return this},addPlot:function(a){return this._add(this.plots,a)},removePlot:function(a){return this._remove(this.plots,a)},addWall:function(a){return this._add(this.walls,a)},removeWall:function(a){return this._remove(this.walls,a)},_add:function(a,
c){dojo.some(a,function(a){return a==c})||(a.push(c),this.view.invalidate());return this},_remove:function(a,c){var b=dojo.filter(a,function(a){return a!=c});return b.length<a.length?(a=b,this.invalidate()):this},_generateWalls:function(){for(var a=0;a<this.walls.length;++a)g.dotProduct(f,this.walls[a].normal)>0&&this.walls[a].generate(this);return this},_generatePlots:function(){for(var a=0,c=dojox.gfx3d.matrix,b=0;b<this.plots.length;++b)a+=this.plots[b].getDepth();for(--b;b>=0;--b){var d=this.view.createScene();
d.setTransform(c.translate(0,0,-a));this.plots[b].generate(this,d);a-=this.plots[b].getDepth()}return this}})}());