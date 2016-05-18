<?php
$string['pluginname'] = 'Gescompeval_MD';
$string['gescompeval'] = 'Gescompeval';
$string['blocktitle'] = 'Título Gescompeval';
$string['blockstring'] = 'Contenido del bloque Gescompeval';
$string['poweredby'] = 'Desarrollado por:<br>Grupo de Investigación <br><span style="font-weight:bold; font-size: 10pt">EVALfor</span>';
$string['gescompeval:addinstance'] = 'Añadir bloque Gescompeval';
$string['gescompeval:myaddinstance'] = 'Añadir Gescompeval a mi página de Moodle';
$string['managementcompetencies'] = 'Gestión de competencias y resultados';
$string['managementsubdimensions'] = 'Gestión de subdimensiones';
$string['notinstance'] = 'No hay ninguna instancia de EvalCOMIX en este curso';
$string['notcapabilities'] = 'No tiene permisos para acceder a esta opción';

// Connect competencies/results with courses
$string['competenciesincluded'] = 'Competencias/Resultados conectados con el curso';
$string['competenciesnotincluded'] = 'Competencias/Resultados no conectados el curso';
$string['connected'] = 'Conectados';
$string['notconnected'] = 'No conectados';
$string['userselectorpreserveselected'] = 'Mantener los elementos seleccionados, incluso si no coinciden más con la búsqueda';
$string['userselectorautoselectunique'] = 'Si sólo un elemento coincide con la búsqueda, seleccionarlo automáticamente';
$string['userselectorsearchanywhere'] = 'Coincidencia del texto de búsqueda en cualquier parte de la descripción del elemento';

// Connect competencies/results with subdimensions
$string['evxtools'] = 'Instrumentos de EvalCOMIX';
$string['selecttool'] = 'Seleccionar instrumento del curso';
$string['selectsub'] = 'Seleccionar subdimensión';
$string['selectsubsubmit'] = 'Relacionar competencias/resultados';
$string['subdimensionsbelonging'] = 'Subdimensiones pertenecientes al instrumento';
$string['selectsubback'] = 'Volver a la selección de subdimensión';
$string['tool'] = 'Instrumento: ';
$string['tooldimension'] = 'Instrumento > Dimensión: ';
$string['dimension'] = 'Dimensión: ';
$string['subdimension'] = 'Subdimensión: ';
$string['attribute'] = 'Atributo: ';
$string['competenciesincludedsub'] = 'Competencias/Resultados conectados con la subdimensión';
$string['competenciesnotincludedsub'] = 'Competencias/Resultados no conectados la subdimensión';

// Get reports
$string['getreports'] = 'Obtener informes';
$string['selectparameters'] = 'Seleccionar parámetros';
$string['selectreporttype'] = 'Seleccionar tipo de informe';
$string['allstudents'] = 'Todos los alumnos del curso';
$string['onestudent'] = 'Un único alumno';
$string['selectstudent'] = 'Seleccionar alumno';
$string['withrelations'] = 'Tener en cuenta las relaciones entre competencias y resultados de aprendizaje';
$string['helprelations'] = 'Relaciones entre competencias y resultados.';
$string['competencereport'] = 'Informe de competencias y resultados de aprendizaje';
$string['value'] = 'Nota: ';
$string['activitieslistofcompetency'] = 'Actividades en las cuales la competencia es evaluada: ';
$string['activitieslistofresult'] = 'Actividades en las cuales el resultado de aprendizaje es evaluado: ';
$string['competencynotassessed'] = 'La competencia no es evaluada directamente en ninguna actividad';
$string['resultnotassessed'] = 'El resultado de aprendizaje no es evaluado directamente en ninguna actividad';
$string['evidences'] = 'Evidencias:';
$string['notevidences'] = 'No hay evidencias para esta actividad';
$string['showevidences'] = 'Mostrar evidencias';
$string['helpevidence'] = 'Mostrar evidencias.';
$string['competencies'] = 'Competencias';
$string['learningoutcomes'] = 'Resultados de aprendizaje';
$string['competenciesconnected'] = 'Competencias relacionadas en este curso: ';
$string['outcomesconnected'] = 'Resultados de aprendizaje relacionados en este curso: ';
$string['nocompetenciesconnected'] = 'No hay competencias relacionadas en este curso';
$string['nooutcomesconnected'] = 'No hay resultados de aprendizaje relacionados en este curso';
$string['competencetypereport'] = 'Tipo de competencia: ';
$string['nocompetencetypereport'] = 'La competencia no tiene tipo asociado';

// Settings
$string['admindescription'] = 'Configura las opciones del servidor de Gescompeval. Asegúrate de que los datos sean correctos. En otro caso la integración no funcionará';
$string['adminheader'] = 'Configuración de Gescompeval';
$string['serverurl'] = 'URL del servidor de Gescompeval:';
$string['serverurlinfo'] = 'Introduce la URL de tu servidor Gescompeval. Ej: http://localhost/Gescompeval';
$string['warning'] = 'AVISO';
$string['warningevalcomix'] = 'EVALCOMIX NO ESTÁ INSTALADO, POR LO QUE LA MAYORÍA DE LAS OPCIONES DE GESCOMPEVAL NO FUNCIONARÁN.';

// Help
$string['helprelations_help'] = 'Si se activa esta casilla se tendrán en cuenta las posibles '.
'relaciones existentes entre competencias y resultados de aprendizaje, de forma que si una'.
' competencia C tiene una nota N para una subdimensión S, a los resultados de aprendizaje'.
' relacionados con la competencia C se les añadirá la nota N para esa subdimensión S. '.
'Para los resultados relacionados con competencias se produce el mismo proceso.';
$string['helpevidence_help'] = 'Si se activa esta casilla se mostrarán las evidencias que un '.
'alumno tenga anotada en cada evaluación de cada actividad.';