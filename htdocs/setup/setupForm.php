<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>PHProjekt 6 - Setup routine</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon" />
    <style>
        @import "../dojo/dojo/resources/dojo.css";
        @import "../css/themes/phprojekt/phprojekt.css";
        @import "../dojo/dijit/themes/dijit.css";
        @import "../dojo/dijit/themes/dijit_rtl.css";
    </style>
    <script type="text/javascript" src="../dojo/dojo/dojo.js"
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
            <div dojoType="dijit.layout.ContentPane" duration="200"    minSize="20" region="leading" class="column span-5 left" style="background: #294064;" id="navigation-container">
                <div id="subheader" class="block">
                    <div id="navigation-container-titel">
                    </div>
                </div>
             </div>
               <div dojoType="dijit.layout.BorderContainer" liveSplitters="false" region="center" style="width:80%; background-color:#FFFFFF" id="centerContent" >
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
                <hr />
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