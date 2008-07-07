dojo.provide("phpr.Administration.Role.Form");
dojo.provide("phpr.Administration.Role.ReadModule");
dojo.require("phpr.Administration.Default.Form");

dojo.declare("phpr.Administration.Role.Form", phpr.Administration.Default.Form, {

    moduleList: new Array(),

    initData: function() {
		// Get modules
		this.moduleStore = new phpr.ReadData({
			url: phpr.webpath+"index.php/" + phpr.module + "/admin/jsonGetModulesAccess/id/" + this.id
		});
		this.moduleStore.fetch({onComplete: dojo.hitch(this, "getModuleData")});
    },

	getFormData: function(items, request) {
        // summary:
        //    This function renders the form data according to the model information
        // description:
        //    This function processes the form data which is stored in a phpr.ReadStore and
        //    renders the actual form according to the received data
		phpr.destroyWidgets("detailsBox");
		phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");

		this.formdata    = "";
		this.historyData = "";
		meta = this.formStore.getValue(items[0], "metadata");
		data = this.formStore.getValue(items[1], "data");
        var writePermissions  = true;
        var deletePermissions = false;
        if (this.id > 0) {
            deletePermissions = true;
        }

		this.fieldTemplate = new phpr.Default.field();
		for (var i = 0; i < meta.length; i++) {
			itemtype     = meta[i]["type"];
			itemid       = meta[i]["key"];
			itemlabel    = meta[i]["label"];
			itemdisabled = meta[i]["readOnly"];
			itemrequired = meta[i]["required"];
			itemlabel    = meta[i]["label"];
			itemvalue    = data[0][itemid];
            itemrange    = meta[i]["range"];

			//render the fields according to their type
			switch (itemtype) {
				case 'checkbox':
					this.formdata += this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue);
					break;

				case 'selectbox':
					this.formdata += this.fieldTemplate.selectRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
					  												 itemdisabled);
					break;
                case 'multipleselectbox':
					this.formdata += this.fieldTemplate.multipleSelectBoxRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
					  												 itemdisabled, 5, "multiple");
					break;
				case 'multipleselect':
					this.formdata += this.fieldTemplate.multipleSelectRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
					  												  		itemdisabled);
					break;
				case 'date':
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

        this.formdata += this.render(["phpr.Administration.Role.template", "formAccess.html"], null, {
        	accessModuleText: phpr.nls.accessModule,
            accessReadText: phpr.nls.accessRead,
            accessWriteText: phpr.nls.accessWrite,
            accessAdminText: phpr.nls.accessAdmin,
            labelfor: phpr.nls.accessAccess,
            label: phpr.nls.accessAccess,
			modules: this.moduleList,
        });

		// later on we need to provide different tabs depending on the metadata
		formtabs = this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.formdata,id:'tab1',title:'Basic Data'});
		this.render(["phpr.Default.template", "content.html"], dojo.byId("detailsBox"),{
            formId: 'detailForm' + this.id,
            id: 'formtab',
            tabsContent: formtabs
        });
        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"),{
            writePermissions: writePermissions,
            deletePermissions: deletePermissions,
            saveText: phpr.nls.save,
            deleteText: phpr.nls.delete,
        });
		this.formWidget = dijit.byId('detailForm'+this.id);

        // action buttons for the form
		dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, "deleteForm"));
	},

    getModuleData: function(items, request) {
        // summary:
        //    This function get all the active modules
        // description:
        //    This function get all the active modules,
        //    and make the array for draw it
		var modules = this.moduleStore.getValue(items[0], "data");
        this.moduleList = new Array();

		for (i in modules) {
		    this.moduleList.push({"id":modules[i]['id'],"name":modules[i]['name'],
		                          "read":modules[i]['read'],"write":modules[i]['write'],"admin":modules[i]['admin']})
		}
	}
});