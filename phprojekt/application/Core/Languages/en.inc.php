<?php
// General Tab
$lang['Company Name'] = "Company Name";

// Words and phrases
// Visual Module Editor
$lang["Open Dialog"]    = "Open Dialog";
$lang["Designer"]       = "Designer";
$lang["Table"]          = "Table";
$lang["Field Name"]     = "Field Name";
$lang["Field Type"]     = "Field Type";
$lang["Table Lenght"]   = "Table Lenght";
$lang["Form"]           = "Form";
$lang["Label"]          = "Label";
$lang["Project List"]   = "Project List";
$lang["User List"]      = "User List";
$lang["Custom Values"]  = "Custom Values";
$lang["Select Type"]    = "Select Type";
$lang["Range"]          = "Range";
$lang["Default Value"]  = "Default Value";
$lang["List"]           = "List";
$lang["List Position"]  = "List Position";
$lang["General"]        = "General";
$lang["Status"]         = "Status";
$lang["Inactive"]       = "Inactive";
$lang["Required Field"] = "Required Field";

// Search
$lang["View all"] = "View all";
$lang["There are no Results"] = "There are no Results";

// Error messages from Module Designer
$lang["Module Designer"] = "Module Designer";
$lang["The Module must contain at least one field"] = "The Module must contain at least one field";
$lang["Please enter a name for this module"] = "Please enter a name for this module";
$lang["The module name must start with a letter"] = "The module name must start with a letter";
$lang["All the fields must have a table name"] = "All the fields must have a table name";
$lang["There are two fields with the same Field Name"] = "There are two fields with the same Field Name";
$lang["The length of the varchar fields must be between 1 and 255"] = "The length of the varchar fields must be "
    . "between 1 and 255";
$lang["The length of the int fields must be between 1 and 11"] = "The length of the int fields must be between 1 and "
    . "11";
$lang["Invalid form Range for the select field"] = "Invalid form Range for the select field";
$lang["The module must have a project selector called project_id"] = "The module must have a project selector called "
    . "project_id";

// User submodule
$lang["Already exists, choose another one please"] = "Already exists, choose another one please";

// Tooltip Help
$lang["Here can be configured general settings of the site that affects all the users."] = "Here can be configured "
    . "general settings of the site that affects all the users.";
$lang["Please choose one of the tabs of above."] = "Please choose one of the tabs of above.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Administration"] = "<br />
    This is the <b>General Help of Administration module</b><br />
    <br />
    This module is only accessible to users with Admin profile.<br />
    Here can be configured general settings of the site that affects <b>all the users</b>.<br />
    <br />
    It is divided into 4 tabs:<br />
    <br />
    <ul>
        <li><b>Module:</b> this is the Module Designer, a very easy-to-use visual drag & drop interface to create
            modules or modify the existing ones.<br />
        <li><b>Tab:</b> here you can create additional tabs to be shown in the modules.<br />
        <li><b>User:</b> for adding, modifying and deleting users of the system.<br />
        <li><b>Role:</b> to edit roles; a role is a set of permissions for the modules that is assigned to
            users.<br />
    </ul>
    <br />
    To see their help, click on the respective tab inside this window.<br />
    <br />
    <br />";

