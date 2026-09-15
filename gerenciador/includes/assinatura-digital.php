<?php
/**
 * Assinatura digital / integridade do Diário Oficial.
 *
 * Se houver um certificado ICP-Brasil configurado em
 * config/assinatura.php, os PDFs são assinados digitalmente (padrão
 * PAdES, via TCPDF + OpenSSL). Sem certificado configurado, o sistema
 * segue funcionando normalmente — só que apenas com hash SHA-256 de
 * integridade, calculado sempre, com ou sem certificado.
 */

function configAssinatura(): array
{
    $caminho = __DIR__ . '/../config/assinatura.php';
    $CERTIFICADO_PFX_PATH = '';
    $CERTIFICADO_SENHA = '';
    $CERTIFICADO_TITULAR = 'Câmara Municipal de Ananás';

    if (is_file($caminho)) {
        require $caminho;
    }

    return [
        'pfx'      => $CERTIFICADO_PFX_PATH,
        'senha'    => $CERTIFICADO_SENHA,
        'titular'  => $CERTIFICADO_TITULAR,
    ];
}

function certificadoDisponivel(): bool
{
    $cfg = configAssinatura();
    return $cfg['pfx'] !== '' && is_file($cfg['pfx']) && $cfg['senha'] !== '';
}

/**
 * Lê o .pfx e devolve o certificado e a chave privada já em memória
 * (formato PEM), prontos para TCPDF::setSignature(). Nunca grava a
 * chave decifrada em disco.
 *
 * @return array{cert: string, pkey: string}|null null se falhar (senha
 *         errada, arquivo corrompido etc.)
 */
function lerCertificadoPfx(): ?array
{
    $cfg = configAssinatura();
    if (!certificadoDisponivel()) {
        return null;
    }

    $conteudoPfx = file_get_contents($cfg['pfx']);
    if ($conteudoPfx === false) {
        return null;
    }

    $certs = [];
    if (!openssl_pkcs12_read($conteudoPfx, $certs, $cfg['senha'])) {
        return null;
    }

    return ['cert' => $certs['cert'], 'pkey' => $certs['pkey']];
}
