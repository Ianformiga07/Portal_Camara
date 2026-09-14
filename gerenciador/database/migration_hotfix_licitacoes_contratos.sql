-- =====================================================================
-- HOTFIX — resolve os erros:
--   "Unknown column 'l.status'"           (licitacoes.php)
--   "Table 'portal_camara.contratos' doesn't exist" (contratos.php)
--
-- O script base que deveria ter criado a tabela `contratos` e a coluna
-- `status` de `licitacoes` nunca chegou a ser executado neste banco (os
-- outros arquivos em gerenciador/database/ só fazem ALTER TABLE nessas
-- estruturas, assumindo que elas já existiam). Este script cria o que
-- está faltando. Pode ser executado quantas vezes forem necessárias —
-- todos os comandos são condicionais (IF NOT EXISTS / INSERT IGNORE) e
-- não afetam dados já existentes.
--
-- Requer MySQL 8.0.29+ ou MariaDB 10.0+ (suporte a "ADD COLUMN IF NOT
-- EXISTS"). Se o seu servidor for mais antigo e o comando abaixo der
-- erro de sintaxe, remova o "IF NOT EXISTS" da linha do ALTER TABLE.
-- =====================================================================
USE portal_camara;

-- ---------------------------------------------------------------------
-- 1) coluna que faltava em `licitacoes`
-- ---------------------------------------------------------------------
ALTER TABLE licitacoes
  ADD COLUMN IF NOT EXISTS status TINYINT(1) NOT NULL DEFAULT 1;

-- ---------------------------------------------------------------------
-- 2) tabelas de apoio usadas por gerenciador/contrato-form.php
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tipos_contratacao (
  id_tipo_contratacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao            VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

-- INSERT ... WHERE NOT EXISTS (em vez de INSERT IGNORE) porque, se esta
-- tabela já existir de uma execução parcial anterior do
-- migration_contratos.sql, ela pode não ter a UNIQUE em `descricao`.
INSERT INTO tipos_contratacao (descricao)
SELECT d FROM (
  SELECT 'Licitação' AS d UNION ALL SELECT 'Dispensa de Licitação'
  UNION ALL SELECT 'Inexigibilidade de Licitação'
  UNION ALL SELECT 'Adesão a Ata de Registro de Preços'
  UNION ALL SELECT 'Convênio'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM tipos_contratacao t WHERE t.descricao = novo.d);

CREATE TABLE IF NOT EXISTS contrato_situacoes (
  id_situacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao   VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO contrato_situacoes (descricao)
SELECT d FROM (SELECT 'Vigente' AS d UNION ALL SELECT 'Encerrado(a)') AS novo
WHERE NOT EXISTS (SELECT 1 FROM contrato_situacoes s WHERE s.descricao = novo.d);

-- ---------------------------------------------------------------------
-- 3) tabela `contratos` (nunca havia sido criada)
--    colunas conforme usadas em gerenciador/contrato-form.php,
--    gerenciador/contratos.php, contratos.php e contrato-detalhe.php
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contratos (
  id_contrato             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_licitacao            INT UNSIGNED NULL,
  id_tipo_contratacao     INT UNSIGNED NULL,
  id_finalidade           INT UNSIGNED NULL,
  numero_contrato         VARCHAR(20) NULL,
  ano_exercicio           YEAR NULL,
  inicio_vigencia         DATE NULL,
  fim_vigencia            DATE NULL,
  data_publicacao_extrato DATE NULL,
  valor_estimado          DECIMAL(15,2) NULL,
  id_fornecedor           INT UNSIGNED NULL,
  id_fiscal               INT UNSIGNED NULL,
  id_gestor               INT UNSIGNED NULL,
  id_situacao             INT UNSIGNED NULL,
  objeto                  TEXT NULL,
  arquivo                 VARCHAR(150) NULL,
  status                  TINYINT(1) NOT NULL DEFAULT 1,
  criado_em               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

-- Observação: as FOREIGN KEYs (id_licitacao -> licitacoes, id_fornecedor
-- -> fornecedores, id_fiscal/id_gestor -> servidores, id_situacao ->
-- contrato_situacoes, id_tipo_contratacao -> tipos_contratacao,
-- id_finalidade -> licitacao_finalidades) foram deixadas de fora
-- propositalmente neste hotfix para garantir que o script rode sem
-- falhar por diferença de tipo com as tabelas já existentes no seu
-- banco. Se quiser, adicione-as manualmente depois de conferir os
-- tipos das colunas correspondentes.

-- ---------------------------------------------------------------------
-- 4) tabelas dependentes de `contratos` / `licitacoes`
--    (podem já existir se migration_contratos.sql / migration_licitacoes_tce.sql
--    tiverem parado no meio da execução — por isso o IF NOT EXISTS)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contratos_aditivos (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_contrato      INT UNSIGNED NOT NULL,
  numero_aditivo   VARCHAR(20) NULL,
  data_aditivo     DATE NULL,
  descricao        VARCHAR(255) NULL,
  arquivo          VARCHAR(150) NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_contrato) REFERENCES contratos(id_contrato) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS licitacoes_participantes (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_licitacao   INT UNSIGNED NOT NULL,
  id_fornecedor  INT UNSIGNED NOT NULL,
  vencedor       TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_licitacao_fornecedor (id_licitacao, id_fornecedor)
) ENGINE=InnoDB CHARSET=utf8mb4;
