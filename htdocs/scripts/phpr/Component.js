dojo.provide("phpr.Component");

// A global cache
var __phpr_templateCache = {};

dojo.declare("phpr.Component", null, {
	main:null,
	module:"",
	render: function(template, node, content) {
		var context = new dojox.dtl.Context(content);
		// Cache the templates if loaded once.
		var tplContent = __phpr_templateCache[template[0]+"."+template[1]];
		if (!tplContent) {
			tplContent = dojo._getText(dojo.moduleUrl(template[0], template[1])+'?'+Math.random());
			__phpr_templateCache[template[0]+"."+template[1]] = content;
		}
		var tpl = new dojox.dtl.Template(tplContent);
		if(node) {
			node.innerHTML = tpl.render(context);
			phpr.initWidgets(node);
		} else {
			return tpl.render(context);
		}
	},
	
	publish:function(/*String*/ name){
		// summary:
		//		Publish the topic for the current module, its always prefixed with the module.
		// description:
		//		I.e. if the module name is "project" this.publish("open)
		//		will then publish the topic "project.open".
		// name: String
		//		The topic of this module that shall be published.
		dojo.publish(this.module+"."+name);
	}
	
});