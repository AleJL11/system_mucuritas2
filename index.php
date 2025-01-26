<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mucuritas 2</title>

    <!-- CSS -->
    <link rel="stylesheet" href="./style/styles.css">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>

    <!-- FAVICON -->
    <link rel="icon" type="img/png" href="./img/icons/favicon.png">

    <!-- ICONS -->
    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="./assets/fontawesome/css/fontawesome.css">
    <link href="./assets/fontawesome/css/brands.css" rel="stylesheet">
    <link href="./assets/fontawesome/css/solid.css" rel="stylesheet">
    <link href="./assets/fontawesome/css/regular.css" rel="stylesheet">
    <script src="./assets/fontawesome/js/fontawesome.js"></script>

    <!-- LINE AWESOME -->
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

    <!-- GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200;300;400&family=Varela+Round&display=swap" rel="stylesheet">

</head>

<body>

    <div class="background"></div>
    <div class="container">

        <div class="logreg-box">
            <div class="form-box login">
                <form action="./data/login.php" method="POST" id="form">
                    <h2>Iniciar Sesión</h2>

                    <div class="input-box">
                        <span class="icon"><i class="las la-user"></i></span>
                        <input type="email" id="email" name="email" value="<?php 
                        include("./data/conexion.php");
                        if(isset($_COOKIE['email'])) {
                            list($encrypted_email, $iv) = explode('::', base64_decode($_COOKIE['email']), 2);
                            echo openssl_decrypt($encrypted_email, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
                        } ?>">
                        <label for="email">Correo</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="fa-regular fa-eye"></i></span>
                        <input type="password" id="pass" name="pass">
                        <label for="pass">Clave</label>
                    </div>
                
                    <div class="remember-forget">
                        <label>
                            <input type="checkbox" name="recordar" <?php if(isset($_COOKIE['email'])) { echo 'checked'; } ?>>
                            Recordar clave
                        </label>
                        <span class="forget-link">¿Olvidó su clave?</span>
                    </div>

                    <?php
                    session_start();
                    session_write_close();
                    if (isset($_SESSION['error_message'])) {
                        echo '<div class="error-message_login">' . $_SESSION['error_message'] . '</div><br>';
                        unset($_SESSION['error_message']);
                    }
                    ?>

                    <button type="submit" class="btn2" id="submit" name="enviar_ing">Iniciar Sesión</button> <br> <br>
                </form>

                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Registrarse
                </button>

            </div>

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Crear Usuario</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" method="POST" id="form_create" name="form_create" class="form_create">

                                <div class="input-box_create">
                                    <span class="icon"><i class="fa-regular fa-address-card"></i></span>
                                    <input type="text" id="CI" placeholder="V- o E-XX.XXX.XXX" name="ci">
                                    <label for="ci">Cédula</label>
                                </div>

                                <div class="input-box_create">
                                    <span class="icon"><i class="las la-user"></i></span>
                                    <input type="text" id="name_last" name="name">
                                    <label for="name">Nombre y Apellido</label>
                                </div>

                                <!--<div class="input-box_create">
                                    <span class="icon"><i class="las la-user"></i></span>
                                    <input type="text" id="nroTel" name="nroTel" placeholder="+58XXXXXXXXXX">
                                    <label for="nroTel">Número de teléfono</label>
                                </div>-->

                                <div class="input-box_create">
                                    <span class="icon"><i class="fa-solid fa-car"></i></span>
                                    <label for="num_vehiculos" class="label_select">Cantidad de Vehículos:</label>
                                    <select id="num_vehiculos" name="num_vehiculos">
                                        <option value="0" selected>Seleccione cantidad</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>

                                <!-- Contenedor para los formularios dinámicos -->
                                <div id="vehiculo_registros"></div>

                                <!-- Contenedor para la leyende -->
                                <div id="vehiculo_leyenda"></div>

                                <div class="input-box_create">
                                    <span class="icon"><i class="las la-at"></i></span>
                                    <input type="email" id="email_modal" name="email_modal" placeholder="Ejemplo: correo@gmail.com">
                                    <label for="email_modal">Correo</label>
                                </div>

                                <div class="input-box_create">
                                    <span class="icon"><i class="fa-regular fa-eye"></i></span>
                                    <input type="text" id="pass_modal" name="pass_modal">
                                    <label for="pass_modal">Clave</label>
                                </div>

                                <button type="submit" class="btn btn-primary" name="enviar_reg" id="enviar">Crear</button>

                                <div class="requirements">
                                    <p class="error-message" id="error-message"></p>
                                    <p class="success-message" id="success-message"></p>
                                </div><br>

                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-box forget">
                <form action="./data/forget.php" id="form_forget" method="POST">
                    <h2>Recuperar Clave</h2>

                    <div class="input-box">
                        <span class="icon"><i class="las la-user"></i></span>
                        <input type="email" name="email_forget" id="email_forget">
                        <label for="email_forget">Correo</label>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class="clase fa-regular fa-eye"></i></span>
                        <input type="text" name="pass_forget" id="pass_forget">
                        <label for="pass_forget">Clave</label>
                    </div>

                    <div class="requirements">
                        <p class="error-message_forget" id="error-message_forget"></p>
                        <p class="success-message_forget" id="success-message_forget"></p>
                    </div>

                    <div class="remember-forget">
                        <span class="login-link">¿Tienes cuenta?</span>
                    </div>

                    <button type="submit" class="btn2" id="submit_forget" name="enviar_forget">Cambiar Clave</button>
                    <!--<p class="error-message" id="error-message"></p>-->
                </form>
            </div>
        </div>

    </div>

    <script src="./js/main.js" defer></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>

</body>

</html>