dojo.provide("phpr.Default.Form");

dojo.require("phpr.Component");
dojo.require("phpr.Default.field");

dojo.declare("phpr.Default.Form", phpr.Component, {
    
    formWidget:null,
	range: new Array(),
	formdata:'',
    
    constructor:function(main, id) {
        this.main = main;
		this.id = id;
        // Render the form element on the right bottom
		this.formStore = new phpr.ReadStore({
			url: phpr.webpath+"index.php/" + phpr.module + "/index/jsonDetail/id/" + this.id
		});
		this.formStore.fetch({onComplete: dojo.hitch(this, "getFormData" )});
    },
	getFormData: function(items, request){
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}		
		this.formdata="";
		meta = this.formStore.getValue(items[0], "metadata");
		data = this.formStore.getValue(items[1], "data");
		newStore = [];
		this.fieldTemplate = new phpr.Default.field();
		for (var i = 0; i < meta.length; i++) {
			
			itemtype = meta[i]["type"];
			itemid = meta[i]["key"];
			itemlabel = meta[i]["label"];
			itemdisabled = meta[i]["readOnly"];
			itemrequired = meta[i]["required"];
			itemlabel = meta[i]["label"];
			itemvalue = data[0][itemid];

			//render the fields according to their type
			switch (itemtype) {
				case 'checkbox':
					this.formdata += this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue);
					break;

				case'selectbox':
					this.formdata += this.fieldTemplate.selectRender(meta[i]["range"],itemlabel, itemid, itemvalue, itemrequired,
					  												 itemdisabled);
					break;
				case 'multipleselect':
					this.formdata += this.fieldTemplate.MultipleSelectRender(meta[i]["range"],itemlabel, itemid, itemvalue, itemrequired,
					  												  		itemdisabled);
					break;
				case'date':
					this.formdata += this.fieldTemplate.dateRender(itemlabel, itemid, itemvalue, itemrequired,
																   itemdisabled);
					break;
				case 'time':
					this.formdata += this.fieldTemplate.timeRender(itemlabel, itemid, itemvalue, itemrequired,
																   itemdisabled);
					break;
				case 'textarea':
					this.formdata += this.fieldTemplate.textAreaRender(itemlabel, itemid, itemvalue, itemrequired,
																       itemdisabled);
					break;
				case 'textfield':
				default:
					this.formdata += this.fieldTemplate.textFieldRender(itemlabel, itemid, itemvalue, itemrequired,
																		itemdisabled);
					break;
			}
		}
		this.formdata += this.fieldTemplate.MultipleSelectRender('tags', 'tags', data[0]['tags'], false,
																		false);
		formtabs ="";
		//later on we need to provide different tabs depending on the metadata
		formtabs = this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.formdata,id:'tab1',title:'First Tab'});
		formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:'',id:'tab2',title:'Secon dummy Tab'});
		this.render(["phpr.Default.template", "content.html"], dojo.byId("detailsBox"),{formId:'detailForm'+this.id, id:'formtab',tabsContent:formtabs});
		this.formWidget = dijit.byId('detailForm'+this.id);
		dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
	},
	submitForm: function(){
		var sendData = this.formWidget.getValues();
		phpr.send({
			url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
			content:   sendData,
			onSuccess: this.publish("form.Submitted", [this.id, sendData['parent']]),
			});
	}
	
});