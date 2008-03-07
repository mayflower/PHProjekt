dojo.provide("phpr.Component");

dojo.declare("phpr.Component", null, {
    main:   null,
    
    render: function(template, node, content) {
        var context = new dojox.dtl.Context(content);
        var tpl     = new dojox.dtl.Template(dojo._getText(dojo.moduleUrl(template[0], template[1])+'?'+Math.random()));

        if(node) {
            node.innerHTML = tpl.render(context);
            phpr.initWidgets(node);
        } else {
            return tpl.render(context);
        }
    }   
});