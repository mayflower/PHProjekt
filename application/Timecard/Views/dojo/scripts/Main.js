dojo.provide("phpr.Timecard.Main");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {

    _view: 'month',
    _date: new Date(),
		
    constructor:function() {
		this.module = 'Timecard';
		this.loadFunctions(this.module);

		this.gridWidget = phpr.Timecard.Grid;
		this.formWidget = phpr.Timecard.Form;
		this.treeWidget = phpr.Timecard.Tree;
        this.updateUrl  = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;

        dojo.subscribe("Workingtimes.start", this, "workingtimesStart");
        dojo.subscribe("Workingtimes.stop", this, "workingtimesStop");
		dojo.subscribe("Timecard.changeListView", this, "changeListView");
		dojo.subscribe("Timecard.changeDate", this, "changeDate");
	},

    reload:function() {
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroySimpleWidget("exportGrid");
        phpr.destroySimpleWidget("saveChanges");
        phpr.destroySimpleWidget("gridNode");
        this.render(["phpr.Timecard.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        this.tree     = new this.treeWidget(this);			
		var updateUrl = null;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        this.form     = new this.formWidget(this,0,this.module, this._date);
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        phpr.destroySimpleWidget("newEntry");
		var buttons = "<a href='javascript:dojo.publish(\"Workingtimes.start\")'>"+phpr.nls.timecardWorkingtimeStart+"</a>";
		buttons += "&nbsp;";
		buttons += "<a href='javascript:dojo.publish(\"Workingtimes.stop\")>"+phpr.nls.timecardWorkingtimeStop+"</a>";
        dojo.byId("subModuleNavigation").innerHTML = buttons;
    },
	
    setDate:function(date) {
         dateFormatted = dojo.date.locale.format(date, {formatLength:'full',selector:'date', locale:this.lang});
         dojo.byId("tcFormHeader").innerHTML = "<h3>Zeiterfassung f&uuml;r den "+dateFormatted+"</h3>";
         dojo.byId("tcBookingsSummary").innerHTML = "<h4>Zeit die am "+dateFormatted+" erfasst wurde:</h4>";
         dojo.byId('tcProjectDate').value = date;
         dojo.byId('tcDate').value = date;
    },

    changeListView:function(view) {
		this._view = view;
		if (view == 'today') {
			this.grid.reloadView(this._view); 
		} else {
			this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
		}
	},
		
    changeDate:function(date) {
		this._date = date
		this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
        this.form = new this.formWidget(this,0,this.module, this._date);
    },
	
    getIsoDate:function(date) {
       return date.getFullYear() + '-' + (date.getMonth()+1) + '-' + date.getDate();
    },
    
    getIsoTime:function(time){
       return time.getHours() + ':' + time.getMinutes() + ':' + time.getSeconds();
    },
		
    workingtimesStop:function() {
        // summary:
        //    This function deactivates the Timecard stopwatch
        // description:
        //    This function calls jsonStop
		phpr.send({
			url:       phpr.webpath + 'index.php/Timecard/index/jsonStop',
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
					this.grid.updateData();
                    this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
                }
            })
        });
    },

    workingtimesStart:function() {
        // summary:
        //    This function deactivates the Timecard startwatch
        // description:
        //    This function calls jsonStart
		phpr.send({
			url:       phpr.webpath + 'index.php/Timecard/index/jsonStart',
            onSuccess: dojo.hitch(this, function(data) {
                new phpr.handleResponse('serverFeedback', data);
                if (data.type == 'success') {
					this.grid.updateData();
					this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
				}
            })
        });
    }
});