<?php
// General translation strings:
// [Form.js] - Form labels
$lang["Title"] = "Title";
$lang["Comment"] = "Comment";
$lang["Who"] = "Who";
$lang["Type"] = "Type";
$lang["Date"] = "Date";
$lang["Sort"] = "Sort after";
$lang["Save"] = "Save";
$lang["Delete"] = "Delete";
$lang["New"] = "New";

// [Form.js] - Tab name
$lang["Items"] = "Items";

// [Form.js] - Selectbox values
$lang["Topic"] = "Topic";
$lang["Statement"] = "Statement";
$lang["Todo"] = "Todo";
$lang["Decision"] = "Decision";
$lang["Date"] = "Date";

// Grid headers - field names from DatabaseDesigner
$lang["Date of Meeting"] = "Date of Meeting";
$lang["Description"] = "Description";
$lang["Start Time"] = "Start Time";
$lang["Place"] = "Place";
$lang["Status"] = "Status";

// Grid values - from DatabaseDesigner
$lang["Planned"] = "Planned";
$lang["Created"] = "Empty";
$lang["Filled"] = "Filled";
$lang["Final"]   = "Final";

// Field labels from DatabaseDesigner
$lang["Moderator"] = "Moderator";
$lang["End time"] = "End time";
$lang["Tag"] = "Tag";
$lang["Invited"] = "Invited";
$lang["Attending"] = "Attending";
$lang["Excused"] = "Excused";
$lang["Recipients"] = "Recipients";

// Mail tab
$lang["Additional Recipients"] = "Additional Recipients";
$lang["Options"] = "Options";
$lang["Include PDF attachment"] = "Include PDF attachment";
$lang["Email addresses of unlisted recipients, comma-separated."] = "Email addresses of unlisted recipients,
    comma-separated.";
$lang["Send mail"] = "Send mail";
$lang["Preview"] = "Preview";
$lang["Mail"] = "Mail";

// Mail functions
$lang["Meeting minutes for"] = "Meeting minutes for";
$lang["The mail could not be sent."] = "The mail could not be sent.";
$lang["The mail was sent successfully."] = "The mail was sent successfully.";
$lang["Invalid email address detected:"] = "Invalid email address detected:";
$lang["No recipient addresses have been specified."] = "No recipient addresses have been specified.";

// PDF formatting strings
$lang["Undefined topicType"] = "Undefined Type";
$lang["No."] = "No.";
$lang["Item"] = "Item";

// Confirmation dialogs
$lang["Confirm"] = "Confirm";
$lang["Are you sure?"] = "Are you sure?";
$lang["OK"] = "OK";
$lang["Cancel"] = "Cancel";
$lang["Unfinalize Minutes"] = "Unfinalize Minutes";
$lang["Are you sure this Minutes entry should no longer be finalized?"] = "Are you sure this Minutes entry should no "
    . "longer be finalized?";
$lang["After proceeding, changes to the data will be possible again."] = "After proceeding, changes to the data will "
    . "be possible again.";
$lang["Finalize Minutes"] = "Finalize Minutes";
$lang["Are you sure this Minutes entry should be finalized?"] = "Are you sure this Minutes entry should be finalized?";
$lang["Write access will be prohibited!"] =  "Write access will be prohibited!";
$lang["Minutes are finalized"] = "Minutes are finalized";
$lang["This Minutes entry is finalized."] = "This Minutes entry is finalized.";
$lang["Editing data is no longer possible."] = "Editing data is no longer possible.";
$lang["Your changes have not been saved."] = "Your changes have not been saved.";

// Messages
$lang["The currently logged-in user is not owner of the given minutes entry."] = "The currently logged-in user is not "
    . "owner of the given minutes entry.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Minutes"] = "<br />
    This is the <b>General Help of the Minutes module</b><br />
    <br />
    This module is meant for the transcription of meeting minutes.<br />
    ";
$lang["Content Help"]["Basic data"] = "DEFAULT";
$lang["Content Help"]["People"] = "<br />
    <b>People tab</b><br />
    <br />
    Allows defining people invited to the meeting, actual attendees and people who are excused.<br/>
    Also permits defining recipients for the Notification email.
    ";
$lang["Content Help"]["Items"] = "<br />
    <b>Items tab</b><br />
    <br />
    This tab permits gathering, writing and organizing all the contents exposed in the meeting.<br/>
    <br/>
    It has a grid listing at the left and a form at the right side.<br/>
    <br/>
    Each item added to the listing has the following fields in the form, for you to edit them:<br/>
    <br/>
    <ul>
        <li><b>Title:</b> a descriptive title for the item to be added, like 'New mobile phone design'.
        <li><b>Type:</b> the type for the item being added, it may be one of the following.
            <ul>
                <li>Topic
                <li>Statement
                <li>Todo
                <li>Decision
                <li>Date
            </ul>
        <li><b>Comment:</b> the descriptive content of the item.
        <li><b>Who (only visible when Type 'Todo' is selected):</b> who is responsible for doing the activity.
        <li><b>Date (only visible when Type 'Todo' or 'Date' is selected):</b> the date for the activity or the item
            that has been discussed.
        <li><b>Sort after:</b> where in the list will be situated this item.
    </ul>
    <br/>
    The grid orders and shows automatically all the items added.<br/>
    <br />
    <br />";

$lang["Content Help"]["Mail"] = "<br />
    <b>Mail tab</b><br />
    <br />
    From here it can be sent an email containing all the information of the selected Minutes register, with an optional
    PDF attachment.<br/>
    <br/>
    Recipients can be selected in the <b>Recipients</b> field and also may be written plain email addresses in the
    <b>Additional Recipients</b> field below it.<br/>
    Check the box <b>Include PDF attachment</b> to send also the same info as a PDF file.<br/>
    <br/>
    Then the button <b>Send mail</b> sends it, or the button <b>Preview</b> shows you the attachment so that you can
    verify the content before sending the email.<br/>
    <br/>
    <br/>";
$lang["Content Help"]["Access"] = "DEFAULT";
$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";
