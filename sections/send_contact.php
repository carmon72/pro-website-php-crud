<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $mensaje = htmlspecialchars($_POST["message"]);

    $destinatario = "carlosmontalvan6@gmail.com"; // 👈 cambia por tu correo real
    $asunto = "Nuevo mensaje de contacto de $nombre";

    $cuerpo = "
    Has recibido un nuevo mensaje desde el formulario de contacto:

    Nombre: $nombre
    Correo: $email

    Mensaje:
    $mensaje
    ";

    $headers = "From: noreply@tusitio.com\r\n";
    $headers .= "Reply-To: $email\r\n";

    if (mail($destinatario, $asunto, $cuerpo, $headers)) {
        $status = "success";
        $msg = "✅ Tu mensaje ha sido enviado correctamente. ¡Gracias por contactarnos!";
    } else {
        $status = "error";
        $msg = "❌ Hubo un error al enviar tu mensaje. Por favor, inténtalo más tarde.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultado de Contacto</title>
<link rel="stylesheet" href="../public/style.css">
<style>
.result-container {
    max-width: 600px;
    margin: 100px auto;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    background: white;
    font-family: Arial, sans-serif;
}
.result-container h2 {
    margin-bottom: 20px;
    font-size: 20px;
}
.result-success { color: #16a34a; }
.result-error { color: #dc2626; }
.back-btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 16px;
    background-color: #0b5cff;
    color: white;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.2s;
}
.back-btn:hover {
    background-color: #084ecf;
}
</style>
</head>
<body>
    <div class="result-container">
        <h2 class="<?= $status === 'success' ? 'result-success' : 'result-error' ?>">
            <?= $msg ?>
        </h2>
        <a href="../index.php?open=contact" class="back-btn">⬅ Regresar al formulario de contacto</a>
    </div>
</body>
</html>

