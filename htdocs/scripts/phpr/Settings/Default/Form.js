dojo.provide("phpr.Settings.Default.Form");
dojo.require("phpr.Default.Form");

dojo.declare("phpr.Settings.Default.Form", phpr.Default.Form, {

    initData: function() {
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

		formtabs = "";
		// later on we need to provide different tabs depending on the metadata
		formtabs = this.render(["phpr.Default.template", "tabs.html"], null,{
		    innerTabs: this.formdata,
		    id:        'tab1',
		    title:     'Basic Data',
            formId:    'dataFormTab'
		});
		this.render(["phpr.Default.template", "content.html"], dojo.byId("detailsBox"),{
            id: 'formtab',
            tabsContent: formtabs
        });
        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"),{
            writePermissions: writePermissions,
            deletePermissions: deletePermissions,
            saveText: phpr.nls.save,
            deleteText: phpr.nls.delete,
        });
		this.formsWidget = dijit.byId('dataFormTab');

        // action buttons for the form
		dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, "deleteForm"));
	},

	submitForm: function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server and publishes
        //    a form.Submitted topic after the data was send.
        for(var i = 0; i < this.formsWidget.length; i++) {
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].getValues());
        }
		phpr.send({
			url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
			content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback',data);
                if (data.type =='success') {
                    this.publish("reload");
                }
            })
        });
	},
});