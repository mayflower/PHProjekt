<?php
// Words and phrases
$lang["View"] = "View";
$lang["List"] = "List";
$lang["Day"] = "Day";
$lang["Week"] = "Week";
$lang["Month"] = "Month";
$lang["Self"] = "Self";
$lang["Selection"] = "Selection";
$lang["Once"] = "Once";
$lang["None"] = "None";
$lang["Daily"] = "Daily";
$lang["Weekly"] = "Weekly";
$lang["Monthly"] = "Monthly";
$lang["Yearly"] = "Yearly";
$lang["Monday"] = "Monday";
$lang["Tuesday"] = "Tuesday";
$lang["Wednesday"] = "Wednesday";
$lang["Thursday"] = "Thursday";
$lang["Friday"] = "Friday";
$lang["Saturday"] = "Saturday";
$lang["Sunday"] = "Sunday";
$lang["Further events"] = "Further events";
$lang["Today"] = "Today";
$lang["Done"] = "Done";
$lang["Select users for the group view"] = "Select users for the group view";
$lang["You have to select at least one user!"] = "You have to select at least one user!";
$lang["User selection"] = "User selection";
$lang["place"] = "Place";
$lang["Calendar week"] = "Calendar week";

$lang["January"] = "January";
$lang["February"] = "February";
$lang["March"] = "March";
$lang["April"] = "April";
$lang["May"] = "May";
$lang["June"] = "June";
$lang["July"] = "July";
$lang["August"] = "August";
$lang["September"] = "September";
$lang["October"] = "October";
$lang["November"] = "November";
$lang["December"] = "December";

$lang["Participants"] = "Participants";
$lang["Recurrence"] = "Recurrence";
$lang["Repeats"] = "Repeats";
$lang["Interval"] = "Interval";
$lang["Until"] = "Until";
$lang["Weekdays"] = "Weekdays";

$lang["Mo"] = "Mo";
$lang["Tu"] = "Tu";
$lang["We"] = "We";
$lang["Th"] = "Th";
$lang["Fr"] = "Fr";
$lang["Sa"] = "Sa";
$lang["Su"] = "Su";

// Tooltip Help
$lang["Interval Help"] = "The interval for the option selected in Repeats. <br>E.g.: Repeats Weekly - Interval"
    . " 2, that will create one event every 2 weeks.";
$lang["Until Help"] = "The day the recurrence will stop happening. <br>The last event's day could not match this day.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Calendar"] = "<br/>
    This is the <b>General Help of Calendar module</b><br/>
    <br/>
    The Calendar is a very complete module for managing events. You can create an event, set descriptive info to it,
    assign it date and time, specify participants, create a particular recurrence for the event, and the rest of
    general properties of the modules, like Access rights, mail Notification and History registry.<br/>
    <br/>
    The <b>screen</b> is divided into 5 sections:<br/>
    <br/>
    <ol>
        <li><b>Top right button bar:</b> here, depending on the items being shown and the user rights, will be shown
            up to 3 buttons.<br/>
            <ul>
                <li><b>Add:</b> you press it and an empty form is open to create a new event.<br/>
                <li><b>Save:</b> the grid can be edited just clicking on the fields you want to change. Then you press
                    this button to save the changes made.<br/>
                <li><b>Export:</b> exports to a CSV file the results and offers you to download it.<br/>
            </ul>
        <li><b>View tabs:</b> there are four types of listings that are activated through <b>List</b>,
            <b>Day</b>, <b>Week</b> and <b>Month</b> tabs.<br/>
            <ul>
                <li><b>List:</b> a grid with all the events for the current user.<br/>
                <li><b>Day:</b> a schedule from 8:00 to 20:00 where all the events of a specific day are shown.<br/>
                    <u>It has two subtypes</u> that are chosen through the <b>Self</b> and <b>Selection</b> tab
                    tabs appear at the right of the same bar, when <b>Day</b> mode is active:
                    <ul>
                        <li><b>Self:</b> the events of the chosen day, for the current user are shown.
                        <li><b>Selection:</b> when this tab is pressed, a pop-up window appears letting the user
                            select a small group of people so that the list of events will have as many columns as
                            users selected; there will be seen a group schedule for the active day.
                    </ul>
                <li><b>Week:</b> a weekly schedule, like the common day schedule, but for the seven days of the week
                    simultaneously.
                <li><b>Month:</b> a monthly schedule, shows in a clear calendar table all the days of the month, and
                    the necessary days of previous and next months in order to complety all the weeks shown.
            </ul>
            When the Day, Week or Month view are active, it appears a schedule bar over the listings.
            It has the <i>previous</i>, <i>today</i> and <i>next</i> links to change the day / week / month in
            sequence. Also it shows the date of the selected period.<br/>
            <br/>
        <li><b>Grid / List:</b> here are shown the list of items or a determined day's schedule, depending on the
            selected view.<br/>
        <br/>
        <li><b>Form:</b> when an item is going to be created or it is clicked one in the grid/schedule above, a form is
            shown here to enter or change its data.<br/>
        <br/>
        <li><b>Bottom button bar:</b> here, depending on the user rights, when an item is being created or modified
            there are shown the <b>Save</b> and <b>Delete</b> buttons.<br/>
    </ol>
    <br/>
    <br/>";

$lang["Content Help"]["Basic data"] = "DEFAULT";
$lang["Content Help"]["Recurrence"] = "<br/>
    <b>Recurrence Tab</b><br/>
    <br/>
    This tab permits assigning a recurrence to the event so that it is set to happen as many times as defined, with the
    frequency days of the week specified.<br/>
    <br/>
    <b>Fields</b><br/>
    <br/>
    <b>Repeats</b> select box: here you can choose how often do you want the event to happen. <i>Once</i> is the
    default recurrence and means that won't be any recurrence. You can choose <i>Daily</i>, <i>Weekly</i>,
    <i>Monthly</i> or <i>Yearly</i> and the event will get repeated with that frequency until the day specified in the
    <u>Until</u> date field.<br/>
    <br/>
    <b>Interval</b> field: you set the interval you want for the recurrence. E.G.: if you choose <i>Monthly</i> in
    <u>Repeats</u> select box and <i>2</i> in <u>Interval</u>, the event will happen every 2 months.<br/>
    <br/>
    <b>Until</b> date field: if you choose an option different from <i>Once</i> in <u>Repeats</u> select box, here you
    must select when do you want this event to stop recurring.<br/>
    <br/>
    <b>Weekdays</b> select list: you can choose what days of the week do you want it to happen.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Access"] = "DEFAULT";
$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";
