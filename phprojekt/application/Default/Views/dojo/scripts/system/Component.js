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
            phpr.send({
                url:       phpr.webpath + 'index.php/Default/Js/jsonGetTemplate/path/'+template[0]+'/name/'+template[1],
                sync:      true,
                onSuccess:function(data) {
                    tplContent = data;
                    __phpr_templateCache[template[0]+"."+template[1]] = tplContent;
                }
            });
        }
        
        var tpl = new dojox.dtl.Template(tplContent);        
        var content = tpl.render(context);
        
        // [a-zA-Z1-9[]:|]
        var eregId = /id=\\?["'][\w\x5b\x5d\x3a\x7c]*\\?["']/gi;
        var result = content.match(eregId);
        if (result) {
            for (i = 0; i < result.length; i++) {
                var id = result[i].replace(/id=\\?["']/gi, '').replace(/\\?["']/gi, '');
                if (dijit.byId(id)) {
                    dijit.byId(id).destroy();
                }
            }
        }
        
        if(node) {
            var dojoType = node.getAttribute('dojoType');
            if ((dojoType == 'dijit.layout.ContentPane') ||
                (dojoType == 'dijit.layout.BorderContainer') ) {
                dijit.byId(node.getAttribute('id')).attr('content', content);
                dojo.addOnLoad(function(){
                    dijit.byId(node.getAttribute('id')).resize();
                });
            } else {
                node.innerHTML = content;
                phpr.initWidgets(node);
            }
        } else {
            return content;
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