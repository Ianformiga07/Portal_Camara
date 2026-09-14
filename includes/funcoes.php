<?php
/**
 * Funções pequenas de formatação, sem depender da extensão intl
 * (que pode não estar habilitada em todo servidor de hospedagem).
 */

function formatarDataExtenso(?string $data): string
{
    if (!$data) {
        return '';
    }
    $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
    ];
    $timestamp = strtotime($data);
    return date('d', $timestamp) . ' de ' . $meses[(int) date('n', $timestamp)] . ' de ' . date('Y', $timestamp);
}

function resumirTexto(?string $texto, int $tamanho = 220): string
{
    $texto = trim(strip_tags($texto ?? ''));
    if (mb_strlen($texto) <= $tamanho) {
        return $texto;
    }
    return mb_substr($texto, 0, $tamanho) . '...';
}

/**
 * Mascara um CPF para exibição pública (LGPD), mantendo só o miolo visível,
 * no mesmo padrão usado pelo Portal da Transparência (ex: xxx.264.141-xx).
 */
function mascararCpf(?string $cpf): string
{
    $numeros = preg_replace('/\D/', '', $cpf ?? '');
    if (strlen($numeros) !== 11) {
        return '—';
    }
    return 'xxx.' . substr($numeros, 3, 3) . '.' . substr($numeros, 6, 3) . '-xx';
}

/**
 * Gera um protocolo único (ex: OUV20260830-4821) verificando contra a
 * coluna "protocolo" da tabela informada até achar um livre.
 */
function gerarProtocoloUnico(PDO $pdo, string $tabela, string $prefixo): string
{
    do {
        $candidato = $prefixo . date('Ymd') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("SELECT 1 FROM {$tabela} WHERE protocolo = :p");
        $stmt->execute(['p' => $candidato]);
        $existe = $stmt->fetch();
    } while ($existe);

    return $candidato;
}
