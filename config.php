<?php

// Definir um fuso horario padrao
date_default_timezone_set('America/Sao_Paulo');

// Credenciais do banco de dados
define('HOST', 'localhost');
define('USER', 'root');
define('PASS', '');
define('DBNAME', 'swifter');
define('PORT', 3306);

// Credenciais do servidor de e-mail
define('HOSTEMAIL', 'sandbox.smtp.mailtrap.io');
define('USEREMAIL', '5781efd7849a70');
define('PASSEMAIL', '0c25732aee3503');
define('SMTPSECURE', 'PHPMailer::ENCRYPTION_STARTTLS');
define('PORTEMAIL', 465);
define('REMETENTE', 'atendimento@swifter.com.br');
define('NOMEREMETENTE', 'Atendimento');