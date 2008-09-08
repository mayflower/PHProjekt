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
		var buttons = "<a href='javascript:dojo.publish(\"Workingtimes.start\")'>"+phpr.Timecard.nls.timecardWorkingtimeStart+"</a>";
		buttons += "&nbsp;";
		buttons += "<a href='javascript:dojo.publish(\"Workingtimes.stop\")>"+phpr.Timecard.nls.timecardWorkingtimeStop+"</a>";
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
        // summary:
        //    Change the list view deppend on the view param
        // description:
        //    Change the list view deppend on the view param		
		this._view = view;
		if (view == 'today') {
			this.grid.reloadView(this._view); 
		} else {
			this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
		}
	},
		
    changeDate:function(date) {
        // summary:
        //    Update the date and reload the views
        // description:
        //    Update the date and reload the views		
		this._date = date
		this.grid.reloadView(this._view, this._date.getFullYear(), (this._date.getMonth()+1));
		this.form.setDate(this._date);
		this.form.loadView(this._date);
    },
	
    getIsoDate:function(date) {
        // summary:
        //    Convert a js date into ISO date
        // description:
        //    Convert a js date into ISO date		
		var day = date.getDate();		
        if (day < 10) {
			day = '0'+day; 
		}
        var month = (date.getMonth()+1);       
        if (month < 10) {
            month = '0'+month 
        }
        return date.getFullYear() + '-' + month + '-' + day;
    },
    
    getIsoTime:function(time){
        // summary:
        //    Convert a js time into ISO time
        // description:
        //    Convert a js time into ISO time		
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
					phpr.DataStore.deleteData({url: this.form._hourUrl}); 
                    this.changeDate(new Date());
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
					this.changeDate(new Date());
				}
            })
        });
    }
});