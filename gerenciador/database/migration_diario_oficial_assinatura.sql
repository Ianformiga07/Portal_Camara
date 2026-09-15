-- =====================================================================
-- MÓDULO — Montagem e assinatura digital do Diário Oficial
--
-- Cria as tabelas usadas pelo novo módulo "Diário Oficial" do
-- gerenciador (diario-oficial-edicoes.php e diario-oficial-editor.php):
-- cada "edição" reúne vários "atos" (decretos, portarias, extratos de
-- licitação/contrato etc., reunidos automaticamente do que já está
-- cadastrado no período, ou digitados avulsos) e gera um único PDF,
-- opcionalmente assinado digitalmente (ICP-Brasil, se houver
-- certificado configurado em gerenciador/config/assinatura.php) ou com
-- hash SHA-256 de integridade (sempre, mesmo sem certificado).
--
-- Idempotente (CREATE TABLE IF NOT EXISTS).
-- =====================================================================
USE portal_camara;

CREATE TABLE IF NOT EXISTS diario_oficial_edicoes (
  id_edicao      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  numero_edicao  VARCHAR(20) NOT NULL,
  ano_exercicio  YEAR NOT NULL,
  data_edicao    DATE NOT NULL,
  status         ENUM('rascunho','publicada') NOT NULL DEFAULT 'rascunho',
  arquivo_pdf    VARCHAR(150) NULL,
  hash_sha256    CHAR(64) NULL,
  assinado_icp   TINYINT(1) NOT NULL DEFAULT 0,
  assinado_em    DATETIME NULL,
  id_documento   INT UNSIGNED NULL COMMENT 'preenchido ao publicar: linha correspondente em documentos',
  criado_por     INT UNSIGNED NULL COMMENT 'id_servidor de quem criou a edição',
  criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_numero_ano (numero_edicao, ano_exercicio)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS diario_oficial_atos (
  id_ato         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_edicao      INT UNSIGNED NOT NULL,
  ordem          INT UNSIGNED NOT NULL DEFAULT 0,
  origem         ENUM('auto','manual') NOT NULL DEFAULT 'manual',
  origem_tabela  VARCHAR(30) NULL COMMENT '"documentos", "licitacoes" ou "contratos" quando origem = auto',
  origem_id      INT UNSIGNED NULL COMMENT 'PK na tabela de origem, para evitar duplicar ao reunir de novo',
  tipo           VARCHAR(60) NOT NULL COMMENT 'ex: Decreto, Portaria, Extrato de Contrato, Ato Avulso',
  numero         VARCHAR(20) NULL,
  titulo         VARCHAR(255) NULL,
  texto          MEDIUMTEXT NULL,
  criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_origem (id_edicao, origem_tabela, origem_id),
  FOREIGN KEY (id_edicao) REFERENCES diario_oficial_edicoes(id_edicao) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;
