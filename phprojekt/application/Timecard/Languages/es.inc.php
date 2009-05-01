<?php
// Words and phrases
$lang["Working Times"] = "Tiempo de Trabajo";
$lang["Start working time"] = "Empezar tiempo de trabajo";
$lang["Stop working time"] = "Terminar tiempo de trabajo";
$lang["Hours"] = "Horas";
$lang["The end time must be after the start time"] = "El tiempo final debe ser mayor al tiempo inicial";
$lang["Change date"] = "Cambiar día";
$lang["Project Bookings"] = "Horas de Proyectos";
$lang["Add working time and drag projects into the bar"] = "Agregar tiempo de trabajo "
    . "y luego arrastrar los proyectos en la barra";
$lang["Manage project list"] = "Admin. lista de proyectos";
$lang["Amount [hhmm]"] = "Cantidad [hhmm]";
$lang["Total"] = "Total";
$lang["The amount is invalid"] = "La cantidad es inválida (De 30 a 1300)";
$lang["Start time has to be between 8:00 and 21:00"] = "La hora de inicio tiene que ser entre 8:00 y 21:00";
$lang["Time period"] = "Período de tiempo";
$lang["Can not save it because it overlaps existing one"] = "No puede grabarse porque se superpone con un período "
    . "existente";
$lang["Can not Start Working Time because this moment is occupied by an existing period."] = "No se puede Empezar "
    . "Tiempo de Trabajo porque este momento está ocupado por un período existente.";
$lang["Can not End Working Time because this moment is occupied by an existing period."] = "No se puede Terminar "
    . "Tiempo de Trabajo porque este momento está ocupado por un período existente.";

// Tooltip Help
$lang["Start Stop Buttons Help"] = "Estos botones Comienzan y Finalizan las horas de trabajo automáticamente.";
$lang["Working Times Help"] = "Aquí puede agregar todas las horas trabajadas del día elegido.";
$lang["Hours Help"] = "El formato de las horas puede ser con o sin símbolo separador:  08:00, 0800, 800, etc.";
$lang["Project Times Help"] = "Dentro de este panel puede Arrastrar y Soltar proyectos dentro de las horas trabajadas.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Asistencia"] = "<br/>
    Esta es la <b>Ayuda General del módulo Asistencia</b><br/>
    <br/>
    Asistencia es un módulo para cargar y asignar horas de trabajo a los Proyectos. Tiene una interfaz visual muy fácil
    de usar.<br/>
    <br/>
    La pantalla se divide en 4 secciones:<br/>
    <br/>
    <ul>
        <li><b>Botoneras superiores</b><br/>
        <li><b>Tiempo de Trabajo</b><br/>
        <li><b>Horas de Proyectos</b><br/>
        <li><b>Grilla Mensual</b><br/>
    </ul><br/>
    Para ayuda sobre cada sección, mire la solapa respectiva en esta ventana.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Botoneras"] = "<br/>
    <b>Botoneras superiores</b><br/>
    <br/>
    Hay una barra <b>superior</b> y otra <b>debajo</b> de ella.<br/>
    <br/>
    <ol>
        <li>La botonera superior tiene dos botones de <b>exportación</b> a la derecha:
            <ul>
                <li><b>Tiempo de Trabajo:</b> exporta en formato CSV los períodos de trabajo para el mes elegido. Qué
                    días el usuario trabajó y en qué momento del día.<br/>
                <li><b>Horas de proyectos:</b> exporta en formato CSV las horas trabajadas asignadas a proyectos y
                    subproyectos, para cada día del mes elegido.<br/>
            </ul>
        <li>La barra que está <b>debajo</b> tiene 2 botones y un campo de fecha:
            <ul>
                <li>Botones <b>Empezar tiempo de trabajo</b> y <b>Terminar tiempo de trabajo</b>: se usan para que el
                    usuario registre automáticamente el tiempo de trabajo.<br/>
                    Ejemplo: un usuario ingresa al sistema a las
                    9:00 y aprieta el botón <b>Empezar tiempo de trabajo</b>. A las 13:30 él / ella sale a almorzar,
                    presiona el botón <b>Terminar tiempo de trabajo</b>. El sistema graba ese período y lo muestra
                    en 3 lugares:<br/>
                    <ol>
                        <li>Debajo del recuadro en el mismo panel.<br/>
                        <li>Dentro del panel <b>Horas de Proyectos</b> en la parte derecha como un recuadro amarillo
                            que abarca ese período desde las 9:00 hasta las 13:30 hs dentro del listado de horas.<br/>
                        <li>En el panel <b>Grilla Mensual</b> la cantidad de horas agregada se sumará a las existentes
                            para ese día en la fila correspondiente de la columna <i>Tiempo de Trabajo</i>.<br/>
                    </ol>
                <li><b>Campo de fecha:</b> aquí puede elegir con qué día trabajar en el módulo.<br/>
            </ul>
    </ol>
    <br/>
    <br/>";

