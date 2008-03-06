dojo.provide("phpr.admin.Elements");


dojo.require("phpr.Component");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.admin.Elements", phpr.Component, {

    _data: null,
    constructor:function(main) {
        this.main = main;
		this._data = phpr.getData('display_elements.json',dojo.hitch(this, 'receiveData'));
		this.metadataStore = new dojo.data.ItemFileReadStore({url: 'geography.json'});
		dojo.connect(dojo.byId('edit_pane'), "onclick", dojo.hitch(this, "displayValues"));
    },
    showTextbox: function (elName)
    {

    },
    receiveData: function(response)
    {
        var i = 0;
        this.data = eval(response);
        this.meta = this.data['items'];
        var html ='';
        for (i; i < this.meta.length; i++) {
            var current = this.meta[i];
            var node = null;
            var context = {label:current.label, icon:current.icon, text:'', id:current.id,};

            html += this.render(["phpr.admin.template", "button.html"],node,context)+'<br>';
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
					var ident =current.id;
					current.id = uID;
					self.render(["phpr.admin.template", current.type+".html"],mynodes[0],current);
					dijit.byId(uID).editorId=ident;
                    dojo.dnd.manager().nodes = mynodes;
                });
            }
        });

    },
    displayValues: function(event)
    {
		var parentEvent = event.target.id;
        var output = '';
        var widget = dijit.getEnclosingWidget(dojo.byId(event.target.id));
		console.debug('widget id'+widget.editorId);
     	this.metadataStore.fetchItemByIdentity({identity:widget.editorId,onItem:this.metadaRender})
		console.debug('der editortype:'+widget.editorId);
        var html = this.render(["phpr.admin.template", "label.html"],null,{label:'Wert'});
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
    },
	metadaRender: function(item,request) {
      console.debug('Pepper is in aisle ' + item.getAttribute('label'));
    }
	
});