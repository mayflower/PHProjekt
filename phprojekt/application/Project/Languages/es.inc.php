<?php
// System
$lang["Project"] = "Projecto";

// Fields
 // Basic Data
$lang["Title"] = "Título";
$lang["Notes"] = "Notas";
$lang["Parent"] = "Parent";
$lang["Start date"] = "Fecha de Inicio";
$lang["End date"] = "Fecha final";
$lang["Priority"] = "Prioridad";
$lang["Current status"] = "Estado actual";
$lang["Offered"] = "Ofrecido";
$lang["Ordered"] = "Ordenado";
$lang["Working"] = "Trabajando";
$lang["Ended"] = "Finalizado";
$lang["Stopped"] = "Parado";
$lang["Re-Opened"] = "Re-Abierto";
$lang["Waiting"] = "Esperando";
$lang["Complete percent"] = "Porcentaje completado";
$lang["Budget"] = "Presupuesto";
$lang["Hourly wage rate"] = "Salario por hora";
$lang["Contact"] = "Contacto";

// Messages
  // View
$lang["The deletion of a project and its subprojects might take a while"] = "La supresión de un proyecto y sus "
    . "subproyectos podría tomar un tiempo";

// Tooltip Help
$lang["Tooltip"]["projectId"] = "El proyecto padre, si no tiene padre elegir PHProjekt.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Project"] = "<br />
    This is the <b>General Help of Project module</b><br />
    <br />
    The Projects are the most important functional part of the system. The user creates a Project to work with, and then
    loads all the data on the Project itself plus the associated modules that were activated for it (view Module tab
    inside a project). As time goes by, data can be added or modified.<br />
    <br />
    The owner and users with appropriate permissions can give specific rights to the users they want on a specific
    Project, like roles or just individual rights for them to access it, see its content and maybe modify it.<br />
    <br />
    The Project module has another modules associated that are part of it. Those associations are defined in Module tab,
    which can be seen when editing the Project itself. The modules that come with the original release of the
    system and can be associated will be called in this help as <b>General modules</b> and they are Project, Gantt,
    Statistic, Todo, Note, Filemanager, Minute and Helpdesk.<br />
    <br />
    Those general modules can be accessed from:<br />
    <br />
    <ul>
        <li>
            <b>The root Project:</b> you click on the logo and the Project module opens, with all the main Projects
            listed in the grid. Those are the Projects associated to the root of the system. You will see all the
            general modules in the main upper tab bar, if you enter there, you will see items of that modules that are
            not associated to any project. Actually when they don't belong to any project, they belong to 'PHProjekt'
            that is the root Project, the one that is parent of all the main projects).<br />
            If you want to see the main information of the Project you can click it in the grid and its main tabs get
            open in the form down. But if you want to see the items of the general modules that are directly associated
            to that Project, you have to access it from the tree, as explained in the next point here.
        </li>
        <li>
            <b>The tree:</b> the tree panel at the left permits accessing to the Projects and Subprojects in a full
            way, it means that the main tab bar will show the <b>Basic Data</b> tab plus the modules <i>associated</i>
            to the Project and if you enter them from that link you will see all  the items of that modules that are
            exclusively associated to that Project (or Subproject) clicked in the tree.
        </li>
    </ul>
    <br />
    To see the help of that modules please click on the tab of that module (both in the root Project or a particular
    Project) and then click on the Help top right link.<br />
    <br />
    For specific help about every Project tab, see the other tabs of this help.<br />
    <br />
    Projects may have subprojects and many other properties that you can start to learn reading <b>Basic Data</b> tab
    inside this help.<br />
    <br />
    <br />";

$lang["Content Help"]["Basic data"] = "<br />
    <b>Basic data tab</b><br />
    <br />
    Inside this tab it goes the main information about the Project.<br />
    <br />
    Fields:<br />
    <br />
    <ul>
        <li>
            <b>Title</b>: title of the Project.
        </li>
        <li>
            <b>Notes</b>: notes about the Project.
        </li>
        <li>
            <b>Project</b>: here goes the parent Project; every Project is child of another, at least child of the Root
            one whose name is the company name.
        </li>
        <li>
            <b>Start Date</b>
        </li>
        <li>
            <b>End Date</b>
        </li>
        <li>
            <b>Priority</b>
        </li>
        <li>
            <b>Status</b>: may be one of the following: Offered, Ordered, Working, Ended, Stopped, Re-Opened and
            Waiting.
        </li>
        <li>
            <b>Percentage completed</b>
        </li>
        <li>
            <b>Budget</b>
        </li>
        <li>
            <b>Contact</b>: contacts list from Contact module for you to choose one.
        </li>
        <li>
            <b>Tag</b>
        </li>
    </ul>
    <br />
    <br />";

$lang["Content Help"]["Access"] = "DEFAULT";

$lang["Content Help"]["Module"] = "<br />
    <b>Module tab</b><br />
    <br />
    This tab has a list of all the modules that can be associated to the project.<br />
    <br />
    The modules that appear checked will be seen as tabs at the sides of the Basic Data
    tab, when you access to the Project from the tree.<br />
    <br />
    The modules that can be associated to a Project inside Module tab so that all their items will have that Project as
    their parent are called Project-modules and they are:<br />
    <br />
    <b>Gantt</b><br />
    This module is a Gantt chart interface, mouse editable, which is automatically synchronized to the Project data
    loaded in the database.<br />
    <br />
    <b>Statistic</b><br />
    This module sums all the worked hours, classifies them by Projects and Sub-Projects and shows them in a chart table.
    <br />
    <br />
    <b>Todo</b><br />
    The Todo is a module to store To-Do items and assign them to a user.<br />
    <br />
    <b>Note</b><br />
    The Note is just a module to leave notes and associate them to a Project or Subproject.<br />
    <br />
    <b>Filemanager</b><br />
    The File manager is a module defined exclusively for uploading and downloading files from the system.<br />
    <br />
    <b>Minute</b><br />
    This module is meant for the transcription of meeting minutes.<br />
    <br />
    <b>Helpdesk</b><br />
    The Helpdesk is a module to report and track bugs or issues that must be solved inside whatever context.<br />
    <br />
    <br />
    Note: all these modules are explained in detail inside its appropriate section of the help.<br />
    <br />
    <br />";

$lang["Content Help"]["Role"] = "<br />
    <b>Role tab</b><br />
    <br />
    This tab permits assigning roles to the users.<br />
    A Role is a specific set of permissions defined for each module. They are set in <b>Administration -&#62; Role</b>.
    For example a role could be 'Admin in all modules' or 'Admin in Filemanager, read only in the rest'.<br />
    The final right for a specific user to work with an item is defined by the conjunction of what is defined both
    inside Role and Access tabs.<br />
    <br />
    By default, no Role is assigned to anybody, and the right to a new project will be <b>Admin</b> access for the
    creator and nothing for the rest. This changes when permissions are assigned in the <b>Access</b> and <b>Role</b>
    tabs.<br />
    <br />
    For example, a specific Role can give Admin access to Todo module and just Read access for Helpdesk module. It means
    that if a user has that Role assigned for a Project, then he/she will be able to do everything with the Todo items
    that have that Project as parent but will just be able to see, and not modify Helpdesk items that also have that
    Project as parent.<br />
    <br />
    Notes:<br />
    <ul>
        <li>
            Each Role scope is defined by Admin users in Administration module.
        </li>
        <li>
            Important: when a Project doesn't have any Roles defined, then it inherits its parent Roles.
        </li>
        <li>
            Roles don't affect top right linked Global modules like Calendar, Contact, etc.
        </li>
    </ul>
    <br />
    <br />";

$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";
