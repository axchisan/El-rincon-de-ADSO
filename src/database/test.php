

<?php
require_once "conexionDB.php";

try {
    $conn = conexionDB::getConexion();
    $stmt = $conn->query("SELECT current_date, current_user");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "✅ Conexión exitosa.<br>";
    echo "📅 Fecha actual: " . $data['current_date'] . "<br>";
    echo "👤 Usuario actual: " . $data['current_user'];
} catch (Exception $e) {
    echo "❌ Fallo en la conexión: " . $e->getMessage();
}
// aqui testeamos la conexion a la base de datos a ver si servia esa monda xD
?>
