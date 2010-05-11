<?php
// Core
  // Fields
$lang["Label"]= "Etiqueta";
  // System
$lang["Setting"] = "Configuración";
$lang["Administration"] = "Administración";
  // Messages
$lang["Admin section is only for admin users"] = "Sección de Administración es sólo para usuarios admin";
  // View
$lang["This module is for the user to set and change specific configuration parameters of his/her profile."] = "Este "
    . "módulo permite al usuario cambiar atributos específicos de configuración de su perfil.";
$lang["Here can be configured general settings of the site that affects all the users."] = "Aquí pueden configurarse "
    . "parámetros generales del sitio que afectan a todos los usuarios.";
$lang["Please choose one of the tabs of above."] = "Por favor, elija una de las solapa de arriba.";

// User
  // System
$lang["User"] = "Usuario";
  // Fields
$lang["Confirm Password"] = "Confirmar contraseña";
$lang["Old Password"] = "Contraseña anterior";
$lang["Admin"] = "Admin";
  // Messages
    // System
$lang["Already exists, choose another one please"] = "Ya existente, elija otro por favor";
$lang["The value for the setting is incorrect"] = "El valor elegido para la configuración es inválido";
$lang["The password and confirmation are different or one of them is empty"] = "La contraseña y su confirmación son "
    . "diferentes o una de ellas está vacía";
$lang["The old password provided is invalid"] = "La contraseña anterior no es correcta";
$lang["The Time zone value is out of range"] = "El valor de la zona horaria está fuera de rango";
$lang["The Language value do not exists"] = "El valor del idioma no existe";
$lang["Invalid email address"] = "Dirección de correo electrónico inválida";
$lang["You need to log out and log in again in order to let changes have effect"] = "Tiene que salir y entrar otra "
    . "vez para que los cambios se realicen";
  // Tooltip Help
$lang["Tooltip"]["timeZone"] = "El Huso Horario (UTC) para la región o país donde usará usted Phprojekt.<br /> "
    . "Es un dato requerido para todos los usuarios pero especialmente útil para coordinación temporal de eventos "
    . "internacionales.";
$lang["Tooltip"]["password"] = "Para los usuarios nuevos, este es un campo obligatorio. "
    . "Para los usuarios existentes, deje este campo en blanco si no desea cambiar la contraseña.";

// Module
  // System
$lang["Module"] = "Módulo";
  // Fields
$lang["Form"] = "Formulario";
$lang["Normal"] = "Normal";
$lang["Global"] = "Global";
$lang["Open Editor"] = "Abrir Editor";
  // Tooltip help
$lang["Open a dialog where you can drag and drop many fields for create the form as you want."] = "Abrir un cuadro de "
    . "diálogo donde usted puede arrastrar y soltar muchos campos para crear el formulario como usted desea.";
  // Module Designer
    // Fields
$lang["Database"] = "Base de datos";
$lang["Field name"] = "Nombre del campo";
$lang["Field type"] = "Tipo del campo";
$lang["Field lenght"] = "Longitud del campo";
$lang["Select Type"] = "Tipo de Select";
$lang["Custom Values"] = "Valores Específicos";
$lang["Values"] = "Valores";
$lang["Default Value"] = "Valor por defecto";
$lang["Grid"] = "Grilla";
$lang["List Position"] = "Posición";
$lang["General"] = "General";
$lang["Required Field"] = "Campo requerido";
    // Messages
      // System
$lang["The module was added correctly"] = "El módulo se agregó correctamente";
$lang["The module was edited correctly"] = "El módulo se editó correctamente";
$lang["The module can not be deleted"] = "El módulo no pudo ser borrado";
$lang["The module was deleted correctly"] = "El módulo fue borrado correctamente";
$lang["There was an error writing the table"] = "Hubo un error al escribir la tabla";
$lang["The table module was created correctly"] = "La tabla del módulo se agregó correctamente";
$lang["The table module was edited correctly"] = "La tabla del módulo se editó correctamente";
$lang["Invalid parameters"] = "Parámetros inválidos";
$lang["The Module must contain at least one field"] = "El módulo debe contener al menos un campo";
$lang["Please enter a name for this module"] = "Por favor ingrese un nombre para este módulo";
$lang["The module name must start with a letter"] = "El nombre del módulo debe comenzar con una letra";
$lang["All the fields must have a table name"] = "Todos los campos deben tener un nombre para la tabla";
$lang["There are two fields with the same Field Name"] = "Hay dos campos con el mismo nombre para la Tabla";
$lang["The length of the varchar fields must be between 1 and 255"] = "El largo de los campos Varchar debe ser entre "
    . "1 y 255";
