/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.drawing.ui.Tooltip"]||(dojo._hasResource["dojox.drawing.ui.Tooltip"]=!0,dojo.provide("dojox.drawing.ui.Tooltip"),dojo.require("dojox.drawing.plugins._Plugin"),function(){var g=null,h=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(){this.createDom()},{show:function(a,d){this.domNode.innerHTML=d;var b=a.data.x+a.data.width+this.mouse.origin.x+30,c=a.data.y+a.data.height+this.mouse.origin.y+30;dojo.style(this.domNode,{display:"inline",left:b+"px",top:c+
"px"});var f=dojo.marginBox(this.domNode);this.createShape(b-this.mouse.origin.x,c-this.mouse.origin.y,f.w,f.h)},createShape:function(a,d,b,c){this.balloon&&this.balloon.destroy();var b=a+b,c=d+c,f=[],e=function(){for(var a=0;a<arguments.length;a++)f.push(arguments[a])};e({x:a,y:d+5},{t:"Q",x:a,y:d},{x:a+5,y:d});e({t:"L",x:b-5,y:d});e({t:"Q",x:b,y:d},{x:b,y:d+5});e({t:"L",x:b,y:c-5});e({t:"Q",x:b,y:c},{x:b-5,y:c});e({t:"L",x:a+5,y:c});e({t:"Q",x:a,y:c},{x:a,y:c-5});e({t:"L",x:a,y:d+5});this.balloon=
this.drawing.addUI("path",{points:f})},createDom:function(){this.domNode=dojo.create("span",{"class":"drawingTooltip"},document.body);dojo.style(this.domNode,{display:"none",position:"absolute"})}});dojox.drawing.ui.Tooltip=dojox.drawing.util.oo.declare(dojox.drawing.plugins._Plugin,function(a){g||(g=new h(a));!a.stencil&&this.button&&(this.connect(this.button,"onOver",this,"onOver"),this.connect(this.button,"onOut",this,"onOut"))},{width:300,height:200,onOver:function(){g.show(this.button,this.data.text)},
onOut:function(){}});dojox.drawing.register({name:"dojox.drawing.ui.Tooltip"},"stencil")}());