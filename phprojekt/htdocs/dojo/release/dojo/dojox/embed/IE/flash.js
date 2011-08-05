/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojox.embed.Flash.place=function(c,a){var b=dojox.embed.Flash.__ie_markup__(c),a=dojo.byId(a);if(!a)a=dojo.doc.createElement("div"),a.id=b.id+"-container",dojo.body().appendChild(a);return b?(a.innerHTML=b.markup,b.id):null};dojox.embed.Flash.onInitialize();