<?php
// System
$lang["Timecard"] = "Asistencia";

// Fields
$lang["Start"] = "Comienzo";
$lang["End"] = "Fin";
$lang["Minutes"] = "Minutos";
$lang["Project"] = "Projecto";
$lang["Notes"] = "Notas";
$lang["Hours"] = "Horas";
$lang["Time period"] = "Período de tiempo";

// Messages
  // System
$lang["Start time has to be between 0:00 and 24:00"] = "La hora de inicio tiene que ser entre 0:00 y 24:00";
$lang["The start time is invalid"] = "La hora de inicio es inválida";
$lang["Can not Start Working Time because this moment is occupied by an existing period or an open one"] = "No se puede"
    . " Empezar Tiempo de Trabajo porque este momento está ocupado por un período existente o uno abierto";
$lang["The end time must be after the start time"] = "La hora final debe ser mayor a la hora inicial";
$lang["End time has to be between 0:00 and 24:00"] = "La hora final tiene que ser entre 8:00 y 24:00";
$lang["The end time is invalid"] = "La hora final es inválida";
$lang["Can not End Working Time because this moment is occupied by an existing period"] = "No se puede Terminar "
    . "Tiempo de Trabajo porque este momento está ocupado por un período existente";
$lang["The entry overlaps with an existing one"] = "No puede grabarse porque se superpone con un período "
    . "existente";

// View
$lang["Manage project list"] = "Admin. lista de proyectos";
$lang["Drag the projects from left to right"] = "Arrastre los proyectos de izquierda a derecha";
$lang["Total hours"] = "Total de horas";

// Tooltip Help
$lang["Click for open the form"] = "Haga clic para abrir el formulario";
$lang["Favorite projects appear first in the select box of the form"] = "Proyectos favoritos aparecen primero en el "
    . "selector de proyectos";

// Setting
$lang["Favorite projects"] = "Proyectos favoritos";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Timecard"] = "<br />
    This is the <b>General Help for Timecard</b><br />
    <br />
    Timecard is a module designed to assign working times to Projects so that it is kept a record about what the people
    involved in the Projects have worked in, and how much time have they spent on each thing.<br />
    These times will also impact on Statistic module.<br />
    <br />
    <br />
    <b>Sections of the module</b><br />
    <br />
    It has a month section at the left which shows all days of selected month and booked time for each one for the
    logged user. On bottom the booked amounts for each day, there is the sum of all them.<br />
    Each day is a link for you to click on and load its contents in the Day section, in the middle of the screen.<br />
    There is also a Date type field on top that permits to change not only the day but also the month and year
    both showed in Month left view and the Day view.
    <br />
    In the middle of the screen there is a schedule for the day. The purpose of it is that the user may perceive
    graphically the amount of time worked for selected day and the Projects involved.<br />
    Clicking on the [+] icon or in a booked box, a pop-up appear with the form for add, edit and delete Project bookings.
    <br />
    <br />
    There is an export button on the top left of the module that exports in CSV file format all booked times for logged
    user and selected month.<br />
    Near it there is the 'Manage project list' button that provides a dialog for manage the favorites projects that
    will appear first on the project selection in the form.<br />
    <br />
    <br />
    <b>Overlapping times error</b><br />
    <br />
    When you try to add or edit a booking so that some of its time overlaps part of the time of another booking, an
    internal checking will bring up an error for you to correct the start and end time of it and/or the date.<br />
    <br />
    <br />";

$lang["Content Help"]["Month"] = "<br />
    <b>Month section</b><br />
    <br />
    The Date field allows selecting a day to show in the schedule. Also, the selected month will be loaded into
    this section.<br />
    <br />
    Month section is a list of the days for the selected month which shows at the right side of each day the booked
    time for the logged user on that day. On bottom the booked amounts for each day, there is the total amount of the
    month.<br />
    <br />
    Each listed day is a link to load it onto the center Day section; if you click on any day of the list, the Day
    schedule will load all booked Projects for that day, if any, and the Date field on top of this section will be
    filled with that date.<br />
    <br />
    To change the listed month, select a date of any month in the Date field over this section and click its check
    button.<br />
    <br />
    <br />
    <b>Colors</b><br />
    <br />
    The weekend days have grey color, the rest of them are black.<br />
    <br />
    If there is an open period in any day, its total time will be red colored so that the user can notice there is
    something unfinished there.<br />
    <br />
    <br />";

