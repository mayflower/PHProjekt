<?php
// System
$lang["Helpdesk"] = "Helpdesk";

// Fields
 // Basic Data
$lang["Title"] = "Titel";
$lang["Assigned"] = "Zugewiesen";
$lang["Date"] = "Datum";
$lang["Project"] = "Projekt";
$lang["Priority"] = "Priorität";
$lang["Attachments"] = "Attachments";
$lang["Description"] = "Beschreibung";
$lang["Status"] = "Status";
$lang["Open"] = "Offen";
$lang["Solved"] = "gelöst";
$lang["Verified"] = "Verifiziert";
$lang["Closed"] = "Erledigt";
$lang["Due date"] = "Fälligkeitsdatum";
$lang["Author"] = "Author";
$lang["Solved by"] = "Gelöst von";
$lang["Solved date"] = "Datum der Lösung";
$lang["Contact"] = "Kontakt";

// Tooltip Help
$lang["Tooltip"]["projectId"] = "Wenn kein Projekt angelegt, dann PHProjekt wählen.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Helpdesk"] = "<br />
    This is the <b>General Help of Helpdesk module</b><br />
    <br />
    The Helpdesk is a module to report and track bugs or things that must be solved inside whatever context.<br />
    <br />
    It has lot of fields that can be filled with the needed information for the assigned user to solve the ticket.
    <br />
    There are some automatic fields: <b>Author</b>, <b>Date</b>, <b>Solved by</b> and <b>Solved date</b> that are
    filled automatically depending on the users that create and solve the items.<br />
    <br />
    <br />";

$lang["Content Help"]["Basic data"] = "<br />
    <b>Basic Data tab</b><br />
    <br />
    Fields:<br />
    <br />
    <b>Title</b><br />
    Text: the title of the item, e.g.: 'Fix my notebook'.<br />
    * Required field<br />
    <br />
    <b>Author</b><br />
    Display: a read-only mode field that is automatically filled with the name of the user who created the item.<br />
    <br />
    <b>Assigned</b><br />
    A user the ticket is assigned to, if any. He/she is the one to solve it.<br />
    When this user is selected, or it is changed to another user, some rights are assigned to him/her: Read, Write,
    Delete and Download access.<br />
    <br />
    <b>Date</b><br />
    Display: a read-only mode field that is automatically filled with the date of creation of the item.<br />
    <br />
    <b>Due Date</b><br />
    Date: when the ticket is expected to have been solved.<br />
    <br />
    <b>Project</b><br />
    Select: parent Project which contains the ticket (item), if none then select PHProjekt.<br />
    * Required field<br />
    <br />
    <b>Priority</b><br />
    Select: a number between 1 and 10 for the priority. When you have many items in the Grid, you can sort them into
    the priority clicking the header of that column. It may be ranked in ascending or descending order.<br />
    <br />
    <b>Attachments</b><br />
    This field allows you to upload one or more files.<br />
    Its way of work is absolutely intuitive: press the button at the right of the field, choose a file from your
    computer and the file is sent.<br />
    Then the uploaded file appears behind the field with a cross button at its right side to delete it. The file itself
    is a link to download it.<br />
    <br />
    <b>Solved by</b><br />
    Display: a read-only mode field that is automatically filled with the name of the user that changed the Status
    field to 'Solved'.<br />
    <br />
    <b>Solved Date</b><br />
    Display: a read-only mode field that is automatically filled with the date when it was changed the Status field to
    'Solved'.<br />
    <br />
    <b>Description</b><br />
    Textarea: the description of the ticket.<br />
    E.g.:<br />
    1 - Turn the notebook on<br />
    2 - Put a CD into the CD-ROM drive<br />
    3 - Try to open its content, you can't<br />
    <br />
    <b>Status</b><br />
    Select: the status of the item, this is supposed to change over time as the task advances.<br />
    The options are self-descriptive:<br />
    1 - Open: the first state.<br />
    2 - Assigned: you select it when item has been assigned to a user.<br />
    3 - Solved: this is supposed to be the final state for a solved issue.<br />
    4 - Verified: the issue was corroborated but it wasn't solved nor closed.<br />
    5 - Closed: this is supposed to be the final state for a NON-solved issue.<br />
    <br />
    When the status is set to Solved, the fields Solved By and Solved Date in the form are automatically filled with
    the logged user name and current date respectively. If you change Solved status by another one, those 2 fields are
    emptied again.<br />
    <br />
    <b>Contact</b><br />
    A contact to relate the item with, maybe a user that can be consulted about the issue.<br />
    <br />
    <b>Tag</b><br />
    The tag field that synchronizes the item with Tags panel.<br />
    <br />
    <br />";

$lang["Content Help"]["Access"] = "DEFAULT";
$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";
