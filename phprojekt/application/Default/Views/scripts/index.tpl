<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Phprojekt {$phprojekt_version}</title>
	<style type="text/css">
		/* dojo.css holds basic sizing and usage, tundra.css holds specific coloring and styling for the tundra theme */
		/* This are the default colors of the widgets */
		@import "{$webPath}/scripts/dojo1.0/dijit/themes/tundra/tundra.css";
		@import "{$webPath}/scripts/dojo1.0/dojo/resources/dojo.css";	
	</style>
  <style type="text/css">
  {literal}
  body {
    margin-top: 0px;
    margin-left: 0px;
    margin-right: 0px;
    font-family: Verdana, Helvetica, sans;
    font-size: 12px;
  }

  #main caption {
    background: SlateGray;
    color: white;
  }

  #main td#treeView,
  #main td#listView,
  #main td#formView {
    background-color: Gainsboro;
    border: 1px outset black;
    vertical-align: top;
  }

  #main td#treeView a:link,
  #main td#treeView a:visited,
  #main td#treeView a:active {
    text-decoration: none;
  }

  #main td#treeView a:hover {
    background: #EFEFEF;
  }

  table.listView th {
    text-align: left;
    background-color: SlateGray;
    color: white;
  }

  table.listView td {
    background-color: white;
  }

  .error {
    color: red;
    margin-left: 2px;
  }

  #breadcrumb {
    background-color: White;
  }

  #tabView a:link,
  #tabView a:visited,
  #tabView a:active {
    font-size: 12px;
    font-weight: bold;
    color: #000000;
    background-color: SlateGray;
    cursor: pointer;
	text-align: center;
	padding-left: 5px;
    padding-right: 5px;
    padding-top: 2px;
    padding-bottom: 2px;
    color: White;
    text-decoration: none;
  }
  
  #tableFormView {
	border-spacing: 10px;
  }
  
  #tableFormView td {
  	background-color: white;
  }
  {/literal}  
  </style>
	<script type="text/javascript" src="{$webPath}/scripts/dojo1.0/dojo/dojo.js" djConfig="parseOnLoad: true, isDebug: true, usePlainJson: true"></script>
	<script type="text/javascript" src="{$webPath}/scripts/Controllers/Controller.js"></script>
	<script type="text/javascript" src="{$webPath}/scripts/Models/Model.js"></script>
	<script type="text/javascript" src="{$webPath}/scripts/Views/View.js"></script>
	<script type="text/javascript">
		dojo.require("dojo.parser");
		dojo.require("dijit.form.DateTextBox");
		dojo.require("dijit.form.ComboBox");
		dojo.require("dijit.form.ValidationTextBox");
		dojo.require("dijit.form.Textarea");
	</script>
	<script type="text/javascript">
	{literal}	
	var controller;	
	dojo.addOnLoad(function () {			
		controller = new Controller();
	});	
	
	function displayListAction(id) {
		controller.displayListAction(id);
	}
				  
	function displayBlock(field) {
		e = document.getElementById(field);
		if (e.style.display == 'inline') {
		    e.style.display = 'none';
		} else {
		    e.style.display = 'inline';
		}
  	}
  {/literal}
  </script>
</head>
<body class="tundra">
<a href="{url action="logout" module="Login"}">Logout</a>
<table width="100%" id="main">
  <caption>PHProjekt {$phprojekt_version}</caption>
  <tbody>
    <tr>
      <td rowspan="2" style="width:20%" id="treeView">{$treeView}</td>
      <td id="listView">
      <div id="breadcrumb">
      <a href="{url action="list" module="Project" nodeId="$projectId"}">{$projectName}</a>&nbsp;
      {if ($breadcrumb != 'Project')}
      /&nbsp;<a href="{url action="list" module="$breadcrumb"}">{$breadcrumb|translate}</a>
      {/if}
      /&nbsp;{$action|capitalize|translate}&nbsp;{$itemid}
      </div>
      <br />
      <div id="tabView">
      {foreach name=itemModule item=itemModule from=$modules}
	     <a href="javascript: displayListAction({$projectId});">{$itemModule|translate}</a>
<!--     <a href="{url action="list" module=$itemModule}">{$itemModule|translate}</a> -->
        {if $smarty.foreach.itemModule.iteration == $smarty.foreach.itemModule.last }
        <br /><br />
        {/if}
      {/foreach}
      </div>
      {$filterView}
      {$listView}
      </td>
    </tr>
    <tr>
      <td id="formView">{$formView}</td>
    </tr>
  </tbody>
</table>
</body>
</html>