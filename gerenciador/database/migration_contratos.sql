-- =====================================================================
-- MIGRAÇÃO — MÓDULO DE CONTRATOS (ajustado ao padrão TCE-TO)
-- Execute depois dos scripts anteriores
-- =====================================================================
USE portal_camara;

CREATE TABLE contrato_situacoes (
  id_situacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao   VARCHAR(20) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO contrato_situacoes (descricao) VALUES ('Vigente'), ('Encerrado(a)');

ALTER TABLE contratos
  ADD COLUMN ano_exercicio YEAR NULL AFTER numero_contrato,
  ADD COLUMN id_finalidade INT UNSIGNED NULL AFTER id_tipo_contratacao,
  ADD COLUMN data_publicacao_extrato DATE NULL AFTER fim_vigencia,
  ADD COLUMN id_gestor INT UNSIGNED NULL AFTER id_fiscal,
  ADD COLUMN id_situacao INT UNSIGNED NULL AFTER id_gestor,
  ADD FOREIGN KEY (id_finalidade) REFERENCES licitacao_finalidades(id_finalidade),
  ADD FOREIGN KEY (id_gestor) REFERENCES servidores(id_servidor),
  ADD FOREIGN KEY (id_situacao) REFERENCES contrato_situacoes(id_situacao);

-- Termos aditivos (o TCE-TO exige que estejam disponíveis junto ao contrato original)
CREATE TABLE contratos_aditivos (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_contrato      INT UNSIGNED NOT NULL,
  numero_aditivo   VARCHAR(20) NULL,
  data_aditivo     DATE NULL,
  descricao        VARCHAR(255) NULL,
  arquivo          VARCHAR(150) NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_contrato) REFERENCES contratos(id_contrato) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;
