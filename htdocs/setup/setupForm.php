<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>PHProjekt</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon" />
    <style type="text/css">
        @import "../css/themes/phprojekt/phprojektCssCompiler.php";
    </style>
    <script type="text/javascript">
        var djConfig = {isDebug: false, parseOnLoad: true, useCommentedJson: true};
    </script>
    <script type="text/javascript" src="../dojo/dojo/dojo.js"></script>
    
    <script type="text/javascript">
        dojo.require("dojo.parser");
        dojo.require("dijit.form.Form");
        dojo.require("dijit.form.Button");
        dojo.require("dijit.form.Textarea");
        dojo.require("dijit.form.TextBox");
        dojo.require("dijit.layout.BorderContainer");
        dojo.require("dijit.layout.ContentPane");
    </script>
    <script type="text/javascript">
        function init() {
            if (document.layers) {
                availHeight = window.innerHeight + window.pageYOffset;
            } else if (document.all) {
                availHeight = document.documentElement.clientHeight + document.documentElement.scrollTop;
            } else if (document.getElementById) {
                availHeight = window.innerHeight + window.pageYOffset;
            }
            dojo.style(dojo.byId('completeContent'), "height", availHeight + "px");
        }
        dojo.addOnLoad(init);
        window.onresize = function() {
            init();
        };
    </script>
</head>
<body class="phprojekt">
<div id="completeContent" dojoType="dijit.layout.ContentPane"
    style="width: 100%; height: 1000px; overflow: hidden;">
    <!-- Top Bar menu -->
    <div dojoType="dijit.layout.ContentPane" id="header" region="top" splitter="false"
    style="height:55px;">
        <img class="left" src="../img/logo.png" alt="PHProjekt 6" />
        <div id="mainNavigation" class="right align-right">
        </div>
    </div>
    <!-- Left Content -->
    <div id="navigation-container"
    style="width:15%; height: 100%">
        <div dojoType="dijit.layout.ContentPane">
            <div id="subheader" class="block">
                <div id="navigation-container-titel">
                </div>
            </div>
            <!-- Tree menu -->
            <div id="tree-navigation" dojoType="dijit.layout.ContentPane">
                <div dojoType="dijit.layout.ContentPane" id="treeBox">
                </div>
            </div>
        </div>
    </div>
    <!-- Center Content -->
    <div dojoType="dijit.layout.ContentPane"
    style="position: absolute; top: 55px; left: 16%; width: 100%;">
        <div dojoType="dijit.layout.ContentPane" design="sidebar"
        style="width: 84%; height: 100%;">
            <!-- Second Top Bar -->
            <div dojoType="dijit.layout.ContentPane" region="top" class="block"
            style="height: 7%;" splitter="false">
                <!-- Submodule navigation -->
                <div class="column left span-17 append-1 align-left" id="subModuleNavigation">
                </div>
                <!-- Add / Save Buttons -->
                <div id="buttonRow" class="align-right append-0">
                </div>
                <!-- spaces -->
                <hr class="space"/>
                <!-- Exception Form -->
                <div id="serverFeedback" class="prepend-24" ><%ERROR_MESSAGE%></div>
                <form name="frm" action="setup.php" method="POST" onsubmit="return validateForm()">
                     <table width="100%" align="center" class="form">
                     <col class="col1" />
                        <tr>
                            <td class="label"><label for="server_type">Server type</label></td>

                            <td>
                                <select name="server_type"><%SERVER_TYPE%></select>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="server_host">Database server:</label></td>

                            <td>
                                <input dojoType="dijit.form.TextBox" type="text" name="server_host" value="<%SERVER_HOST%>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="server_user">Database username:</label></td>

                            <td>
                                <input dojoType="dijit.form.TextBox" type="text" name="server_user" value="<%SERVER_USERNAME%>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="server_password">Database password:</label></td>

                            <td>
                                <input dojoType="dijit.form.TextBox" type="password" name="server_pass" value="<%SERVER_PASS%>" />
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><label for="server_database">Database name:</label></td>

                            <td>
                                <input dojoType="dijit.form.TextBox" type="text" name="server_database" value="<%SERVER_DATABASE%>" />
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
                            <td></td>
                            <td>
                            <h3>Migration</h3>
                            Please, provide the path to the config.inc.php file of your old PHProjekt 5.x.<br />
                            <u>Notes</u>:<br />
                            <ul>
                              <li>It could take several minutes depending on the number of projects to be migrated.</li>
                              <li>The migration is compatible with version 5.1 or superion.</li>
                              <li>The config.inc.php needs to be on the same server where you are installing PHProjekt 6</li>
                              <li>User passwords will be migrated from version 5.2.1 or better. All previous versions migration will have the same username and password.</li>
                              <li>Root user will not be migrated. Please use the admin user.</li>
                              <li>The modules to be migrated are: Calendar, Projects, Notes, Todos, Timeproj, Timecard and Filemanager.</li>
                              <li>Customized module fields will not be migrated.</li>
                              <li>If Filemanager files does not work after migration please try moving manually the uploaded files (e.g. move the files on /phprojekt5.x/uploads to /phprojekt6/uploads).</li>
                            </ul>
                            </td>
                        <tr>
                        <tr>
                            <td class="label"><label for="migration_config">Configuration file:</label></td>
                            <td>
                                <input dojoType="dijit.form.TextBox" type="text" name="migration_config" value="<%MIGRATION_CONFIG%>" />
                                <br />File path (e.g. /var/www/html/config.inc.php)

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
    if (returnValue && frm.server_host.value == "") {
        alert("The database server address can't be empty");
        returnValue = false;
    }
    if (returnValue && frm.server_user.value == "") {
        alert("The Database user can't be empty");
        returnValue = false;
    }
    if (returnValue && frm.server_database.value == "") {
        alert("Please, select a database to install PHProjekt 6");
        returnValue = false;
    }
    
    return returnValue;
}
</script>