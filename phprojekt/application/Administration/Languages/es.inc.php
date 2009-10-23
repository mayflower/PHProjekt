<?php
// General Tab
$lang['Company Name'] = "Nombre de la companía";

// Words and phrases
// Visual Module Editor
$lang["Open Dialog"]    = "Abrir Diseñador";
$lang["Designer"]       = "Diseñador";
$lang["Table"]          = "Tabla";
$lang["Field Name"]     = "Nombre del campo";
$lang["Field Type"]     = "Tipo de campo";
$lang["Table Lenght"]   = "Largo del campo";
$lang["Form"]           = "Formulario";
$lang["Label"]          = "Etiqueta";
$lang["Project List"]   = "Lista de Proyectos";
$lang["User List"]      = "Lista de Usuarios";
$lang["Custom Values"]  = "Valores Específicos";
$lang["Select Type"]    = "Tipo de Select";
$lang["Range"]          = "Rango";
$lang["Default Value"]  = "Valor predeterminado";
$lang["List"]           = "Lista";
$lang["List Position"]  = "Posición";
$lang["General"]        = "General";
$lang["Status"]         = "Estado";
$lang["Inactive"]       = "Inactivo";
$lang["Required Field"] = "Campo requerido";

// Search
$lang["View all"] = "Ver todos";
$lang["There are no Results"] = "No se encontraron resultados";

// Error messages from Module Designer
$lang["Module Designer"] = "Diseñador de Módulos";
$lang["The Module must contain at least one field"] = "El módulo debe contener al menos un campo";
$lang["Please enter a name for this module"] = "Por favor ingrese un nombre para este módulo";
$lang["The module name must start with a letter"] = "El nombre del módulo debe comenzar con una letra";
$lang["All the fields must have a table name"] = "Todos los campos deben tener un nombre para la tabla";
$lang["There are two fields with the same Field Name"] = "Hay dos campos con el mismo nombre para la Tabla";
$lang["The length of the varchar fields must be between 1 and 255"] = "El largo de los campos Varchar debe ser entre "
    . "1 y 255";
$lang["The length of the int fields must be between 1 and 11"] = "El largo de los campos Int debe ser entre 1 y 11";
$lang["Invalid form Range for the select field"] = "Rango invalido para el campo Select";
$lang["The module must have a project selector called project_id"] = "El módulo debe tener un selector de proyecto "
    . "llamado project_id";

// User submodule
$lang["Already exists, choose another one please"] = "Ya existente, elija otro por favor";

// Tooltip Help
$lang["Here can be configured general settings of the site that affects all the users."] = "Aquí pueden configurarse "
    . "parámetros generales del sitio que afectan a todos los usuarios.";
$lang["Please choose one of the tabs of above."] = "Por favor, elija una de las solapa de arriba.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Administración"] = "<br />
    Esta es la <b>Ayuda General del módulo Administración</b><br />
    <br />
    Este módulo es sólo accesible por usuario con perfil Admin.<br />
    Aquí pueden configurarse parámetros generales del sitio que afectan a <b>todos los usuarios</b>.<br />
    <br />
    Está dividido en 4 partes:<br />
    <br />
    <ul>
            <li><b>Modulo:</b> este es el Diseñador de Módulos, una interfaz muy fácil de usar, con un manejo visual
            y con métodos <i>arrastrar y soltar</i> (drag and drop), que sirve para crear módulos o modificar los
            existentes.<br />
        <li><b>Solapa:</b> aquí puedes crear solapas adicionales para ser mostradas en los módulos.<br />
        <li><b>Usuario:</b> para agregar, modificar y borrar usuarios del sistema.<br />
        <li><b>Rol:</b> aquí se editan roles; un rol es un set de permisos para los módulos, que se asigna a
            usuarios.<br />
    </ul>
    <br />
    Para ver la ayuda de cada sección, haga clic en la solapa respectiva de esta ventana.<br />
    <br />
    <br />";