$lang["Content Help"]["Module"] = "<br />
    <b>Module tab</b><br />
    <br />
    The Module Designer is a very easy-to-use visual drag & drop interface to create modules or modify the existing
    ones.<br />
    <br />
    Firstly you press the <b>add</b> button or select an existing module from the grid. Then the form shows
    the label and permits you to choose whether it is currently active or not (whether it is shown in the system).
    <br />
    Pressing the <b>Open Dialog</b> button a pop-up window appears with the designer itself.<br />
    <br />
    <b>The Module Designer interface</b><br />
    <br />
    It has 3 panels:<br />
    <br />
    <ul>
        <li><b>The left fields panel:</b> here are all the field types; <i>text</i>, <i>date</i>, <i>time</i>,
            <i>select</i>, <i>checkbox</i>, <i>percentage</i>, <i>textarea</i> and <i>upload</i>.<br />
            You can drag & drop fields to the right panel, that right panel is the tab of
            the module you are creating or modifying as it will be seen (but without buttons <b>Edit</b> and
            <b>Delete</b>).<br />
            The <b>Edit</b> button in the right side of the fields, permits modifying its data before adding them to
            the module tab (right panel) although it is also possible to drag and drop it first and then edit it
            pressing that button in the right panel.<br />
            <br />
        <li><b>The right tabs panel:</b> this has the main tabs of the module as they are going to be seen when you
            open it outside the <b>Module Designer</b> (except for the Edit / Delete buttons). You add fields dragging
            and dropping them from the left panel, then pressing
            <b>Edit</b> button the edit panel is opened in the left bottom part of the window so that you
            can configure the field.<br />
            The fields can be reordered with drag & drop method and deleted from the
            tab dragging & dropping them back to the left panel or just pressing <b>Delete</b> button.<br />
            There are as many tabs in this panel as tabs are defined in module <b>Administration</b> submodule
            <b>Tab</b>.<br />
            You don't need to use all the tabs created. The tab will appear in the module only if there are fields
            inside it.<br />
            <br />
        <li><b>The left bottom editing panel:</b> here, when it is pressed the <b>Edit</b> button of a field, a
            window appears to modify its values and parameters.<br />
            It has 4 tabs:<br />
            <ul>
                <li><b>Table:</b> to edit the data of the database.<br />
                <li><b>Form:</b> to edit the data shown in the form.<br />
                <li><b>List:</b> to edit whether the field is shown in the list or not, and its position inside it.
                <br />
                <li><b>General:</b> general parameters.<br />
            </ul>
    </ul>
    <br />
    <b>How to create a module, from zero</b><br />
    <br />
    <ol>
        <li>Assuming you are in the <b>Module</b> tab of <b>Administration</b> module, press <b>Add</b> button.<br />
        <li>An empty form appears. Write the name of the new module in the <b>Label</b> textbox.<br />
        <li>Press the <b>Open Dialog</b> button. A big pop-up window containing the module designer appears.<br />
            You will see the two big panels, one at the left and one at the right, and an empty space in the left
            bottom where the field editing window eventually appears.<br />
            Inside the right panel (the module as it will be seen outside the designer) there is a Project select box.
            That field should exist for the module to work, it is the relation between the item and the projects, don't
            delete it.<br />
        <li>Add to the right panel using drag & drop a field of your choice. If there is more than one tab in the right
            panel, you can select the tab you want previous to the drag & drop to set the field there. In both cases,
            after dropping it the editing window appears for you to configure the field.<br />
            Note: to drop the field, you
            have to position it with the mouse over a place where the floating box that you are dragging converts
            itself from reddish pink to green color; that color means that you are able to drop the field there, up or
            behind another field.<br />
        <li>Configure the field attending to each of the 4 tabs of the editing window as explained here above.<br />
        <li>Repeat the steps 5 and 6 as many times as fields you want to add.<br />
        <li>Arrange the fields in the right panel in the order you want using drag & drop.<br />
        <li>Press the <b>Close</b> button in the left bottom of the window.<br />
        <li>The pop-up window has been closed. Press <b>Save</b> and the module is finished. The saving act, for
            example when <i>creating</i> a module, creates the table in the database, saves the parameters and creates
            the structure of folders and files.<br />
    </ol>
    <b>Notes:</b><br />
    <br />
    <ul>
        <li>After saving a new module, it is needed to refresh the page in the browser.<br />
        <li>The module will be added the <b>Access</b>, <b>Notification</b> and <b>History</b> tabs.<br />
        <li>There could be dragged back right panel fields to the left panel. This is useful to take them back later
            to the right panel, or to move them to another right panel tab.<br />
        <li><u>It is not recommended to modify the original modules that come with the system. Most of them have
            additional functionality that was not made with the Module Designer and could stop working if modified with
            it.</u><br />
    </ul>
    <br />
    <b>Other special tabs</b><br />
    <br />
    There are other tabs used in the system modules that are not defined here, nor could be modified. They depend on
    the module you are working with:<br />
    <br />
    <ul>
        <li>General modules: tabs <i>Access</i>, <i>Notification</i> and <i>History</i>.<br />
            All the modules <i>created</i> with the <b>Module designer</b> will have, apart from the tabs designed by
            the user, these 3 tabs explained in the help of most of the modules. <i>History</i> will only be shown in
            edit mode.</br>
        <li>Project module: tabs <i>Module</i> and <i>Role</i> explained in Project's help.<br />
        <li>Calendar module: tab <i>Recurrence</i> explained in Calendar's help.<br />
    </ul>
    <br />
    <br />";

$lang["Content Help"]["Tab"] = "<br />
    <b>TAB tab</b><br />
    <br />
    This section permits modifying the tabs of the modules.<br />
    <br />
    Its purpose is just to manage how many user defined tabs could exist in the modules, and their names. For example,
    if there are 3 tabs: the default 'Basic Data' one plus two created by you, in the 'Module Designer' you can define
    which tabs to use in a specific module; you don't need to use all the tabs created. The tab will appear in the
    module only if when modifying it with the <b>Module Designer</b> you drop fields inside it.<br />
    E.g.: you can create an extra tab 'Geographic Info' and create a module with the <b>Module designer</b>.
    If you just drag & drop fields to the 'Basic Data' tab and nothing to the 'Geographic Info' tab, then the second
    tab won't be seen in the module.<br />
    <br />
    <b>Other special tabs</b><br />
    <br />
    There are other tabs used in the system modules that are not defined here, nor could be modified.<br />
    <ul>
        <li>General modules: tabs <i>Access</i>, <i>Notification</i> and <i>History</i>.<br />
            All the modules <i>created</i> with the <b>Module designer</b> will have, apart from the tabs designed by
            the user, this 3 tabs explained in the help of most of the modules. <i>History</i> will only be shown in
            edit mode.</br>
        <li>Project module: tabs <i>Module</i> and <i>Role</i> explained in Project's help.<br />
        <li>Calendar module: tab <i>Recurrence</i> explained in Calendar's help.<br />
    </ul>
    <br />
    <br />";

