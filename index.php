<?php

/**
 * Las nuevas funcionalidades que debes incorporar a la aplicación son las siguientes:
 * • Preferencias de usuario en la utilización de colores para mostrar los diferentes
 * tipos de días.
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

session_start();

// Si se ha enviado el formulario de inicio de sesión
if (isset($_POST["inicio"])) {
    // Obtén los valores del formulario
    $usuario_form = trim($_POST["usuario"]);
    $password_form = trim($_POST["password"]);

    // Comparar con las credenciales del archivo de texto
    $archivo = fopen("config/credenciales.txt", "r");
    $usuario_correcto = trim(fgets($archivo));
    $password_correcto = trim(fgets($archivo));
    fclose($archivo);


    if ($usuario_form == $usuario_correcto && $password_form == $password_correcto) {
        // Inicia la sesión
        $_SESSION["usuario"] = $usuario_form;
        $_SESSION["password"] = $password_form;

        // Redirigir a la página de calendario
        header("Location: calendario.php");
        exit();
    } else {
        // Mostrar un mensaje de error o realizar alguna acción si las credenciales no son válidas
        echo "Usuario o contraseña incorrectos.";
    }
}


// Incluimos el fichero de configuración de los festivos.
include("config/festivos.php");

// Creamos las variables para el día, mes y año actual.
$dia_actual = date("j");
$mes_actual = date("m");
$ano_actual = date("Y");
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
    <br /><br />

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
                $dia = $i - $dia_semana + 1;
                $clase_css = '';

                // Verificamos si es el día actual
                if ($dia == $dia_actual && $mes_seleccionado == $mes_actual && $ano_seleccionado == $ano_actual) {
                    $clase_css .= ' clase_dia_hoy';
                }

                // Verificamos si es un domingo
                if ($i % 7 == 0) {
                    $clase_css .= ' clase_domingo';
                }

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

                echo "<td class='$clase_css'>" . $dia . "</td>";
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

    <!-- Formulario de inicio de sesión -->
    <form action="" method="post">
        <label for="usuario">Usuario</label>
        <input type="text" name="usuario" id="usuario" value="victor">
        <br />
        <label for="password">Contraseña</label>
        <input type="password" name="password" id="password" value="1234">
        <br />
        <br />
        <input type="submit" value="Iniciar sesión" name="inicio">
    </form>
    
    <br /><br />

    <footer>
        <a href="#" target="_blank"> Link de Github al Ejercicio </a>
    </footer>
</body>

</html>