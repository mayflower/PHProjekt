<?php
// General translation strings:
// [Form.js] - Form labels
$lang["Title"] = "Título";
$lang["Comment"] = "Comentario";
$lang["Who"] = "Quién";
$lang["Type"] = "Tipo";
$lang["Date"] = "Fecha";
$lang["Sort after"] = "Ubicar luego de";
$lang["New"] = "Nuevo";

// [Form.js] - Tab name
$lang["Items"] = "Items";

// [Form.js] - Selectbox values
$lang["Topic"] = "Tópico";
$lang["Statement"] = "Declaración";
$lang["Todo"] = "Pendiente";
$lang["Decision"] = "Decision";
$lang["Date"] = "Fecha";

// Grid headers - field names from DatabaseDesigner
$lang["Date of Meeting"] = "Fecha de Reunión";
$lang["Description"] = "Descripción";
$lang["Start Time"] = "Hora de inicio";
$lang["Place"] = "Lugar";
$lang["Status"] = "Estado";

// Grid values - from DatabaseDesigner
$lang["Planned"] = "Planificada";
$lang["Created"] = "Vacía";
$lang["Filled"] = "Llenada";
$lang["Final"]   = "Final";

// Field labels from DatabaseDesigner
$lang["Moderator"] = "Moderador";
$lang["End Time"] = "Fecha de fin";
$lang["Tag"] = "Tag";
$lang["Invited"] = "Invitados";
$lang["Attending"] = "Asistentes";
$lang["Excused"] = "Excusados";
$lang["Recipients"] = "Destinatarios";

// Mail tab
$lang["Additional Recipients"] = "Destinatarios adicionales";
$lang["Options"] = "Opciones";
$lang["Include PDF attachment"] = "Incluir archivo adjunto PDF";
$lang["Email addresses of unlisted recipients, comma-separated."] = "Direcciones de correo electrónico de participantes
    no listados, separados por comas.";
$lang["Send mail"] = "Enviar correo";
$lang["Preview"] = "Vista previa";
$lang["Correo"] = "Correo";

// Mail functions
$lang["Meeting minutes for"] = "Minuta de reunión para";
$lang["The mail could not be sent."] = "El correo no pudo ser enviado.";
$lang["The mail was sent successfully."] = "El correo fue enviado exitosamente.";
$lang["Invalid email address detected:"] = "Dirección de correo inválida:";
$lang["No recipient addresses have been specified."] = "No se han especificado direcciones de destinatarios.";

// PDF formatting strings
$lang["Undefined topicType"] = "Tipo no definido";
$lang["No."] = "No.";
$lang["Item"] = "Item";

// Confirmation dialogs
$lang["Confirm"] = "Confirmación";
$lang["Are you sure?"] = "Está seguro/a?";
$lang["OK"] = "Aceptar";
$lang["Cancel"] = "Cancelar";
$lang["Unfinalize Minutes"] = "Des-finalizar Minutas";
$lang["Are you sure this Minutes entry should no longer be finalized?"] = "Está seguro/a que esta Minuta no estará
    más finalizada?";
$lang["After proceeding, changes to the data will be possible again."] = "Luego de proceder, se podrán efectuar cambios
    a los datos nuevamente.";
$lang["Finalize Minutes"] = "Finalizar Minutas";
$lang["Are you sure this Minutes entry should be finalized?"] = "Está seguro/a que esta Minuta debe ser finalizada?";
$lang["Write access will be prohibited!"] =  "El acceso de escritura será prohibido!";
$lang["Minutes are finalized"] = "Minuta finalizada";
$lang["This Minutes entry is finalized."] = "Esta minuta está finalizada.";
$lang["Editing data is no longer possible."] = "No es posible editar datos.";
$lang["Your changes have not been saved."] = "Sus cambios no serán grabados.";

// Messages
$lang["The currently logged-in user is not owner of the given minutes entry."] = "El usuario en curso no es dueño de "
    . "esta minuta.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Minuta"] = "<br />
    Esta es la <b>Ayuda General del módulo Minuta</b><br />
    <br />
    Este módulo está destinado a la transcripción de minutas de reuniones.<br/>
    ";
$lang["Content Help"]["Datos Básicos"] = "DEFAULT";
$lang["Content Help"]["Personas"] = "<br />
    <b>Solapa Personas</b><br />
    <br />
    Definir personas invitadas a la reunión, asistentes reales y gente excusada.<br/>
    También permite definir destinatarios para la Notificación de correo electrónico.<br/>
    <br/>
    <br/>";
$lang["Content Help"]["Items"] = "<br />
    <b>Solapa Items</b><br />
    <br />
    Esta solapa permite reunir, escribir y organizar todo el contenido expuesto en la reunión.<br/>
    <br/>
    Tiene una grilla con un listado a la izquierda y un formulario del lado derecho.<br/>
    <br/>
    Cada ítem agregado al listado tiene los siguientes campos en el formulario, para que los edite:<br/>
    <br/>
    <ul>
        <li><b>Título:</b> un título descriptivo para el ítem que se agrega, como 'Nuevo diseño de teléfono
        celular'.
        <li><b>Tipo:</b> el tipo de ítem que se agrega, puede ser uno de los siguientes.
            <ul>
                <li>Tópico
                <li>Declaración
                <li>Pendiente
                <li>Decisión
                <li>Fecha
            </ul>
        <li><b>Comentario:</b> El contenido descriptivo del ítem.
        <li><b>Quién (sólo visible cuando el tipo 'Pendiente' está elegido):</b> quién es responsable de hacer la
            actividad.
        <li><b>Fecha (sólo visible cuando el tipo 'Pendiente' o 'Fecha' está elegido):</b> la fecha para la
        actividad o el ítem que se ha discutido.
        <li><b>Ubicar luego de:</b> en qué lugar del listado se situará este ítem.
    </ul>
    <br/>
    La grilla ordena y muestra automáticamente todos los ítems agregados.<br/>
    <br />
    <br />";
$lang["Content Help"]["Correo"] = "<br />
    <b>Solapa Correo</b><br />
    <br />
    Desde aquí se puede enviar un correo electrónico conteniendo toda la información del registro de Minuta
    seleccionado, con un archivo adjunto PDF opcional.<br/>
    <br/>
    Destinatarios pueden ser seleccionados en el campo <b>Destinatarios</b> y también pueden ser escritas
    direcciones de correo electrónico en el campo <b>Destinatarios adicionales</b> que se encuentra debajo de él.
    <br/>
    Tilde la caja <b>Incluir archivo adjunto PDF</b> para mandar también la misma información en un archivo PDF.<br/>
    <br/>
    Luego el botón <b>Enviar correo</b> lo envía, o el botón <b>Vista previa</b> le muestra el adjunto de modo que
    pueda verificar el contenido antes de enviar el correo electrónico.<br/>
    <br/>
    <br/>";
$lang["Content Help"]["Accesos"] = "DEFAULT";
$lang["Content Help"]["Notificación"] = "DEFAULT";
$lang["Content Help"]["Historial"] = "DEFAULT";
