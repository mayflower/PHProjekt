/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.mobile.app.compat"]||(dojo._hasResource["dojox.mobile.app.compat"]=!0,dojo.provide("dojox.mobile.app.compat"),dojo.require("dojox.mobile.compat"),dojo.extend(dojox.mobile.app.AlertDialog,{_doTransition:function(a){console.log("in _doTransition and this = ",this);var b=dojo.marginBox(this.domNode.firstChild).h,c=this.controller.getWindowSize().h,b=c-b,c=dojo.fx.slideTo({node:this.domNode,duration:400,top:{start:a<0?b:c,end:a<0?c:b}}),b=dojo[a<0?"fadeOut":"fadeIn"]({node:this.mask,
duration:400}),c=dojo.fx.combine([c,b]),d=this;dojo.connect(c,"onEnd",this,function(){if(a<0)d.domNode.style.display="none",dojo.destroy(d.domNode),dojo.destroy(d.mask)});c.play()}}),dojo.extend(dojox.mobile.app.List,{deleteRow:function(){console.log("deleteRow in compat mode",a);var a=this._selectedRow;dojo.style(a,{visibility:"hidden",minHeight:"0px"});dojo.removeClass(a,"hold");var b=dojo.contentBox(a).h;dojo.animateProperty({node:a,duration:800,properties:{height:{start:b,end:1},paddingTop:{end:0},
paddingBottom:{end:0}},onEnd:this._postDeleteAnim}).play()}}),dojox.mobile.app.ImageView&&!dojo.create("canvas").getContext&&dojo.extend(dojox.mobile.app.ImageView,{buildRendering:function(){this.domNode.innerHTML="ImageView widget is not supported on this browser.Please try again with a modern browser, e.g. Safari, Chrome or Firefox";this.canvas={}},postCreate:function(){}}),dojox.mobile.app.ImageThumbView&&dojo.extend(dojox.mobile.app.ImageThumbView,{place:function(a,b,c){dojo.style(a,{top:c+"px",
left:b+"px",visibility:"visible"})}}));