<?php

/**
 * Las nuevas funcionalidades que debes incorporar a la aplicación son las siguientes.
 * • Preferencias de usuario en la utilización de colores para mostrar los diferentes
 *  tipos de días.
 * • Uso de un fichero de texto para almacenar las credenciales de usuario. Para
 * simplificar la aplicación trabajaremos con un único usuario registrado.
 * • Añadir un gestor de tareas para el usuario registrado. Como mínimo deberás
 * implementar el alta de una nueva tarea.
 * • Mostrar en negrita los días que tienen tareas.
 * • Utilizar un fichero de texto para guardar y recuperar las tareas del usuario.
 *
 * @author Victor Fernandez
 * @version 1.0
 * Fecha: 18/12/2023
 */

// Incluimos el fichero de configuración de los festivos.
include("config/festivos.php");

session_start();

// comprobar si el usuario está logueado
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["password"])) {
    header("Location: index.php");
    exit();
}

// comparar las variables de sesión con las del fichero de texto
$archivo = fopen("config/credenciales.txt", "r");
$usuario_correcto = trim(fgets($archivo));
$password_correcto = trim(fgets($archivo));
fclose($archivo);

if ($_SESSION["usuario"] !== $usuario_correcto || $_SESSION["password"] !== $password_correcto) {
    header("Location: index.php");
    exit();
}

// Creamos las variables para el día, mes y año actual.
$dia_actual = date("j");
$mes_actual = date("m");
$ano_actual = date("Y");

if (isset($_POST["cierra"])) {
    header("Location: cerrarsesion.php");
    exit();
}

