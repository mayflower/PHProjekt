/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.charting.axis2d.common"]||(dojo._hasResource["dojox.charting.axis2d.common"]=!0,dojo.provide("dojox.charting.axis2d.common"),dojo.require("dojox.gfx"),function(){var l=dojox.gfx,m=function(b){b.marginLeft="0px";b.marginTop="0px";b.marginRight="0px";b.marginBottom="0px";b.paddingLeft="0px";b.paddingTop="0px";b.paddingRight="0px";b.paddingBottom="0px";b.borderLeftWidth="0px";b.borderTopWidth="0px";b.borderRightWidth="0px";b.borderBottomWidth="0px"};dojo.mixin(dojox.charting.axis2d.common,
{createText:{gfx:function(b,d,c,f,i,g,j,k){return d.createText({x:c,y:f,text:g,align:i}).setFont(j).setFill(k)},html:function(b,d,c,f,i,g,j,k,h){var d=dojo.doc.createElement("div"),a=d.style,e;m(a);a.font=j;d.innerHTML=String(g).replace(/\s/g,"&nbsp;");a.color=k;a.position="absolute";a.left="-10000px";dojo.body().appendChild(d);g=l.normalizedLength(l.splitFontString(j).size);if(!h)d.getBoundingClientRect?(e=d.getBoundingClientRect(),e=e.width||e.right-e.left):e=dojo.marginBox(d).w;dojo.body().removeChild(d);
a.position="relative";if(h)switch(a.width=h+"px",i){case "middle":a.textAlign="center";a.left=c-h/2+"px";break;case "end":a.textAlign="right";a.left=c-h+"px";break;default:a.left=c+"px",a.textAlign="left"}else switch(i){case "middle":a.left=Math.floor(c-e/2)+"px";break;case "end":a.left=Math.floor(c-e)+"px";break;default:a.left=Math.floor(c)+"px"}a.top=Math.floor(f-g)+"px";a.whiteSpace="nowrap";c=dojo.doc.createElement("div");f=c.style;m(f);f.width="0px";f.height="0px";c.appendChild(d);b.node.insertBefore(c,
b.node.firstChild);return c}}})}());