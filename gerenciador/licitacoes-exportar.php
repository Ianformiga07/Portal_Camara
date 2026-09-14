<?php
require_once __DIR__ . '/includes/auth.php';

$busca      = trim($_GET['busca'] ?? '');
$modalidade = $_GET['modalidade'] ?? '';
$situacao   = $_GET['situacao'] ?? '';

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(l.numero_processo LIKE :busca OR l.objeto LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($modalidade !== '') {
    $condicoes[] = 'l.id_modalidade = :modalidade';
    $parametros['modalidade'] = $modalidade;
}
if ($situacao !== '') {
    $condicoes[] = 'l.id_situacao = :situacao';
    $parametros['situacao'] = $situacao;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$sql = "SELECT l.numero_processo, l.numero_licitacao, l.ano_exercicio, o.descricao AS orgao,
               m.descricao AS modalidade, l.objeto, l.data_abertura, l.data_homologacao,
               l.valor_estimado, l.valor_despesa, s.descricao AS situacao,
               sv.nome_completo AS pregoeiro
        FROM licitacoes l
        LEFT JOIN orgaos o ON o.id_orgao = l.id_orgao
        LEFT JOIN licitacao_modalidades m ON m.id_modalidade = l.id_modalidade
        LEFT JOIN licitacao_situacoes s ON s.id_situacao = l.id_situacao
        LEFT JOIN servidores sv ON sv.id_servidor = l.id_pregoeiro
        $whereSql
        ORDER BY l.data_abertura DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$linhas = $stmt->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="licitacoes_' . date('Y-m-d') . '.csv"');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF"); // BOM UTF-8, para abrir corretamente no Excel

fputcsv($saida, [
    'Processo', 'Número Licitação', 'Exercício', 'Órgão', 'Modalidade', 'Objeto',
    'Data Abertura', 'Data Homologação', 'Valor Estimado', 'Valor Despesa', 'Situação', 'Pregoeiro',
], ';');

foreach ($linhas as $linha) {
    fputcsv($saida, [
        $linha['numero_processo'],
        $linha['numero_licitacao'],
        $linha['ano_exercicio'],
        $linha['orgao'],
        $linha['modalidade'],
        $linha['objeto'],
        $linha['data_abertura'] ? date('d/m/Y', strtotime($linha['data_abertura'])) : '',
        $linha['data_homologacao'] ? date('d/m/Y', strtotime($linha['data_homologacao'])) : '',
        $linha['valor_estimado'] !== null ? number_format($linha['valor_estimado'], 2, ',', '.') : '',
        $linha['valor_despesa'] !== null ? number_format($linha['valor_despesa'], 2, ',', '.') : '',
        $linha['situacao'],
        $linha['pregoeiro'],
    ], ';');
}

fclose($saida);
exit;
