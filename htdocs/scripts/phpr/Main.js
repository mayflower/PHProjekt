// This script loads all scripts which are needed for the complete Application
dojo.provide("phpr.Main");

//Load General widgets
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.Menu");
dojo.require("dojox.dtl");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.Textarea");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dojo.date.locale");

//Load widgets from Lib
dojo.require("phpr.Component");

//Load all module mains
//When a new Module is created please add here
dojo.require("phpr.Todo.Main");
dojo.require("phpr.Default.Main");
dojo.require("phpr.Project.Main");
dojo.require("phpr.Note.Main");
dojo.require("phpr.Administration.Main");

dojo.declare("phpr.Main", null, {
    // summary: Main class for PHProjekt Gui
        
    constructor: function(/*String*/webpath, /*String*/currentModule, /*Int*/rootProjectId){
        // summary:
        //    Initialize all components for the javascript Userinterfae.
        // description:
        //    I.e. if the module name is "project" this.publish("open)
        //    will then publish the topic "project.open".
        // webpath: String
        //    The path to the htdocs folder of the current PHProjekt Installation.
        // currentModule: String
        //    The module which should be displayed on start
        // rootProjectId): Int
        //    The Id of the root Project - This is important as user rights depend on Project
        
        phpr.module           = currentModule;
        phpr.webpath          = webpath;
        phpr.rootProjectId    = rootProjectId; 
        phpr.currentProjectId = rootProjectId ;
        
        //All modules are initialized in the constructor
        this.Todo           = new phpr.Todo.Main();
        this.Note           = new phpr.Note.Main();
        this.Project        = new phpr.Project.Main();
        this.Administration = new phpr.Administration.Main();
        
        //The load method of the currentModule is called
        dojo.publish(phpr.module + ".load");
    }
});