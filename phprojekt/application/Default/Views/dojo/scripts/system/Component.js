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
            __phpr_templateCache[template[0]+"."+template[1]] = tplContent;
        }
        var tpl = new dojox.dtl.Template(tplContent);
        if(node) {
            var dojoType = node.getAttribute('dojoType');
            if ((dojoType == 'dijit.layout.ContentPane') ||
                (dojoType == 'dijit.layout.BorderContainer') ) {
                dijit.byId(node.getAttribute('id')).attr('content', tpl.render(context));
            } else {
                node.innerHTML = tpl.render(context);
                phpr.initWidgets(node);
            }
        } else {
            return tpl.render(context);
        }
    },

    publish:function(/*String*/ name, /*array*/args){
        // summary:
        //    Publish the topic for the current module, its always prefixed with the module.
        // description:
        //    I.e. if the module name is "project" this.publish("open)
        //    will then publish the topic "project.open".
        // name: String
        //    The topic of this module that shall be published.
        // args: Array
        //    Arguments that should be published with the topic
        dojo.publish(phpr.module+"."+name, args);
    },

    subscribe:function(/*String*/name, /*String or null*/ context, /*String or function*/ method ){
        // summary:
        //    Subcribe topic which was published for the current module, its always prefixed with the module.
        // description:
        //    I.e. if the module name is "project" this.subscribe("open)
        //    will then subscribe the topic "project.open".
        // name: String
        //    The topic of this module that shall be published.
        // args: Array
        //    Arguments that should be published with the topic
        dojo.subscribe(phpr.module+"."+name, context, method);
    }
});