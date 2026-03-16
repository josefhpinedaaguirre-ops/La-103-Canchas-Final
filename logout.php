<?php
session_start();
// Destruimos todas las variables de sesión
session_unset();
// Destruimos la sesión como tal
session_destroy();

// Lo mandamos de regreso al login (tu reservar.html)
echo "<script>
        alert('Sesión cerrada. ¡Vuelve pronto a La 103!');
        window.location.href='registro.html';
      </script>";
exit();
?>