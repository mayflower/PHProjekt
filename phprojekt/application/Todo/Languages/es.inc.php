<?php
// System
$lang["Todo"] = "Tarea";

// Fields
 // Basic Data
$lang["Title"] = "Título";
$lang["Notes"] = "Notas";
$lang["Start date"] = "Inicio";
$lang["End date"] = "Fin";
$lang["Priority"] = "Prioridad";
$lang["Current status"] = "Estado actual";
$lang["Waiting"] = "Esperando";
$lang["Accepted"] = "Aceptado";
$lang["Working"] = "Trabajando";
$lang["Stopped"] = "Parado";
$lang["Ended"] = "Finalizado";
$lang["Project"] = "Projecto";
$lang["User"] = "Usuario";

// Tooltip Help
$lang["Tooltip"]["projectId"] = "El proyecto padre, si no tiene padre elegir PHProjekt.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Todo"] = "<br />
    This is the <b>General Help of Todo module</b><br />
    <br />
    The Todo is a module to store To-Do items and assign them to a user.<br />
    <br />
    They are always associated to a Project or Subproject and have a Start date, End date, Priority, State and
    other fields to configure all its data.<br />
    <br />
    <br />";

$lang["Content Help"]["Basic data"] = "<br />
    <b>Basic Data tab</b><br />
    <br />
    Fields:<br />
    <br />
    <b>Title</b><br />
    Text: the title of the item, e.g.: 'Buy 5 notebooks for the team'.<br />
    * Required field<br />
    <br />
    <b>Notes</b><br />
    Textarea: some descriptive note.<br />
    E.g.:<br />
    1 - Compare quality and prices<br />
    2 - Consult the team<br />
    3 - Buy and bring them here<br />
    <br />
    <b>Project</b><br />
    Select: parent Project which contains the to-do, if none then select PHProjekt.<br />
    * Required field<br />
    <br />
    <b>Start Date</b><br />
    Date: when task is planned to start, or indeed started.<br />
    <br />
    <b>End Date</b><br />
    Date: when task is planned to end, or indeed ended.<br />
    <br />
    <b>Priority</b><br />
    Select: a number between 1 and 10 for the priority. When you have many items in the Grid, you can sort them into
    the priority clicking the header of that column. It may be ranked in ascending or descending order.<br />
    <br />
    <b>Status</b><br />
    Select: the status of the item, this is supposed to change over time, as the task advances.<br />
    The options are self-descriptive:<br />
    1 - Waiting<br />
    2 - Accepted<br />
    3 - Working<br />
    4 - Stopped<br />
    5 - Ended<br />
    <br />
    <b>User</b><br />
    A user the task has been assigned to. He is the one to do it.<br />
    There are some permissions added automatically to the assigned user, if any: Read, Write and Delete ones, so that
    he/she can work on the item. The permissions will be added after the item is saved and will be seen next time it is
    open, in Access tab.<br />
    <br />
    <b>Tag</b><br />
    The tag field that synchronizes the item with Tags panel.<br />
    <br />
    <br />";

$lang["Content Help"]["Access"] = "DEFAULT";
$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";
