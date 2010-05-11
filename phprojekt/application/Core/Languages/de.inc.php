<?php
// Core
  // Fields
$lang["Label"] = "Beschriftung";
  // System
$lang["Setting"] = "Einstellung";
$lang["Administration"] = "Administration";
  // Messages
$lang["Admin section is only for admin users"] = "Admin-Bereich ist nur für den Admin-Benutzer";
  // View
$lang["This module is for the user to set and change specific configuration parameters of his/her profile."] = "Dieses "
    . "Modul ermöglicht dem Benutzer seine Konfigurationsparameter und sein Profil zu ändern.";
$lang["Here can be configured general settings of the site that affects all the users."] = "Hier können allgemeine "
    . "Einstellungen der Website angepasst werden. Die Änderungen betreffen ale Nutzer.";
$lang["Please choose one of the tabs of above."] = "Bitte wählen Sie eine der Registerkarten oben.";

// User
  // System
$lang["User"] = "Benutzer";
  // Fields
$lang["Confirm Password"] = "Passwort bestätigen";
$lang["Old Password"] = "Altes Passwort";
$lang["Admin"] = "Admin";
  // Messages
    // System
$lang["Already exists, choose another one please"] = "existiert bereits, bitte wählen Sie einen anderen";
$lang["The value for the setting is incorrect"] = "Der Wert für die Einstellung ist falsch";
$lang["The password and confirmation are different or one of them is empty"] = "Das Passwort und die "
    . "Passwortwiederholung sind unterschiedlich.";
$lang["The old password provided is invalid"] = "Das alte Kennwort ist ungültig";
$lang["The Time zone value is out of range"] = "Die Zeitzone liegt außerhalb der Reichweite";
$lang["The Language value do not exists"] = "Es ist keine Sprache eingestellt";
$lang["Invalid email address"] = "Ungültige E-Mail-Adresse";
$lang["You need to log out and log in again in order to let changes have effect"] = "Sie müssen sich abmelden und "
    . "wieder anmelden, um Änderungen wirksam  zu machen.";
  // Tooltip Help
$lang["Tooltip"]["timeZone"] = "Die Zeitzone (UTC) für die Region oder Land, in dem Sie PHProjekt verwenden werden. "
    . "<br /> Dies ist für alle Benutzer erforderlich, aber besonders nützlich für zeitliche Koordination "
    . "internationaler Veranstaltungen.";
$lang["Tooltip"]["password"] = "Für neue Benutzer ist dies ist ein Pflichtfeld. Wenn Sie nicht möchten, das "
    . "Passwort zu ändern, lassen Sie dieses Feld für bestehende Nutzer leer.";

// Module
  // System
$lang["Module"] = "Module";
  // Fields
$lang["Form"] = "Form";
$lang["Normal"] = "Normal";
$lang["Global"] = "Global";
$lang["Open Editor"] = "Editor Öffnen";
  // Tooltip help
$lang["Open a dialog where you can drag and drop many fields for create the form as you want."] = "Öffnen Sie ein "
    . "Dialogfeld, in dem Sie per Drag & Drop viele Felder für das Formular erstellen können.";
  // Module Designer
    // Fields
$lang["Database"] = "Datenbank";
$lang["Field name"] = "Feldname";
$lang["Field type"] = "Feldtyp";
$lang["Field lenght"] = "Länge des Feldes";
$lang["Select Type"] = "Auswahltyp";
$lang["Custom Values"] = "Benutzerdefinierte Werte";
$lang["Values"] = "Werte";
$lang["Default Value"] = "Standartwer";
$lang["Grid"] = "Raster";
$lang["List Position"] = "Listesposition";
$lang["General"] = "Allgemein";
$lang["Required Field"] = "Pflichtfeld";
    // Messages
      // System
