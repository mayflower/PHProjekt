dojo.provide("phpr.Timecard.Main");

// app specific files
dojo.require("phpr.Default.Main");
dojo.require("phpr.Timecard.Tree");
dojo.require("phpr.Timecard.Grid");
dojo.require("phpr.Timecard.Form");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
	constructor:function() {
		this.module     = 'Timecard';
		this.gridWidget = phpr.Timecard.Grid;
		this.formWidget = phpr.Timecard.Form;
		this.treeWidget = phpr.Timecard.Tree;
        this.updateUrl  = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;

		// Subscribe to all topics which concern this module
		dojo.subscribe("Timecard.load", this, "load");
		dojo.subscribe("Timecard.changeProject", this, "setProject");
		dojo.subscribe("Timecard.reload", this, "reload");
		dojo.subscribe("Timecard.changeDate", this, "setDate");
		dojo.subscribe("Timecard.form.Submitted", this, "submitForm");
        dojo.subscribe("Timecard.submitSearchForm", this, "submitSearchForm");
		dojo.subscribe("Timecard.showSearchResults", this, "showSearchResults");
        dojo.subscribe("Timecard.drawTagsBox", this, "drawTagsBox");
		dojo.subscribe("Timecard.showTagsResults", this, "showTagsResults");
		dojo.subscribe("Timecard.updateCacheData", this, "updateCacheData");
        dojo.subscribe("Workingtimes.start", this, "workingtimesStart");
        dojo.subscribe("Workingtimes.stop", this, "workingtimesStop");
	},

    load:function() {
        // summary:
        //    This function initially renders the page
        // description:
        //    This function should only be called once as there is no need to render the whole page
        //    later on. Use reload instead to only replace those parts of the page which should change

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        this.render(["phpr.Default.template", "main.html"], dojo.body(),{
            webpath:phpr.webpath,
            currentModule:phpr.module,
            searchText:phpr.nls.search,
            administrationText:phpr.nls.administration,
            administratorText:phpr.nls.administrator,
            settingsText:phpr.nls.settings,
            timecardText:phpr.nls.timecard,
            timecardOverviewText:phpr.nls.timecardOverview,
            timecardWorkingtimeText:phpr.nls.timecardWorkingtime,
            timecardWorkingtimeStartText:phpr.nls.timecardWorkingtimeStart,
            timecardWorkingtimeStopText:phpr.nls.timecardWorkingtimeStop,
            helpText:phpr.nls.help,
            logoutText:phpr.nls.logout,
        });
        this.render(["phpr.Timecard.template", "mainContent.html"], dojo.byId('centerMainContent') ,{
            webpath:phpr.webpath,
            currentModule:phpr.module
        });

        this.search = new dojo.dnd.Moveable("searchsuggest");
        this.tags = new dojo.dnd.Moveable("tagsbox");

        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.setSubmoduleNavigation();
                this.setSearchForm();
                this.tree = new this.treeWidget(this);
                this.grid = new this.gridWidget(this.updateUrl, this, phpr.currentProjectId);
                //this.form = new this.formWidget(this);
            })
        );
    },

    reload:function() {
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");

        this.render(["phpr.Timecard.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubmoduleNavigation();
        if (!this.search) {
            this.search = new dojo.dnd.Moveable("searchsuggest");
        }
        if (!this.tags) {
            this.tags = new dojo.dnd.Moveable("tagsbox");
        }
        this.setSearchForm();
        this.tree     = new this.treeWidget(this);
        this.grid     = new this.gridWidget(this.updateUrl, this, phpr.currentProjectId);
        //this.form     = new this.formWidget(this,ParamsIn);
    },

    setProject: function(project) {
        // summary:
        //    this function changes the Project in the Timecard form
        // description:
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = project.id;
        if(!phpr.currentProjectId) phpr.currentProjectId = phpr.rootProjectId;
        dijit.byId('tcProjectId').setValue(project.id);
    },

    setDate: function(date) {
         dateFormatted = dojo.date.locale.format(date, {formatLength:'full',selector:'date', locale:this.lang});
         dojo.byId("tcFormHeader").innerHTML = "<h3>Zeiterfassung f&uuml;r den "+dateFormatted+"</h3>";
         dojo.byId("tcBookingsSummary").innerHTML = "<h4>Zeit die am "+dateFormatted+" erfasst wurde:</h4>";
         dojo.byId('tcProjectDate').value = date;
         dojo.byId('tcDate').value = date;
    },

    workingtimesStop: function() {
        // summary:
        //    This function deactivates the Timecard stopwatch
        // description:
        //    This function calls jsonStop
		phpr.send({
			url:       phpr.webpath + 'index.php/Timecard/index/jsonStop'
            //onSuccess: this.publish("reload")
        });
    },

    workingtimesStart: function() {
        // summary:
        //    This function deactivates the Timecard startwatch
        // description:
        //    This function calls jsonStart
		phpr.send({
			url:       phpr.webpath + 'index.php/Timecard/index/jsonStart'
            //onSuccess: this.publish("reload")
        });
    }
});