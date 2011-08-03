/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.themes.Chris"]||(dojo._hasResource["dojox.charting.themes.Chris"]=!0,dojo.provide("dojox.charting.themes.Chris"),dojo.require("dojox.gfx.gradutils"),dojo.require("dojox.charting.Theme"),function(){var g=dojox.charting,h=g.themes,e=g.Theme,a=e.generateGradient,c={type:"linear",space:"shape",x1:0,y1:0,x2:0,y2:100};h.Chris=new g.Theme({chart:{fill:"#c1c1c1",stroke:{color:"#666"}},plotarea:{fill:"#c1c1c1"},series:{stroke:{width:2,color:"white"},outline:null,fontColor:"#333"},
marker:{stroke:{width:2,color:"white"},outline:{width:2,color:"white"},fontColor:"#333"},seriesThemes:[{fill:a(c,"#01b717","#238c01")},{fill:a(c,"#d04918","#7c0344")},{fill:a(c,"#0005ec","#002578")},{fill:a(c,"#f9e500","#786f00")},{fill:a(c,"#e27d00","#773e00")},{fill:a(c,"#00b5b0","#005f5d")},{fill:a(c,"#ac00cb","#590060")}],markerThemes:[{fill:"#01b717",stroke:{color:"#238c01"}},{fill:"#d04918",stroke:{color:"#7c0344"}},{fill:"#0005ec",stroke:{color:"#002578"}},{fill:"#f9e500",stroke:{color:"#786f00"}},
{fill:"#e27d00",stroke:{color:"#773e00"}},{fill:"#00b5b0",stroke:{color:"#005f5d"}},{fill:"#ac00cb",stroke:{color:"#590060"}}]});h.Chris.next=function(b,a,c){var f=b=="line";if(f||b=="area"){var d=this.seriesThemes[this._current%this.seriesThemes.length];d.fill.space="plot";if(f)d.stroke={color:d.fill.colors[1].color},d.outline={width:2,color:"white"};f=e.prototype.next.apply(this,arguments);delete d.outline;delete d.stroke;d.fill.space="shape";return f}return e.prototype.next.apply(this,arguments)};
h.Chris.post=function(b,a){b=e.prototype.post.apply(this,arguments);if((a=="slice"||a=="circle")&&b.series.fill&&b.series.fill.type=="radial")b.series.fill=dojox.gfx.gradutils.reverse(b.series.fill);return b}}());