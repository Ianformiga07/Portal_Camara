<?php
require_once __DIR__ . '/includes/auth.php';

$busca      = trim($_GET['busca'] ?? '');
$modalidade = $_GET['modalidade'] ?? '';

$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(c.tipo_compra LIKE :busca OR c.descricao LIKE :busca OR f.razao_social LIKE :busca)';
    $parametros['busca'] = '%' . $busca . '%';
}
if ($modalidade !== '') {
    $condicoes[] = 'c.id_modalidade = :modalidade';
    $parametros['modalidade'] = $modalidade;
}
$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

$sql = "SELECT c.tipo_compra, c.descricao, c.data_compra, f.razao_social, f.cnpj_cpf,
               m.descricao AS modalidade, c.valor_total, c.valor_desconto, c.valor_final
        FROM compras c
        LEFT JOIN fornecedores f ON f.id_fornecedor = c.id_fornecedor
        LEFT JOIN licitacao_modalidades m ON m.id_modalidade = c.id_modalidade
        $whereSql
        ORDER BY c.data_compra DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$linhas = $stmt->fetchAll();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="compras_' . date('Y-m-d') . '.csv"');

$saida = fopen('php://output', 'w');
fwrite($saida, "\xEF\xBB\xBF");

fputcsv($saida, ['Tipo/Objeto', 'Descrição', 'Data', 'Fornecedor', 'CNPJ/CPF', 'Modalidade', 'Valor Total', 'Desconto', 'Valor Final'], ';');

foreach ($linhas as $linha) {
    fputcsv($saida, [
        $linha['tipo_compra'],
        $linha['descricao'],
        $linha['data_compra'] ? date('d/m/Y', strtotime($linha['data_compra'])) : '',
        $linha['razao_social'],
        $linha['cnpj_cpf'],
        $linha['modalidade'],
        $linha['valor_total'] !== null ? number_format($linha['valor_total'], 2, ',', '.') : '',
        $linha['valor_desconto'] !== null ? number_format($linha['valor_desconto'], 2, ',', '.') : '',
        $linha['valor_final'] !== null ? number_format($linha['valor_final'], 2, ',', '.') : '',
    ], ';');
}

fclose($saida);
exit;
