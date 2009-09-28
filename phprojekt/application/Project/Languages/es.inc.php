<?php
$lang["The deletion of a project and its subprojects might take a while"] = "La supresión de un proyecto y sus "
    . "subproyectos podría tomar un tiempo";

// Tooltip Help
$lang["Tooltip"]["projectId"] = "El proyecto padre, si no tiene padre elegir PHProjekt.";

// General Help
$lang["Content Help"]["General"] = "DEFAULT";
$lang["Content Help"]["Proyecto"] = "<br />
    Esta es la <b>Ayuda General del módulo Proyecto</b><br />
    <br />
    Los Proyectos son la parte funcional más importante del sistema. El usuario crea un proyecto para trabajar con él,
    y luego carga toda la información en el proyecto en sí más los módulos asociados que se activaron para él (ver
    solapa Módulo dentro de un proyecto). Con el transcurso del tiempo, puede agregar o modificar información.<br />
    <br />
    Puede darle permisos específicos a los usuarios que desee, como roles o sólo permisos individuales.<br />
    <br />
    El módulo Proyecto tiene otros módulos asociados que son parte de él. Esas asociaciones están definidas en la
    solapa Módulo, que se ve cuando se edita el proyecto. Los módulos que vienen con la versión original del sistema
    y pueden ser asociados serán llamados en esta ayuda como <b>Módulos generales</b> y ellos son: Archivos, Gantt,
    Nota, Estadísticas y Todo.<br />
    <br />
    Esos módulos generales son accesibles desde:
    <ul>
        <li><b>El proyecto raíz:</b> Ud. hace clic en el logo y el módulo Proyecto se abre, con todos los proyectos
            principales listados en la grilla. Esos son los proyectos que no tienen padre, que no dependen de ninguno,
            aunque en realidad en el sistema se los asocia al proyecto 'PHProjekt', que representa el padre de todos
            los proyectos principales.<br />
            Verá todos los módulos generales en la fila de solapas principal en la parte superior de la pantalla. Si
            entra allí, verá los ítems de módulos que no están asociados a ningún proyecto (aunque técnicamente,
            lo están al proyecto 'PHProjekt').<br />
            Si quiere ver la información principal del proyecto, puede cliquearlo en la grilla y sus solapas
            principales se abrirán en el formulario debajo. Pero si quiere ver los ítems de los módulos generales
            que están directamente asociados a ese proyecto, tiene que acceder al proyecto desde el árbol a la
            izquierda, tal como se explica en el siguiente punto aquí.
        <li><b>El árbol:</b> el panel de árbol de proyectos a la izquierda permite acceder a los Proyectos y
            Subproyectos de un modo completo, significa que la fila principal de solapas arriba mostrará la solapa
            <b>Datos Básicos</b> más los módulos <b>asociados</b> al proyecto, y si accede a esos módulos desde
            allí, se listarán sólo los ítems del módulo que pertenecen a al proyecto o subproyecto cliqueado en el
            árbol.<br />
    </ul>
    <br />
    Para ver la ayuda de esos otros módulos, por favor cliquee en dicha solapa (puede ser estando en el proyecto raíz
    o en un proyecto en particular) y luego haga clic en el vínculo de Ayuda arriba a la derecha.<br />
    <br />
    Para ayuda específica acerca de cada solapa del módulo Proyecto, entre en las otras solapas de esta ayuda.<br />
    <br />
    Los proyectos pueden tener subproyectos y muchas otras propiedades que puede empezar a aprender leyendo la solapa
    <b>Datos Básicos</b> dentro de esta ayuda.<br />
    <br />
    <br />";

$lang["Content Help"]["Datos Básicos"] = "DEFAULT";
$lang["Content Help"]["Accesos"] = "DEFAULT";

$lang["Content Help"]["Módulo"] = "<br />
    <b>Solapa Módulo</b><br />
    <br />
    Esta solapa tiene un listado de todos los módulos que pueden asociarse al proyecto.<br />
    <br />
    Los módulos que están tildados
    serán vistos como solapas al costado de la solapa Datos Básicos, cuando acceda al Proyecto desde el árbol (ver
    explicación completa en la solapa Proyecto dentro de esta ayuda).<br />
    <br />
    <br />";

$lang["Content Help"]["Rol"] = "<br />
    <b>Solapa Rol</b><br />
    <br />
    Esta solapa permite asignar Roles a los usuarios. Un Rol es un conjunto específico de permisos definidos para
    cada módulo. Se configuran en <b>Administración -&#62; Rol</b>. Por ejemplo un rol puede ser 'Admin en todos los
    módulos' o 'Admin en Archivos, sólo lectura en el resto'.<br />
    El permiso final de un usuario específico para trabajar con un ítem está definido por la combinación de lo que
    esté asignado en las solapas Rol y Accesos.<br />
    <br />
    Predeterminadamente, ningún rol se asigna a nadie y el permiso para un proyecto nuevo será el acceso <b>Admin</b>
    para el creador y ninguno para el resto. Esto cambia cuando se asignan permisos en las solapas <b>Accesos</b> y
    <b>Rol</b>.<br />
    <br />
    <br />";

$lang["Content Help"]["Notificación"] = "DEFAULT";
$lang["Content Help"]["Historial"] = "DEFAULT";
