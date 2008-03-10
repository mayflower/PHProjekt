dojo.provide("phpr.Default.Form");

dojo.require("phpr.Component");

dojo.declare("phpr.Default.Form", phpr.Component, {
    
    formWidget:null,
	range: new Array(),
	formdata:'',
    
    constructor:function(main, id, module) {
        this.main = main;
		this.id = id;
		this.module = module;
        // Render the form element on the right bottom
		this.formStore = new phpr.CompleteReadStore({
			url: this.main.webpath+"index.php/" + this.module + "/index/jsonList/Id/" + this.id
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
		for (var i = 0; i < meta.length; i++) {
			
			itemtype = meta[i]["type"];
			itemid = meta[i]["key"];
			itemlabel = meta[i]["label"];
			itemdisabled = meta[i]["readOnly"];
			itemrequired = meta[i]["required"];
			itemlabel = meta[i]["label"];
			itemvalue = data[0][itemid];
			
			/**
			 * render the fields according to their type
			 */
			switch (itemtype) {
				case'selectbox':
				var range = meta[i]["range"];
				var options=new Array();
				var j=0;
				for (j in range){
					options.push(range[j]);
					j++;
				}
				this.formdata += this.render(["phpr.Default.template", "formfilterselect.html"], null, {
						label: itemlabel,
						labelfor: itemid,
						id: itemid,
						value: itemvalue,
						required: itemrequired,
						disabled: itemdisabled,
						values: options
					});
				break;
				case'date':
				this.formdata += this.render(["phpr.Default.template", "formdate.html"], null, {
						label: itemlabel,
						labelfor: itemid,
						id: itemid,
						value: itemvalue,
						required: itemrequired,
						disabled: itemdisabled
					});
					break;
				case 'textfield':
				default:
					this.formdata += this.render(["phpr.Default.template", "formtext.html"], null, {
						label: itemlabel,
						labelfor: itemid,
						id: itemid,
						value: itemvalue,
						required: itemrequired,
						disabled: itemdisabled
					});
					break;
			}
		}
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
			url: this.main.webpath + 'index.php/' + this.module + '/index/save/id/' + this.id,
			content:sendData,
			onSuccess: dojo.publish(this.module+".form.Submitted", [this.id, this.module, sendData['parent']])
			});
	}
	
});