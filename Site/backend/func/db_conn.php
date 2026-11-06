<?php

# Lido do ambiente (Docker); sem variáveis, usa o padrão do XAMPP.
$sName = getenv("DB_HOST") ?: "localhost";
$uName = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "";
$db_name = getenv("DB_NAME") ?: "Rsmercearia";

/**
creating database connection 
useing The PHP Data Objects (PDO)
 **/
try {
  $conn = new PDO(
    "mysql:host=$sName;dbname=$db_name;charset=utf8mb4",
    $uName,
    $pass
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  // A mensagem do PDO pode expor host e usuário do banco: fica só no log.
  error_log("Connection failed: " . $e->getMessage());
  die("Não foi possível conectar ao banco de dados.");
}
