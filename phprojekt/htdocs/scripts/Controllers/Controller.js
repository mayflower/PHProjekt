/**
 * JavaScript Controller for the Todo Module in PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007/2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
dojo.declare('Controller', null,
{
    /**
     * @classDescription This class is responsible for delegating the right model to a certain view and executes the xhr- call  
     * 
     * @constructor
     * The constructor initialise a new view- object and calls the method that is responsible for
     * receiving the data from the server.
     */
	constructor : function (path)
    {	
		this.View = new View();	
		this.webpath = path;	
    },

	/**
	 * This method is responsible for calling the data from the server.
	 * The received data will be stored in the model.
	 * 
	 * @return void
	 */
	getDataFromServer : function (module, view) {				
		self = this;
		dojo.xhrPost
		(
			{				
				url         :	this.webpath + 'index.php/' + module + '/index/jsonList/view/'+view,
				handleAs    :   'json',
				timeout     :   5000,			
				load        :   function(response) {													
									var received = typeof(response);
									if (received == 'object') {
										self.Model = new Model(response);										
									} else {
										alert('Error! Wrong data format received from the server!\n\n Expected: object\n Received: ' + received);
										return response;
									}
								},
				error       :   function(response, ioArgs) {					
							        alert('Error! No data received! ' + ioArgs);
							        return response;
				    			},
				sync		:	true,
			}
		);
	},
	    
	/**
	 * This method receives the data from the model 
	 * and calls a method from the view that is responsible
	 * for displaying the data in a certain div, that 
	 * 
	 * @param {Integer} id The id to which project the list belongs to
	 * @param {String} module Which module?
	 * @param {String} div The name of the div that will be generated
	 * @return void
	 */
    displayListAction : function (id, module, div)
    {	
		var distinction = '';
		
		// @todo: fixme beautiful 
		// to differ the Todo module from the Project module
		if (module == 'Todo') {
			distinction = 'projectId';
		} else {
			distinction = 'parent';
		}
		
		// getting the right data from the Model
		var data = this.Model.setDataForListView(id, distinction);
		
		// ... and calling the list generating View
		this.View.showListView(data, div);
		this.View.showAddBtn(module);	
    },

	/**
	 * This Method is responsible for displaying the Details of a list entry, selected by id.
	 * 
	 * @param {Integer} id The 
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
	 */
	saveAction : function (id, module)
	{
		dojo.xhrPost (
			{
				url: this.webpath + 'index.php/' + module + '/index/save/id/' + id,
				load: function(data){
				     //controller.displayListAction(id, module, 'listView');	
				     //controller.displayTreeAction('treeStore');	
				},
				error: function(data){
					alert("An error has occurred: " + data);
				},
				timeout: 1000000,
				content: {
					id: id,
					projectId: dijit.byId('projectId').getValue(),
					title: dojo.byId('title').value,
					notes: dijit.byId('dijit_form_Textarea_0').getValue(),
					
					// its necessary to use the "old school way" without dojo to get the value of the date text boxes,
					// because there is a bug in using dijit.byId('anyId').getValue()
					startDate: document.getElementById('dijit_form_DateTextBox_0').value,
					endDate: document.getElementById('dijit_form_DateTextBox_1').value,
					priority: dijit.byId('priority').getValue(),
					currentStatus: dijit.byId('currentStatus').getValue()
				}
			}
		);

	},
	
	/**
	 * Method to display the input form which is a table
	 * 
	 * @return void
	 */
	displayInputFormAction : function (module)
	{
		var projects 		= this.Model.getProjects();
		var priorityScale	= this.Model.getPriorityScale();
		var currentStatus	= this.Model.setCurrentStatus();
		var metadata		= this.Model.setMetadata();
		
		this.View.showInputForm(projects, priorityScale, currentStatus, metadata, module);	
	},
    
	/**
	 * Method to display the tree view
	 * @param treeStoreName
	 * @return void
	 */
	displayTreeAction : function (treeStoreName)
	{
		this.View.showTreeView(treeStoreName);
	}
	
		
})