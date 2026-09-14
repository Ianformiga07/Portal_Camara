-- =====================================================================
-- MIGRAÇÃO — ADEQUAÇÃO DE LICITAÇÕES AO PADRÃO MÍNIMO DO TCE-TO
-- Execute depois do portal_camara_mysql.sql e do migration_auth.sql
-- Referência: Cartilha de Transparência Pública - TCE/TO
-- =====================================================================
USE portal_camara;

-- Número da licitação e exercício/ano separados do número do processo
-- (o TCE-TO exige os três campos distintos, cada um pesquisável)
ALTER TABLE licitacoes
  ADD COLUMN numero_licitacao VARCHAR(20) NULL AFTER numero_processo,
  ADD COLUMN ano_exercicio YEAR NULL AFTER numero_licitacao,
  ADD COLUMN id_pregoeiro INT UNSIGNED NULL AFTER id_situacao,
  ADD FOREIGN KEY (id_pregoeiro) REFERENCES servidores(id_servidor);

-- Empresas participantes do certame, com indicação da vencedora
-- (exigência: "Empresa(s) Licitante(s) ... indicando o(s) vencedor(es)")
CREATE TABLE licitacoes_participantes (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_licitacao   INT UNSIGNED NOT NULL,
  id_fornecedor  INT UNSIGNED NOT NULL,
  vencedor       TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uq_licitacao_fornecedor (id_licitacao, id_fornecedor),
  FOREIGN KEY (id_licitacao) REFERENCES licitacoes(id_licitacao) ON DELETE CASCADE,
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB CHARSET=utf8mb4;

-- Situações padronizadas conforme a cartilha do TCE-TO (substitui a lista
-- anterior, que usava termos equivalentes mas não idênticos ao padrão).
-- Observação: como isto reseta os IDs da tabela, licitações já cadastradas
-- ficarão com a situação em branco e precisarão ser reclassificadas —
-- tranquilo nesta fase de testes, sem dados de produção ainda.
DELETE FROM licitacao_situacoes;
ALTER TABLE licitacao_situacoes AUTO_INCREMENT = 1;
INSERT INTO licitacao_situacoes (descricao) VALUES
('Andamento'), ('Realizada(o)'), ('Anulada(o)'), ('Cancelada(o)'),
('Deserta(o)'), ('Fracassada(o)'), ('Rescindida(o)'), ('Revogada(o)'), ('Suspensa(o)');
