<?php
// System
$lang["Gantt"] = "Gantt";

// Fields
$lang["Projects"] = "Projects";
$lang["Project"] = "Project";

// Messages
  // System
$lang["No project info was received"] = "No project info was received";
$lang["Incomplete data received"] = "Incomplete data received";
$lang["Id not found #"] = "Id not found #";
$lang["Project id #"] = "Project id #";
$lang["Start date invalid"] = "Start date invalid";
$lang["End date invalid"] = "End date invalid";
$lang["Start date can not be after End date"] = "Start date can not be after End date";
  // View
$lang["Attention: parent project"] = "Attention: parent project";
$lang["Attention: sub-project"] = "Attention: subproject";
$lang["starts after sub-project"] = "starts after subproject";
$lang["ends before sub-project"] = "ends before subproject";
$lang["ends after parent project"] = "ends after parent project";
$lang["starts before parent project"] = "starts before parent project";
$lang["Click \"OK\" to adjust parent project to new start date"] = "Click \"OK\" to adjust parent project to new
    start date";
$lang["Click \"OK\" to adjust parent project to new end date"] = "Click \"OK\" to adjust parent project to new
    end date";
$lang["Click \"OK\" to adjust sub-project to new end date"] = "Click \"OK\" to adjust subproject to new end date";
$lang["Click \"OK\" to adjust sub-project to new start date"] = "Click \"OK\" to adjust subproject to new start date";
$lang["Click \"Reset\" to reset current project"] = "Click \"Reset\" to reset current project";
$lang["Click \"x\" or \"ESC\" to do nothing"] = "Click \"x\" or \"ESC\" to do nothing";
$lang["There are no valid projects"] = "There are no valid projects";

// View
$lang["Selected Project Timeline"] = "Selected Project Timeline";
$lang["Warning"] = "Warning";

// Tooltip Help
$lang["Click on a Project timeline and see and/or change here the Start and End dates."] = "Click on a Project timeline"
    . " and see and/or change here the Start and End dates.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Gantt"] = "<br />
    This is the <b>General Help of Gantt module</b><br />
    <br />
    This module is a Gantt chart interface, mouse editable, which is automatically synchronized with the data stored in
    the database.<br />
    <br />
    A Gantt chart is a type of bar chart that illustrates a project schedule. Gantt charts illustrate the start and
    finish dates of the terminal (children) elements and summary (parent) elements of a Project.<br />
    The module reads the Projects and Subprojects and elaborates a graphic that has one horizontal bar per Project
    (timeline) which can be moved with the mouse scrolling it over time or just increasing or decreasing the start and
    end dates.<br />
    <br />
    Up the chart there is a space where a panel called 'Selected Project Timeline' appears. When you click on a Project
    timeline bar, this panel's fields get loaded with the start and end dates of clicked bar Project, so that you can
    see and/or modify them writing different dates or selecting them in the calendar that pops up for each field.<br />
    <br />
    <br />";

$lang["Content Help"]["Listed Projects"] = "<br />
    The listed Projects are those you have at least Read access to, and also that they have filled 'Start Date' and
    'End Date' values.<br />
    <br />
    Firstly there will be listed all Projects that are children of the active one, no matter whether it is Root Project
    (PHProjekt) or another one.<br />
    If one of the listed Projects has at least one child, it will be shown a [+] plus sign at its left for you to click
    it or the Project name and open it and see its children under it. The children may also have Sub-Projects, and so
    on.<br />
    <br />
    <b>Timeline</b><br />
    <br />
    According to the earlier Start Date and latest End Date for all the Projects, a specific amount of years will fill
    the horizontal scope of the timeline.<br />
    The timeline always starts in January and ends in December but those months not necessary correspond to the same
    year.<br />
    For all years that have at least one month occupied by a Project bar, 12 months will be shown.<br />
    E.g.: If 2 Projects are listed, one starting in Jul-08 and ending in Aug-08, and the other starting in Dec-08 and
    ending in Mar-09 then 24 months will be shown in the timeline, from Jan-08 to Dic-09.<br />
    <br />
    <b>Bars</b><br />
    <br />
    The bar of each Project occupies a proportional horizontal surface and position according its dates, so that you can
    visually perceive its duration in time, relation and proportion paying attention to the column headers, and compare
    it with its Sub-Projects, parent or another Projects.<br />
    <br />
    <br />";

