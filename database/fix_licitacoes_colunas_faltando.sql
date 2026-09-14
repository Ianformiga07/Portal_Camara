-- =====================================================================
-- CORREÇÃO PONTUAL — colunas/tabela faltando de migration_licitacoes_tce.sql
--
-- Use este arquivo se, ao acessar licitacoes.php, aparecer o erro:
--   Unknown column 'l.numero_licitacao' in 'field list'
-- Isso indica que gerenciador/database/migration_licitacoes_tce.sql não
-- chegou a rodar por completo no seu banco.
--
-- Versão compatível com versões mais antigas do MySQL (sem o "ADD COLUMN
-- IF NOT EXISTS", que só existe a partir do MySQL 8.0.29). Rode só uma
-- vez: como aqui não tem IF NOT EXISTS nas colunas, rodar de novo depois
-- que elas já existirem vai dar erro "Duplicate column name" — o que é
-- só um sinal de que já está tudo certo, pode ignorar.
-- =====================================================================

USE portal_camara;

ALTER TABLE licitacoes
  ADD COLUMN numero_licitacao VARCHAR(20) NULL AFTER numero_processo,
  ADD COLUMN ano_exercicio YEAR NULL AFTER numero_licitacao,
  ADD COLUMN id_pregoeiro INT UNSIGNED NULL AFTER id_situacao;

CREATE TABLE IF NOT EXISTS licitacoes_participantes (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_licitacao   INT UNSIGNED NOT NULL,
  id_fornecedor  INT UNSIGNED NOT NULL,
  vencedor       TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_licitacao_fornecedor (id_licitacao, id_fornecedor),
  FOREIGN KEY (id_licitacao) REFERENCES licitacoes(id_licitacao) ON DELETE CASCADE,
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB CHARSET=utf8mb4;