$lang["Content Help"]["Modulo"] = "<br />
    <b>Solapa Módulo</b><br />
    <br />
    El Diseñador de Módulos es una interfaz muy fácil de usar, con un manejo visual y con métodos <i>arrastrar y
    soltar</i> (drag and drop), que sirve para crear módulos o modificar los existentes.<br />
    <br />
    Primero presione el botón <b>agregar</b> o haga clic en un módulo existente en la grilla. Luego el formulario
    muestra el nombre y permite elegir si está activo o no (si se mostrará en el sistema).
    <br />
    Presionando el botón <b>Abrir Diseñador</b> una ventana emergente con el diseñador en sí.<br />
    <br />
    <b>La interfaz del Diseñador de Módulos</b><br />
    <br />
    Tiene 3 paneles:<br />
    <br />
    <ul>
        <li><b>El panel de campos izquierdo:</b> aquí están todos los tipos de campos; <i>text</i>, <i>date</i>,
            <i>time</i>, <i>select</i>, <i>checkbox</i>, <i>percentage</i>, <i>textarea</i> and <i>upload</i>.<br />
            Puedes arrastrar y soltar campos al panel derecho, aquel panel derecho es la solapa que está creando o
            modificando como será vista (pero sin los botones <i>Editar</i> y <i>Borrar</i>).<br />
            El botón <b>Editar</b> del lado derecho de los campos permite modificar los parámetros del campo antes de
            arrastrarlo hacia la solapa del módulo (panel derecho), aunque también se puede arrastrar primero y luego
            editarlo (presionando dicho botón en la solapa derecha).<br />
            <br />
        <li><b>El panel derecho de solapas:</b> tiene las solapas principales tal cómo se van a mostrar cuando abra
            el módulo fuera del <b>Diseñador de Módulos</b> (excepto por los botones Editar / Borrar). Se agregan los
            campos arrastrando y soltándolos desde el panel izquierdo, luego presionando el botón <b>Editar</b> el
            panel de edición se abre en la parte izquierda baja de la ventana para que configurar el campo.<br />
            Los campos pueden ser reordenados mediante arrastrar y soltar y borrados de la solapa arrastrándolos de
            vuelta hacia el panel izquierdo o sólo presionando <b>Borrar</b>.<br />
            Hay tantas solapas en este panel como solapas están definidas en el módulo <b>Administración</b>
            submódulo <b>Solapa</b>.<br />
            No necesita usar todas las solapas creadas. La solapa aparecerá en el módulo sólo si hay campos
            dentro de ella.<br />
            <br />
        <li><b>El panel de edición, debajo a la izquierda:</b> aquí, cuando se presiona el botón <b>Editar</b> de un
            campo, un panel aparece para modificar sus valores y parámetros.<br />
            Posee 4 solapas:<br />
            <ul>
                <li><b>Tabla:</b> para editar lo concerniente a la base de datos.<br />
                <li><b>Formulario:</b> para editar los datos mostrados en el formulario.<br />
                <li><b>Lista:</b> para editar si el campo se muestra o no en la lista y su posición dentro de
                    ella.<br />
                <li><b>General:</b> parámetros generales.<br />
            </ul>
    </ul>
    <br />
    <b>Cómo crear un módulo, desde cero</b><br />
    <br />
    <ol>
        <li>Asumiendo que está en la sección <b>Modulo</b> del módulo <b>Administración</b>, presione el botón
            <b>Agregar</b>.<br />
        <li>Un formulario vacío aparece. Escriba el nombre del nuevo módulo en el campo <b>Etiqueta</b>.<br />
        <li>Presione el botón <b>Abrir Diseñador</b>. Una gran ventana emergente aparece, conteniendo el
            diseñador de módulos.<br />
            Verá dos grandes paneles, uno a la izquierda y otro a la derecha, y un espacio vacío abajo a la izquierda
            donde la ventana de edición de campos aparece eventualmente.<br />
            En el panel derecho (el módulo como será visto fuera del diseñador) hay un campo Project. Ese campo debe
            existir para que el módulo funcione, es la relación del ítem con los proyectos, no lo borre.<br />
        <li>Agregue al panel derecho mediante arrastrar y soltar un campo de su elección. Si hay más de una solapa
            en el panel derecho, puede elegir la solapa previo a depositar el campo, para ponerlo allí. En ambos casos,
            luego de soltarlo la ventana de edición aparece para que lo configure.<br />
            Nota: para soltar el campo al
            arrastrarlo, tiene que posicionarlo con el mouse sobre un lugar donde la caja flotante que está
            arrastrando se convierta de rojo rosado a verde; eso significa que en ese lugar puede soltar el campo
            (arriba o abajo de otro campo).<br />
        <li>Configure el campo ocupándose de cada una de las 4 solapas de la ventana de edición como se explica aquí
            arriba.<br />
        <li>Repita los pasos 5 y 6 tantas veces como campos quiera agregar.<br />
        <li>Acomode los campos en el panel derecho en el orden que quiera mediante arrastrar y soltar.<br />
        <li>Presione el botón <b>Cerrar</b> abajo a la izquierda dentro de la pantalla.<br />
        <li>La ventana emergente se ha cerrado. Presione <b>Grabar</b> y el módulo esta terminado. El acto de grabar,
            por ejemplo cuando se <i>crea</i> un módulo, crea la tabla en la base de datos, graba los parámetros y
            crea la estructura de carpetas y archivos.<br />
    </ol>
    <b>Notas:</b><br />
    <br />
    <ul>
        <li>Luego de grabar un módulo nuevo, se necesita refrescar la página en su navegador.<br />
        <li>Al módulo se le agregarán las solapas <b>Accesos</b>, <b>Notificación</b> e <b>Historial</b>.<br />
        <li>Pueden ser arrastrados de vuelta campos del panel derecho hacia el izquierdo. Esto es útil para
            regresarlos más tarde al panel derecho, o para moverlos de esta forma hacia otra solapa del panel
            derecho.<br />
        <li><u>Se recomienda no modificar los módulos originales que vienen con el sistema.
            La mayoría de ellos tienen
            funcionalidad adicional que no fue hecha con el diseñador de módulos y pueden fallar si son modificados
            con él.</u><br />
    </ul>
    <br />
    <br />
    <b>Otras solapas especiales</b><br />
    <br />
    Existen otras solapas de módulos usadas en el sistema, que no están definidas aquí, ni pueden ser modificadas.
    <br />
    <br />
    <ul>
        <li>Módulos generales: solapas <i>Accesos</i>, <i>Notificación</i> e <i>Historial</i>.<br />
            Todos los módulos <i>creados</i> con el <b>Diseñador de módulos</b> tendrán, aparte de las solapas
            diseñadas por el usuario, estas 3 solapas explicadas en la ayuda de la mayoría de los módulos.
            <i>Historial</i> sólo se mostrará en el modo de edición.</br>
        <li>Módulo Proyecto: solapas <i>Módulo</i> y <i>Rol</i>, explicadas en la ayuda de Proyecto.<br />
        <li>Módulo Calendario: solapa <i>Repetición</i> explicada en la ayuda de Calendario.<br />
    </ul>
    <br />
    <br />";

