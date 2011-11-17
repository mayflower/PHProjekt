/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.geo.charting._Marker"]||(dojo._hasResource["dojox.geo.charting._Marker"]=!0,dojo.provide("dojox.geo.charting._Marker"),dojo.declare("dojox.geo.charting._Marker",null,{constructor:function(a,b){this.features=b.mapObj.features;this.markerData=a},show:function(a){this.markerText=this.features[a].markerText||this.markerData[a]||a;this.currentFeature=this.features[a];dojox.geo.charting.showTooltip(this.markerText,this.currentFeature.shape,"before")},hide:function(){dojox.geo.charting.hideTooltip(this.currentFeature.shape)},
_getGroupBoundingBox:function(a){var a=a.children,b=a[0].getBoundingBox();this._arround=dojo.clone(b);dojo.forEach(a,function(a){a=a.getBoundingBox();this._arround.x=Math.min(this._arround.x,a.x);this._arround.y=Math.min(this._arround.y,a.y)},this)},_toWindowCoords:function(a,b,c){var d=(a.x-this.topLeft[0])*this.scale,e=(a.y-this.topLeft[1])*this.scale;dojo.isFF==3.5?(a.x=b.x,a.y=b.y):dojo.isChrome?(a.x=c.x+d,a.y=c.y+e):(a.x=b.x+d,a.y=b.y+e);a.width=this.currentFeature._bbox[2]*this.scale;a.height=
this.currentFeature._bbox[3]*this.scale;a.x+=a.width/6;a.y+=a.height/4}}));