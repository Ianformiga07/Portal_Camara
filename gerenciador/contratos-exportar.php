<?php
require_once __DIR__ . '/includes/auth.php';

$busca    = trim($_GET['busca'] ?? '');
$situacao = $_GET['situacao'] ?? '';

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(c.numero_contrato LIKE :busca OR c.objeto LIKE :busca OR f.razao_social LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($situacao !== '') {
    $condicoes[] = 'c.id_situacao = :situacao';
    $parametros['situacao'] = $situacao;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$sql = "SELECT c.numero_contrato, c.ano_exercicio, f.razao_social, f.cnpj_cpf, c.objeto,
               c.inicio_vigencia, c.fim_vigencia, c.data_publicacao_extrato, c.valor_estimado,
               s.descricao AS situacao, fis.nome_completo AS fiscal, ges.nome_completo AS gestor
        FROM contratos c
        LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
        LEFT JOIN contrato_situacoes s ON s.id_situacao = c.id_situacao
        LEFT JOIN servidores fis ON fis.id_servidor = c.id_fiscal
        LEFT JOIN servidores ges ON ges.id_servidor = c.id_gestor
        $whereSql
        ORDER BY c.inicio_vigencia DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$linhas = $stmt->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="contratos_' . date('Y-m-d') . '.csv"');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF");

fputcsv($saida, [
    'Número', 'Exercício', 'Contratada', 'CNPJ/CPF', 'Objeto', 'Início Vigência', 'Fim Vigência',
    'Publicação Extrato', 'Valor', 'Situação', 'Fiscal', 'Gestor',
], ';');

foreach ($linhas as $linha) {
    fputcsv($saida, [
        $linha['numero_contrato'],
        $linha['ano_exercicio'],
        $linha['razao_social'],
        $linha['cnpj_cpf'],
        $linha['objeto'],
        $linha['inicio_vigencia'] ? date('d/m/Y', strtotime($linha['inicio_vigencia'])) : '',
        $linha['fim_vigencia'] ? date('d/m/Y', strtotime($linha['fim_vigencia'])) : '',
        $linha['data_publicacao_extrato'] ? date('d/m/Y', strtotime($linha['data_publicacao_extrato'])) : '',
        $linha['valor_estimado'] !== null ? number_format($linha['valor_estimado'], 2, ',', '.') : '',
        $linha['situacao'],
        $linha['fiscal'],
        $linha['gestor'],
    ], ';');
}

fclose($saida);
exit;
