dojo.provide("phpr._EditableGrid");
dojo.require("phpr.grid");

dojo.declare("phpr._EditableGrid", phpr.grid, {
    
    _updateUrl:"",
    _newRowValues:null, // Init in constructor with an object.
    
    constructor:function(updateUrl) {
        this._updateUrl = updateUrl;
        this._newRowValues = {};
    },
    
    onLoaded:function() {
        // onApplyEdit is called every time a cell in a row was edited and looses focus.
        dojo.connect(this.grid.widget, "onApplyCellEdit", dojo.hitch(this, "cellEdited"))
        dojo.connect(this.grid.widget, "onStartEdit", dojo.hitch(this, function() {
            this.toggleSaveButtons(true);
        }));
    },
    
    toggleSaveButtons:function(activate) {
        //// Deactivate all the other button.
        //var btns = dojo.query("input[type=button]", this.grid.widget.domNode.parentNode);
        //for (var i=0, l=btns.length; i<l; i++) {
        //    btns[i].disabled = activate ? "disabled" : "";
        //}
        // Activate "save changes" buttons.
        var saveBtns = dojo.query("input.saveChanges", this.grid.widget.domNode.parentNode);
        for (var i=0, l=saveBtns.length; i<l; i++) {
            saveBtns[i].disabled = activate ? "" : "disabled";
        }
    },

    cellEdited:function(value, rowNum, fieldNum) {
        if (!this._newRowValues[rowNum]) {
            this._newRowValues[rowNum] = {};
        }
        var fieldName = this.grid.widget.model.fields.get(fieldNum).name;
        this._newRowValues[rowNum][fieldName] = value;
    },
    
    saveChanges:function() {
        // Make sure, that an element that is still in edit mode calls "onApplyCellEdit",
        // so we also get the new data into _newRowValues.
        this.grid.widget.edit.apply();
        
        // Get all the IDs for the data sets.
        var content = "";
        for (var i in this._newRowValues) {
            var curId = this.grid.widget.model.data[i].id;
            for (var j in this._newRowValues[i]) {
                content += '&data['+ encodeURIComponent(curId) +']['+encodeURIComponent(j)+']='+encodeURIComponent(this._newRowValues[i][j]);
            }
        }
        
        //post the content of all changed forms
        dojo.rawXhrPost( {
            url: this._updateUrl,
            postData: content,
            handleAs: "json-comment-filtered",
            load: dojo.hitch(this,function(response, ioArgs) {
                    this._newRowValues = {};
                    this.toggleSaveButtons(false);
                    new phpr.handleResponse('serverFeedback',response);
                    alert(dojo.toJson(response));
                    return response;
            }),
            error: function(response, ioArgs) {
                new phpr.handleResponse('serverFeedback',response);
            }
        });
    }
});
