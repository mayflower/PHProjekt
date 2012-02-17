/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojo.data.api.Notification"]||(dojo._hasResource["dojo.data.api.Notification"]=!0,dojo.provide("dojo.data.api.Notification"),dojo.require("dojo.data.api.Read"),dojo.declare("dojo.data.api.Notification",dojo.data.api.Read,{getFeatures:function(){return{"dojo.data.api.Read":!0,"dojo.data.api.Notification":!0}},onSet:function(){throw Error("Unimplemented API: dojo.data.api.Notification.onSet");},onNew:function(){throw Error("Unimplemented API: dojo.data.api.Notification.onNew");},
onDelete:function(){throw Error("Unimplemented API: dojo.data.api.Notification.onDelete");}}));