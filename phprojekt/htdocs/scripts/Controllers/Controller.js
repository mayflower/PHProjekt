/**
 * JavaScript Controller for the Todo Module in PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
dojo.declare('Controller', null,
{
    /**
     * @classDescription This class is responsible for delegating the right model 
     * 					 to a certain view and executes the xhr- call  
     * @constructor
     * 
     * The constructor initialise a new view- object and calls the method that is responsible for
     * receiving the data from the server. 
     * 
     */
	constructor : function ()
    {	
		this.View = new View()
		this.getDataFromServer();
    },
	
	/**
	 * This method is responsible for calling the data from the server.
	 * The received data will be stored in the model.
	 * 
	 * @return void
	 * 
	 * TODO: remove the hardcoded way to get the data from the server
	 */
	getDataFromServer : function () {		
		self = this;
		dojo.xhrPost
		(
			{
				url         :   'http://localhost/phprojekt6/htdocs/index.php/Todo/index/jsonList/',
				handleAs    :   'json',
				timeout     :   5000,
				load        :   function(response) {
									self.Model = new Model(response);
								},
				error       :   function(response, ioArgs) {
							        alert("Error! No data received! " + ioArgs);
							        return response;
				    			}
			}
		);
	},
	    
	/**
	 * This method receives the data from the model 
	 * and calls a method from the view that is responsible
	 * for displaying the data
	 * 
	 * @param {Integer} id The palce where the list should be displayed
	 * @return void
	 */
    displayListAction : function (id)
    {
		var data 		= this.Model.setDataForListView(id);
		
		this.View.showListView(data);
		this.View.showAddBtn();	
    },

	/**
	 * This Method is responsible for displaying the Details of a list entry, selected by id.
	 * 
	 * @param {Integer} id
	 * 
	 * @return void
	 */
	displayDetailAction : function (id)
	{	
		var data = this.Model.setDataForDetailView(id)
		this.View.showDetailView(data);
	},
	
	/**
	 * This method is responsible for closing the detail- view
	 * 
	 * @return void
	 */
	closeAction : function ()
	{
		this.View.showAddBtn();	
	},
	
	/**
	 * Method for saving the data
	 * 
	 * @param data The data that will be saved after the asynchronous transfer
	 * @return void
	 * 
	 * @deprecated This method doesn´t work fine!
	 */
	saveAction : function ()
	{
		dojo.xhrPost (
			{
//				url		: '../htdocs/index.php/Todo/index/save',
				load	: function(data){
               		 		alert (data);					
       					 	},
       			error	: function(data){
               				 alert("An error occurred: " + data);
        					},
     		   	timeout	: 2000,
				content : {	
							projectId		: dijit.byId('dijit_form_ComboBox_0').getValue(),
							title			: dojo.byId('title').value,
							notes			: dijit.byId('dijit_form_Textarea_0').getValue(), 
							startDate		: dijit.byId('dijit_form_DateTextBox_0').getValue(),
							endDate			: dijit.byId('dijit_form_DateTextBox_1').getValue(),
							priority		: dijit.byId('dijit_form_ComboBox_1').getValue(),
							currentStatus	: dijit.byId('dijit_form_ComboBox_2').getValue()
						  }
			}
		);
	},
	
	/**
	 * Method to display the input form wich is a table
	 * 
	 * @return void
	 */
	displayInputFormAction : function ()
	{
		var projects 		= this.Model.getProjects();
		var priorityScale	= this.Model.getPriorityScale();
		var currentStatus	= this.Model.setCurrentStatus();
		var metadata		= this.Model.setMetadata();
		
		this.View.showInputForm(projects, priorityScale, currentStatus, metadata);	
	}
})