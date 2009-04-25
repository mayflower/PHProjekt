<?php
// Words and phrases
$lang["View"] = "Ver";
$lang["List"] = "Lista";
$lang["Day"] = "Día";
$lang["Week"] = "Semana";
$lang["Month"] = "Mes";
$lang["Self"] = "Propio";
$lang["Selection"] = "Selección";
$lang["None"] = "Ninguna";
$lang["Once"] = "Una vez";
$lang["Daily"] = "Diaria";
$lang["Weekly"] = "Semanal";
$lang["Monthly"] = "Mensual";
$lang["Yearly"] = "Anual";
$lang["Monday"] = "Lunes";
$lang["Tuesday"] = "Martes";
$lang["Wednesday"] = "Miercoles";
$lang["Thursday"] = "Jueves";
$lang["Friday"] = "Viernes";
$lang["Saturday"] = "Sábado";
$lang["Sunday"] = "Domingo";
$lang["Further events"] = "Otros eventos";
$lang["Today"] = "Hoy";
$lang["Done"] = "Listo";
$lang["Select users for the group view"] = "Elija los usuarios para la vista grupal";
$lang["You have to select at least one user!"] = "Debe elegir al menos un usuario!";
$lang["User selection"] = "Selección de usuario";
$lang["place"] = "Lugar";
$lang["Calendar week"] = "Calendario semanal";

$lang["January"] = "Enero";
$lang["February"] = "Febrero";
$lang["March"] = "Marzo";
$lang["April"] = "Abril";
$lang["May"] = "Mayo";
$lang["June"] = "Junio";
$lang["July"] = "Julio";
$lang["August"] = "Agosto";
$lang["September"] = "Septiembre";
$lang["October"] = "Octubre";
$lang["November"] = "Noviembre";
$lang["December"] = "Diciembre";

$lang["Participants"] = "Participantes";
$lang["Recurrence"] = "Repetición";
$lang["Repeats"] = "Repeticiones";
$lang["Interval"] = "Intervalo";
$lang["Until"] = "Hasta";
$lang["Weekdays"] = "Días de la semana";

$lang["Mo"] = "Lu";
$lang["Tu"] = "Ma";
$lang["We"] = "Mi";
$lang["Th"] = "Ju";
$lang["Fr"] = "Vi";
$lang["Sa"] = "Sa";
$lang["Su"] = "Do";

// Tooltip Help
$lang["Interval Help"] = "El intervalo para la opción seleccionada en Repeticiones. <br>Ej.: Repeticiones Semanal - "
    . "Intervalo 2, se creará un evento cada 2 semanas.";