// Si quiere añadir una nueva tarea y ha enviado el formulario
if (isset($_POST["anadir"])) {
    $tarea = trim($_POST["tarea"]);
    $fecha = trim($_POST["fecha"]);

    if ($tarea != "" && $fecha != "") {
        $xml = simplexml_load_file("config/tareas.xml");

        $nuevaTarea = $xml->addChild('task');

        $nuevaTarea->addChild('fecha')->addChild('dia', date('d', strtotime($fecha)));
        $nuevaTarea->fecha->addChild('mes', date('F', strtotime($fecha)));
        $nuevaTarea->fecha->addChild('anio', date('Y', strtotime($fecha)));

        $nuevaTarea->addChild('asunto', $tarea);

        $xml->asXML("config/tareas.xml");
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Victor Fernandez España</title>
</head>

<body>
    <h1>Calendario</h1>

    <br />
    <br />

    <?php
    if (isset($_POST["mes"]) && isset($_POST["ano"])) {
        $mes_seleccionado = $_POST["mes"];
        $ano_seleccionado = $_POST["ano"];
        $dias_del_mes = $numero = cal_days_in_month(CAL_GREGORIAN, $mes_seleccionado, $ano_seleccionado);
    } else {
        $mes_seleccionado = date("m");
        $ano_seleccionado = date("Y");
        $dias_del_mes = $numero = cal_days_in_month(CAL_GREGORIAN, $mes_seleccionado, $ano_seleccionado);
    }
    ?>

    <table border="1">
        <tr>
            <th colspan="7">
                <?php
                echo array_keys($aMeses)[$mes_seleccionado - 1] . " " . $ano_seleccionado;
                ?>
            </th>
        </tr>
        <tr>
            <th>Lunes</th>
            <th>Martes</th>
            <th>Miércoles</th>
            <th>Jueves</th>
            <th>Viernes</th>
            <th>Sábado</th>
            <th>Domingo</th>
        </tr>
        <?php
        $dia_semana = date("N", mktime(0, 0, 0, $mes_seleccionado, 1, $ano_seleccionado));
        $dia_semana_fin = date("N", mktime(0, 0, 0, $mes_seleccionado, $dias_del_mes, $ano_seleccionado));

        $festivos_mes = isset($aMeses[array_keys($aMeses)[$mes_seleccionado - 1]]['festivos'])
            ? $aMeses[array_keys($aMeses)[$mes_seleccionado - 1]]['festivos']
            : [];

        for ($i = 1; $i <= $dias_del_mes + $dia_semana - 1; $i++) {
            if ($i % 7 == 1) {
                echo "<tr>";
            }

            if ($i < $dia_semana) {
                echo "<td></td>";
            } else {
                $diaTarea = false;
                $dia = $i - $dia_semana + 1;
                $clase_css = '';

                // Verificamos si es el día actual
                if ($dia == $dia_actual && $mes_seleccionado == $mes_actual && $ano_seleccionado == $ano_actual) {
                    $clase_css .= ' clase_dia_hoy';
                } elseif ($i % 7 == 0) {
                    // Verificamos si es un domingo
                    $clase_css .= ' clase_domingo';
                } else {
                    // Verificamos si es un día festivo local
                    foreach ($festivos_mes as $festivo) {
                        if ($dia == $festivo['dia'] && $festivo['tipo'] == 'local') {
                            $clase_css .= ' clase_dia_local';
                        }
                    }

                    // Verificamos si es un día festivo de comunidad
                    foreach ($festivos_mes as $festivo) {
                        if ($dia == $festivo['dia'] && $festivo['tipo'] == 'comunidad') {
                            $clase_css .= ' clase_dia_comunidad';
                        }
                    }

                    // Verificamos si es un día festivo nacional
                    foreach ($festivos_mes as $festivo) {
                        if ($dia == $festivo['dia'] && $festivo['tipo'] == 'nacional') {
                            $clase_css .= ' clase_dia_nacional';
                        }
                    }

                    // Verificamos si es un día con tarea
                    $xml = simplexml_load_file("config/tareas.xml");
                    foreach ($xml->task as $tarea) {
                        if ($dia == $tarea->fecha->dia && $mes_seleccionado == date('m', strtotime($tarea->fecha->mes)) && $ano_seleccionado == $tarea->fecha->anio) {
                            $diaTarea = true;
                            $clase_css .= ' clase_dia_tarea';
                        }
                    }
                }

                echo "<td class='$clase_css'>" . $dia;
                if ($diaTarea) {
                    echo "<br /><span style='font-size: 8px;'>Tareas Pendientes</span>";
                }
                echo "</td>";
            }

            if ($i % 7 == 0) {
                echo "</tr>";
            }
        }
        ?>
    </table>

    <br />

    <form action="" method="post">
        <label for="mes">Mes</label>
        <input type="number" name="mes" id="mes" min="1" max="12" value="<?php echo $mes_actual; ?>">
        <label for="ano">Año</label>
        <input type="number" name="ano" id="ano" min="1900" max="2100" value="<?php echo $ano_actual; ?>">
        <input type="submit" value="Mostrar">
    </form>

    <!-- Botón de cierre de sesión -->
    <form action="" method="post">
        <p>Has iniciado sesión como: <?php echo $_SESSION["usuario"]; ?></p>
        <input type="submit" value="Cerrar sesión" name="cierra">
    </form>

    <!-- Creamos el formulario para añadir una nueva tarea -->
    <div class="tareas">
        <h2>Gestor de tareas</h2>
        <form action="" method="post">
            <label for="tarea">Asunto</label>
            <input type="text" name="tarea" id="tarea">
            <label for="fecha">Fecha</label>
            <input type="date" name="fecha" id="fecha">
            <br /><br />
            <input type="submit" value="Añadir tarea" name="anadir">
        </form>
    </div>

    <!-- Mostramos las tareas pendientes del mes en el que nos encontramos -->
    <div class="tareas">
        <h2>Tareas pendientes</h2>
        <table border="1">
            <tr>
                <th>Fecha</th>
                <th>Asunto</th>
            </tr>
            <?php
            $xml = simplexml_load_file("config/tareas.xml");
            foreach ($xml->task as $tarea) {
                if ($mes_seleccionado == date('m', strtotime($tarea->fecha->mes)) && $ano_seleccionado == $tarea->fecha->anio) {
                    echo "<tr>";
                    echo "<td>" . $tarea->fecha->dia . "/" . date('m', strtotime($tarea->fecha->mes)) . "/" . $tarea->fecha->anio . "</td>";
                    echo "<td>" . $tarea->asunto . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </table>
    </div>

    <footer>
        <a href="#" target="_blank"> Link de Github al Ejercicio </a>
    </footer>
</body>

</html>
