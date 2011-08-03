/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.data.api.Identity"]||(dojo._hasResource["dojo.data.api.Identity"]=!0,dojo.provide("dojo.data.api.Identity"),dojo.require("dojo.data.api.Read"),dojo.declare("dojo.data.api.Identity",dojo.data.api.Read,{getFeatures:function(){return{"dojo.data.api.Read":!0,"dojo.data.api.Identity":!0}},getIdentity:function(){throw Error("Unimplemented API: dojo.data.api.Identity.getIdentity");},getIdentityAttributes:function(){throw Error("Unimplemented API: dojo.data.api.Identity.getIdentityAttributes");
},fetchItemByIdentity:function(a){if(!this.isItemLoaded(a.item))throw Error("Unimplemented API: dojo.data.api.Identity.fetchItemByIdentity");}}));