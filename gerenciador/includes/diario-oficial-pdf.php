<?php
/**
 * Monta o PDF de uma edição do Diário Oficial (capa + sumário + cada
 * ato em sequência) e, se houver certificado configurado, assina
 * digitalmente durante a geração — a assinatura TCPDF precisa ser
 * definida antes do Output(), não dá pra "carimbar" depois num PDF
 * já pronto.
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/assinatura-digital.php';

/**
 * @param array $edicao linha de diario_oficial_edicoes
 * @param array $atos   linhas de diario_oficial_atos, já ordenadas por `ordem`
 * @return array{arquivo: string, caminho: string, hash: string, assinado: bool}
 */
function gerarPdfEdicaoDiarioOficial(array $edicao, array $atos): array
{
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Portal da Câmara Municipal de Ananás');
    $pdf->SetAuthor('Câmara Municipal de Ananás');
    $pdf->SetTitle('Diário Oficial nº ' . $edicao['numero_edicao'] . '/' . $edicao['ano_exercicio']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(18, 18, 18);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->setFooterFont(['dejavusans', '', 8]);

    // --- Capa ---
    $pdf->AddPage();
    $pdf->Ln(30);
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 14, 'DIÁRIO OFICIAL', 0, 1, 'C');
    $pdf->SetFont('dejavusans', '', 13);
    $pdf->Cell(0, 8, 'Câmara Municipal de Ananás — TO', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Edição nº ' . $edicao['numero_edicao'] . '/' . $edicao['ano_exercicio'], 0, 1, 'C');
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 8, 'Data de publicação: ' . date('d/m/Y', strtotime($edicao['data_edicao'])), 0, 1, 'C');

    // --- Sumário ---
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->Cell(0, 10, 'SUMÁRIO', 0, 1, 'L');
    $pdf->Ln(2);
    $pdf->SetFont('dejavusans', '', 10);
    if (empty($atos)) {
        $pdf->MultiCell(0, 6, 'Nenhum ato incluído nesta edição.', 0, 'L');
    }
    foreach ($atos as $i => $ato) {
        $linha = ($i + 1) . '. ' . $ato['tipo'];
        if (!empty($ato['numero'])) {
            $linha .= ' nº ' . $ato['numero'];
        }
        if (!empty($ato['titulo'])) {
            $linha .= ' — ' . $ato['titulo'];
        }
        $pdf->MultiCell(0, 6, $linha, 0, 'L');
    }

    // --- Cada ato em sua própria página ---
    foreach ($atos as $ato) {
        $pdf->AddPage();
        $pdf->SetFont('dejavusans', 'B', 13);
        $cabecalho = $ato['tipo'];
        if (!empty($ato['numero'])) {
            $cabecalho .= ' nº ' . $ato['numero'];
        }
        $pdf->MultiCell(0, 8, $cabecalho, 0, 'L');

        if (!empty($ato['titulo'])) {
            $pdf->SetFont('dejavusans', 'B', 11);
            $pdf->MultiCell(0, 6, $ato['titulo'], 0, 'L');
        }
        $pdf->Ln(2);
        $pdf->SetFont('dejavusans', '', 10);
        $texto = $ato['texto'] !== null && $ato['texto'] !== ''
            ? $ato['texto']
            : '(sem texto detalhado cadastrado — consulte o documento original anexado no módulo correspondente)';
        $pdf->MultiCell(0, 5.5, $texto, 0, 'J');
    }

    // --- Assinatura digital (se houver certificado configurado) ---
    $assinado = false;
    if (certificadoDisponivel()) {
        $certs = lerCertificadoPfx();
        if ($certs !== null) {
            $cfg = configAssinatura();
            $pdf->setSignature($certs['cert'], $certs['pkey'], '', '', 2, [
                'Name'     => $cfg['titular'],
                'Location' => 'Ananás - TO',
                'Reason'   => 'Publicação oficial do Diário Oficial nº ' . $edicao['numero_edicao'] . '/' . $edicao['ano_exercicio'],
            ]);
            $pdf->setSignatureAppearance($pdf->getPageWidth() - 55, $pdf->getPageHeight() - 25, 45, 15);
            $assinado = true;
        }
    }

    $pastaDestino = __DIR__ . '/../assets/uploads/diario-oficial/';
    if (!is_dir($pastaDestino)) {
        mkdir($pastaDestino, 0755, true);
    }
    $nomeArquivo = 'diario-oficial-' . preg_replace('/[^A-Za-z0-9\-]/', '', $edicao['numero_edicao'])
        . '-' . $edicao['ano_exercicio'] . '-' . time() . '.pdf';
    $caminhoCompleto = $pastaDestino . $nomeArquivo;

    $pdf->Output($caminhoCompleto, 'F');

    return [
        'arquivo'  => $nomeArquivo,
        'caminho'  => $caminhoCompleto,
        'hash'     => hash_file('sha256', $caminhoCompleto),
        'assinado' => $assinado,
    ];
}
