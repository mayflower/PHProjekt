<?php
/**
 * Setup routine
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: setupForm.php 828 2008-07-07 02:05:54Z gustavo $
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

if (!defined('SETUP_ROUTINE')) die('Please use this page only with setup routine');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>PHProjekt 6 - Setup routine</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon" />
    <style>
        @import "../scripts/dojo/dojo/dojo.css";
        @import "../css/themes/phprojekt/phprojekt.css";
    </style>
    <script type="text/javascript" src="../scripts/dojo/dojo/dojo.js"
     djConfig="isDebug: true, parseOnLoad: true, useCommentedJson: true"></script>
	<script type="text/javascript">
		dojo.require("dojo.parser");
		dojo.require("dijit.form.Form");
    	dojo.require("dijit.form.Button");
		dojo.require("dijit.form.Textarea");
		dojo.require("dijit.form.TextBox");
		dojo.require("dijit.layout.BorderContainer");
		dojo.require("dijit.layout.ContentPane");
	</script>

</head>
	<body class="phprojekt">
    	<div dojoType="dijit.layout.BorderContainer" id="completeContent">
    		<div id="header" region="top" class="block" style="height:55px;" >
    		    <img class="left" src="../img/logo.png" alt="PHProjekt 6" />
			</div>

			<!-- Border Container which splits page in navigation and form/list view: -->
			<div dojoType="dijit.layout.BorderContainer" liveSplitters="false" region="center"  style="margin-top: 55px;">
			<!-- navigation panel -->

    		<div dojoType="dijit.layout.ContentPane" duration="200"	minSize="20" region="leading" class="column span-5 left" style="background: #294064;" id="navigation-container">
    			<div id="subheader" class="block">
 	    			<div id="navigation-container-titel">
        			</div>
				</div>
     		</div>
   			<div dojoType="dijit.layout.BorderContainer" liveSplitters="false" region="center" style="width:80%;" id="centerContent" >
    		<!-- Submodule navigation: -->
    			<div class="block">

        			<div class="column">
        				<img src="../img/subheaderborder.png" alt="" />
        			</div>
        			<div class="column left span-17 append-1 align-left" id="subModuleNavigation">
        			Welcome to PHProjekt 6 Setup routine!
					</div>
 				</div>
 				<!-- spaces -->
        		<hr class="space"/>
        		<hr>

        		<hr class="space"/>
        		<!-- Exception Form -->
        		<div id="serverFeedback" class="prepend-24"><%ERROR_MESSAGE%></div>
			    <form name="frm" action="setup.php" method="POST" onsubmit="return validateForm()">
     				<table width="100%" align="center" class="form">
     				<col class="col1" />
    					<tr>
        					<td class="label"><label for="mysql_server">Mysql server:</label></td>

        					<td>
        					    <input dojoType="dijit.form.TextBox" type="text" name="mysql_server" value="<%MYSQL_SERVER%>" />
        					</td>
    					</tr>
    					<tr>
        					<td class="label"><label for="mysql_user">Mysql username:</label></td>

        					<td>
        					    <input dojoType="dijit.form.TextBox" type="text" name="mysql_user" value="<%MYSQL_USERNAME%>" />
        					</td>
    					</tr>
    					<tr>
        					<td class="label"><label for="mysql_password">Mysql password:</label></td>

        					<td>
        					    <input dojoType="dijit.form.TextBox" type="password" name="mysql_pass" value="<%MYSQL_PASS%>" />
        					</td>
    					</tr>
    					<tr>
        					<td class="label"><label for="mysql_database">Mysql database:</label></td>

        					<td>
        					    <input dojoType="dijit.form.TextBox" type="text" name="mysql_database" value="<%MYSQL_DATABASE%>" />
        					</td>
    					</tr>
    					
    					<tr>
        					<td class="label"><label for="admin_pass">Admin user pass:</label></td>
        					<td>
        					    <input dojoType="dijit.form.TextBox" type="password" name="admin_pass" value="" />

        					</td>
    					<tr>
    					<tr>
        					<td class="label"><label for="admin_pass_confirm">Admin pass confirm:</label></td>
        					<td>
        					    <input dojoType="dijit.form.TextBox" type="password" name="admin_pass_confirm" value="" />

        					</td>
    					<tr>
    					<tr>
    						<td  class="label"><button dojoType="dijit.form.Button" baseClass="positive" id="submitButton" type="submit" iconClass="tick">
									Install
								</button>
							</td>
    						<td></td>
    					</tr>

    				</table>

    			</form>
         	</div>
		</div>
		</div>
	</body>
</html>
<script>
function validateForm() {
    returnValue = true;
    frm = document.forms[0];
    
    if (frm.admin_pass.value != frm.admin_pass_confirm.value) {
        alert("The admin password and confirmation are different");
        returnValue = false;
    }
    if (returnValue && frm.admin_pass.value == "") {
        alert("The admin password can't be empty");
        returnValue = false;
    }
    if (returnValue && frm.mysql_server.value == "") {
        alert("The MySQL server address can't be empty");
        returnValue = false;
    }
    if (returnValue && frm.mysql_user.value == "") {
        alert("The MySQL user can't be empty");
        returnValue = false;
    }
    if (returnValue && frm.mysql_database.value == "") {
        alert("Please, select a database to install PHProjekt 6");
        returnValue = false;
    }
    
    return returnValue;
}
</script>