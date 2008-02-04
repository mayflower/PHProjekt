dojo.provide("phpr.Component");

dojo.declare("phpr.Component", null, {
    
    // This will contain a reference to the app.default.Main instance.
    // Using this we can refer to all components.
    main:null,
    
    render:function(template, node, content) {
        // Example:
        //      this.render(["phpr.app.default.templates", "tree.html"])
        var context = new dojox.dtl.Context(content);
        // TODO Do the following right without _getText but with dojox.dtl.HtmlTemplate
        var temp = dojo._getText(dojo.moduleUrl(template[0], template[1]));
        var tpl = new dojox.dtl.Template(temp);
        if(node){
            node.innerHTML = tpl.render(context);
            // Init the widgets inside the node.
            phpr.initWidgets(node);
        }
        else{
            return tpl.render(context);
        }
    }
    
});