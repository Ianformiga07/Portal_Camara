-- =====================================================================
-- CORREÇÃO PONTUAL — colunas/tabela faltando de migration_licitacoes_tce.sql
--
-- Use este arquivo se, ao acessar licitacoes.php, aparecer o erro:
--   Unknown column 'l.numero_licitacao' in 'field list'
-- Isso indica que gerenciador/database/migration_licitacoes_tce.sql não
-- chegou a rodar por completo no seu banco.
--
-- Seguro para rodar mesmo que parte já exista (usa IF NOT EXISTS em
-- tudo) — não apaga nem repete nada que já esteja funcionando.
-- =====================================================================

USE portal_camara;

ALTER TABLE licitacoes
  ADD COLUMN IF NOT EXISTS numero_licitacao VARCHAR(20) NULL AFTER numero_processo,
  ADD COLUMN IF NOT EXISTS ano_exercicio YEAR NULL AFTER numero_licitacao,
  ADD COLUMN IF NOT EXISTS id_pregoeiro INT UNSIGNED NULL AFTER id_situacao;

CREATE TABLE IF NOT EXISTS licitacoes_participantes (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_licitacao   INT UNSIGNED NOT NULL,
  id_fornecedor  INT UNSIGNED NOT NULL,
  vencedor       TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_licitacao_fornecedor (id_licitacao, id_fornecedor),
  FOREIGN KEY (id_licitacao) REFERENCES licitacoes(id_licitacao) ON DELETE CASCADE,
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB CHARSET=utf8mb4;
