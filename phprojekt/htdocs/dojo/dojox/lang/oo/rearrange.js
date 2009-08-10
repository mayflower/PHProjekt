/*
	Copyright (c) 2004-2009, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


if(!dojo._hasResource["dojox.lang.oo.rearrange"]){ //_hasResource checks added by build. Do not use _hasResource directly in your code.
dojo._hasResource["dojox.lang.oo.rearrange"] = true;
dojo.provide("dojox.lang.oo.rearrange");

dojox.lang.oo.rearrange = function(bag, map){
	//	summary:
	//		Process properties in place by removing and renaming them.
	//	description:
	//		Properties of an object are to be renamed or removed specified
	//		by "map" argument. Only own properties of "map" are processed.
	//	example:
	//	|	oo.rearrange(bag, {
	//	|		abc: "def",	// rename "abc" attribute to "def"
	//	|		ghi: null	// remove/hide "ghi" attribute
	//	|	});
	//	bag: Object:
	//		the object to be processed
	//	map: Object:
	//		the dictionary for renaming (false value indicates removal of the named property)
	//	returns: Object:
	//		the original object

	for(var name in map){
		if(map.hasOwnProperty(name) && name in bag){
			var newName = map[name], temp = bag[name];
			if(!(delete bag[name])){
				// can't delete => hide it
				bag[name] = undefined;
			}
			if(newName){
				bag[newName] = temp;
			}
		}
	}

	return bag;	// Object
};

}
