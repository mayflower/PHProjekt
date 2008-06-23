dojo.provide("phpr.Default.Form");
dojo.require("phpr.Component");
dojo.require("phpr.Default.field");

dojo.declare("phpr.Default.Form", phpr.Component, {
    // summary:
    //    Class for displaying a PHProjekt Detail View
    // description:
    //    This Class takes care of displaying the form information we receive from our Server
    //    in a dojo form with tabs

    formWidget:null,
	range: new Array(),
    sendData: new Array(),
	formdata:'',
	historyData:'',

    constructor:function(main, id, module) {
        // summary:
        //    render the form on construction
        // description:
        //    this function receives the form data from the server and renders the corresponding form
        //    If the module is a param, is setted
        this.main = main;
		this.id = id;

		if (undefined != module) {
		    phpr.module = module
		}

        // Render the form element on the right bottom
		this.formStore = new phpr.ReadStore({
			url: phpr.webpath+"index.php/" + phpr.module + "/index/jsonDetail/id/" + this.id
		});
		this.formStore.fetch({onComplete: dojo.hitch(this, "getFormData" )});

		// Render the history information
		this.historyStore = new phpr.ReadHistory({
			url: phpr.webpath+"index.php/History/index/jsonList/moduleName/" + phpr.module + "/itemId/" + this.id
		});
		this.historyStore.fetch({onComplete: dojo.hitch(this, "getHistoryData" )});
    },

	getFormData: function(items, request) {
        // summary:
        //    This function renders the form data according to the database manager settings
        // description:
        //    This function processes the form data which is stored in a phpr.ReadStore and
        //    renders the actual form according to the received data
		phpr.destroyWidgets("detailsBox");
		phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");

		this.formdata="";
		this.historyData="";
		meta = this.formStore.getValue(items[0], "metadata");
		data = this.formStore.getValue(items[1], "data");
        var itemwrite = 15;
        if(this.id > 0){
            itemwrite    = data[0]["rights"];
        }
		newStore = [];
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
            //special workaround for new projects - set parent to current ProjectId
            if(itemid == 'projectId' && !itemvalue){
                itemvalue = phpr.currentProjectId;
            }

			//render the fields according to their type
			switch (itemtype) {
				case 'checkbox':
					this.formdata += this.fieldTemplate.checkRender(itemlabel, itemid, itemvalue);
					break;

				case'selectbox':
					this.formdata += this.fieldTemplate.selectRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
					  												 itemdisabled);
					break;
			   case'multipleselectbox':
					this.formdata += this.fieldTemplate.multipleSelectBoxRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
					  												 itemdisabled, 5, "multiple");
					break;
				case 'multipleselect':
					this.formdata += this.fieldTemplate.MultipleSelectRender(itemrange ,itemlabel, itemid, itemvalue, itemrequired,
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

        // add tags at the end of the first tab
		this.formdata += this.displayTagInput();
		formtabs ="";

		//later on we need to provide different tabs depending on the metadata
		formtabs = this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.formdata,id:'tab1',title:'Basic Data'});
		formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:'',id:'tab2',title:'Access'});
        formtabs += this.render(["phpr.Default.template", "tabs.html"], null,{innerTabs:this.historyData,id:'tab3',title:'History'});
		this.render(["phpr.Default.template", "content.html"], dojo.byId("detailsBox"),{
            formId: 'detailForm' + this.id,
            id: 'formtab',
            tabsContent: formtabs
        });
        this.render(["phpr.Default.template", "formbuttons.html"], dojo.byId("bottomContent"),{
            writePermissions: itemwrite,
            itemDelete: this.id,
            saveText: phpr.nls.save,
            deleteText: phpr.nls.delete,
        });
		this.formWidget = dijit.byId('detailForm'+this.id);
		dojo.connect(dijit.byId("submitButton"), "onClick", dojo.hitch(this, "submitForm"));
        dojo.connect(dijit.byId("deleteButton"), "onClick", dojo.hitch(this, "deleteForm"));
	},

	submitForm: function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server and publishes
        //    a form.Submitted topic after the data was send.
		this.sendData = this.formWidget.getValues();
		phpr.send({
			url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
			content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data){
               new phpr.handleResponse('serverFeedback',data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type =='success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/' + phpr.module + '/Tag/jsonSaveTags/id/' + this.id,
                        content:   this.sendData,
                        onSuccess: this.publish("reload")
                    });
               }
            })
        });
	},

	deleteForm: function() {
        // summary:
        //    This function is responsible for deleting a dojo element
        // description:
        //    This function calls jsonDeleteAction
		this.sendData = this.formWidget.getValues();
		phpr.send({
			url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonDelete/id/' + this.id,
            onSuccess: this.publish("reload")
                });
	},

    displayTagInput: function() {
        // summary:
        // This function manually receives the Tags for the current element
        // description:
        // By calling the TagController this function receives all data it needs
        // for rendering a Tag from the server and renders those tags in a MultiSelectBox

        //first of all update the User Tags
        phpr.receiveUserTags();

        var tags          = phpr.getUserTags();
        var meta          = tags["metadata"][0];
        var data          = new Array();
        var value         = '';
        if (this.id > 0) {
            phpr.receiveCurrentTags(this.id);
            var currentTags = phpr.getCurrentTags();
            for (var i = 0; i < currentTags['data'].length; i++){
                 value +=currentTags['data'][i]['string']+',';
            }
        }
        for (var i = 0; i < tags['data'].length; i++) {
            data.push({"id":tags['data'][i]['string'],"name":tags['data'][i]['string']});
        }
        return this.fieldTemplate.MultipleSelectRender(data ,meta['label'], meta['key'], value, false, false);
    },

    getHistoryData: function(items, request) {
        // summary:
        //    This function renders the history data
        // description:
        //    This function processes the form data which is stored in a phpr.ReadStore and
        //    renders the actual form according to the received data
        var history = "";

        this.historyData = "<table>";

		history = this.historyStore.getValue(items[0], "history");

		newStore = [];
		for (var i = 0; i < history.length; i++) {
			historyUser     = history[i]["userId"];
			historyModule   = history[i]["moduleId"];
			historyItemId   = history[i]["itemId"];
			historyField    = history[i]["field"];
			historyOldValue = history[i]["oldValue"];
			historyNewValue = history[i]["newValue"];
			historyAction   = history[i]["action"];
			historyDate     = history[i]["datetime"];

			this.historyData += "<tr><td>" + historyDate + "</td><td>" + historyUser + "</td><td>" + historyField + "</td><td>" + historyOldValue;
		}
		this.historyData += "</table>";
	}
});