dojo.provide("phpr.Administration.Default.Form");
dojo.require("phpr.Default.Form");

dojo.declare("phpr.Administration.Default.Form", phpr.Default.Form, {

    initData:function() {
    },

    setPermissions:function (data) {
        this._writePermissions = true;
        this._deletePermissions = false;
        if (this.id > 0) {
            this._deletePermissions = true;
        }
        this._accessPermissions = true;
    },

    addBasicFields:function() {
    },

    addModuleTabs:function(data) {
    },

	submitForm:function() {
        // summary:
        //    This function is responsible for submitting the formdata
        // description:
        //    This function sends the form data as json data to the server
        //    and call the reload routine
        for(var i = 0; i < this.formsWidget.length; i++) {
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].getValues());
        }
		phpr.send({
			url:       phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/' + this.id,
			content:   this.sendData,
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback',data);
                if (data.type =='success') {
                    this.publish("updateCacheData");
                    this.publish("reload");
                }
            })
        });
	},
});