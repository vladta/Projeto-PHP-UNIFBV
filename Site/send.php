<?php

declare(strict_types=1);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/SMTP.php';


$GMAIL_USERNAME = 'charlezitopocoto@gmail.com';  
$GMAIL_APP_PASS = 'chave google'; 
$DESTINO        = 'charlezitopocoto@gmail.com'; 


$nome     = trim($_POST['nome']    ?? '');
$mensagem = trim($_POST['mensagem']?? '');
$honeypot = trim($_POST['website'] ?? '');

if ($honeypot !== '') {

  http_response_code(400);
  exit('<p class="err">Erro: envio inválido.</p>');
}

if ($nome === '' || $mensagem === '') {
  http_response_code(422);
  exit('<p class="err">Por favor, preencha nome e mensagem.</p>');
}


if (mb_strlen($nome) > 120 || mb_strlen($mensagem) > 5000) {
  http_response_code(413);
  exit('<p class="err">Conteúdo muito grande.</p>');
}

$nomeSafe     = htmlspecialchars($nome, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$mensagemSafe = nl2br(htmlspecialchars($mensagem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

$mail = new PHPMailer(true);

try {

  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = $GMAIL_USERNAME;
  $mail->Password   = $GMAIL_APP_PASS;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
  $mail->Port       = 587;


  $mail->setFrom($GMAIL_USERNAME, 'Form Contato');
  $mail->addAddress($DESTINO); 
  
  $mail->isHTML(true);
  $mail->Subject = 'Nova mensagem do formulário de contato';
  $mail->Body    = "
    <h2>Nova mensagem</h2>
    <p><strong>Nome:</strong> {$nomeSafe}</p>
    <p><strong>Mensagem:</strong><br>{$mensagemSafe}</p>
    <hr>
    <small>Enviado de: {$_SERVER['REMOTE_ADDR']} em ".date('d/m/Y H:i:s')."</small>
  ";
  $mail->AltBody = "Nova mensagem\n\nNome: {$nome}\n\nMensagem:\n{$mensagem}\n\n--\nIP: {$_SERVER['REMOTE_ADDR']}";


  if ($mail->send()) {
    echo '<p class="ok">Mensagem enviada com sucesso! Verifique sua caixa de entrada.</p>';
  } else {
    http_response_code(500);
    echo '<p class="err">Falha ao enviar a mensagem.</p>';
  }

} catch (Exception $e) {
  http_response_code(500);
  echo '<p class="err">Erro ao enviar: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
}