$lang["The module was added correctly"] = "Das Modul wurde ordnungsgemäß hinzugefügt";
$lang["The module was edited correctly"] = "Das Modul wurde korrekt bearbeitet";
$lang["The module can not be deleted"] = "Das Modul kann nicht gelöscht werden";
$lang["The module was deleted correctly"] = "Das Modul wurde korrekt gelöscht";
$lang["There was an error writing the table"] = "Ein Fehler beim Schreiben in die Tabelle is aufgetreten";
$lang["The table module was created correctly"] = "Die Tabelle für das neue Modul wurde korrekt erstellt";
$lang["The table module was edited correctly"] = "Die Tabelle für das Modul wurde korrekt geändert";
$lang["Invalid parameters"] = "Ungültiger Parameter";
$lang["The Module must contain at least one field"] = "Die Module müssen mindestens ein Feld enthalten";
$lang["Please enter a name for this module"] = "Bitte geben Sie einen Namen für dieses Modul";
$lang["The module name must start with a letter"] = "Der Modulname muss mit einem Buchstaben beginnen";
$lang["All the fields must have a table name"] = "Alle Felder einer Tabelle müssen benannt werden";
$lang["There are two fields with the same Field Name"] = "Es sind zwei Felder mit identischem Namen";
$lang["The length of the varchar fields must be between 1 and 255"] = "Die Länge der VARCHAR Felder muss zwischen 1 "
    . "und 255 Zeichen sein";
$lang["The length of the int fields must be between 1 and 11"] = "Die Länge der INT Felder muss zwischen 1 und 11 "
    . "liegen";
$lang["Invalid form Range for the select field"] = "Ungültiger Wertebereich für das Select-Feld";
$lang["The module must have a project selector called project_id"] = "Das Modul muss einen Projekt-Selektor genannt "
    . "project_id haben";
$lang["Project module must be a normal module"] = "Projekt-Modul muss ein Standardmodul sein";
$lang["The module must have at least one field with the list position greater than 0"] = "Das Modul muss mindestens "
    . "ein Feld mit der Liste Position größer als 0 haben";
      // View
$lang["You can not delete system modules"] = "Sie können nicht löschen Systemmodule";
    // View
$lang["Module Designer"] = "Modul Designer";
$lang["Repository of available field types"] = "Archiv verfügbarer Feldtypen";
$lang["Active fields in the module"] = "Aktive Felder in dem Modul";
$lang["Example Project 1"] = "Beispiel Projekt 1";
$lang["Example Project 2"] = "Beispiel Projekt 2";
$lang["Example User 1"] = "Beispiel User 1";
$lang["Example User 2"] = "Beispiel User 2";
$lang["Example Contact 1"] = "Beispiel Kontakt 1";
$lang["Example Contact 2"] = "Beispiel Kontakt 2";
    // Tooltip Help
$lang["Each option have the key, and the value to display, separated by #."] = "Jede Option verfügt über einen "
    . "Schlüssel und einen Wert getrennt durch #.";
$lang["Separate the diferent options with '|'."] = "Trennen Sie die verschiedenen Optionen mit\"|\".";
$lang["For Modules queries, use Module#keyField#displayField."] = "Für Module Abfragen verwenden "
    . "Modul#Schlüsselfeld#Anzeigefeld.";
$lang["The API will get all the keyField of the module and will use the displayField for show it."] = "Die API sucht "
    . "über das Schlüsselfeld des Moduls und zeigt den Wert des Anzeigefeldes.";
$lang["Defines the position of the field in the grid. Starts with 1 in the left. 0 for do not show it."] = "Bestimmt "
    . "die Position des Feldes in der Tabelle. Beginnt mit 1 auf der linken Seite. 0 macht unsichtbar.";
$lang["1. Drag a field into the right pane."] = "1. Ziehen Sie ein Feld in den rechten Bereich.";
$lang["2. Edit the parameters of the field in the lower left pane."] = "2. Bearbeiten Sie die Feldparameter im "
    . "unteren linken Bereich.";
$lang["Drop in this panel all the fields that you want to have in this tab."] = "Drag & Drop in dieses Fenster alle "
    . "Felder, die Sie auf dieser Registerkarte haben wollen.";
