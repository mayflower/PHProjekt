dojo.provide("phpr.app.admin.Elements");


dojo.require("phpr.Component");

dojo.declare("phpr.app.admin.Elements", phpr.Component, {

    _data: null,
    constructor:function(main) {
        this.main = main;
        this._data = phpr.getData('display_elements.json',dojo.hitch(this, 'receiveData'));
		dojo.connect(dojo.byId('edit_pane'), "onclick", dojo.hitch(this, "displayValues"));
    },
    showTextbox: function (elName)
    {

    },
    receiveData: function(response)
    {
        var i = 0;
        this.data = eval(response);
        this.meta = this.data['elements'];
        var html ='';
        for (i; i < this.meta.length; i++) {
            var current = this.meta[i];
            var node = null;
            var context = {label:current.label, icon:current.icon, text:'', id:current.id,};

            html += this.render(["phpr.app.admin.template", "button.html"],node,context)+'<br>';
        }
        dojo.byId('myelements').innerHTML = html;
        phpr.initWidgets(dojo.byId('myelements'));
        var container = new dojo.dnd.Source(dojo.byId('myelements'));
        container.copyOnly = true;
        var target = new dojo.dnd.Target(dojo.byId('edit_pane'));
        var self =this;
        /**
        * Register a start function on draf and set a clone of the dragged object into
        * the dnd.manager to modified the dragged element
        */
        dojo.subscribe("/dnd/start", function(source,nodes,iscopy){
            if(source == container) {
                var mynodes = dojo.clone(nodes);
                var buttons = dojo.query("button",mynodes[0]);
                dojo.forEach(buttons,
                function(elements) {
                    var elementId = elements.getAttribute("id");
					var uID = dojo.dnd.getUniqueId();
					var current = phpr.getCurrent(self.meta,'id',elementId);
					current.id = uID;
					
					self.render(["phpr.app.admin.template", current.type+".html"],mynodes[0],current);
					dijit.byId(uID).editorype='nina';
                    dojo.dnd.manager().nodes = mynodes;
                });
            }
        });

    },
    displayValues: function(event)
    {
		var parentEvent = event.target.id;
		var output ='';
		var widge = dojo.byId(parentEvent);
		 console.debug('der editortype:'+parentEvent.editorype);
        var html = this.render(["phpr.app.admin.template", "label.html"],null,{label:'Wert'});
        dojo.byId('myvalues').innerHTML =html;
        //dojo.connect(dojo.byId(newId2), "onchange",dojo.hitch(this, "updateValues"));

    },
    
    updateValues: function(){
        /**
          switch(dom){
                case 'value':
                alert(dojo.byId(newId2).getAttribute("value"));
                //domnode.value = dojo.byId(newId2).getAttribute("value");
                break;
                case 'inner':
                default:
                alert(dojo.byId(newId2).getAttribute("value"));
                //domnode.innerHTML = dojo.byId(newId2).getAttribute("value");
                break;

            }
            */
    }
});