$lang["Content Help"]["Solapa"] = "<br />
    <b>Solapa SOLAPA</b><br />
    <br />
    Esta sección permite modificar las solapas de los módulos.<br />
    <br />
    Su propósito es sólo administrar cuántas solapas definidas por el usuario pueden existir en los módulos, y sus
    nombres. Por ejemplo, si hay 3 solapas: la predeterminada 'Datos Básicos' más dos creadas por usted, en el
    'Diseñador de Módulos' puede definir que solapas usar en un módulo específico: no necesita usar todas las
    solapas creadas.
    La solapa aparecera en el módulo sólo si cuando la está modificando con el <b>Diseñador de Módulos</b>
    usted deposita campos en ella.<br />
    Ejemplo: puede crear una solapa adicional 'Información Geográfica' y crer un módulo con el
    'Diseñador de Módulos'.
    Si sólo arrastra y suelta campos a la solapa 'Datos Básicos' y ninguno a la solapa 'Información Geográfica',
    entonces la segunda solapa no se verá en el módulo.<br />
    <br />
    <b>Otras solapas especiales</b><br />
    <br />
    Existen otras solapas de módulos usadas en el sistema, que no están definidas aquí, ni pueden ser modificadas.
    Esto depende del módulo con el que esté trabajando:<br />
    <ul>
        <li>Módulos generales: solapas <i>Accesos</i>, <i>Notificación</i> y <i>Historial</i>.<br />
            Todos los módulos <i>creados</i> con el <b>Diseñador de módulos</b> tendrán, aparte de las solapas
            diseñadas por el usuario, estas 3 solapas explicadas en la ayuda de la mayoría de los módulos.
            <i>Historial</i> sólo se mostrará en el modo de edición.</br>
        <li>Módulo Proyecto: solapas <i>Módulo</i> y <i>Rol</i>, explicadas en la ayuda de Proyecto.<br />
        <li>Módulo Calendario: solapa <i>Repetición</i> explicada en la ayuda de Calendario.<br />
    </ul>
    <br />
    <br />";

$lang["Content Help"]["Usuario"] = "<br />
    <b>Solapa Usuario</b><br />
    <br />
    Esta sección está diseñada para administrar todos los usuarios del sistema.<br />
    <br />
    Aquí se pueden agregar, modificar y borrar usuarios del sitio.<br />
    <br />
    <br />";

$lang["Content Help"]["Rol"] = "<br />
    <b>Solapa Rol</b><br />
    <br />
    Esta sección permite administrar los Roles.<br />
    <br />
    Un Rol es un set específico de permisos para cada módulo. Ese Rol es luego asignado a los usuarios de los
    Proyectos que desee, de modo que esos usuarios tendrán esos permisos. Cuando crea o edita un Proyecto,
    puere darle a los usuarios que desee ese Rol en la solapa Rol.<br />
    El permiso final que un usuario tenga para trabajar con un ítem está definido por la combinación de lo
    especificado tanto en la solapa Rol como en la solapa Accesos de un proyecto determinado.<br />
    <br />
    Predeterminadamente, ningún Rol se asigna a nadie, y el permiso para acceder a un nuevo proyecto será acceso
    <b>Admin</b> para el creador y nada para el resto.<br />
    <br />
    <b>Ejemplo</b>
    <br />
    Puede crear un Rol llamado 'Puede leer TODOs y NOTAs'.<br />
    Y especificar en ese Rol, acceso <i>Lectura</i> para los módulos <b>Todo</b> y <b>Nota</b>.<br />
    Luego crea un Proyecto y le asigna al usuario 'juan' el Rol 'Puede leer TODOs y NOTAs', entonces cuando él entra
    al proyecto, lo único que podrá hacer además de leer la información principal del proyecto en sí, es leer
    (pero no modificar) ítems de los módulos <b>Todo</b> y <b>Nota</b>.<br />
    <br />
    <b>Nota:</b> en el listado de módulos para asignar permisos a un Rol, hay uno llamado <b>Project</b>, ese
        representa los Subproyectos de Proyectos.<br />
    <br />
    <br />";