$lang["Content Help"]["Day"] = "<br />
    <b>Day section</b><br />
    <br />
    Day section is located in the center of the Timecard view.<br />
    It has a schedule showing all time of the day with its Project bookings inside, if any.<br />
    <br />
    <br />
    <b>Rows</b><br />
    <br />
    The schedule is formed by 48 rows; one per half hour.<br />
    Each row has a (+) plus sign to add a booking for that time. If you press it the selected Date and Time will be
    loaded into the Form in field Start. The End Time will be filled automatically, its value is one hour after
    Start Time.<br />
    <br />
    <br />
    <b>Bookings</b><br />
    <br />
    The bookings are shown as a rectangle that occupies an area proportional to its length in time.
    It has the name of the booked Project inside.<br />
    The purpose is that the user can perceive graphically the amount of time worked and the Projects involved.<br />
    You may click on a Project to open it in the Form and modify its contents or delete it.<br />
    <br />
    Note: the open periods are shown in red color, they will become 'normal' when their end time get filled.<br />
    <br />
    <br />";

$lang["Content Help"]["Form"] = "<br />
    <b>Form section</b><br />
    <br />
    The Form provides a couple of fields to add or edit bookings.<br />
    <br />
    You can add an item pressing the (+) plus sign in the center schedule and the Form will be loaded with the Date and
    Time pressed, and End time set to 1 hour after Start Time. You select a Project, write a Note and press Save to add
    it.<br />
    To edit a booking, click on it inside the day schedule; its contents will be loaded into the Form. Make changes and
    press Save, or just press Delete to take out the booking from the schedule.<br />
    <br />
    <br />
    Form fields:<br />
    <br />
    <b>Start</b><br />
    Date/time: the date and time for the booking start.<br />
    This field is automatically filled in the following cases.<br />
    1 - When you click on a date of the left Month section -> clicked date and current time<br />
    2 - When you select a date up the day schedule and press the check button -> selected date and current time<br />
    3 - When you click on a booking in the day schedule -> start date and time of booking<br />
    4 - When you click on a (+) plus button of a specific row of the day schedule -> active date and clicked row
    time<br />
    <br />
    <b>End Time</b><br />
    Time: end time for the booking.<br />
    It is automatically filled in the following cases.<br />
    1 - When you click on a booking in the day schedule -> the end time of it<br />
    2 - When you click on a (+) plus button of a specific row of the day schedule -> one hour after clicked row
    time<br />
    <br />
    <b>Project</b><br />
    Select: the booked Project.<br />
    All the favorites projects will appear first, then all the rest.<br />
    <br />
    <b>Notes</b><br />
    Textarea (without HTML mode): a Note with some description of the booking.<br />
    It is automatically filled when you click on a booking of the day schedule.<br />
    <br />
    <br />";

$lang["Content Help"]["Favorite Projects"] = "<br />
    <br />
    <b>Managing favorite Projects list</b><br />
    <br />
    When you press 'Manage project list' button, a little pop-up window appears. It has two panels. The left one shows
    all Projects and Sub-Projects you are involved in, it allows you to drag and drop each Project with the mouse to the
    second panel, at the right. That right panel is the one that has the Projects that will be seen under the Form when
    you save changes pressing the Check button and this window gets closed.<br />
    <br />
    If you want to take out a Project from the right panel of this pop-up window, drag and drop it back to the left
    panel using the mouse.<br />
    <br />
    When you have finished doing the changes, press the Check button to save and close the pop-up window. The new
    Projects listing appears under the Form. Each Project is shown as a yellow ochre color rectangle with curved
    ends.<br />
    Projects available to be added to favorites list are those you have at least Read access to.<br />
    <br />
    <br />";

$lang["Content Help"]["Booking times"] = "<br />
    <br />
    <b>Clicking a (+) plus button in a schedule row</b><br />
    <br />
    Each row of the schedule has a (+) plus button to add a booking for that time. If you press it the selected Date
    and Time will be loaded into the Form in field Start, and the Form will get into Add mode and End Time will be
    filled automatically as one hour after Start Time, Project and Notes fields will be emptied.<br />
    <br />
    <br />";
