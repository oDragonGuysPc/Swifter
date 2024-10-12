<?php

session_start(); // Iniciar a sessão

ob_start(); // Limpar o buffer de saída

// Importar as classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir o arquivo config
include_once "./config.php";

// Incluir o arquivo com a conexão com banco de dados
include_once "./conexao.php";

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swifter - Login</title>
</head>

<body>

    <h2>Login</h2>

    <?php
        // Exemplo criptografar a senha
        //echo password_hash(123456, PASSWORD_DEFAULT);

        // Receber os dados do formulário
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

    // Acessar o IF quando o usuário clicar no botão acessar do formulário
    if (!empty($dados['SendLogin'])) {
        //var_dump($dados);

        // Recuperar os dados do usuário no banco de dados
        $query_usuario = "SELECT id, nome, usuario, senha_usuario 
                            FROM usuarios
                            WHERE usuario =:usuario
                            LIMIT 1";

        // Preparar a QUERY
        $result_usuario = $conn->prepare($query_usuario);

        // Substituir o link da query pelo valor que vem do formulário
        $result_usuario->bindParam(':usuario', $dados['usuario']);

        // Executar a QUERY
        $result_usuario->execute();

        // Acessar o IF quando encontrar usuário no banco de dados
        if (($result_usuario) and ($result_usuario->rowCount() != 0)) {
            // Ler os registros retorando do banco de dados
            $row_usuario = $result_usuario->fetch(PDO::FETCH_ASSOC);
            //var_dump($row_usuario);

            // Acessar o IF quando a senha é válida
            if (password_verify($dados['senha_usuario'], $row_usuario['senha_usuario'])) {
                // Salvar os dados do usuário na sessão
                $_SESSION['id'] = $row_usuario['id'];
                $_SESSION['usuario'] = $row_usuario['usuario'];

                // Recuperar a data atual
                $data = date('Y-m-d H:i:s');

                // Gerar número randômico entre 100000 e 999999
                $codigo_autenticacao = mt_rand(100000, 999999);
                //var_dump($codigo_autenticacao);

                // QUERY para salvar no banco de dados o código e a data gerada
                $query_up_usuario = "UPDATE usuarios SET
                                codigo_autenticacao =:codigo_autenticacao,
                                data_codigo_autenticacao =:data_codigo_autenticacao
                                WHERE id =:id
                                LIMIT 1";

                // Preparar a QUERY
                $result_up_usuario = $conn->prepare($query_up_usuario);

                // Substituir o link da QUERY pelo valores
                $result_up_usuario->bindParam(':codigo_autenticacao', $codigo_autenticacao);
                $result_up_usuario->bindParam(':data_codigo_autenticacao', $data);
                $result_up_usuario->bindParam(':id', $row_usuario['id']);

                // Executar a QUERY
                $result_up_usuario->execute();

                // Incluir o Composer
                require './lib/vendor/autoload.php';

                // Criar o objeto e instanciar a classe do PHPMailer
                $mail = new PHPMailer(true);

                // Verificar se envia o e-mail corretamente com try catch
                try {
                    // Imprimir os erro com debug
                    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;  

                    // Permitir o envio do e-mail com caracteres especiais
                    $mail->CharSet = 'UTF-8';

                    // Definir para usar SMTP
                    $mail->isSMTP();         

                    // Servidor de envio de e-mail
                    $mail->Host       = HOSTEMAIL; 

                    // Indicar que é necessário autenticar
                    $mail->SMTPAuth   = true;     

                    // Usuário/e-mail para enviar o e-mail                              
                    $mail->Username   = USEREMAIL; 

                    // Senha do e-mail utilizado para enviar e-mail                  
                    $mail->Password   = PASSEMAIL;      

                    // Ativar criptografia                         
                    $mail->SMTPSecure = SMTPSECURE;  

                    // Porta para enviar e-mail          
                    $mail->Port       = PORTEMAIL;

                    // E-mail do rementente
                    $mail->setFrom(REMETENTE, NOMEREMETENTE);

                    // E-mail de destino
                    $mail->addAddress($row_usuario['usuario'], $row_usuario['nome']);

                    // Definir formato de e-mail para HTML
                    $mail->isHTML(true);  
                    
                    // Título do e-mail
                    $mail->Subject = 'Aqui está o código de verificação de 6 dígitos que você solicitou';

                    // Conteúdo do e-mail em formato HTML
                    $mail->Body    = "Olá " . $row_usuario['nome'] . ", Autenticação multifator.<br><br>Seu código de verificação de 6 dígitos é $codigo_autenticacao<br><br>Esse código foi enviado para verificar seu login.<br><br>";

                    // Conteúdo do e-mail em formato texto
                    $mail->AltBody = "Olá " . $row_usuario['nome'] . ", Autenticação multifator.\n\nSeu código de verificação de 6 dígitos é $codigo_autenticacao\n\nEsse código foi enviado para verificar seu login.\n\n";

                    // Enviar e-mail
                    $mail->send();

                    // Redirecionar o usuário
                    header('Location: validar_codigo.php');

                } catch (Exception $e) { // Acessa o catch quando não é enviado e-mail corretamente
                    echo "E-mail não enviado com sucesso. Erro: {$mail->ErrorInfo}";
                    $_SESSION['msg'] = "<p style='color: #f00;'>Erro: E-mail não enviado com sucesso!</p>";
                }
            } else {
                $_SESSION['msg'] = "<p style='color: #f00;'>Erro: Usuário ou senha inválida!</p>";
            }
        } else {
            $_SESSION['msg'] = "<p style='color: #f00;'>Erro: Usuário ou senha inválida!</p>";
        }
    }

    // Imprimir a mensagem da sessão
    if (isset($_SESSION['msg'])) {
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }

    ?>


    <!-- Inicio do formulário de login -->
    <form method="POST" action="">
        <label>Usuário: </label>
        <input type="text" name="usuario" placeholder="Digite o usuário"><br><br>
        
        <label>Senha: </label>
        <input type="password" name="senha_usuario" placeholder="Digite a senha"><br><br>

        <input type="submit" name="SendLogin" value="Acessar"><br><br>

    </form><br>
    <!-- Fim do formulário de login -->

    <a href="cadastrar.php">Cadastrar</a> - 
    <a href="recuperar_senha.php">Esqueceu a senha?</a><br><br>

    Usuário: cesar@celke.com.br<br>
    Senha: 123456

</body>

</html>