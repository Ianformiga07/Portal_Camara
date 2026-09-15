<?php
/**
 * Configuração da assinatura digital ICP-Brasil do Diário Oficial.
 *
 * COMO USAR:
 *   1. Copie este arquivo para "assinatura.php" (mesma pasta).
 *   2. Coloque o certificado .pfx em algum lugar FORA da pasta
 *      "assets/uploads" (que é pública) — esta pasta "config" já é
 *      protegida pelo .htaccess que vem junto, mas prefira um caminho
 *      absoluto fora do projeto se possível, ex: em produção,
 *      C:\certificados\camara.pfx.
 *   3. Preencha o caminho e a senha abaixo.
 *
 * Enquanto "assinatura.php" não existir (ou os campos abaixo estiverem
 * vazios), o Diário Oficial é publicado normalmente, só que sem
 * assinatura digital — apenas com o hash SHA-256 de integridade, que
 * não depende de certificado nenhum.
 *
 * "assinatura.php" (o arquivo real, com a senha) está no .gitignore —
 * nunca é enviado ao repositório Git.
 */

// Caminho completo para o arquivo .pfx do certificado ICP-Brasil (A1).
$CERTIFICADO_PFX_PATH = '';

// Senha do certificado.
$CERTIFICADO_SENHA = '';

// Nome exibido na assinatura (normalmente o nome da Câmara ou do
// responsável legal pelo Diário Oficial).
$CERTIFICADO_TITULAR = 'Câmara Municipal de Ananás';
