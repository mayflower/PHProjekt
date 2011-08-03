/*
	Copyright (c) 2004-2011, The Dojo Foundation All Rights Reserved.
	Available via Academic Free License >= 2.1 OR the modified BSD license.
	see: http://dojotoolkit.org/license for details
*/


require.paths.unshift("/opt/less/lib","C:/less/lib");var fs=require("fs"),path=require("path"),less=require("less"),options={compress:!1,optimization:1,silent:!1},allFiles=[].concat(fs.readdirSync("."),fs.readdirSync("form").map(function(a){return"form/"+a}),fs.readdirSync("layout").map(function(a){return"layout/"+a})),lessFiles=allFiles.filter(function(a){return a&&a!="variables.less"&&/\.less$/.test(a)});
lessFiles.forEach(function(a){console.log("=== "+a);fs.readFile(a,"utf-8",function(b,c){b&&(console.error("lessc: "+b.message),process.exit(1));(new less.Parser({paths:[path.dirname(a)],optimization:options.optimization,filename:a})).parse(c,function(b,c){if(b)less.writeError(b,options),process.exit(1);else try{var d=c.toCSS({compress:options.compress}),e=a.replace(".less",".css");fd=fs.openSync(e,"w");fs.writeSync(fd,d,0,"utf8")}catch(f){less.writeError(f,options),process.exit(2)}})})});