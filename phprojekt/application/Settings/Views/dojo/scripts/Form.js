dojo.provide("phpr.Settings.Form");

dojo.declare("phpr.Settings.Form", phpr.Default.Form, {
    
    setPermissions:function (data) {
        if (this.id > 0) {
            this._writePermissions  = true;
            this._deletePermissions = false;
            this._accessPermissions = true;
        }
    },
    
    addBasicFields:function() {
    },
    
    addAccessTab:function(data) {
    },
    
    addModuleTabs:function(data) {
    },
    
    submitForm: function() {
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
               new phpr.handleResponse('serverFeedback', data);
               if (!this.id) {
                   this.id = data['id'];
               }
               if (data.type =='success') {
                    this.publish("updateCacheData");
                    this.publish("reload");
                }
            })
        });
    },
});