$lang["Until Help"] = "El día en que la recurrencia terminará. <br>El día del último evento puede no coincidir con "
    . "este día.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Evento"] = "<br/>
    Esta es la <b>Ayuda General del módulo Evento</b><br/>
    <br/>
    El módulo Evento es una forma muy completa de administrar eventos. Puede crear uno, ingresar información
    descriptiva sobre él, asignarle fecha y hora, especificar participantes, crearle recurrencia y el resto de
    propiedades generales de los módulos, como permisos de Acceso, Notificación por email e Historial.<br/>
    <br/>
    La <b>pantalla</b> se divide en 5 secciones:<br/>
    <br/>
    <ol>
        <li><b>Botonera superior derecha:</b> aquí, según los ítems mostrados y los permisos del usuario, se mostrarán
            hasta 3 botones.<br/>
            <ul>
                <li><b>Agregar:</b> lo presiona y un formulario vacío se abre para que cree un nuevo evento.<br/>
                <li><b>Grabar:</b> la grilla puede ser editada sólo cliqueando en los campos que desee cambiar. Luego
                    presiona este botón para grabar los cambios hechos.<br/>
                <li><b>Exportar:</b> exporta a un archivo CSV los resultados y ofrece descargarlo.<br/>
            </ul>
        <li><b>Solapas de Vistas:</b> hay cuatro tipos de listados que se activan por medio de estas solapas.<br/>
            <ul>
                <li><b>Lista:</b> una grilla con todos los eventos para el usuario logueado.<br/>
                <li><b>Día:</b> una agenda desde las 8:00 hasta las 20:00 hs. donde se muestran todos los eventos de un
                    día determinado.<br/>
                    <u>Tiene dos subtipos</u> que son elegidos a través de las solapas <b>Propio</b> y <b>Selección</b>
                    que aparecen a la derecha de la misma barra, cuando el modo <b>Día</b> está activo:
                    <ul>
                        <li><b>Propio:</b> se muestran los eventos del día elegido, para el usuario activo.
                        <li><b>Selección:</b> cuando se presiona este botón, una ventana emergente aparece permitiendo
                            al usuario seleccionar un pequeño grupo de gente de modo que la lista de eventos contendrá
                            tantas columnas como usuarios elegidos; se verá una agenda grupal para el día activo.
                    </ul>
                <li><b>Semana:</b> una agenda semanal, igual la diaria pero para los siete días de la semana
                    simultáneamente.
                <li><b>Mes:</b> una agenda mensual que expone en un formato claro de calendario todos los días del mes,
                    más los días necesarios del mes anterior y siguiente para completar todas las semanas mostradas.
            </ul>
            Cuando Día, Semana o Mes son la vista activa, aparece una barra adicional sobre los listados. Esta tiene
            los vínculos <i>previo</i>, <i>hoy</i> y <i>siguiente</i>, para cambiar el día / semana / mes
            secuencialmente. Además muestra la fecha del período seleccionado.<br/>
            <br/>
        <li><b>Grilla / Lista:</b> aquí se muestra la lista de ítems o la agenda de un día determinado, según la vista
            elegida.<br/>
        <br/>
        <li><b>Formulario:</b> cuando un ítem esta por ser creado o se cliquea en uno del listado, un formulario se
            muestra aquí para completar o modificar sus datos.<br/>
        <br/>
        <li><b>Botonera inferior:</b> aquí, según los permisos del usuario, cuando un ítem está siendo creado o
            modificado se muestran los botones <b>Grabar</b> y <b>Borrar</b>.<br/>
    </ol>
    <br/>
    <br/>";

$lang["Content Help"]["Datos Básicos"] = "DEFAULT";
$lang["Content Help"]["Repetición"] = "<br/>
    <b>Solapa Repetición</b><br/>
    <br/>
    Esta solapa permite asignar repetición al evento para que suceda tantas veces como se especifique, con la
    frecuencia y días de la semana definidos.<br/>
    <br/>
    <b>Campos</b><br/>
    <br/>
    <b>Repeticiones</b>: aquí elige cuán seguido quiere que ocurra el evento. <i>Una vez</i> es la
    repetición predeterminada y significa que no habrá repetición. Puede elegir <i>Diario</i>,
    <i>Semanal</i>, <i>Mensual</i> o <i>Anual</i> y el evento se repetirá con dicha frecuencia hasta el día elegido
    en el campo <u>Hasta</u>.<br/>
    <br/>
    <b>Intervalo</b>: especifique aquí el intervalo que quiere para las repeticiones. Ej.: si elige <i>Mensual</i> en
    el campo <u>Repeticiones</u> y <i>2</i> en <u>Intervalo</u>, el evento sucederá cada 2 meses.<br/>
    <br/>
    <b>Hasta</b>: si elige una opción distinta a <i>Una vez</i> en el campo <u>Repeticiones</u>, aquí debe elegir
    cuándo quiere que las repeticiones cesen.<br/>
    <br/>
    <b>Días de la semana</b>: puede elegir qué días de la semana sucederá la repetición del evento.<br/>
    <br/>
    <br/>";

$lang["Content Help"]["Accesos"] = "DEFAULT";
$lang["Content Help"]["Notificación"] = "DEFAULT";
$lang["Content Help"]["Historial"] = "DEFAULT";
