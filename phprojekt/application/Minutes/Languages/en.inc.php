<?php
// System
$lang["Minute"] = "Minute";

// Fields
 // Basic Data
$lang["Title"] = "Title";
$lang["Start"] = "Start";
$lang["End"] = "End";
$lang["Project"] = "Project";
$lang["Description"] = "Description";
$lang["Place"] = "Place";
$lang["Moderator"] = "Moderator";
$lang["Invited"] = "Invited";
$lang["Attending"] = "Attending";
$lang["Excused"] = "Excused";
$lang["Status"] = "Status";
$lang["Planned"] = "Planned";
$lang["Empty"] = "Empty";
$lang["Filled"] = "Filled";
$lang["Final"]   = "Final";

 // Mail tab
$lang["Mail"] = "Mail";
$lang["Recipients"] = "Recipients";
$lang["Additional Recipients"] = "Additional Recipients";
$lang["Options"] = "Options";
$lang["Include PDF attachment"] = "Include PDF attachment";
$lang["Send mail"] = "Send mail";
$lang["Preview"] = "Preview";

// Messages
  // System
$lang["The currently logged-in user is not owner of the given minutes entry"] = "The currently logged-in user is not "
    . "owner of the given minutes entry";
  // View
$lang["Are you sure this Minutes entry should no longer be finalized?"] = "Are you sure this Minutes entry should no "
    . "longer be finalized?";
$lang["After proceeding, changes to the data will be possible again."] = "After proceeding, changes to the data will "
    . "be possible again.";
$lang["Are you sure this Minutes entry should be finalized?"] = "Are you sure this Minutes entry should be finalized?";
$lang["Write access will be prohibited!"] =  "Write access will be prohibited!";
$lang["Minutes are finalized"] = "Minutes are finalized";
$lang["This Minutes entry is finalized."] = "This Minutes entry is finalized.";
$lang["Editing data is no longer possible."] = "Editing data is no longer possible.";
$lang["Your changes have not been saved."] = "Your changes have not been saved.";

// Mail
  // Messages
$lang["The mail was sent successfully"] = "The mail was sent successfully";
$lang["The mail could not be sent"] = "The mail could not be sent";
$lang["No recipient addresses have been specified"] = "No recipient addresses have been specified";
$lang["Invalid email address detected:"] = "Invalid email address detected:";
  // Content
$lang["Meeting minutes for"] = "Meeting minutes for";

// PDF
  // Content
$lang["No."] = "No.";
$lang["Type"] = "Type";
$lang["Item"] = "Item";

// View
$lang["Unfinalize Minutes"] = "Unfinalize Minutes";
$lang["Finalize Minutes"] = "Finalize Minutes";
$lang["Confirm"] = "Confirm";
$lang["Are you sure?"] = "Are you sure?";

// Tooltip Help
  // Mail tab
$lang["Email addresses of unlisted recipients, comma-separated."] = "Email addresses of unlisted recipients,
    comma-separated.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Minute"] = "<br />
    This is the <b>General Help of the Minute module</b><br />
    <br />
    This module is meant for the transcription of meeting minutes.<br />
    Minute is a big and organized module to track and report meetings and its contents.<br />
    It has many tabs that can be filled with the needed information to register all contents in detail.<br />
    <br />
    It has a Grid and a Form, like most of the modules.<br />
    <br />
    It has a submodule inside Items tab, which has a little grid and a little form, where you can add and organize
    the contents of the meeting into categories like Topics, Statements, Todos, Decisions and Dates.<br />
    <br />
    <br />";

$lang["Content Help"]["Basic data"] = "<br />
    <b>Basic data tab</b><br />
    <br />
    Inside this tab it goes the main information about the Minute.<br />
    <br />
    Fields:<br />
    <br />
    <ul>
        <li>
            <b>Title</b>: title of the Minute.
        </li>
        <li>
            <b>Start</b>: date/time when the meeting starts/started.
        </li>
        <li>
            <b>End</b>: time when the meeting ends/ended.
        </li>
        <li>
            <b>Project</b>: here goes the parent Project; every Project is child of another, at least child of the
            Root one whose name is the company name.
        </li>
        <li>
            <b>Description</b>: a brief description of the meeting (the contents and subjects themselves will be
            filled in Items tab).
        </li>
        <li>
            <b>Place</b>: where the meeting takes place.
        </li>
        <li>
            <b>Moderator</b>: name/s of the moderator/s of the meeting.
        </li>
        <li>
            <b>Status</b>: the status of the item, this is supposed to change over time once the meeting takes place
            and minute's content is filled.<br />
            The options are self-descriptive:<br />
            1 - Planned: the first state.<br />
            2 - Empty.<br />
            3 - Filled: this status is set automatically when some content is filled inside Items tab.<br />
            4 - Final: this status makes the item to be read-only, because it is supposed to be finalized.<br />
            <br />
            After the status is saved as Final, no content can be modified unless you change it back to Filled state.
        </li>
        <li>
            <b>Tag</b>
        </li>
    </ul>
    <br />
    <br />";

$lang["Content Help"]["People"] = "<br />
    <b>People tab</b><br />
    <br />
    This tab allows defining people invited to the meeting, actual attendees and people who are excused.<br />
    <br />
    Fields:<br />
    <br />
    <ul>
        <li>
            <b>Invited</b>: people who is invited to the meeting.
        </li>
        <li>
            <b>Attending</b>: who are going to attend, or have attended the meeting.
        </li>
        <li>
            <b>Excused</b>: people who is not going to, or did not attend the meeting.
        </li>
    </ul>
    <br />
    <br />";

$lang["Content Help"]["Access"] = "DEFAULT";
$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";

$lang["Content Help"]["Mail"] = "<br />
    <b>Mail tab</b><br />
    <br />
    This tab allows sending an email that is different from the regular Notification.<br />
    The focus of the email is the Items tab contents.<br />
    The contents will be the title of the Minutes item, the description and then all Items tab contents in HTML format
    and an optional PDF attachment having almost all Basic Data tab info plus People tab info and Items tab Grid
    contents again.<br />
    <br />
    Fields:<br />
    <br />
    <b>Recipients</b><br />
    You can select to whom send this emails in the 'Recipients' Multiple Select box. To select more than one, click on
    all users you want while holding CTRL key in your keyboard.<br />
    <br />
    <b>Additional Recipients</b><br />
    You can write email addresses of non-listed users, or alternative email addresses for already listed users in
    'Additional Recipients' textbox, comma-separated.<br />
    <br />
    <b>Optional PDF Attachment</b><br />
    You may click 'Include PDF attachment' to attach a PDF file to the email.<br />
    The PDF file has almost all Basic Data tab info plus People tab info and Items tab Grid contents again.<br />
    <br />
    <b>Send mail</b><br />
    Press it to Send the email. You will read at the top right of screen the result of the action, the expected message
    is 'The mail was sent successfully'.<br />
    <br />
    <b>Preview</b><br />
    You can receive a copy of the PDF file previous to sending it pressing 'Preview' button: you will be offered to
    download the file.<br />
    <br />
    <br />";