$lang["Content Help"]["Tiempo de Trabajo"] = "<br/>
    <b>Tiempo de Trabajo</b><br/>
    <br/>
    Este es el panel donde las horas de tiempo trabajado son cargadas (y eventualmente borradas).<br/>
    <br/>
    El período se llena dentro de los campos de texto <b>Comienzo</b> y <b>Fin</b>.<br/>
    El formato para escribir las horas es <b>HH?MM</b>, en el que HH es la hora, el signo de interrogación representa
    los dos puntos (:) u otro signo a elección; aunque no es obligatorio, y MM los minutos. Ejemplos: 08:00, 8:00,
    0800, 800, 08.00 o 8.00<br/>
    <br/>
    Luego el <b>botón con la tilde</b> lo graba y el período aparece en 3 lugares dentro de la pantalla:<br/>
    <ol>
        <li>Debajo del recuadro en el mismo panel.<br/>
        <li>Dentro del panel <b>Horas de Proyectos</b> en la parte derecha como un recuadro amarillo que abarca ese período
            dentro del listado de horas.<br/>
        <li>En el panel <b>Grilla Mensual</b> la cantidad de horas agregada se sumará a las existentes para ese día
            en la fila correspondiente de la columna <i>Tiempo de Trabajo</i>.<br/>
    </ol>
    <br/>
    <br/>";