$lang["Content Help"]["User"] = "<br />
    <b>User tab</b><br />
    <br />
    This section is designed to manage all the users of the system.<br />
    <br />
    Here could be added, modified and deleted the users of the site.<br />
    <br />
    <br />";

$lang["Content Help"]["Role"] = "<br />
    <b>Role tab</b><br />
    <br />
    This tab permits managing the Roles.<br />
    <br />
    A Role is a specific set of permissions for each module. That Role then
    is assigned to users of the Projects you want, so that he will have that rights. When you
    create or edit a Project, you can give every different user that Role inside Rol tab.<br />
    The final right for a specific user to work with an item is defined by the conjunction of the data defined both
    inside Role and Access tabs of Project module.<br />
    <br />
    By default, no Role is assigned to anybody, and the permission to access to a new project will be <b>Admin</b>
    access  for the creator and nothing for the rest.<br />
    <br />
    <b>Example</b>
    <br />
    You can create a Role called 'Can read TODOs and NOTEs'.<br />
    And set in that Role <i>Read</i> access to the <b>Todo</b> and <b>Note</b> modules.<br />
    Then you create a Project and assign the user 'john' the Role 'Can read TODOs and NOTEs', so when
    he enters the Project, the only thing he will be able to do apart from reading the main data of the Project itself
    is to read (but not modify) items of <b>Todo</b> and <b>Note</b> modules.<br />
    <br />
    <b>Note:</b> in the listing of modules that you select to assign rights to a rol, the <b>Project</b> module
        represents the Subprojects of Projects.<br />
    <br />
    <br />";

// Words and phrases
$lang["Please, log out and log in again to the application to apply the changes"] = "Please, "
    . "log out and log in again to the application to apply the changes";
$lang["The value for the setting is incorrect"] = "The value for the setting is incorrect";
$lang["The password and confirmation are different or one of them is empty"] = "The password and confirmation are "
    . "different or one of them is empty";
$lang["The old password provided is invalid"] = "The old password provided is invalid";
$lang["keyValue"] = "Configuration";
$lang["value"] = "value";
$lang["Confirm Password"] = "confirm password";
$lang["Old Password"] = "Old Password";
$lang["Language"] = "language";
$lang["Email"] = "Email";
$lang["Time Zone"] = "Time Zone";
$lang["Favorite projects"] = "Favorite projects";
$lang["Max Number of favorites projects"] = "Max Number of favorites projects";

// Tooltip Help
$lang["Tooltip"]["amount"] = "Projects that will be allowed to be seen in Timecard and will be ready to Drag and Drop.";
$lang["Tooltip"]["timeZone"] = "The Time Zone (UTC) for the region or country you will be using Phprojekt.<br />"
    . "This is required for all users but specially useful for international events time coordination.";
$lang["This module is for the user to set and change specific configuration parameters of his/her profile."] = "This "
    . "module is for the user to set and change specific configuration parameters of his/her profile.";
$lang["Please choose one of the tabs of above."] = "Please choose one of the tabs of above.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Setting"] = "<br />
    This is the <b>General Help of Setting module</b><br />
    <br />
    This module is for the user to set and change specific configuration parameters of his/her profile.<br />
    <br />
    It has 2 tabs: <b>User</b> and <b>Timecard</b>.<br />
    To see their help, click on the respective tab inside this window.<br />
    <br />
    <br />";

$lang["Content Help"]["User"] = "<br />
    <b>User tab</b><br />
    <br />
    Inside this tab the user basic settings are configured.<br />
    <br />
    The title of every field is self-descriptive about its content.<br />
    It can be changed: <i>Password</i>, <i>Email</i>, <i>Language</i> y <i>Time zone</i>.<br />
    <br />
    <br />";

$lang["Content Help"]["Timecard"] = "<br />
    <b>Timecard tab</b><br />
    <br />
    Here the user can configure the Projects where is working the most, so that they will appear for drag and drop
    inside the Timecard module.<br />
    <br />
    The title of every field is self descriptive about its content.<br />
    It can be changed: <i>Max Number of favorites projects</i> and which <i>Favorite projects</i> to choose.<br />
    <br />
    <br />";

