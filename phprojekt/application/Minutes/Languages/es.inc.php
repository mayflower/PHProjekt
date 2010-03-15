<?php
// System
$lang["Minute"] = "Minuta";

// Fields
 // Basic Data
$lang["Title"] = "Título";
$lang["Start"] = "Comienzo";
$lang["End"] = "Fin";
$lang["Project"] = "Projecto";
$lang["Description"] = "Descripción";
$lang["Place"] = "Lugar";
$lang["Moderator"] = "Moderador";
$lang["Invited"] = "Invitados";
$lang["Attending"] = "Asistentes";
$lang["Excused"] = "Excusados";
$lang["Status"] = "Estado";
$lang["Planned"] = "Planificada";
$lang["Empty"] = "Vacía";
$lang["Filled"] = "Llenada";
$lang["Final"]   = "Final";

 // Mail tab
$lang["Mail"] = "Correo";
$lang["Recipients"] = "Destinatarios";
$lang["Additional Recipients"] = "Destinatarios adicionales";
$lang["Options"] = "Opciones";
$lang["Include PDF attachment"] = "Incluir archivo adjunto PDF";
$lang["Send mail"] = "Enviar correo";
$lang["Preview"] = "Vista previa";

// Messages
  // System
$lang["The currently logged-in user is not owner of the given minutes entry"] = "El usuario en curso no es dueño de "
    . "esta minuta";
  // View
$lang["Are you sure this Minutes entry should be finalized?"] = "Está seguro/a que esta Minuta debe ser finalizada?";
$lang["After proceeding, changes to the data will be possible again."] = "Luego de proceder, se podrán efectuar cambios
    a los datos nuevamente.";
$lang["Are you sure this Minutes entry should no longer be finalized?"] = "Está seguro/a que esta Minuta no estará "
    . "más finalizada?";
$lang["Write access will be prohibited!"] =  "El acceso de escritura será prohibido!";
$lang["Minutes are finalized"] = "Minuta finalizada";
$lang["This Minutes entry is finalized."] = "Esta minuta está finalizada.";
$lang["Editing data is no longer possible."] = "No es posible editar datos.";
$lang["Your changes have not been saved."] = "Sus cambios no serán grabados.";

// Mail
  // Messages
$lang["The mail was sent successfully"] = "El correo fue enviado exitosamente";
$lang["The mail could not be sent"] = "El correo no pudo ser enviado";
$lang["No recipient addresses have been specified"] = "No se han especificado direcciones de destinatarios";
$lang["Invalid email address detected:"] = "Dirección de correo inválida:";
  // Content
$lang["Meeting minutes for"] = "Minuta de reunión para";

// PDF
  // Content
$lang["No."] = "No.";
$lang["Type"] = "Tipo";
$lang["Item"] = "Item";

// View
$lang["Unfinalize Minutes"] = "Des-finalizar Minutas";
$lang["Finalize Minutes"] = "Finalizar Minutas";
$lang["Confirm"] = "Confirmación";
$lang["Are you sure?"] = "Está seguro/a?";

// Tooltip Help
  // Mail tab
$lang["Email addresses of unlisted recipients, comma-separated."] = "Direcciones de correo electrónico de participantes
    no listados, separados por comas.";

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
    Fields:<br />
    <br />
    <ul>
        <li>
            <b>Title</b>: title of the Minute.
        </li>
        <li>
            <b>Start</b>: date/time when the meeting starts/started.
        </li>
        <li>
            <b>End</b>: time when the meeting ends/ended.
        </li>
        <li>
            <b>Project</b>: here goes the parent Project; every Project is child of another, at least child of the
            Root one whose name is the company name.
        </li>
        <li>
            <b>Description</b>: a brief description of the meeting (the contents and subjects themselves will be
            filled in Items tab).
        </li>
        <li>
            <b>Place</b>: where the meeting takes place.
        </li>
        <li>
            <b>Moderator</b>: name/s of the moderator/s of the meeting.
        </li>
        <li>
            <b>Status</b>: the status of the item, this is supposed to change over time once the meeting takes place
            and minute's content is filled.<br />
            The options are self-descriptive:<br />
            1 - Planned: the first state.<br />
            2 - Empty.<br />
            3 - Filled: this status is set automatically when some content is filled inside Items tab.<br />
            4 - Final: this status makes the item to be read-only, because it is supposed to be finalized.<br />
            <br />
            After the status is saved as Final, no content can be modified unless you change it back to Filled state.
        </li>
        <li>
            <b>Tag</b>
        </li>
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
        <li>
            <b>Invited</b>: people who is invited to the meeting.
        </li>
        <li>
            <b>Attending</b>: who are going to attend, or have attended the meeting.
        </li>
        <li>
            <b>Excused</b>: people who is not going to, or did not attend the meeting.
        </li>
    </ul>
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
