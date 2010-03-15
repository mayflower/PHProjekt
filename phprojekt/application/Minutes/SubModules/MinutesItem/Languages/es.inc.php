<?php
// System
$lang["MinutesItem"] = "Items";

// Fields
 // Basic Data
$lang["Title"] = "Título";
$lang["Type"] = "Tipo";
$lang["Topic"] = "Tópico";
$lang["Statement"] = "Declaración";
$lang["Todo"] = "Pendiente";
$lang["Decision"] = "Decisión";
$lang["Comment"] = "Comentario";
$lang["Date"] = "Fecha";
$lang["Who"] = "Quién";
$lang["Sort after"] = "Ubicar luego de";

// Messages
  // System
$lang["This minutes is final and cannot be edited."] = "Esta minuta es final y no puede ser editada.";
$lang["Undefined topicType"] = "Tipo no definido";

// General Help
$lang["Content Help"]["Items"] = "<br />
    <b>Items tab</b><br />
    <br />
    Items tab is a submodule inside a tab.<br />
    It has a Grid at its left and a Form at its right, this right Form is used to fill the left Grid, just like a
    common module.<br />
    <br />
    The Items Grid is intended to show the contents of the meeting ordered and divided by Topics. Each Topic may have
    Statements, Todos, Decisions and Dates, and according to each of these 5 kind of content for the Grid, different
    columns of it will be filled.<br />
    <br />
    New Items Grid elements are added to the Items Grid from an empty Form or pressing New in the Form, then filling it
    and pressing Save. To edit or delete existing Items Grid elements click on them and the content will be filled into
    the Items Form at the right.<br />
    <br />
    <hr style='height: 2px;'>
    <b>GRID</b><br />
    <br />
    The purpose is that, when meeting contents are 100% filled in this Tab Grid, it shows all the contents of the
    meeting ordered, maybe chronologically, and showing behind each Topic row its statements, todos, decisions and
    dates so that the Grid is a clear representation of all main events and ideas discussed during the meeting.<br />
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
    'inside-Topic' element number. So that if you add a 'Statement' row behind Topic 1, it will show here '1.1'.
    If there is no any Topic over it, it will show '0.1'.<br />
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
    <hr style='height: 2px;'>
    <b>FORM</b><br />
    <br />
    This right Form inside Items tab is used to fill the left Grid, just as if it was a common module inside a tab.
    <br />
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
    'inside-Topic' element number. So that if you add a 'Statement' row behind Topic 1, it will show here '1.1'.
    If there is no any Topic over it, it will show '0.1'.<br />
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
    Textarea: this field is present in all types of selected Topics and is used to describe the row to be added to
    Items Grid.<br />
    <br />
    <b>Who</b><br />
    Select: this field is only shown when 'Todo' is the selected Topic and it is used to select the user who is going
    to carry out the planned task.<br />
    <br />
    <b>Date</b><br />
    Date: this field is just shown when 'Todo' or 'Date' are the selected Topics and it is used to establish when the
    task is going to be carried out or when the event is going to take place, respectively.<br />
    <br />
    <b>Sort After</b><br />
    Select: this is a very important field, it is used for establishing the order of each row: when you create or edit
    a row, here you select 'after which' one it will be located in the left grid. You can re-arrange a whole listing
    clicking on each row in the Grid, changing its 'Sort after' value in the Form and then clicking Save.<br />
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
