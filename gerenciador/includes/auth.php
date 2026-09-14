<?php
/**
 * Controle de sessão. Inclua este arquivo no topo de qualquer página
 * que exija login. Ele garante a sessão, valida se o usuário está
 * logado e carrega os dados básicos do servidor logado em $usuarioLogado.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function usuarioEstaLogado(): bool
{
    return !empty($_SESSION['id_servidor']);
}

function exigirLogin(): void
{
    if (!usuarioEstaLogado()) {
        header('Location: login.php');
        exit;
    }
}

function usuarioLogado(): ?array
{
    if (!usuarioEstaLogado()) {
        return null;
    }
    return [
        'id_servidor'   => $_SESSION['id_servidor'],
        'nome_completo' => $_SESSION['nome_completo'],
        'nivel_acesso'  => $_SESSION['nivel_acesso'],
        'foto_perfil'   => $_SESSION['foto_perfil'],
        'cargo'         => $_SESSION['cargo'],
    ];
}

/** Nível 1 e 2 têm acesso à área de administração (gestão de usuários). */
function ehAdministrador(): bool
{
    return usuarioEstaLogado() && (int) $_SESSION['nivel_acesso'] <= 2;
}

exigirLogin();
$usuario = usuarioLogado();
