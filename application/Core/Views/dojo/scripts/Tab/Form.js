dojo.provide("phpr.Tab.Form");

dojo.declare("phpr.Tab.Form", phpr.Core.Form, {
    updateData: function(){
        phpr.DataStore.deleteData({url: this._url});
        var tabStore = new phpr.Store.Tab();
        tabStore.update();
    }
});