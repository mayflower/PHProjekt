/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo._hasResource["dijit.robotx"]||(dojo._hasResource["dijit.robotx"]=!0,dojo.provide("dijit.robotx"),dojo.require("dijit.robot"),dojo.require("dojo.robotx"),dojo.experimental("dijit.robotx"),function(){var b=doh.robot._updateDocument;dojo.mixin(doh.robot,{_updateDocument:function(){b();var a=dojo.global;if(a.dijit)window.dijit=a.dijit}})}());