$lang["The length of the int fields must be between 1 and 11"] = "El largo de los campos Int debe ser entre 1 y 11";
$lang["Invalid form Range for the select field"] = "Rango invalido para el campo Select";
$lang["The module must have a project selector called project_id"] = "El módulo debe tener un selector de proyecto "
    . "llamado project_id";
$lang["Project module must be a normal module"] = "Módulo de Proyecto debe ser un módulo normal";
$lang["The module must have at least one field with the list position greater than 0"] = "El módulo debe tener al "
    . "menos un campo con la posición en la lista mayor a 0";
      // View
$lang["You can not delete system modules"] = "No se pueden eliminar módulos del sistema";
    // View
$lang["Module Designer"] = "Diseñador de Módulos";
$lang["Repository of available field types"] = "Repositorio de los tipos de campo disponibles";
$lang["Active fields in the module"] = "Campos activos en el módulo";
$lang["Example Project 1"] = "Ejemplo projecto 1";
$lang["Example Project 2"] = "Ejemplo projecto 2";
$lang["Example User 1"] = "Ejemplo usuario 1";
$lang["Example User 2"] = "Ejemplo usuario 2";
$lang["Example Contact 1"] = "Ejemplo conacto 1";
$lang["Example Contact 2"] = "Ejemplo conacto 1";
    // Tooltip Help
$lang["Each option have the key, and the value to display, separated by #."] = "Cada opción tiene la clave, y el "
    . "valor para mostrar, separados por #.";
$lang["Separate the diferent options with '|'."] = "Separe a las distintas opciones con '|'.";
$lang["For Modules queries, use Module#keyField#displayField."] = "Para consultas sobre los módulos, use "
    . "Módulo#campoClave#campoParaMostrar";
$lang["The API will get all the keyField of the module and will use the displayField for show it."] = "La API "
    . "obtendrá todos los campoClaves del módulos y utilizará el campoParaMostrar para mostrarlos.";
$lang["Defines the position of the field in the grid. Starts with 1 in the left. 0 for do not show it."] = "Define la "
    . "posición del campo en la grilla. Empieza con 1 en la izquierda. 0 para no mostrarlo.";
$lang["1. Drag a field into the right pane."] = "1. Arrastre un campo en el panel derecho.";
$lang["2. Edit the parameters of the field in the lower left pane."] = "2. Edite los parámetros del campo en el "
    . "panel inferior izquierdo.";
$lang["Drop in this panel all the fields that you want to have in this tab."] = "Suelte en este panel todos los campos "
    . "que desea incluir en esta solapa.";
$lang["For sort the fields, just drag and drop it in the correct position."] = "Para ordenar los campos, sólo "
     . "arrastre y suéltelo en la posición correcta.";
$lang["Number of stars"] = "Número de estrellas";

// Tab
  // System
$lang["Tab"] = "Solapa";
  // Labels
$lang["Basic Data"] = "Datos Básicos";
$lang["People"] = "Personas";

// Role
  // System
$lang["Role"] = "Rol";

// General
  // Fields
$lang["Company Name"] = "Nombre de la compañía";
  // Messages
$lang["The Company name is empty"] = "El nombre de la compañía está vacío";

// Notification
  // Fields
$lang["Login / Logout"] = "Iniciar sesión / Cerrar sesión ";
$lang["Data Records"] = "Datos de los ítems";
$lang["Alerts"] = "Alertas";
  // System
$lang["Notification"] = "Notificaciones";
  // Tooltip Help
$lang["Tooltip"]["alerts"] = "Esto es para activar/desactivar las alertas, por ejemplo, algunos minutos antes de que "
    . "un evento comience.";
$lang["Tooltip"]["loginLogout"] = "Activar/desactivar esta opción para ver o no los mensajes sobre el inicio de "
    . "sesión/cierre de sesión de otros usuarios";
$lang["Tooltip"]["usergenerated"] = "Esto es para activar/desactivar los mensajes de otros usuarios para usted.";
$lang["Tooltip"]["datarecords"] = "Esto es para activar/desactivar los mensajes generados por el sistema.<br />"
    . "Eventos que desencadenan este mensaje: crear un nuevo ítem por ejemplo, un proyecto, Nota o Todo), eliminar "
    . "un ítem existente y editar un ítem.";

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