$lang["For sort the fields, just drag and drop it in the correct position."] = "Für die gewünschte Sortierung der "
    . "Felder, einfach per Drag & Drop in die richtige Position bringen.";
$lang["Number of stars"] = "Anzahl der Sterne";
// Tab
  // System
$lang["Tab"] = "Reiter";
  // Labels
$lang["Basic Data"] = "Allgemeine Daten";
$lang["People"] = "Personen";

// Role
  // System
$lang["Role"] = "Rolle";

// General
  // Fields
$lang["Company Name"] = "Firma";
  // Messages
$lang["The Company name is empty"] = "Der Name der Firma ist leer";

// Notification
  // Fields
$lang["Login / Logout"] = "Login / Logout";
$lang["Data Records"] = "Datensätze";
$lang["Alerts"] = "Warnungen";
  // System
$lang["Notification"] = "Benachrichtigung";
  // Tooltip Help
$lang["Tooltip"]["alerts"] = "Damit können Sie die Benachrichtigungen ein- und ausschalten.";
$lang["Tooltip"]["loginLogout"] = "Damit können Sie die Meldungen über den Login/Logout anderer Benutzer aktivieren "
    . "bzw. deaktivieren.";
$lang["Tooltip"]["usergenerated"] = "Damit können Sie die Nachrichten von einem anderen Benutzern an Sie aktivieren "
    . "bzw. deaktivieren.";
$lang["Tooltip"]["datarecords"] = "Damit können Sie die Nachrichten über Systemereignisse wie zB ein "
    . "Projekterstellung, Anmerkung- oder Todospeicherung, Löschen bestehender Artikel aktivieren bzw. deaktivieren.";

// General Help (Administration)
$lang["Content Help Administration"]["General"] = "DEFAULT";
$lang["Content Help Administration"]["Administration"] = "<br />
    This is the <b>General Help of Administration module</b><br />
    <br />
    This module is only accessible to users with Admin profile.<br />
    It is located at the top right global modules, the last one.<br />
    <br />
    Here, general settings of the site that affect all users or specific ones in case of User submodule, can be set and
    modified.<br />
    <br />
    It is formed by 5 tabs:<br />
    <br />
    <b>Module</b><br />
    This is the Module Designer, a very easy-to-use visual drag & drop interface to create modules or modify many
    existing ones.<br />
    <br />
    <b>Tab</b><br />
    Here additional tabs for modules can be created and modified.<br />
    <br />
    <b>User</b><br />
    To administer users of the system: their main info and settings.<br />
    <br />
    <b>Role</b><br />
    To edit roles; a role is a set of permissions for the Project-modules (non Global ones) that is assigned to
    users.<br />
    <br />
    <b>General</b><br />
    This is a general configurations tab.<br />
    Currently it has only one field: 'Company name'.<br />
    <br />
    <br />";

