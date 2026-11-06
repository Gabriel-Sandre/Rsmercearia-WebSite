<?php
// Lido do ambiente (Docker); sem variáveis, usa o padrão do XAMPP.
$hostName = getenv("DB_HOST") ?: "localhost";
$dbUser = getenv("DB_USER") ?: "root";
$dbPassword = getenv("DB_PASSWORD") ?: "";
$dbName = getenv("DB_NAME") ?: "Rsmercearia";
$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);
if (!$conn) {
    die("Alguma coisa está muito errada..");
}