$lang["Content Help"]["Horas de Proyectos"] = "<br/>
    <b>Horas de Proyectos</b><br/>
    <br/>
    Este es el panel más interesante de la pantalla, diseñado para asignar tareas a las horas de trabajo ya
    cargadas. La mayor parte de las cosas se muestran y se manejan gráficamente.<br/>
    <br/>
    <b>Lista de proyectos:</b><br/>
    <br/>
    Hay un botón <b>Admin. lista de proyectos</b> que permite seleccionar proyectos
    <i>favoritos</i> que son los con los cuales se va a trabajar, es decir, se van a asignar a horas de trabajo
    cargadas.<br/>
    Esos proyectos aparecerán sobre dicho botón y son un sólo grupo para todo el módulo, o sea que si cambia de fecha
    en la vista del módulo, se seguirá viendo el mismo grupo de proyectos.<br/>
    <br/>
    <b>Agenda horaria:</b><br/>
    <br/>
    Va desde las 8:00 hasta las 20:00 horas. Aquí figuran todas las horas cargadas en el panel
    <b>Tiempo de Trabajo</b>.<br/>
    <br/>
    Cada uno de esos períodos aparece como un recuadro amarillo que ocupa su superficie
    correspondiente. Puede asignar un proyecto (o subproyecto) a un período; es muy simple, tiene que arrastrar y
    soltar un proyecto (del listado de proyectos favoritos) hacia un recuadro amarillo dentro de la <i>Agenda
    horaria</i>.
    Luego de soltar el proyecto aparece una ventana flotante debajo del botón <b>Admin. lista de proyectos</b> para
    que ingrese cuantas horas trabajó en ese proyecto y que escriba una nota.<br/>
    Luego presiona <b>Grabar</b> y la ventana flotante se cierra y el recuadro amarillo se llena con otro recuadro
    gris conteniendo el proyecto seleccionado. La cantidad de área ocupada por este último recuadro gris, es
    proporcional a la cantidad de horas asignadas al mismo.<br/>
    Para editarlo basta con cliquear sobre él y la ventana flotante aparecerá nuevamente permitiéndole modificar los
    datos o borrar dicha asignación de proyecto.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Grilla Mensual"] = "<br/>
    <b>Grilla Mensual</b><br/>
    <br/>
    Esta grilla es un resumen día por día de todas las horas trabajadas del mes, y de cuánto tiempo de ellas ha sido
    asignada a Proyectos (y Subproyectos).<br/>
    <br/>
    Puede hacer clic en un día para cargarlo en los otros paneles del módulo.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Paso a paso"] = "<br/>
    <b>Cómo cargar las horas trabajadas y asignarlas a proyectos, paso a paso</b><br/>
    <br/>
    <br/>
    <ol>
        <li>Dentro del módulo <b>Asistencia</b> seleccione el día para el cuál quiere cargar las horas trabajadas
            mediante el campo de fecha de la barra superior. Luego presione el botón que está a la derecha del
            mismo.<br/>
            <br/>
        <li>En el panel <b>Tiempo de Trabajo</b> ingrese un período trabajado en el día, por ejemplo si usted trabajó
            desde las 9:00 hasta las 13:00 y desde las 14:00 hasta las 18:00, esto serían dos períodos, comience
            cargando el primero. Para ello en el campo <b>Comienzo</b> ingrese <i>9:00</i> y en el campo <b>Fin</b>
            escriba <i>13:00</i> (puede omitir los dos puntos escribiendo 900 y 1300 si lo desea).<br/>
            <br/>
        <li>Presione el botón con el tilde ubicado bajo esos campos para grabar su ingreso. Luego de unos instantes
            aparecerá el período dentro de esta pantalla en varios lugares:<br/>
            <ul>
                <li>Debajo, en el mismo panel como un rectángulo pequeño de color, con el período que acaba de ingresar.
                    El mismo posee un botón con una cruz, que le permite borrarlo. Debajo de dicho recuadro
                    dirá el total de tiempo de trabajo cargado para este día.
                <li>En el panel <b>Horas de Proyectos</b> aparecerá en la parte derecha, en la agenda diaria de 8:00 a
                    20:00, un recuadro amarillo que ocupa la superficie acorde al período.
                <li>En la <b>Grilla Mensual</b> que es el panel de la derecha de la pantalla, para el día
                    activo se agregará al total de horas, en la columna <b>Tiempo de Trabajo</b> la cantidad de horas
                    que usted acaba de cargar. Si es el primer período que carga en el día elegido, aparecerá sólo esa
                    cantidad de horas.
            </ul>
        <li>Ahora tiene que focalizarse en el panel central que es en el que los proyectos (y subproyectos) se asignan
            a los períodos trabajados. Asegúrese que los proyectos de su incumbencia se encuentren listados en el
            panel, entre el título del panel y el botón <b>Admin. lista de proyectos</b>. Si no hay ningún proyecto, o
            desea agregar uno o modificar el listado, presione el botón <b>Admin. lista de proyectos</b>.<br/>
            <br/>
        <li>Si presiona el botón <b>Admin. lista de proyectos</b> aparecerá una ventana que le mostrará los proyectos
            en los que usted está involucrado en un panel izquierdo y le permitirá arrastrarlos con el mouse hacia el
            panel derecho que es el de los proyectos que se mostrarán luego, al cerrar esta ventana emergente, en el
            panel <b>Horas de Proyectos</b>.<br/>
            <br/>
        <li>Si desea quitar un proyecto del panel derecho de la ventana emergente, arrástrelo de regreso hacia el panel
            izquierdo utilizando el mouse.<br/>
            <br/>
        <li>Cuando haya finalizado los cambios presione el botón con el tilde para grabar y cerrar la
            ventana emergente.<br/>
            <br/>
        <li>El nuevo listado de proyectos aparece bajo el título <b>Horas de Proyectos</b>. Cada proyecto se muestra
            como un rectángulo gris con extremos curvos.<br/>
            <br/>
        <li>Asigne un proyecto de dicho listado a las horas trabajadas. Esto se hace arrastrando con el mouse un
            proyecto y soltándolo dentro del recuadro amarillo en la agenda horaria.<br/>
            <br/>
        <li>Aparecerá una ventana donde debe especificar qué cantidad de horas ha trabajado en ese proyecto y escribir
            una nota.<br/>
            <br/>
        <li>Presione <b>Grabar</b> para guardar los cambios o bien <b>Cancelar</b> para volver atrás y repetir los
            últimos dos pasos.<br/>
            <br/>
        <li>Una vez grabados los cambios, la ventanita se cierra y el recuadro amarillo muestra gráficamente en su
            interior el proyecto asignado, cuyo tamaño es proporcional a las horas especificadas en la ventana
            emergente que se acaba de cerrar.<br/>
            <br/>
        <li>Si el período posee más tiempo libre puede arrastrar y soltar otro proyecto en él hasta llenarlo
            completamente, repitiendo los últimos pasos.<br/>
            <br/>
        <li>Si desea modificar o quitar la asignación de un proyecto en particular de un período horario trabajado,
            haga clic sobre el mismo y una ventana emergente le permitirá modificar las horas y la nota o bien quitar
            la asignación de proyecto mediante el botón <b>Borrar</b>.<br/>
            <br/>
        <li>En la <b>Grilla Mensual</b> que es el panel de la derecha de la pantalla, en la fila del día
            activo, en la columna <b>Horas de Proyectos</b> se agregará a las horas existentes la cantidad de horas que
            usted asignó a proyectos.<br/>
            <br/>
        <li>Puede cargar un día determinado en pantalla mediante el campo de fecha en la parte superior del módulo o
            haciendo clic dentro del panel <b>Grilla Mensual</b> a la derecha de la pantalla, sobre la fecha
            elegida.<br/>
            <br/>
    </ol>
    <br/>
    <br/>";
