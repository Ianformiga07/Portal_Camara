<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['id_servidor'])) {
    header('Location: index.php');
    exit;
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cpf   = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($cpf === '' || $senha === '') {
        $erro = 'Informe CPF e senha.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id_servidor, nome_completo, senha_hash, nivel_acesso, ativo, foto_perfil,
                    (SELECT descricao FROM cargos c WHERE c.id_cargo = s.id_cargo) AS cargo
             FROM servidores s
             WHERE cpf = :cpf
             LIMIT 1'
        );
        $stmt->execute(['cpf' => $cpf]);
        $servidor = $stmt->fetch();

        if (!$servidor) {
            $erro = 'CPF ou senha inválidos.';
        } elseif ((int) $servidor['ativo'] !== 1) {
            $erro = 'Este usuário está inativo. Procure o administrador do sistema.';
        } elseif (empty($servidor['senha_hash']) || !password_verify($senha, $servidor['senha_hash'])) {
            $erro = 'CPF ou senha inválidos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['id_servidor']   = $servidor['id_servidor'];
            $_SESSION['nome_completo'] = $servidor['nome_completo'];
            $_SESSION['nivel_acesso']  = $servidor['nivel_acesso'];
            $_SESSION['foto_perfil']   = $servidor['foto_perfil'];
            $_SESSION['cargo']         = $servidor['cargo'];
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acesso ao Gerenciador — Câmara Municipal de Ananás</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="pagina-login">

  <form class="caixa-login" method="post" action="login.php" autocomplete="off">
    <h1>Gerenciador Legislativo</h1>
    <p class="subtitulo">Câmara Municipal de Ananás — TO</p>

    <?php if ($erro): ?>
      <div class="alerta alerta-erro"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <div class="campo">
      <label for="cpf">CPF</label>
      <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required autofocus>
    </div>

    <div class="campo">
      <label for="senha">Senha</label>
      <input type="password" id="senha" name="senha" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-primario"><i class="fa-solid fa-right-to-bracket"></i> Entrar</button>

    <a href="../index.php" class="voltar-site">↩ Voltar para o site</a>
  </form>

  <script>
    // Máscara simples de CPF, sem depender de nenhuma lib externa
    document.getElementById('cpf').addEventListener('input', function (e) {
      let v = e.target.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      e.target.value = v;
    });
  </script>
</body>
</html>
