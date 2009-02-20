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

// Help
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
    El período se llena dentro de los campos de texto <b>Comienzo</b> y <b>Fin</b>. El <b>botón con la tilde</b> lo
    graba y el período aparece en 3 lugares dentro de la pantalla:<br/>
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
    <b>Tiempo de Trabajo</b>. Cada uno de esos períodos aparece como un recuadro blanco que ocupa su superficie
    correspondiente. Puede asignar un proyecto (o subproyecto) a un período; es muy simple, tiene que arrastrar y
    soltar un proyecto (del listado de proyectos favoritos) hacia un recuadro amarillo dentro de la <i>Agenda
    horaria</i>.
    Luego de soltar el proyecto aparece una ventana flotante debajo del botón <b>Admin. lista de proyectos</b> para
    que ingrese cuantas horas trabajó en ese proyecto y que escriba una nota.<br/>
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

// General Tooltip buttons Help
$lang["Start Stop Buttons Help"] = "Estos botones Comienzan y Finalizan las horas de trabajo automáticamente.";
$lang["Working Times Help"] = "Aquí puede agregar todas las horas trabajadas del día elegido.";
$lang["Hours Help"] = "El formato de las horas es sin los dos puntos ( : ) 08:00 es 0800";
$lang["Project Times Help"] = "Dentro de este panel puede Arrastrar y Soltar proyectos dentro de las horas trabajadas.";
