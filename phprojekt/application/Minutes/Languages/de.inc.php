<?php
// System
$lang["Minute"] = "Protokolle";

// Fields
 // Basic Data
$lang["Title"] = "Titel";
$lang["Start"] = "Start";
$lang["End"] = "Ende";
$lang["Project"] = "Projekt";
$lang["Description"] = "Beschreibung";
$lang["Place"] = "Ort";
$lang["Moderator"] = "Moderator";
$lang["Invited"] = "Eingeladen";
$lang["Attending"] = "Teilnehmer";
$lang["Excused"] = "Entschuldigt";
$lang["Status"] = "Status";
$lang["Planned"] = "Geplant";
$lang["Empty"] = "Noch leer";
$lang["Filled"] = "Ausgefüllt";
$lang["Final"]   = "Final";

 // Mail tab
$lang["Mail"] = "E-Mail";
$lang["Recipients"] = "Empfänger";
$lang["Additional Recipients"] = "Weitere Empfänger";
$lang["Options"] = "Optionen";
$lang["Include PDF attachment"] = "PDF als Anhang hinzufügen";
$lang["Send mail"] = "E-Mail absenden";
$lang["Preview"] = "Vorschau";

// Messages
  // System
$lang["The currently logged-in user is not owner of the given minutes entry"] = "Die derzeit angemeldeten Benutzers "
    . "ist nicht Eigentümer der gegebenen Minuten Eintrag";
  // View
$lang["Are you sure this Minutes entry should no longer be finalized?"] = "Sind Sie sicher, daß dieses Protokol nicht "
    . "mehr finalisiert sein soll?";
$lang["After proceeding, changes to the data will be possible again."] = "Änderungen wären dann wieder möglich.";
$lang["Are you sure this Minutes entry should be finalized?"] = "Sind Sie sicher, daß dieses Protokoll finalisiert "
    . "werden soll?";
$lang["Write access will be prohibited!"] = "Änderungen sind danach nicht mehr möglich!";
$lang["Minutes are finalized"] = "Protokoll ist finalisiert";
$lang["This Minutes entry is finalized."] = "Dieses Protokoll ist finalisiert.";
$lang["Editing data is no longer possible."] = "Einträge sind nicht mehr möglich.";
$lang["Your changes have not been saved."] = "Ihre Änderungen wurden nicht gespeichert.";

// Mail
  // Messages
$lang["The mail was sent successfully"] = "Die E-Mail wurde erfolgreich versandt";
$lang["The mail could not be sent"] = "Die E-Mail konnte nicht gesendet werden";
$lang["No recipient addresses have been specified"] = "Keine Empfänger-Adressen angegeben wurden";
$lang["Invalid email address detected:"] = "Ungültige E-Mail Adresse:";
  // Content
$lang["Meeting minutes for"] = "Meeting-Protokoll";

// PDF
  // Content
$lang["No."] = "Nr.";
$lang["Type"] = "Typ";
$lang["Item"] = "Eintrag";

// View
$lang["Unfinalize Minutes"] = "Protokoll unfinalisieren";
$lang["Finalize Minutes"] = "Protokoll finalisieren";
$lang["Confirm"] = "Bestätigen";
$lang["Are you sure?"] = "Sind Sie sicher?";

// Tooltip Help
  // Mail tab
$lang["Email addresses of unlisted recipients, comma-separated."] = "E-Mail Adressen für hier nicht gelistete
    Empfänger, kommasepariert";

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
    Fields:<br/>
    <br/>
    <ul>
    <li><b>Title</b>: title of the Minute
    <li><b>Start</b>: date/time when the meeting starts/started.
    <li><b>End</b>: time when the meeting ends/ended.
    <li><b>Project</b>: here goes the parent Project; every Project is child of another, at least child of the Root one
        whose name is the company name.
    <li><b>Description</b>: a brief description of the meeting (the contents and subjects themselves will be filled in
        Items tab).
    <li><b>Place</b>: where the meeting takes place.
    <li><b>Moderator</b>: name/s of the moderator/s of the meeting.
    <li><b>Status</b>: the status of the item, this is supposed to change over time once the meeting takes place and
        minute's content is filled.<br />
        The options are self-descriptive:<br />
        1 - Planned: the first state.<br />
        2 - Empty.<br />
        3 - Filled: this status is set automatically when some content is filled inside Items tab.<br />
        4 - Final: this status makes the item to be read-only, because it is supposed to be finalized.<br />
        <br/>
        After the status is saved as Final, no content can be modified unless you change it back to Filled state.<br/>
    <li><b>Tag</b>
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
    <li><b>Invited</b>: people who is invited to the meeting.
    <li><b>Attending</b>: who are going to attend, or have attended the meeting.
    <li><b>Excused</b>: people who is not going to, or did not attend the meeting.
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

