<?php
define("DB_HOST", getenv('PGHOST'));
define("DB_PORT", getenv('PGPORT'));
define("DB_NAME", getenv('PGDATABASE'));
define("DB_USER", getenv('PGUSER'));
define("DB_PASSWORD", getenv('PGPASSWORD'));
define("DB_SSLMODE", "disable"); // Railway no requiere SSL explícito
define("DB_SSLROOTCERT", ""); // No se necesita certificado en Railway
?>