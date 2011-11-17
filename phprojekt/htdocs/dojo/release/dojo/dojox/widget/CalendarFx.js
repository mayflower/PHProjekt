/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dojox.widget.CalendarFx"]||(dojo._hasResource["dojox.widget.CalendarFx"]=!0,dojo.provide("dojox.widget.CalendarFx"),dojo.require("dojox.widget.FisheyeLite"),dojo.declare("dojox.widget._FisheyeFX",null,{addFx:function(a,b){dojo.query(a,b).forEach(function(a){new dojox.widget.FisheyeLite({properties:{fontSize:1.1}},a)})}}),dojo.declare("dojox.widget.CalendarFisheye",[dojox.widget.Calendar,dojox.widget._FisheyeFX],{}));