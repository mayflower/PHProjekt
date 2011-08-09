/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.embed.Object"]||(dojo._hasResource["dojox.embed.Object"]=!0,dojo.provide("dojox.embed.Object"),dojo.experimental("dojox.embed.Object"),dojo.require("dijit._Widget"),dojo.require("dojox.embed.Flash"),dojo.require("dojox.embed.Quicktime"),dojo.declare("dojox.embed.Object",dijit._Widget,{width:0,height:0,src:"",movie:null,params:null,reFlash:/\.swf|\.flv/gi,reQtMovie:/\.3gp|\.avi|\.m4v|\.mov|\.mp4|\.mpg|\.mpeg|\.qt/gi,reQtAudio:/\.aiff|\.aif|\.m4a|\.m4b|\.m4p|\.midi|\.mid|\.mp3|\.mpa|\.wav/gi,
postCreate:function(){if(!this.width||!this.height){var a=dojo.marginBox(this.domNode);this.width=a.w;this.height=a.h}a=dojox.embed.Flash;if(this.src.match(this.reQtMovie)||this.src.match(this.reQtAudio))a=dojox.embed.Quicktime;if(!this.params&&(this.params={},this.domNode.hasAttributes()))for(var d={dojoType:"",width:"",height:"","class":"",style:"",id:"",src:""},c=this.domNode.attributes,b=0,e=c.length;b<e;b++)if(!d[c[b].name])this.params[c[b].name]=c[b].value;this.movie=new a({path:this.src,width:this.width,
height:this.height,params:this.params},this.domNode)}}));