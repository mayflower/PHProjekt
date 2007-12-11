/**
 * JavaScript Model for the Todo Module in PHProjekt 6.0
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
dojo.declare('Model', null,
{	
	/**
	 * @classDescription This class is responsible for saving the data on the client side and 
	 * 					 returning the data in the right form
	 * @constructor
	 */
	constructor : function(response)
    {
		this.responseData 	= eval(response);
		this.metadata		= eval(response['metadata']);
		
		/**
		 * Defining a hash- table and setting default values.
		 */
		this.dataForServer = new Object();
		this.dataForServer = {
			'title' 		: '',
			'notes'			: '',
			'startDate'		: '', 
			'endDate'		: '',
			'prority'		: '',
			'currentStatus' : '',	
			'projectname'	: ''						
		};
		
		/**
		 * Defining the possible values for the current status
		 * in the next versions this is no longer needed
		 */ 
		this.curStatus = new Array(	'Offered',
									'Ordered',
									'Re- opened',
									'Waiting',
									'Ended',
									'Stopped');
    },
	
	/**
	 * This method returns all the received data
	 * 
	 * @return {Object} responseData 
	 */
	fetchAll : function ()
	{
		return this.responseData;
	},
	
	/**
	 * Returns only the projects for displaying in the dropdown boxes.
	 * In addition duplicate entries will be removed.  
	 * 
	 * @return {Object} projectsHelper
	 */
	getProjects : function ()
	{		
		var projects = new Array();
		var projectsHelper = new Array();
	
		var i = 0
		var j = 0;

		// Getting the projects into an array
		for (i; i < this.responseData['data'].length; i++) {
			projects.push(this.responseData['data'][j]['title']);
			j++;
		}				
		
		// Remove duplicate entries
		var sort = new Array;;
		for (var i = 0, j = projects.length; i < j; i++) {
			sort[projects[i] + typeof projects[i]] = projects[i];
		}
				
		for (var key in sort) {
			projectsHelper.push(sort[key]);
		}				
		return projectsHelper;
	},
	
	/**
	 * This method returns an array with a scale (1 - 10) for the priority of a project.
	 * 
	 * @return {Object} scale 
	 * 
	 * TODO: remove this method in the next version, because all the information is coming from 
	 * the metadata
	 */
	getPriorityScale : function ()
	{
		var scale = new Array();
		var i = 0;
		var j = 1;
		
		for (i; i < 10; i++) {
			scale.push(j);
			j++;
		}
		return scale;
	},
	
	/**
	 * This method returns an array with the options for the current status.
	 * 
	 * @return {Object} curStatus
	 * TODO: remove this method in the next version, because all the information is coming from 
	 * the metadata	
	 */	
	setCurrentStatus : function ()
	{		
		return this.curStatus;	
	},
	
	/**
	 * Method for adding the values of a certain form into the array.
	 * 
	 * @param {String} key
	 * @param {String} value
	 * 
	 * @return void
	 * 
	 * @deprecated No longer needed since 04.12.2007
	 */
	setFormValues : function (key, value)	
	{
		this.dataForServer[key] = value;
	},
	
	/**
	 * Returns the data that will be sended to the server
	 * 
	 * @return {Object} dataForServer 
	 * 
	 * @deprecated This Method needs a refactoring
	 */
	dataForServer : function () 
	{
		if (this.dataForServer != null) {
			return this.dataForServer;
		} else {
			alert('Error! No data received!');
		}
	},
	
	/**
	 * This Method returns only the data with the assigned project id
	 * 
	 * @param {Integer} pId The project id is needed to know which list should be generated
	 * @return {Object} listData
	 */
	setDataForListView : function (pId)
	{
		var i = 0;
		var listData = [];
		
		for (i; i < this.responseData['data'].length; i++) {
			if (this.responseData['data'][i]['id'] == pId) {
				listData.push(this.responseData['data'][i]);
			}
		}			
		return listData;
	},
	
	/**
	 * This Method sets the data in right format for the 
	 * detail view
	 *
	 * @param {Integer} id 
	 * 
	 * @return {Object} detailData
	 */	
	setDataForDetailView : function (id)
	{
		var i = 0;
		var detailData = [];
		
		for (i; i < this.responseData['data'].length; i++) {
			if (this.responseData['data'][i]['id'] == id) {
				detailData.push(this.responseData['data'][i]);
			}
		}					
		return detailData;	
	},
	
	/**
	 * Setting the metadata into a usable format
	 * 
	 * @return {Object} metadata 
	 */
	setMetadata : function () {
		return this.metadata;
	}
}
);