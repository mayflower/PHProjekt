<?php
$lang["The deletion of a project and its subprojects might take a while"] = "The deletion of a project and its "
    . "subprojects might take a while";

// Tooltip Help
$lang["Tooltip"]["projectId"] = "The parent project, if none then select PHProjekt.";

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
    The Project module has another modules associated that are part of it. Those associations are defined in tab
    Module, which can be seen when editing the Project itself. The modules that come with the original release of the
    system and can be associated will be called in this help as <b>General modules</b> and they are Filemanager, Gantt,
    Note, Statistic and Todo.<br />
    <br />
    Those general modules can be accessed from:
    <ul>
        <li><b>The root Project:</b> you click on the logo and the Project module opens, with all the main Projects
            listed in the grid. Those are the Projects associated to the root of the system. You will see all the
            general modules in the main upper tab bar, if you enter there, you will see items of that modules that are
            not associated to any project. Actually when they don't belong to any project, they belong to 'PHProjekt'
            that is the root Project, the one that is parent of all the main projects).<br />
            If you want to see the main information of the Project you can
            click it in the grid and its main tabs get open in the form down. But if you want to see the items of the
            general modules that are directly associated to that Project, you have to access it from the tree, as
            explained in the next point here.
        <li><b>The tree:</b> the tree panel at the left permits accessing to the Projects and Subprojects in a full
            way, it means that the main tab bar will show the <b>Basic Data</b> tab plus the modules <i>associated</i>
            to the Project and if you enter them from that link you will see all (and no more than them) the items of
            that modules that are exclusively associated to that Project (or Subproject) clicked in the tree.<br />
    </ul>
    <br />
    To see the help of that modules please click on the tab of that module (both in the root Project or a particular
    Project) and then click on the Help top right link.<br />
    <br />
    For specific help about every Project tab, see the other tabs of this help.<br />
    <br />
    Projects may have subprojects and many other properties that you can start to learn reading <b>Basic Data</b> tab
    inside this help.";

$lang["Content Help"]["Basic data"] = "DEFAULT";
$lang["Content Help"]["Access"] = "DEFAULT";

$lang["Content Help"]["Module"] = "<br />
    <b>Module tab</b><br />
    <br />
    This tab has a list of all the modules that can be associated to the project.<br />
    <br />
    The modules that appear checked will be seen as tabs at the sides of the Basic Data
    tab, when you access to the Project from the tree (view full explanation in tab Project inside this help).<br />
    <br />
    <br />";

$lang["Content Help"]["Role"] = "<br />
    <b>Role tab</b><br />
    <br />
    This tab permits assigning roles to the users.<br />
    A Role is a
    specific set of permissions defined for each module. They are set in <b>Administration -&#62; Role</b>. For
    example a role could be 'Admin in all modules' or 'Admin in Filemanager, read only in the rest'.<br />
    The final right for a specific user to work with an item is defined by the conjunction of what is defined both
    inside Role and Access tabs.<br />
    <br />
    By default, no Role is assigned to anybody, and the right to a new project will be <b>Admin</b> access
    for the creator and nothing for the rest. This changes when permissions are assigned in the <b>Access</b> and
    <b>Role</b> tabs.<br />
    <br />
    <br />";

$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";
