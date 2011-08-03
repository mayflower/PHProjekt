/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


dojo.baseUrl=dojo.config.baseUrl?dojo.config.baseUrl:"./";dojo._name="spidermonkey";dojo.isSpidermonkey=!0;dojo.exit=function(a){quit(a)};if(typeof print=="function")console.debug=print;if(typeof line2pc=="undefined")throw Error("attempt to use SpiderMonkey host environment when no 'line2pc' global");
dojo._spidermonkeyCurrentFile=function(a){var b="";try{throw Error("whatever");}catch(d){b=d.stack}var c=b.match(/[^@]*\.js/gi);if(!c)throw Error("could not parse stack string: '"+b+"'");a=typeof a!="undefined"&&a?c[a+1]:c[c.length-1];if(!a)throw Error("could not find file name in stack string '"+b+"'");return a};dojo._loadUri=function(a){load(a);return 1};if(dojo.config.modulePaths)for(var param in dojo.config.modulePaths)dojo.registerModulePath(param,dojo.config.modulePaths[param]);