$lang["Content Help"]["Items"] = "<br />
    <b>Items tab</b><br />
    <br />
    Items tab is a submodule inside a tab.<br />
    It has a Grid at its left and a Form at its right, this right Form is used to fill the left Grid, just like a common
    module.<br />
    <br />
    The Items Grid is intended to show the contents of the meeting ordered and divided by Topics. Each Topic may have
    Statements, Todos, Decisions and Dates, and according to each of these 5 kind of content for the Grid, different
    columns of it will be filled.<br />
    <br />
    New Items Grid elements are added to the Items Grid from an empty Form or pressing New in the Form, then filling it
    and pressing Save. To edit or delete existing Items Grid elements click on them and the content will be filled into
    the Items Form at the right.<br />
    <br />
    <br />
    <hr style='height: 2px;'>
    <b>GRID</b><br />
    <br />
    The purpose is that, when meeting contents are 100% filled in this Tab Grid, it shows all the contents of the
    meeting ordered, maybe chronologically, and showing behind each Topic row its statements, todos, decisions and dates
    so that the Grid is a clear representation of all main events and ideas discussed during the meeting.<br />
    <br />
    It has many columns but not all of them are used in the five kind of items added from the Form.<br />
    <br />
    Columns:<br />
    <br />
    <b>Topic</b><br />
    Each time a item of Topic type row is added to the Grid this column shows an integer that follows the previous one.
    E.g.: if you add a Topic as the first item for Items Grid, it will show number 1 in this column. If you add another
    one behind it, it will show number 2.<br />
    The rest of types of items added here will be under a Topic and will have the Topic's number plus a point and a
    'inside-Topic' element number. So that if you add a 'Statement' row behind Topic 1, it will show here '1.1'. If
    there is no any Topic over it, it will show '0.1'.<br />
    This column shows a value on all types of rows.<br />
    <br />
    <b>Title</b><br />
    This column is used in the five types of rows and represents the title of the row.<br />
    <br />
    <b>Type</b><br />
    May be: Topic, Statement, Todo, Decision or Date.<br />
    <br />
    <b>Date</b><br />
    This column is just used in the rows of the type 'Todo' and 'Date', the Date for doing a Todo or the Date of an
    event.<br />
    <br />
    <b>Who</b><br />
    This column is just used by Todo type rows. It represents who is going to do the task.<br />
    <br />
    <br />
    <hr style='height: 2px;'>
    <b>FORM</b><br />
    <br />
    This right Form inside Items tab is used to fill the left Grid, just as if it was a common module inside a
    tab.<br />
    <br />
    The Items Grid is intended to show the contents of the meeting ordered and divided by topics; all that contents are
    filled and defined from this Form.<br />
    <br />
    New Items Grid elements are added to the Items Grid from an empty Form or pressing New in the Form, then filling it
    and pressing Save. To edit or delete existing Items Grid elements click on them and the content will be filled into
    the Items Form at the right.<br />
    <br />
    This form has many fields, how many of them are shown depends on the selection in 'Type' field.<br />
    <br />
    <br />
    <hr style='height: 2px;'>
    <b>FORM FIELDS</b><br />
    <br />
    Fields:<br />
    <br />
    <b>Title</b><br />
    This column is used in the five types of rows and represents the title of them.<br />
    * Required field<br />
    <br />
    <b>Type</b><br />
    Select: possibilities are Topic, Statement, Todo, Decision and Date.<br />
    * Required field<br />
    <br />
    1 - Topic:<br />
    This is a subject discussed or exposed in the meeting, it will content other items inside.<br />
    Each time a item of Topic type is added to the Grid that column shows an integer that follows the previous
    one.<br />
    E.g.: if you add a Topic as the first item for Items Grid, it will show number 1 in this column. If you add another
    one behind it, it will show number 2.<br />
    The rest of types of items added there will be under a Topic and will have the Topic's number plus a point and a
    'inside-Topic' element number. So that if you add a 'Statement' row behind Topic 1, it will show here '1.1'. If
    there is no any Topic over it, it will show '0.1'.<br />
    That column is used by all types of rows.<br />
    Fields used by Topic type: Title, Comment and Sort After.<br />
    <br />
    2 - Statement<br />
    This will be shown inside a Topic in the Grid, and is a text representing an idea, a point, a concept, etc.<br />
    Fields shown in this Form when 'Statement' is selected here: Title, Comment and Sort After.<br />
    <br />
    3 - Todo<br />
    This will be inside a Topic in the Grid and means a planned task to carry out.<br />
    Fields showed in this Form when 'Todo' is selected here: Title, Comment, Who, Date and Sort After.<br />
    <br />
    4 - Decision<br />
    This will be inside a Topic in the Grid and means a planned task to carry out.<br />
    Fields showed in this Form when 'Decision' is selected here: Title, Comment and Sort After.<br />
    <br />
    5 - Date<br />
    This will be inside a Topic in the Grid and represents an discussed event or something planned during the meeting.
    Fields showed in this Form when 'Decision' is selected here: Title, Comment, Date and Sort After.<br />
    <br />
    <b>Comment</b><br />
    Textarea: this field is present in all types of selected Topics and is used to describe the row to be added to Items
    Grid.<br />
    <br />
    <b>Who</b><br />
    Select: this field is only shown when 'Todo' is the selected Topic and it is used to select the user who is going to
    carry out the planned task.<br />
    <br />
    <b>Date</b><br />
    Date: this field is just shown when 'Todo' or 'Date' are the selected Topics and it is used to establish when the
    task is going to be carried out or when the event is going to take place, respectively.<br />
    <br />
    <b>Sort After</b><br />
    Select: this is a very important field, it is used for establishing the order of each row: when you create or edit a
    row, here you select 'after which' one it will be located in the left grid. You can re-arrange a whole listing
    clicking on each row in the Grid, changing its 'Sort after' value in the Form and then clicking Save.<br />
    <br />
    <br />
    <hr style='height: 2px;'>
    <b>FORM BUTTONS</b><br />
    <br />
    Buttons are shown at the bottom of Form fields, which ones are shown depends on the state of the Form: if you are
    editing an item: Save, Delete and New will be shown, if not, just Save will be enough.<br />
    <br />
    <b>New</b><br />
    Just shown when editing an item of Items Grid. Changes made to the Form will be lost when pressed, if any, and an
    empty Form will be opened.<br />
    <br />
    <b>Save</b><br />
    Shown when editing a row of the Grid, press it to apply changes.<br />
    <br />
    <b>Delete</b><br />
    Shown after clicking a row of the Grid, press it to delete it.<br />
    <br />
    <br />";
