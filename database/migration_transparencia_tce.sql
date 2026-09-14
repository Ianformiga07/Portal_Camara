-- =====================================================================
-- MIGRAÇÃO — ADEQUAÇÃO AO MODELO DE PORTAL DA TRANSPARÊNCIA DO TCE-TO
-- Execute depois dos scripts anteriores
-- =====================================================================
USE portal_camara;

-- Campos exigidos pela cartilha em "Quadro de Servidores" que ainda não existiam
ALTER TABLE servidores
  ADD COLUMN tipo_vinculo VARCHAR(50) NULL COMMENT 'Efetivo, Comissionado, Eletivo, Estagiário, etc.' AFTER id_escolaridade,
  ADD COLUMN data_desligamento DATE NULL AFTER data_admissao,
  ADD COLUMN jornada_semanal VARCHAR(20) NULL COMMENT 'ex: 40h semanais' AFTER data_desligamento;

-- Convênios e Instrumentos Congêneres (categoria exigida em Compras e Licitações)
CREATE TABLE convenios (
  id_convenio        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  numero             VARCHAR(20) NULL,
  ano_exercicio      YEAR NULL,
  tipo               VARCHAR(80) NULL COMMENT 'Convênio, Acordo de Cooperação Técnica, Termo de Adesão, etc.',
  categoria          ENUM('Desembolso financeiro','Recebimento financeiro','Não envolveu recursos financeiros') NULL,
  valor              DECIMAL(15,2) NULL,
  numero_processo    VARCHAR(50) NULL,
  participe          VARCHAR(200) NULL COMMENT 'nome da outra parte do convênio',
  objeto             TEXT NULL,
  situacao           ENUM('Vigente','Encerrado') NULL,
  data_inicio        DATE NULL,
  data_fim           DATE NULL,
  fundamentacao_legal VARCHAR(255) NULL,
  arquivo            VARCHAR(150) NULL,
  status             TINYINT(1) NOT NULL DEFAULT 1,
  criado_em          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

-- Leis Orçamentárias (PPA, LDO, LOA) — reaproveitam a estrutura de "documentos"
INSERT INTO categorias_documentos (descricao, slug) VALUES
('PPA - Plano Plurianual', 'ppa'),
('LDO - Lei de Diretrizes Orçamentárias', 'ldo'),
('LOA - Lei Orçamentária Anual', 'loa');
