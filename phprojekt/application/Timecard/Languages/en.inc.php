<?php
// Words and phrases
$lang["Working Times"] = "Working Times";
$lang["Start working time"] = "Start working time";
$lang["Stop working time"] = "Stop working time";
$lang["Hours"] = "Hours";
$lang["The end time must be after the start time"] = "The end time must be after the start time";
$lang["Change date"] = "Change date";
$lang["Project Bookings"] = "Project Bookings";
$lang["Add working time and drag projects into the bar"] = "Add working time and drag projects into the bar";
$lang["Manage project list"] = "Manage project list";
$lang["Amount [hhmm]"] = "Amount [hhmm]";
$lang["Total"] = "Total";

// Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Timecard"] = "<br/>
    This is the <b>General Help for Timecard</b><br/>
    <br/>
    Timecard is a module for charging and assigning working hours to the Projects. It has a visual interface and is
    very easy to use.<br/>
    <br/>
    The screen is divided into 4 sections:<br/>
    <br/>
    <ul>
        <li><b>Top button bars</b><br/>
        <li><b>Working Times</b><br/>
        <li><b>Project Bookings</b><br/>
        <li><b>Month Grid</b><br/>
    </ul><br/>
    For each section's help, see the respective tab in this window.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Button bars"] = "<br/>
    <b>Button bars</b><br/>
    <br/>
    There is one <b>top</b> bar and one <b>below</b> that.<br/>
    <br/>
    <ol>
        <li>The top button bar has two <b>export</b> buttons at right:
            <ul>
                <li><b>Working Times:</b> exports in CSV file format the working periods for the selected month. Which days
                    the user worked in, and in what time of the day.<br/>
                <li><b>Project Bookings:</b> exports in CSV file format the working hours assigned to projects and
                    subprojects for each day of the selected month.<br/>
            </ul>
        <li>The bar that is <b>below</b> that has 2 buttons and a date field:
            <ul>
                <li><b>Start working times</b> and <b>Stop working times</b> buttons: they are used for the user to
                    register automatically the time he has been working in.<br/>
                    Example: a user logs in to the
                    system at 9:00 and presses the <i>Start working times</i> button. At 13:30 he / she goes out for
                    lunch, presses <i>Stop working times</i> button. The system records that time and shows it in
                    three places:<br/>
                    <ol>
                        <li>Below the square, in the same panel.<br/>
                        <li>Inside <b>Project Bookings</b> panel, in the right part as a yellow square that involves
                            the time period from 9:00 to 13:30 in the hours listing.<br/>
                        <li>In the <b>Month Grid</b> that charged amount of hours is added to the existing ones for
                            that day in the corresponding row of <i>Working Times</i> column.<br/>
                    </ol>
                <li><b>Date field:</b> here you can select what day do you want to work with.<br/>
            </ul>
    </ol>
    <br/>
    <br/>";

$lang["Content Help"]["Working Times"] = "<br/>
    <b>Working times</b><br/>
    <br/>
    This is the panel where the hours of working time are charged (and eventually deleted).<br/>
    <br/>
    The period is filled inside <b>Start</b> and <b>End</b> textboxes. The <b>check button</b> saves it and the period
    appears in 3 places over the current screen:<br/>
    <ol>
        <li>Below the square, in the same panel.<br/>
        <li>Inside <b>Project Bookings</b> panel, in the right part as a yellow square that involves that time period
        in the hours listing.<br/>
        <li>In the <b>Month Grid</b> that charged amount of hours is added to the existing ones for that day in the
        corresponding row of <i>Working Times</i> column.<br/>
    </ol>
    <br/>
    <br/>";

$lang["Content Help"]["Project Bookings"] = "<br/>
    <b>Project Bookings</b><br/>
    <br/>
    This is the more interesting panel, desgined to assign tasks to the charged worked time, the most of things are
    shown and managed graphically.<br/>
    <br/>
    <b>Project list:</b><br/>
    <br/>
    There is a button <b>Manage project list</b> to select <i>favorite</i> projects
    that are the ones you will work with, it means, they will be assigned to the charged hour periods.<br/>
    Those selected projects will appear over the button and they are just one group for all the module, it means that
    if it is changed the date of the current Timecard view, the same projects group will be shown.<br/>
    <br/>
    <b>Time schedule:</b><br/>
    <br/>
    Goes from 8:00 to 20:00. Here lie all the hour times loaded in the <b>Working Times</b>
    panel. Each of those periods appears as a yellow square that occupies its corresponding surface. You can assign
    a period to a specific project; that's very simple, you drag & drop a project (that should be listed in the
    favorite list over <b>Manage project list</b> button), to the yellow square. After releasing the project a
    floating window appears over the <b>Manage project list</b> button for you to set how many hours you worked on that
    Project and to write a Note.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Month Grid"] = "<br/>
    <b>Month Grid</b><br/>
    <br/>
    This grid is a day by day summary of the worked hours for all the month, also shows how much time of them has been
    assigned to Projects (and Subprojects).<br/>
    <br/>
    You can click on a date to load it in the other panels of the module.<br/>
    <br/>
    <br/>";

// General Tooltip buttons Help
$lang["Start Stop Buttons Help"] = "This buttons Start and Stop working time automatically.";
$lang["Working Times Help"] = "Here you should add the all the worked hours of the selected day.";
$lang["Hours Help"] = "The format of the hours is without colon ( : ) 08:00 es 0800";
$lang["Project Times Help"] = "Inside this panel you can Drag & Drop Projects into the worked hours.";
