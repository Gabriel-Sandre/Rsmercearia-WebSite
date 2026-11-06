<?php
session_start();
if (isset($_SESSION["user"])) {
	header("Location: index.php");
	exit;
}

// O formulário é processado antes de qualquer HTML: assim o redirecionamento
// do login funciona mesmo sem output buffering (ex.: `php -S`).
$messages = array(); // cada item: [classe css, texto]

if (isset($_POST["register"])) {
	$fullname = trim($_POST["fullname_reg"] ?? "");
	$email = trim($_POST["email_reg"] ?? "");
	$tell = trim($_POST["tell_reg"] ?? "");
	$password = $_POST["password_reg"] ?? "";
	$errors = array();
	if ($fullname === "" or $email === "" or $tell === "" or $password === "") {
		array_push($errors, "Todos campos precisam estar preenchidos");
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		array_push($errors, "Email não é valido");
	}
	if (strlen($password) < 8) {
		array_push($errors, "A senha precisa ter 8 caracteres ou mais");
	}
	if (count($errors) === 0) {
		require_once "backend/conexao.php";
		// Consulta parametrizada: o e-mail nunca é concatenado no SQL.
		$stmt = mysqli_prepare($conn, "SELECT id_user FROM Users WHERE email = ?");
		mysqli_stmt_bind_param($stmt, "s", $email);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_store_result($stmt);
		if (mysqli_stmt_num_rows($stmt) > 0) {
			array_push($errors, "Esté email já está registrado");
		}
		mysqli_stmt_close($stmt);
	}
	if (count($errors) > 0) {
		foreach ($errors as $error) {
			$messages[] = array("false", "-" . $error);
		}
	} else {
		$passwordhash = password_hash($password, PASSWORD_DEFAULT);
		$stmt = mysqli_prepare($conn, "INSERT INTO Users (fullname, email, tell, password) VALUES (?, ?, ?, ?)");
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $tell, $passwordhash);
			mysqli_stmt_execute($stmt);
			$messages[] = array("true", "-Registrado com sucesso");
		} else {
			$messages[] = array("false", "-Alguma coisa está muito errada..");
		}
	}
}

if (isset($_POST["login"])) {
	$email = trim($_POST["email_log"] ?? "");
	$password = $_POST["password_log"] ?? "";
	require_once "backend/conexao.php";
	$stmt = mysqli_prepare($conn, "SELECT fullname, email, password FROM Users WHERE email = ?");
	mysqli_stmt_bind_param($stmt, "s", $email);
	mysqli_stmt_execute($stmt);
	$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
	mysqli_stmt_close($stmt);
	// A sessão só recebe dados depois que a senha confere.
	if ($user && password_verify($password, $user["password"])) {
		session_regenerate_id(true); // evita fixação de sessão
		$_SESSION["user"] = "yes";
		$_SESSION["name"] = $user["fullname"];
		$_SESSION["email"] = $user["email"];
		header("Location: index.php");
		exit;
	}
	// Mesma mensagem para e-mail e senha: não revela quais e-mails existem.
	$messages[] = array("false", "-E-mail ou senha inválidos");
}
?>
<html>

<head>
	<!-- Meta tags Obrigatórias -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<!-- Bootstrap CSS/Js -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
	<!-- Meu CSS/Js-->
	<link rel="stylesheet" href="front/myfront/all.css">
	<link rel="stylesheet" href="front/myfront/login/login.css">
	<script src="front/myfront/login/login.js"></script>
	<!-- Externo CSS/Js-->
	<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<title>Login</title>
</head>

<body>
	<div class="img-back" style="
	background-image: url(front/img/login/login.gif);
	padding: 1px;
	min-height: 100vh;
	display: flex;
	align-items: center;
	justify-content: center;
	background-repeat: round;">
		<div class="alerts">
			<?php foreach ($messages as $message) { ?>
				<div class="<?php echo $message[0]; ?>"><?php echo htmlspecialchars($message[1], ENT_QUOTES, "UTF-8"); ?></div>
			<?php } ?>
		</div>

		<div class="veen">
			<div class="login-btn splits">
				<p>Já é usuário?</p>
				<button class="">Login</button>
			</div>
			<div class="rgstr-btn splits">
				<p>Não tem uma conta?</p>
				<button class="">Registrar</button>
			</div>

			<div class="wrapper">

				<form action="login.php" method="post" id="login">
					<h3>Login</h3>
					<div class="grup-log">
						<input name="email_log" type="text">
						<label>E-mail</label>
					</div>
					<div class="grup-log">
						<input name="password_log" type="password">
						<label>Senha</label>
					</div>
					<div class="grup-log">
						<input name="login" type="submit" class="button" value="Login">
					</div>
				</form>

				<form action="login.php" method="post" id="register">
					<h3>Criar conta</h3>
					<div class="grup-reg">
						<input name="fullname_reg" type="text">
						<label>Nome completo</label>
					</div>
					<div class="grup-reg">
						<input name="email_reg" type="text">
						<label>E-mail</label>
					</div>
					<div class="grup-reg">
						<input name="tell_reg" type="tel">
						<label>Telefone</label>
					</div>
					<div class="grup-reg">
						<input name="password_reg" type="password">
						<label>Senha</label>
					</div>
					<div class="grup-reg">
						<input name="register" type="submit" class="button" value="Registrar">
					</div>
				</form>
			</div>
		</div>
	</div>

</body>

</html>