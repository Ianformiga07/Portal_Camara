<?php
$page_title = 'Receitas e Despesas — Câmara Municipal de Ananás';
$tituloPaginaInterna = 'Receitas e Despesas';
$subtituloPaginaInterna = 'Execução orçamentária da Câmara Municipal de Ananás.';
$itensPendentes = [
  ['titulo' => 'Empenhos', 'desc' => 'Número, credor, data, rubrica, fase e valor de cada empenho.'],
  ['titulo' => 'Liquidações', 'desc' => 'Liquidações vinculadas a cada empenho.'],
  ['titulo' => 'Pagamentos', 'desc' => 'Pagamentos efetuados, com credor, valor e data.'],
  ['titulo' => 'Diárias', 'desc' => 'Diárias pagas a servidores, com destino e período.'],
  ['titulo' => 'Gastos com Combustível', 'desc' => 'Despesas com combustível por credor e período.'],
  ['titulo' => 'Receitas Arrecadadas', 'desc' => 'Receitas arrecadadas por rubrica e mês.'],
  ['titulo' => 'Informações Consolidadas', 'desc' => 'Consolidado de empenhos, liquidações e pagamentos por credor.'],
  ['titulo' => 'Informações Consolidadas — Restos a Pagar', 'desc' => 'Consolidado de restos a pagar.'],
  ['titulo' => 'Ordem Cronológica de Pagamentos', 'desc' => 'Ordem cronológica de pagamentos, conforme normativa do TCE-TO.'],
  ['titulo' => 'Despesas Fixadas', 'desc' => 'Dotação inicial por rubrica, fonte, programa e função.'],
];
include 'includes/pendente.php';
