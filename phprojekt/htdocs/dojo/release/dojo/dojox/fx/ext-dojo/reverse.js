/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.fx.ext-dojo.reverse"]||(dojo._hasResource["dojox.fx.ext-dojo.reverse"]=!0,dojo.provide("dojox.fx.ext-dojo.reverse"),dojo.require("dojo.fx.easing"),dojo.require("dojo.fx"),dojo.extend(dojo.Animation,{_reversed:!1,reverse:function(l,g){var d=this.status()=="playing";this.pause();this._reversed=!this._reversed;var e=this.duration,h=e*this._percent,i=e-h,j=(new Date).valueOf(),b=this.curve._properties,c=this.properties,a;this._endTime=j+h;this._startTime=j-i;d&&this.gotoPercent(i/
e);for(a in c)d=c[a].start,c[a].start=b[a].start=c[a].end,c[a].end=b[a].end=d;if(this._reversed){if(!this.rEase)if(this.fEase=this.easing,g)this.rEase=g;else{var b=dojo.fx.easing,k,f;for(a in b)if(this.easing==b[a]){k=a;break}if(k){if(/InOut/.test(a)||!/In|Out/i.test(a)?this.rEase=this.easing:f=/In/.test(a)?a.replace("In","Out"):a.replace("Out","In"),f)this.rEase=dojo.fx.easing[f]}else console.info("ease function to reverse not found"),this.rEase=this.easing}this.easing=this.rEase}else this.easing=
this.fEase;!l&&this.status()!="playing"&&this.play();return this}}));