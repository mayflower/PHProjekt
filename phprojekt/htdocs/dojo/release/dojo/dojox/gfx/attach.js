/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.gfx.attach"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.gfx.attach"] = true;
dojo.provide("dojox.gfx.attach");

dojo.require("dojox.gfx");

// rename an attacher conditionally

(function(){
	var r = dojox.gfx.svg.attach[dojox.gfx.renderer];
	dojo.gfx.attachSurface = r.attachSurface;
	dojo.gfx.attachNode = r.attachNode;
})();

}
