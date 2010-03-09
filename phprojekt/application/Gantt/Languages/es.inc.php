<?php
// System
$lang["Gantt"] = "Gantt";

// Fields
$lang["Projects"] = "Projectos";
$lang["Project"] = "Projecto";

// Messages
  // System
$lang["No project info was received"] = "No se han recibido datos de proyectos";
$lang["Incomplete data received"] = "Se recibieron datos incompletos";
$lang["Id not found #"] = "Id no encontrado #";
$lang["Project id #"] = "Projecto id #";
$lang["Start date invalid"] = "Fecha de inicio inválida";
$lang["End date invalid"] = "Fecha final inválida";
$lang["Start date can not be after End date"] = "La fecha de inicio no puede ser posterior a la de fin";
  // View
$lang["Attention: parent project"] = "Atención: El proyecto padre";
$lang["Attention: sub-project"] = "Atención: El sub-proyecto";
$lang["starts after sub-project"] = "inicia después del sub-proyecto";
$lang["ends before sub-project"] = "termina antes del sub-proyecto";
$lang["ends after parent project"] = "termina después del proyecto padre";
$lang["starts before parent project"] = "comienza antes del proyecto padre";
$lang["Click \"OK\" to adjust parent project to new start date"] = "Haga click en \"OK\" para ajustar el proyecto
    padre a una nueva fecha de inicio";
$lang["Click \"OK\" to adjust parent project to new end date"] = "Haga click en \"OK\" para ajustar el proyecto
    padre a una nueva fecha de fin";
$lang["Click \"OK\" to adjust sub-project to new end date"] = "Haga click en \"OK\" para ajustar el sub-proyecto a "
    . "una nueva fecha de fin";
$lang["Click \"OK\" to adjust sub-project to new start date"] = "Haga click en \"OK\" para ajustar el sub-proyecto a "
    . "una nueva fecha de inicio";
$lang["Click \"Reset\" to reset current project"] = "Haga click en \"Reset\" para reajustar el proyecto actual";
$lang["Click \"x\" or \"ESC\" to do nothing"] = "Haga click \"x\" o precione \"ESC\" para no hacer nada";
$lang["There are no valid projects"] = "No hay proyectos válidos";

// View
$lang["Selected Project Timeline"] = "Fechas del proyecto seleccionado";
$lang["Warning"] = "Advertencia";

// Tooltip Help
$lang["Click on a Project timeline and see and/or change here the Start and End dates."] = "Haga clic en la línea de "
    . "tiempo de un proyecto para ver y/o modificar aquí las fechas de comienzo y fin.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Gantt"] = "<br />
    Esta es la <b>Ayuda General del módulo Gantt</b><br />
    <br />
    Este módulo es un diagrama Gantt dinámico, editable vía mouse, sincronizado automáticamente con los datos
    cargados en la base de datos.<br />
    <br />
    El módulo lee los Proyectos y Subproyectos y elabora este gráfico que tiene una barra horizontal por proyecto
    (línea de tiempo) la cual puede ser desplazada con el mouse sobre el eje temporal, o sólo aumentando o
    disminuyendo las fechas de comienzo y fin.<br />
    <br />
    Arriba del gráfico hay un panel 'Fechas del proyecto seleccionado', cuando hace click en una línea de tiempo de
    un proyecto, los campos de este panel se cargan con las fechas de inicio y fin para verlas y/o modificarlas
    escribiendo otra fecha o gráficamente eligiendo un día en el calendario que aparece.<br />
    <br />
    <br />";