$lang["Content Help Administration"]["Module"] = "<br />
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
        <li>
            <b>The left fields panel:</b> here are all the field types; <i>text</i>, <i>date</i>, <i>time</i>,
            <i>datetime</i>, <i>select</i>, <i>checkbox</i>, <i>percentage</i>, <i>rating</i>, <i>textarea</i> and
            <i>upload</i>.<br />
            You can drag & drop fields to the right panel, that right panel is the tab of the module you are creating
            or modifying as it will be seen (but without buttons <b>Edit</b> and <b>Delete</b>).<br />
            The <b>Edit</b> button in the right side of the fields, permits modifying its data before adding them to
            the module tab (right panel) although it is also possible to drag and drop it first and then edit it
            pressing that button in the right panel.
        </li>
        <li>
            <b>The right tabs panel:</b> this has the main tabs of the module as they are going to be seen when you
            open it outside the <b>Module Designer</b> (except for the Edit / Delete buttons). You add fields dragging
            and dropping them from the left panel, then pressing <b>Edit</b> button the edit panel is opened in the
            left bottom part of the window so that you can configure the field.<br />
            The fields can be reordered with drag & drop method and deleted from the tab dragging & dropping them back
            to the left panel or just pressing <b>Delete</b> button.<br />
            There are as many tabs in this panel as tabs are defined in module <b>Administration</b> submodule
            <b>Tab</b>.<br />
            You don't need to use all the tabs created. The tab will appear in the module only if there are fields
            inside it.
        </li>
        <li>
            <b>The left bottom editing panel:</b> here, when it is pressed the <b>Edit</b> button of a field, a
            window appears to modify its values and parameters.<br />
            It has 4 tabs:<br />
            <ul>
                <li>
                    <b>Databse:</b> to edit the data of the database.
                </li>
                <li>
                    <b>Form:</b> to edit the data shown in the form.
                </li>
                <li>
                    <b>Grid:</b> to edit whether the field is shown in the grid or not, and its position inside it.
                </li>
                <li>
                    <b>General:</b> general parameters.
                </li>
            </ul>
        </li>
    </ul>
    <br />
    <b>How to create a module, from zero</b><br />
    <br />
    <ol>
        <li>
            Assuming you are in the <b>Module</b> tab of <b>Administration</b> module, press <b>Add</b> button.
        </li>
        <li>
            An empty form appears. Write the name of the new module in the <b>Label</b> textbox.
        </li>
        <li>
            Press the <b>Open Dialog</b> button. A big pop-up window containing the module designer appears.<br />
            You will see the two big panels, one at the left and one at the right, and an empty space in the left
            bottom where the field editing window eventually appears.<br />
            Inside the right panel (the module as it will be seen outside the designer) there is a Project select box.
            That field should exist for the module to work, it is the relation between the item and the projects, don't
            delete it.
        </li>
        <li>
            Add to the right panel using drag & drop a field of your choice. If there is more than one tab in the right
            panel, you can select the tab you want previous to the drag & drop to set the field there. In both cases,
            after dropping it the editing window appears for you to configure the field.<br />
            Note: to drop the field, you have to position it with the mouse over a place where the floating box that
            you are dragging converts itself from reddish pink to green color; that color means that you are able to
            drop the field there, up or behind another field.
        </li>
        <li>
            Configure the field attending to each of the 4 tabs of the editing window as explained here above.
        </li>
        <li>
            Repeat the steps 5 and 6 as many times as fields you want to add.
        </li>
        <li>
            Arrange the fields in the right panel in the order you want using drag & drop.
        </li>
        <li>
            Press the <b>Close</b> button in the left bottom of the window.
        </li>
        <li>
            The pop-up window has been closed. Press <b>Save</b> and the module is finished. The saving act, for
            example when <i>creating</i> a module, creates the table in the database, saves the parameters and creates
            the structure of folders and files.
        </li>
    </ol>
    <br />
    <b>Notes:</b><br />
    <ul>
        <li>
            After saving a new module, it is needed to refresh the page in the browser.
        </li>
        <li>
            The module will be added the <b>Access</b>, <b>Notification</b> and <b>History</b> tabs.
        </li>
        <li>
            There could be dragged back right panel fields to the left panel. This is useful to take them back later
            to the right panel, or to move them to another right panel tab.
        </li>
        <li>
            <u>It is not recommended to modify the original modules that come with the system. Most of them have
            additional functionality that was not made with the Module Designer and could stop working if modified
            with it.</u>
        </li>
    </ul>
    <br />
    <b>Other special tabs</b><br />
    <br />
    There are other tabs used in the system modules that are not defined here, nor could be modified. They depend on
    the module you are working with:<br />
    <br />
    <ul>
        <li>
            General modules: tabs <i>Access</i>, <i>Notification</i> and <i>History</i>.<br />
            All the modules <i>created</i> with the <b>Module designer</b> will have, apart from the tabs designed by
            the user, these 3 tabs explained in the help of most of the modules. <i>History</i> will only be shown in
            edit mode.
        </li>
        <li>
            Project module: tabs <i>Module</i> and <i>Role</i> explained in Project's help.
        </li>
        <li>
            Calendar module: tab <i>Recurrence</i> explained in Calendar's help.
        </li>
    </ul>
    <br />
    <br />";

