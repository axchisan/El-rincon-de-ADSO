<?php
session_start();
session_destroy();
header("Location: ../frontend/inicio/index.php");
?>
