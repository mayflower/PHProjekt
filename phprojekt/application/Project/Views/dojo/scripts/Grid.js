dojo.provide("phpr.Project.Grid");

dojo.declare("phpr.Project.Grid", phpr.Default.Grid, {	
    updateData:function() {
        phpr.DataStore.deleteData({url: this.url});
        phpr.DataStore.deleteData({url: this._tagUrl});
		
		// Delete parent cache
		var parentId = this.main.tree.getParentId(phpr.currentProjectId);
		var url = phpr.webpath+"index.php/"+phpr.module+"/index/jsonList/nodeId/" + parentId;
		phpr.DataStore.deleteData({url: url});
    }
});