<?php

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Minutes"] = "<br/>
    Dies ist die <b>Allgemeine Hilfe zum Protokoll-Modul</b><br/>
    <br/>
    Dieses Modul ist für die Erfassung von Meeting-Protokollen gedacht.<br/>
    ";
$lang["Content Help"]["Basic data"] = "DEFAULT";
$lang["Content Help"]["People"] = "<br/>
    Dieser Karteireiter dient zur Festlegung von Personen, die zu dem Meeting eingeladen sind bzw. tatsächlich daran
    teilnehmen,
    oder die entschuldigt sind. Darüber hinaus können Personen angegeben werden, die das Protokoll erhalten sollen.
    ";
$lang["Content Help"]["Items"] = "<br/>
    Hilfetext für Protokolleinträge
    ";
$lang["Content Help"]["E-Mail"] = "<br/>
    Hilfetext für den E-Mail Versand von Protokollen
    ";

$lang["Content Help"]["Access"] = "DEFAULT";
$lang["Content Help"]["Notification"] = "DEFAULT";
$lang["Content Help"]["History"] = "DEFAULT";

// General translation strings:

// [Form.js] - Form labels
$lang['Title'] = 'Titel';
$lang['Comment'] = 'Kommentar';
$lang['Who'] = 'Wer';
$lang['Type'] = 'Typ';
$lang['Date'] = 'Datum';
$lang['Sort'] = 'Einordnen nach';
$lang['Save'] = 'Speichern';
$lang['Delete'] = 'Löschen';
$lang['Clear'] = 'Leeren';
$lang['Topic'] = 'Thema';

// [Form.js] - Validation messages
$lang['Title must not be empty'] = 'Der Titel darf nicht leer sein';
$lang['Please choose a type for this item'] = 'Bitte wählen Sie den Typ dieses Eintrags';
$lang['Please choose a user name'] = 'Bitte wählen Sie einen gültigen Benutzernamen aus';
$lang['No date given or date format is not valid (must be YYYY-MM-DD)'] = 'Das Datumsformat ist ungültig
    (Schema: YYYY-MM-DD)';

// [Form.js] - Tab name
$lang['Items'] = 'Einträge';
$lang['People'] = 'Personen';

// [Form.js] - Selectbox values
$lang['TOPIC'] = 'TOP';
$lang['STATEMENT'] = 'Stellungnahme';
$lang['TODO'] = 'Aufgabe';
$lang['DECISION'] = 'Entscheidung';
$lang['DATE'] = 'Datum';

// Grid headers - field names from DatabaseDesigner
$lang['Date of Meeting'] = 'Meeting-Datum';
$lang['Description'] = 'Beschreibung';
$lang['Start Time'] = 'Startzeit';
$lang['Place'] = 'Ort';
$lang['Status'] = 'Status';

// Grid values - from DatabaseDesigner
$lang['PLANNED'] = 'geplant';
$lang['CREATED'] = 'noch leer';
$lang['PREVIEW'] = 'ausgefüllt';
$lang['FINAL']   = 'fixiert'; 

// Field labels from DatabaseDesigner
$lang['Moderator'] = 'Moderator';
$lang['End time'] = 'Endzeit';
$lang['Tag'] = 'Tag';
$lang['Invited'] = 'Eingeladen';
$lang['Attending'] = 'Teilnehmer';
$lang['Excused'] = 'Entschuldigt';
$lang['recipients'] = 'Empfänger';

// Mail tab
$lang['Recipients'] = 'Empfänger';
$lang['Additional Recipients'] = 'Weitere Empfänger';
//$lang['Comment'] = 'Zusatztext';
$lang['Options'] = 'Optionen';
$lang['Include PDF attachment'] = 'PDF als Anhang hinzufügen';
$lang['Email addresses of unlisted recipients, comma-separated.'] = 'E-Mail Adressen für hier nicht gelistete
    Empfänger, kommasepariert';
$lang['Send mail'] = 'E-Mail absenden';
$lang['Preview'] = 'Vorschau';
$lang['Mail'] = 'E-Mail';

// Mail functions
$lang['Meeting minutes for "%s", %s'] = 'Meeting-Protokoll "%s", %s';
$lang['The mail could not be sent.'] = 'Die E-Mail konnte nicht gesendet werden.';
$lang['The mail was sent successfully.'] = 'Die E-Mail wurde erfolgreich versandt.';
$lang['Invalid email address detected: %s'] = 'Ungültige E-Mail Adresse: %s';

// PDF formatting strings
$lang["%1\$s\n%2\$s"] = "%1\$s\n%2\$s";
$lang["%1\$s\n%2\$s\nWHO: %4\$s\nDATE: %3\$s"] = "%1\$s\n%2\$s\nZuständig: %4\$s\nDatum: %3\$s";
$lang["%1\$s\n%2\$s\nDATE: %3\$s"] = "%1\$s\n%2\$s\nDatum: %3\$s";
$lang["Undefined topicType"] = "Undefinierter Typ";
$lang['No.'] = 'Nr.';
$lang['TYPE'] = 'Typ';
$lang['ITEM'] = 'Eintrag';

//$lang[''] = '';
