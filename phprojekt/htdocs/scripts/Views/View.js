/**
 * JavaScript View for the Todo Module in PHProjekt 6.0
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
dojo.declare('View', null,
{
    /**
     * @classDescription The View class is responsible for displaying the data received from the model
     * @constructor
     */
	constructor : function()
    {
		this.listRoot = dojo.byId('listView');
		this.formRoot = dojo.byId('formView');
		
		// Simple configuration for creating the table. 
		// Example: id : 'title' ==> <td id='title'></td>
		// this is no longer needed since 09.12.2007, MRuprecht
		this.tableConf = new Object();
		this.tableConf = [
					// Project
					{id				: 'projectId',
					 value			: 'Project',
					 name			: 'projectId',
					 hint			: 'Please enter a Project!'},
					// Title 
					{id				: 'title',
					 value			: 'Title',
					 name			: 'title',
					 hint			: 'Please enter a title!'},
					// Notes
					{id				: 'notes',
					 value			: 'Notes',
					 name			: 'notes',
					 hint			: 'Please enter any content!'},
					// Start date
					{id				: 'startDate', 
					 value			: 'StartDate',
					 name			: 'startDate',
					 hint			: 'Please enter a date! yyyy-mm-dd'},
					 // End date
					{id				: 'endDate',
					 value			: 'EndDate',
					 name			: 'endDate',
					 hint			: 'Please enter a date! yyyy-mm-dd'},
					 // Priority
					{id				: 'priority',
					 value			: 'Priority',
					 name			: 'priority',
					 hint			: 'Please select a value!'},
					// Current Status
					{id				: 'currentStatus',
					 value			: 'CurrentStatus',
					 name			: 'currentStatus',
					 hint			: 'Please select a value!'}];	
    },
	
	/**
	 * Method to show the received data in a table. If there is an id in
	 * the function- call, another function will be called to show the detail- view
	 * 
	 * @param {Object} data The data from the server
	 * 
	 * @return void
	 */
	showListView : function(data)
	{			
		var i = 0;
		var j = 0;
	
		var d 		= document;
		var root    = dojo.byId('listView');
		
		// creating the table
		var table  	= d.createElement('table');
		table.setAttribute('class', 'listView');
		table.width = '100%';
	
		var tbody	= d.createElement('tbody');
		var tr 		= d.createElement('tr');
		
		// getting the table headers		
		for (tableHeader in data[0]) {

		var th	= d.createElement('th');
			var txt	= d.createTextNode(tableHeader);
			
			th.appendChild(txt);
			tr.appendChild(th);
			tbody.appendChild(tr);
			table.appendChild(tbody);	
		}	
	
		// ... and the data
		for (i; i < data.length; i++) {
			var tr2	= d.createElement('tr');												
			for (var keyVar in data[i]) {											
				var td2	 = d.createElement('td');
				var link = d.createElement('a');
				link.setAttribute('href', 'javascript: controller.displayDetailAction( ' + data[i]['id'] + ');');
				
				var txt2 = d.createTextNode(data[i][keyVar]);
								
				link.appendChild(txt2);
				td2.appendChild(link);
				tr2.appendChild(td2);
				tbody.appendChild(tr2);					
			}
		}
		table.appendChild(tbody);
		this.listRoot.appendChild(table);
		this.listRoot.appendChild(d.createElement('br'));	
			
	},

	/**
	 * Method to show the whole form
	 * 
	 * Requires the data and the id from the data- set that detail- view
	 * should be displayed
	 * 
	 * @param {Object} data The data from the server
	 * @param {Integer} id The id witch data- set should be displayed
	 * 
	 * @return void
	 */
	showDetailView : function(data)
	{
		var i = 0;

		var d 		= document;
		var table  	= d.createElement('table');
		table.setAttribute('id', 'tableFormView');
	
		var tbody	= d.createElement('tbody');		
		
		for (i; i < data.length; i++) {
			for (keyVar in data[i]) {
				var tr 		= d.createElement('tr');				
				var td	= d.createElement('td');
				td.setAttribute('valign', 'top');
				var key = d.createTextNode(keyVar);
				td.appendChild(key);
				
				var td2	= d.createElement('td');
				var val = d.createTextNode(data[i][keyVar]);
				td2.appendChild(val);
				tr.appendChild(td);
				tr.appendChild(td2);
				tbody.appendChild(tr);
			}					
		}	
		table.appendChild(tbody);
		this.formRoot.innerHTML = '';
		this.formRoot.appendChild(table);
		this.formRoot.appendChild(d.createElement('br'));
		var link = d.createElement('a');
		link.setAttribute('href', 'javascript: controller.closeAction();');
		var txt  = d.createTextNode('close');
		link.appendChild(txt);
		this.formRoot.appendChild(link);
		this.formRoot.appendChild(d.createElement('br'));	
		this.formRoot.appendChild(d.createElement('br'));
	},
	
	/**
	 * Shows the "Add"- button in the formView- div
	 * 
	 * @return void
	 */
	showAddBtn : function ()
	{
		this.formRoot.innerHTML = '';
		var d = document;		
		this.formRoot.appendChild(d.createElement('br'));
		
		var link = d.createElement('a');
		link.setAttribute('href', 'javascript: controller.displayInputFormAction();');
		link.innerHTML = 'Add';
		this.formRoot.appendChild(link);
		
		this.formRoot.appendChild(d.createElement('br'));
		this.formRoot.appendChild(d.createElement('br'));
	},
	
	/**
	 * Executes the closing of the desired detail- view
	 * 
	 * @return void
	 */
	closeDetailView : function()
	{
		this.formRoot.innerHTML = '';
	},
		
	/**
	 * This methode creates a textbox programmatically, that can be a regular textbox or a one that is required.
	 * In case this is a validate textbox and there is no entry in there 
	 * the promptMessage will be shown
	 * 
	 * @param {String} elName The name of the created textbox
	 * @param {Boolean} mandatory If 'true' it´s required to enter anything, if 'false' not. Accepts only 'true' or 'false'
	 * @param {String} msg The displayed text if you type nothing in the textbox
	 * @param {Integer} refNode The place where the box should be displayed
	 * 
	 * @return void
	 */
	createTextbox : function (elName, mandatory, msg, refNode)
	{
		var properties = {
			name			: elName,
			required		: mandatory,												
			promptMessage	: msg,
			style			: 'width:180px'
		};		
		
		var w = new dijit.form.ValidationTextBox(properties, refNode);
		dojo.parser.parse(this.formRoot, true);
	},
	
	/**
	 * Method for creating a textarea programmatically
	 * 
	 * @param {String} elName The name of the created textarea
	 * @param {Integer} row Amount of rows that the textbox have
	 * @param {Integer} refNode Place where the textarea should be displayed
	 * 
	 * @return void
	 */
	createTextarea : function (elName, rowAmount, refNode)
	{
		var span = document.createElement('span');
		dojo.byId(refNode).appendChild(span);
		
		var properties = {
			name	: elName,
			rows	: rowAmount,
			style	: 'width:180px'		
		};
		var w = new dijit.form.Textarea(properties, span);
	},
	
	/** 
	 * Method for creating a date from
	 * 
	 * @param {String} elName The name of the created textarea
	 * @param {Integer} refNode Place where the dateform should be displayed
	 * 
	 * @return void
	 */
	createDateForm : function (elName, refNode)
	{
		var input = document.createElement('input');
		input.setAttribute('dojoType', 'dijit.form.DateTextBox');
		input.setAttribute('required', 'true');
		input.setAttribute('type', 'text');
		input.setAttribute('name', elName);
		input.setAttribute('value', '2007-09-05');
		input.setAttribute('size', '8');	
		input.setAttribute('style', 'width:180px');

		dojo.byId(refNode).appendChild(input);
		dojo.parser.parse(this.formRoot, true);
	},
	
	/**
	 * Method for creating a dojo combo- box. This widget is similar to a "drop down input"- element.
	 * This method requires an array with the possible options. 
	 * 
	 * @param {Object} opt An array of the possible options
	 * @param {Integer} id The id of the referenced node
	 * 
	 * @return void
	 */
	createComboBox : function (opt, refNode)
	{
		if (opt != null) {
			var options = opt;
			var d		= document;
			var select	= d.createElement('select');
			select.setAttribute('dojoType', 'dijit.form.ComboBox');
			select.setAttribute('autocomplete', 'false');
			select.setAttribute('style', 'width:180px');
			
			var i = 0;
			var j = 0;
			
			for (i; i < options.length; i++) {
				var option	= d.createElement('option');
				option.selected = 'selected';
				option.innerHTML = options[j];
				
				select.appendChild(option);						
				j++;
			}
			dojo.byId(refNode).appendChild(select);	
			dojo.parser.parse(this.formRoot, true);
			
		}else {
			alert('No data received!');	
		}		
	},
	
	/**
	 * This Methode is responsible for displaying the whole table
	 * 
	 * @param {Object} projects Array of the currently existing projects
	 * @param {Object} priorityScale Array of the scale 
	 * @param {Object} currentStatus Array with a list of possible values for the current status
	 * @param {Object} metadata Array with all the necessary informations about the dojo widgets
	 * 
	 * @return void
	 */
	showInputForm : function (projects, priorityScale, currentStatus, metadata)
	{	
					
		var d = document;
		var i = 0;
		var j = 0;
				
		var form = d.createElement('form');

		form.method = 'POST';
		form.id = 'addForm';
		
		var link = d.createElement('a');
		// hardcodes link tho the save action, but not implemented
		// link.href = 'javascript: controller.saveAction();';
		link.href = '#';
		link.innerHTML = 'Send';

		//first create the table...
		var table = d.createElement('table');
		table.id = 'tab';
		
		var thead	= d.createElement('thead');
		table.appendChild(thead);
		var tbody	= d.createElement('tbody');		
		
		for (i; i < this.tableConf.length; i++){
			var tr	= d.createElement('tr');				
			
			for (var keyVar in this.tableConf[j]) {					
				var td		 = d.createElement('td');					
				td.valign    = 'top';
				td.innerHTML = this.tableConf[j]['value'];
				
				var td2 	 = d.createElement('td');
				td2.id 		 = this.tableConf[j]['id'];									
				td2.name	 = this.tableConf[j]['name'];
			}
			j++;
			tr.appendChild(td);
			tr.appendChild(td2);
			tbody.appendChild(tr);	
		}	
		table.appendChild(tbody);
		form.appendChild(table);
		form.appendChild(link);
		this.formRoot.appendChild(form);
		
		//... and now the dojo widgets
		this.createComboBox(projects, this.tableConf[0]['id']);	
		this.createComboBox(priorityScale, this.tableConf[5]['id']);	
		this.createComboBox(currentStatus, this.tableConf[6]['id']);
		this.createTextarea(this.tableConf[2]['name'], 1, this.tableConf[2]['id']);
		this.createDateForm(this.tableConf[3]['name'], this.tableConf[3]['id']);
		this.createDateForm(this.tableConf[4]['name'], this.tableConf[4]['id']);
		this.createTextbox(this.tableConf[1]['name'], 'true', this.tableConf[1]['hint'], this.tableConf[1]['id']);			
	}
}
)