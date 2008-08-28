dojo.provide("phpr.Core.Form");

dojo.declare("phpr.Core.Form", phpr.Default.Form, {

    initData:function() {
    },

    setUrl:function() {
        this._url = phpr.webpath+"index.php/Core/"+phpr.module.toLowerCase()+"/jsonDetail/id/" + this.id
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
        for(var i = 0; i < this.formsWidget.length; i++) {
            this.sendData = dojo.mixin(this.sendData, this.formsWidget[i].getValues());
        }
		phpr.send({
			url:       phpr.webpath + 'index.php/Core/'+phpr.module.toLowerCase()+'/jsonSave/id/' + this.id,
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

    deleteForm: function() {
        phpr.send({
            url:       phpr.webpath + 'index.php/Core/'+phpr.module.toLowerCase()+'/jsonDelete/id/' + this.id,
            onSuccess: dojo.hitch(this, function(data) {
               new phpr.handleResponse('serverFeedback', data);
               if (data.type == 'success') {
                   phpr.send({
                        url: phpr.webpath + 'index.php/Default/Tag/jsonDeleteTags/moduleName/' + phpr.module + '/id/' + this.id,
                        onSuccess: dojo.hitch(this, function(data) {
                            new phpr.handleResponse('serverFeedback', data);
                            if (data.type =='success') {
                                this.publish("updateCacheData");
                                this.publish("reload");
                            }
                        }),
                    });
               }
            })
        });
    },
});