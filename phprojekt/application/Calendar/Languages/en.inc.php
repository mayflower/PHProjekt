<?php
// Words and phrases
$lang["View"] = "View";
$lang["Day"] = "Day";
$lang["List"] = "List";
$lang["Once"] = "Once";
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
$lang["Other events"] = "Other events";
$lang["Today"] = "Today";

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

$lang["Recurrence"] = "Recurrence";
$lang["Repeats"] = "Repeats";
$lang["Interval"] = "Interval";
$lang["Until"] = "Until";
$lang["Weekdays"] = "Weekdays";

// Help
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
        <li><b>List / Day button bar:</b> there are two types of listings that are activated through <b>List</b> and
            <b>Day</b> buttons.<br/>
            <ul>
                <li><b>List:</b> a grid with all the events for the current user.<br/>
                <li><b>Day:</b> a schedule from 8:00 to 20:00 where all the events of a specific day are shown.<br/>
            </ul>
            When the Day view is active, another things appear on this button bar. They are a <i>date field</i> to
            select the schedule of a specific day and <i>previous</i>, <i>today</i> and <i>next</i> buttons to change
            the day in sequence.<br/>
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