$lang["Content Help"]["Changing dates"] = "<br />
    <b>MOVING AND RESIZING THE BARS</b><br />
    <br />
    There are two ways of modifying times of a Project through the mouse and the bars: Moving and Resizing them.<br />
    <br />
    <b>Moving the Bars</b><br />
    <br />
    Click on the body of any of them (not the darker left and right borders) and without releasing the mouse button,
    drag them left or right to the new position.<br />
    You won't be able to drag it to a point outside the timeline, like a previous or posterior year.<br />
    <br />
    <b>Resizing the Bars</b><br />
    <br />
    You can click on a bar border and without releasing the mouse drag it left or back to increase or decrease Project
    length.<br />
    <br />
    <b>Selected Project Timeline panel</b><br />
    <br />
    When you click on the bar, like in the previous cases, a panel 'Selected Project Timeline' appears showing you the
    exact Start and End dates for the Project.<br />
    Also, after you drag or resize a bar using the mouse, those dates will be shown and get updated automatically
    according to the new position of the bar.<br />
    See 'Changing dates using the panels' part of this help for more details of the panel use.<br />
    <br />
    <hr style='height: 2px;'>
    <b>USING THE PANELS</b><br />
    <br />
    You can see the exact Start and End dates for any bar of the Projects.<br />
    <br />
    Just click on one bar, and a panel called 'Selected Project Timeline' will appear on top of the window Gantt
    section showing you both dates. After changing any bar width and/or position, both fields get updated automatically
    with the changes made to the bar.<br />
    <br />
    <b>Modifying the Dates using those fields</b><br />
    <br />
    Those 2 dates are editable; you can click on any of them and pick a date in the dynamic pop-up calendar that
    appears behind, or write it manually in the field.<br />
    Then press the Check button; if no error is found the bar will move onto its new parameters.<br />
    <br />
    <br />";

$lang["Content Help"]["Checks and errors"] = "<br />
    After you change Project dates either from the bar (moving the whole bar or just a border of it) or upper panel
    fields, internal checks are performed. If any error is found, one of the following four errors will be brought up
    in a dialog for you to choose what to do. XX in the error title means a Project name.<br />
    <br />
    Note: all errors are connected with the fact that in this structure of Projects hierarchy all Sub-Projects are
    supposed to happen inside parent's time.<br />
    <br />
    Errors:<br />
    <br />
    <b>Subproject XX starts before parent project XX</b><br />
    <br />
    When you change a Start date of a Project so that any of its children now starts before it, this error appears.
    <br />
    <br />
    The Error pop-up dialog offers you to choose one of these three options:<br />
    Click OK for the Sub-Project Start date to be moved forward to match parent's Start date.<br />
    Click Reset to restore the moved Project to its previous position and/or width.<br />
    Click the top right [x] cross button or Esc to leave things as they are.<br />
    <br />
    <br />
    <b>Subproject XX ends after parent project XX</b><br />
    <br />
    When you change an End date of a Project so that now it ends before any of its children, this error appears.<br />
    <br />
    The Error pop-up dialog offers you to choose one of these three options:<br />
    Click OK for the Sub-Project End date to be moved backwards to match parent's End date.<br />
    Click Reset to restore the moved Project to its previous position and/or width.<br />
    Click the top right [x] cross button or Esc to leave things as they are.<br />
    <br />
    <br />
    <b>Parent project XX starts after subproject XX</b><br />
    <br />
    When you change a Start date of a Sub-Project so that now it starts before its parent, this error appears.<br />
    <br />
    The Error pop-up dialog offers you to choose one of these three options:<br />
    Click OK for the parent Project Start date to be moved backwards to match child's Start date.<br />
    Click Reset to restore the moved Sub-Project to its previous position and/or width.<br />
    Click the top right [x] cross button or Esc to leave things as they are.<br />
    <br />
    <br />
    <b>Parent project XX ends before subproject XX</b><br />
    <br />
    When you change an End date of a Sub-Project so that now it ends after its parent, this error appears.<br />
    <br />
    The Error pop-up dialog offers you to choose one of these three options:<br />
    Click OK for the parent Project End date to be moved forward to match child's End date.<br />
    Click Reset to restore the moved Sub-Project to its previous position and/or width.<br />
    Click the top right [x] cross button or Esc to leave things as they are.<br />
    <br />
    <br />";

$lang["Content Help"]["Write access"] = "<br />
    You will be able to make changes only in the Projects where you have write permission.<br />
    <br />
    If you have right permission in at least one of the listed Projects then the 'Save' button will be seen.<br />
    The rest of the Project bars will be disabled and you won't be able to move them.<br />
    <br />
    <br />";
