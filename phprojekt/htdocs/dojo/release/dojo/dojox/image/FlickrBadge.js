/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.image.FlickrBadge"]||(dojo._hasResource["dojox.image.FlickrBadge"]=!0,dojo.provide("dojox.image.FlickrBadge"),dojo.require("dojox.image.Badge"),dojo.require("dojox.data.FlickrRestStore"),dojo.declare("dojox.image.FlickrBadge",dojox.image.Badge,{children:"a.flickrImage",userid:"",username:"",setid:"",tags:"",searchText:"",target:"",apikey:"8c6803164dbc395fb7131c9d54843627",_store:null,postCreate:function(){this.username&&!this.userid&&dojo.io.script.get({url:"http://www.flickr.com/services/rest/",
preventCache:!0,content:{format:"json",method:"flickr.people.findByUsername",api_key:this.apikey,username:this.username},callbackParamName:"jsoncallback"}).addCallback(this,function(a){if(a.user&&a.user.nsid)this.userid=a.user.nsid,this._started||this.startup()})},startup:function(){if(!this._started&&this.userid){var a={userid:this.userid};if(this.setid)a.setid=this.setid;if(this.tags)a.tags=this.tags;if(this.searchText)a.text=this.searchText;var d=arguments;this._store=new dojox.data.FlickrRestStore({apikey:this.apikey});
this._store.fetch({count:this.cols*this.rows,query:a,onComplete:dojo.hitch(this,function(a){dojo.forEach(a,function(a){var b=dojo.doc.createElement("a");dojo.addClass(b,"flickrImage");b.href=this._store.getValue(a,"link");if(this.target)b.target=this.target;var c=dojo.doc.createElement("img");c.src=this._store.getValue(a,"imageUrlThumb");dojo.style(c,{width:"100%",height:"100%"});b.appendChild(c);this.domNode.appendChild(b)},this);dojox.image.Badge.prototype.startup.call(this,d)})})}}}));