$lang["Content Help Administration"]["Tab"] = "<br />
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
        <li>
            General modules: tabs <i>Access</i>, <i>Notification</i> and <i>History</i>.<br />
            All the modules <i>created</i> with the <b>Module designer</b> will have, apart from the tabs designed by
            the user, this 3 tabs explained in the help of most of the modules. <i>History</i> will only be shown in
            edit mode.
        </li>
        <li>
            Project module: tabs <i>Module</i> and <i>Role</i> explained in Project's help.
        </li>
        <li>
            Calendar module: tab <i>Recurrence</i> explained in Calendar's help.
        </li>
    </ul>
    <br />
    <br />";

$lang["Content Help Administration"]["User"] = "<br />
    <b>User tab</b><br />
    <br />
    This sub-module allows modifying users main data and settings.<br />
    Here all the users of the system will be seen.<br />
    <br />
    It has a Grid and a Form like most of modules.<br />
    <br />
    <hr style='height: 2px;'>
    <b>GRID</b><br />
    <br />
    Fields:<br />
    <br />
    <b>Username</b><br />
    Text: the login name for the user.<br />
    <br />
    <b>First name</b><br />
    Text: first name of the user.<br />
    <br />
    <b>Last name</b><br />
    Text: last name of the user.<br />
    <br />
    <b>Status</b><br />
    Select: whether this user is active in the system or not (if not, he/she can't log in).<br />
    <br />
    <b>Admin</b><br />
    Select: whether this user has Admin access or not.<br />
    <br />
    <hr style='height: 2px;'>
    <b>FORM</b><br />
    <br />
    Note:<br />
    The users can be added and modified but not deleted, if you want to delete a user, set it to Inactive instead,
    through Status field. He won't be able to log in and won't appear in the User Select fields.<br />
    <br />
    Fields:<br />
    <br />
    <b>Username</b><br />
    Text: the login name for the user.<br />
    <br />
    <b>Password</b><br />
    Text: if you want to change a password write here the NEW one. Black dots will be seen instead of typed characters.
    If you don't want to change it leave this field blank.<br />
    <br />
    <b>First name</b><br />
    Text: first name of the user.<br />
    <br />
    <b>Last name</b><br />
    Text: last name of the user.<br />
    <br />
    <b>Email</b><br />
    Text: the email of the user, mainly used to send Notifications.<br />
    <br />
    <b>Language</b><br />
    Select: choose a language for the user to be shown site text and messages.<br />
    <br />
    <b>Time zone</b><br />
    Select: The Time zone (UTC) for the region or country the user will be using Phprojekt. This is required for all
    users but specially useful for international events time coordination, so that each participant of an event will
    see the time (and eventually the date) converted to his/her local time.<br />
    <br />
    <b>Status</b><br />
    Select: whether this user is active in the system or not (if not, he/she can't log in).<br />
    <br />
    <b>Admin</b><br />
    Select: whether this user has Admin access or not.<br />
    <br />
    <br />";

$lang["Content Help Administration"]["Role"] = "<br />
    <b>Role tab</b><br />
    <br />
    This tab permits managing the Roles.<br />
    <br />
    A Role is a specific set of permissions for each module. That Role then is assigned to users of the Projects you
    want, so that he will have that rights. When you create or edit a Project, you can give every different user that
    Role inside Rol tab.<br />
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
    Then you create a Project and assign the user 'john' the Role 'Can read TODOs and NOTEs', so when he enters the
    Project, the only thing he will be able to do apart from reading the main data of the Project itself is to read
    (but not modify) items of <b>Todo</b> and <b>Note</b> modules.<br />
    <br />
    <b>Note:</b> in the listing of modules that you select to assign rights to a rol, the <b>Project</b> module
    represents the Subprojects of Projects.<br />
    <br />
    <br />";

$lang["Content Help Administration"]["General tab"] = "<br />
    <b>General tab</b><br />
    <br />
    This is a general configurations tab.<br />
    <br />
    Currently it has only one field 'Company name' where you set the company name that will be seen in the beginning of
    the breadcrumb trail when a Project related module is selected (Project, Gantt, Todo, etc).<br />
    <br />
    <br />";

// General Help (Setting)
$lang["Content Help Setting"]["General"] = "DEFAULT";
$lang["Content Help Setting"]["Setting"] = "<br />
    This is the <b>General Help of Setting module</b><br />
    <br />
    Settings module allows logged user to modify personal information.<br />
    <br />
    It is formed by three sub-modules: User, Notification and Timecard. You can switch between them clicking their tabs
    in the upper central tab bar.<br />
    The first sub-module allows setting and changing Password, Email, Language and Time zone, the second one corresponds
    to the real-time notifications and server synchronization and the third one is for Timecard favorite Projects.<br />
    <br />
    All users including Admin type ones only can modify their own information here.<br />
    Admin type users are able to modify other users information, but not here, they do it in Administration
    module.<br />
    <br />
    <br />";

$lang["Content Help Setting"]["User"] = "<br />
    <b>User tab</b><br />
    <br />
    Here you will see logged user info.<br />
    Password fields don't show anything for security reasons, if you want to change your password, fill the 3 fields
    as explained here later and then press Save. If you leave them blank and press Save, password won't be modified.
    <br />
    The rest of fields show current values and let you modify them.<br />
    <br />
    Fields:<br />
    <br />
    <b>Password</b><br />
    Text: if you want to change your password write here the NEW one. Black dots will be seen instead of typed
    characters.<br />
    <br />
    <b>Confirm Password</b><br />
    Text: if you want to change your password write here the NEW one again. Black dots will be seen instead of typed
    characters.<br />
    <br />
    <b>Old password</b><br />
    Text: if you want to change your password write here the OLD one that will be verified for security reasons. Black
    dots will be seen instead of typed characters.<br />
    <br />
    <b>Email</b><br />
    Text: your email registered in the system. It is mainly used to send you Notifications.<br />
    <br />
    <b>Language</b><br />
    Select: choose a language for you to be shown site text and messages.<br />
    <br />
    <b>Time zone</b><br />
    Select: The Time zone (UTC) for the region or country you will be using Phprojekt. This is required for all users
    but specially useful for international events time coordination, so that each participant of an event will see the
    time (and eventually the date) converted to his/her local time.<br />
    Each option of the select field has a local time number and a descriptive earth location to help finding
    yours.<br />
    <br />
    <br />";

$lang["Content Help Setting"]["Notifications"] = "<br />
    <b>Notifications tab</b><br />
    <br />
    Here you can configure the notifications and real-time connection with the server.<br />
    This functionality tells you some actions of other users in little pop-up dialogs at the right bottom of the
    screen, like logging in / out, alerts, modifications of items, etc.<br />
    It also updates content on screen according to what you are seeing and what has been modified by other users.
    <br />
    <br />
    You can activate/inactivate this real-time synchronization through these check boxes:<br />
    <br />
    <b>Login / logout</b><br />
    A pop-up dialog tells you when other users have logged in and out.<br />
    <br />
    <b>Data records</b><br />
    If you check this box, then when some contents that you have access to are modified, you receive a message pointing
    you that event.<br />
    Also, if for example a Project name has been modified and you are editing it in the Form, the tree gets updated,
    also the grid, and each modified form field gets updated and the field border gets thick and red coloured.<br />
    <br />
    <b>User Generated Messages</b><br />
    It is not developed yet, the check box is disabled.<br />
    <br />
    <b>Alerts</b><br />
    It is not developed yet, the check box is disabled.<br />
    <br />
    <br />";

$lang["Content Help Setting"]["Timecard"] = "<br />
    <b>Timecard tab</b><br />
    <br />
    Here you will see and will be able to change the Favorite Projects of Timecard module.<br />
    <br />
    They will appear first on the project selection in the form of the Timecard module. This list of Projects is
    independent for each user and its purpose is to shorten booking process time.<br />
    <br />
    When you have finished doing the changes in this Multiple Select Box, press Save.<br />
    <br />
    <br />";
