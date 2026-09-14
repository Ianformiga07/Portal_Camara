-- =====================================================================
-- HOTFIX — resolve o erro:
--   "Table 'portal_camara.manifestacoes' doesn't exist" (gerenciador/index.php)
--
-- Mesmo caso de `contratos`: o script base que deveria ter criado a
-- tabela `manifestacoes` (usada pelo módulo de Ouvidoria) nunca chegou
-- a ser executado neste banco. Cria o que falta a partir das colunas
-- realmente usadas em ouvidoria.php, ouvidoria-consultar.php,
-- gerenciador/manifestacoes.php, manifestacao-form.php e
-- relatorio-ouvidoria.php.
--
-- Idempotente: pode rodar mais de uma vez sem duplicar nada.
-- =====================================================================
USE portal_camara;

-- ---------------------------------------------------------------------
-- 1) tipos de manifestação (Denúncia, Reclamação, Elogio, Sugestão...)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tipos_manifestacao (
  id_tipo_manifestacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao             VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO tipos_manifestacao (descricao)
SELECT d FROM (
  SELECT 'Denúncia' AS d UNION ALL SELECT 'Reclamação'
  UNION ALL SELECT 'Elogio' UNION ALL SELECT 'Sugestão'
  UNION ALL SELECT 'Solicitação de Informação'
) AS novo
WHERE NOT EXISTS (
  SELECT 1 FROM tipos_manifestacao t WHERE t.descricao = novo.d COLLATE utf8mb4_unicode_ci
);

-- ---------------------------------------------------------------------
-- 2) tabela `manifestacoes` (Ouvidoria)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS manifestacoes (
  id_manifestacao     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tipo_manifestacao INT UNSIGNED NULL,
  protocolo           VARCHAR(30) NOT NULL UNIQUE,
  anonimo             TINYINT(1) NOT NULL DEFAULT 0,
  forma_recebimento   VARCHAR(30) NULL,
  nome                VARCHAR(150) NULL,
  cpf                 VARCHAR(14) NULL,
  email               VARCHAR(150) NULL,
  telefone            VARCHAR(20) NULL,
  anexo               VARCHAR(150) NULL,
  texto               TEXT NOT NULL,
  respondida          TINYINT(1) NOT NULL DEFAULT 0,
  resposta            TEXT NULL,
  respondido_por      INT UNSIGNED NULL,
  respondido_em       DATETIME NULL,
  criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

-- FOREIGN KEYs (id_tipo_manifestacao -> tipos_manifestacao,
-- respondido_por -> servidores) deixadas de fora propositalmente,
-- mesmo motivo do hotfix de licitacoes/contratos: evitar falha por
-- diferença de tipo/collation com tabelas já existentes no seu banco.
