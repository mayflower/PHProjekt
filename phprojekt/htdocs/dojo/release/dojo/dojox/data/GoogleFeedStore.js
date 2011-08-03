/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.data.GoogleFeedStore"]||(dojo._hasResource["dojox.data.GoogleFeedStore"]=!0,dojo.provide("dojox.data.GoogleFeedStore"),dojo.require("dojox.data.GoogleSearchStore"),dojo.experimental("dojox.data.GoogleFeedStore"),dojo.declare("dojox.data.GoogleFeedStore",dojox.data.GoogleSearchStore,{_type:"",_googleUrl:"http://ajax.googleapis.com/ajax/services/feed/load",_attributes:["title","link","author","published","content","summary","categories"],_queryAttrs:{url:"q"},getFeedValue:function(a,
b){var c=this.getFeedValues(a,b);return dojo.isArray(c)?c[0]:c},getFeedValues:function(a,b){return!this._feedMetaData?b:this._feedMetaData[a]||b},_processItem:function(a,b){this.inherited(arguments);a.summary=a.contentSnippet;a.published=a.publishedDate},_getItems:function(a){return a.feed?(this._feedMetaData={title:a.feed.title,desc:a.feed.description,url:a.feed.link,author:a.feed.author},a.feed.entries):null},_createContent:function(a,b,c){var d=this.inherited(arguments);d.num=(c.count||10)+(c.start||
0);return d}}));