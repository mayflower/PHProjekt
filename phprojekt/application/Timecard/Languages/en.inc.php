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
$lang["The amount is invalid"] = "The amount is invalid (From 30 to 1300)";
$lang["Start time has to be between 8:00 and 21:00"] = "Start time has to be between 8:00 and 21:00";

// Tooltip Help
$lang["Start Stop Buttons Help"] = "This buttons Start and Stop working time automatically.";
$lang["Working Times Help"] = "Here you should add the all the worked hours of the selected day.";
$lang["Hours Help"] = "The format of the hours may be with or without a symbol: 08:00, 0800, 800, etc.";
$lang["Project Times Help"] = "Inside this panel you can Drag & Drop Projects into the worked hours.";

// General Help
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
                <li><b>Working Times:</b> exports in CSV file format the working periods for the selected month. Which
                    days the user worked in, and in what time of the day.<br/>
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
                        <li>Below the box, in the same panel.<br/>
                        <li>Inside <b>Project Bookings</b> panel, in the right part as a yellow box that involves
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
    The period is filled inside <b>Start</b> and <b>End</b> textboxes.<br/>
    The format for writing the hours is <b>HH?MM</b> where HH is the hour, the question mark represents the colon (:)
    or whatever; although it is not obligatory, and MM the minutes. Examples: 08:00, 8:00, 0800, 800, 08.00 or 8.00
    <br/>
    <br/>
    Then the <b>check button</b> saves it and the period appears in 3 places over the current screen:<br/>
    <ol>
        <li>Below the box, in the same panel.<br/>
        <li>Inside <b>Project Bookings</b> panel, in the right part as a yellow box that involves that time period
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
    panel.<br/>
    <br/>
    Each of those periods appears as a yellow box that occupies its corresponding surface. You can assign
    a period to a specific project; that's very simple, you drag & drop a project (that should be listed in the
    favorite list over <b>Manage project list</b> button), to the yellow box. After releasing the project a
    floating window appears below the <b>Manage project list</b> button for you to set how many hours you worked on that
    Project and to write a Note.<br/>
    Then you press <b>Save</b> and the floating window gets closed and the yellow box gets filled with another
    box; gray, that contains the selected project. The amount of space occupied by this gray box is in
    proportion with the amount of hours assigned to the project.<br/>
    To edit the assigned project you click on it inside the schedule (the gray box of the project) and the floating
    window appears again allowing to modify its data or deleting it from there.<br/>
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

$lang["Content Help"]["Step by step"] = "<br/>
    <b>How to load the worked hours and assign them to projects, step by step</b><br/>
    <br/>
    <br/>
    <ol>
        <li>Inside <b>Timecard</b> module, select the day when you want to load the worked hours using the date field
            from the upper bar. Then press the check button at its right.<br/>
            <br/>
        <li>In the <b>Working Times</b> panel enter a worked period of the selected day, for example if you worked from
            9:00 to 13:00 and from 14:00 to 18:00, this would be two periods, begin loading the first one. For that
            purpose inside <b>Start</b> field enter <i>9:00</i> and inside <b>End</b> field write <i>13:00</i> (you can
            omit the colon by writing just 900 and 1300 if you want so).<br/>
            <br/>
        <li>Press the check button under those fields to save them. After an instant the period will appear inside the
            screen in many places:<br/>
            <ul>
                <li>Below, in the same panel, as small color rectangle with the period you just entered. It has a cross
                    button that allows you to delete it. Under that rectangle there is a legend with the total worked
                    hours for that day.
                <li>In the <b>Working Times</b> panel, at the right side, inside the day schedule that goes from 8:00
                    to 20:00, there will appear a yellow box that occupies a surface proportional to the loaded period.
                <li>In the <b>Month Grid</b>, that is the panel at the right of the screen, in the active day row,
                    there will be added to the amount of hours, in the column <b>Working times</b> the amount of hours
                    just loaded. If it is the first period you load for this day, there will only appear just that
                    amount of hours of that period.
            </ul>
        <li>Now you have to focus in the central panel that is the one where the projects (and subprojects) are
            assigned to the worked periods. Assure yourself that your favorite projects are listed on that panel,
            between the title of the panel and the <b>Manage project list</b> button. If there is not any project, or
            you want to add one or modify the listing, press the <b>Manage project list</b> button.<br/>
            <br/>
        <li>When you press the <b>Manage project list</b> button, a little window will appear, having two panels, the
            first at the left, showing all projects and subprojects you are involved in. That panel allows you to drag
            and drop each project with the mouse to the second panel, at the right. That right panel is the one that
            has the projects that will be seen, when you save changes and close this window, inside <b>Project
            Bookings</b> panel.<br/>
            <br/>
        <li>If you want to take out a project from the right panel of this pop-up window, drag and drop it back
            to the left panel using the mouse.<br/>
            <br/>
        <li>When you have finished doing the changes, press the check button to save and close the pop-up window.<br/>
            <br/>
        <li>The new project listing appears under the legend <b>Project Bookings</b>. Each project is shown as a gray
            rectangle with curved ends.<br/>
            <br/>
        <li>Assign a project of that listing to the worked hours. Do it dragging and dropping a project to
            the inner part of the yellow box in the day schedule.<br/>
            <br/>
        <li>A pop-up window will appear, there you can enter how much hours you have worked in that project and write
            a note.<br/>
            <br/>
        <li>Press <b>Save</b> to set the changes, or <b>Cancel</b> to go back and repeat the last two steps.<br/>
            <br/>
        <li>Once the modifications you have done have been saved, the pop-up window gets closed and the yellow box
            shows graphically inside it, the assigned project whose size is proportional to the specified hours in
            the just closed pop-up window.<br/>
            <br/>
        <li>If the period has more empty time you can drag and drop another project until you fill it completely,
            repeating the last steps.<br/>
            <br/>
        <li>If you want to modify or take out the assignment of a project to a worked period, click on it and a pop-up
            window appears letting you modify the hours and the note or take out the project assignment through the
            <b>Delete</b> button.<br/>
            <br/>
        <li>In the <b>Month Grid</b>, that is the right panel of the module, in the active day row, in the column
            <b>Project Bookings</b> it will be added to the existing hours, the amount of hours you have just assigned
            to projects.<br/>
            <br/>
        <li>You can load a specific day on screen through the date field in the upper bar or clicking inside <b>Month
            Grid</b> panel, at the right of the screen, on the cell of the chosen date.<br/>
            <br/>
    </ol>
    <br/>
    <br/>";
