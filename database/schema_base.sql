-- =====================================================================
-- SCHEMA BASE DO PORTAL_CAMARA (RECONSTRUÍDO)
--
-- Este arquivo cria as tabelas centrais do sistema (portal público +
-- gerenciador) que nunca chegaram a ser versionadas no repositório —
-- só os scripts de MIGRAÇÃO incremental (ALTER TABLE / CREATE TABLE de
-- tabelas novas) foram commitados, e todos eles pressupõem que essas
-- tabelas de base já existem. Sem este arquivo, o banco fica sem a
-- tabela "licitacoes" (e várias outras), causando erros como:
--   SQLSTATE[42S02]: Base table or view not found: 1146
--   Table 'portal_camara.licitacoes' doesn't exist
--
-- Reconstruído lendo todo o código PHP do projeto (listas de colunas de
-- INSERT/UPDATE, campos de formulário, cláusulas WHERE/SELECT) e
-- cruzando com os scripts de migração existentes, para que as colunas
-- que essas migrações adicionam via ALTER TABLE NÃO fiquem duplicadas
-- aqui.
--
-- ORDEM DE EXECUÇÃO COMPLETA (rode todos, nesta ordem, num banco novo):
--   1. database/schema_base.sql                        (este arquivo)
--   2. gerenciador/database/migration_auth.sql
--   3. gerenciador/database/migration_licitacoes_tce.sql
--   4. gerenciador/database/migration_contratos.sql
--   5. gerenciador/database/migration_fix_contratos_objeto.sql
--   6. database/migration_transparencia_tce.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS portal_camara CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portal_camara;

-- =====================================================================
-- 1) TABELAS DE APOIO (RH)
-- =====================================================================

CREATE TABLE IF NOT EXISTS cargos (
  id_cargo   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao  VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS departamentos (
  id_departamento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao       VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS escolaridades (
  id_escolaridade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao       VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

-- =====================================================================
-- 2) SERVIDORES (base — sem as colunas que migration_auth.sql e
--    migration_transparencia_tce.sql adicionam depois: senha_hash,
--    nivel_acesso, tipo_vinculo, data_desligamento, jornada_semanal)
-- =====================================================================

CREATE TABLE IF NOT EXISTS servidores (
  id_servidor      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_completo    VARCHAR(150) NOT NULL,
  cpf              VARCHAR(11) NOT NULL UNIQUE,
  data_nascimento  DATE NULL,
  sexo             ENUM('M','F') NULL,
  foto_perfil      VARCHAR(150) NULL,
  id_cargo         INT UNSIGNED NULL,
  id_departamento  INT UNSIGNED NULL,
  id_escolaridade  INT UNSIGNED NULL,
  matricula        VARCHAR(20) NULL,
  data_admissao    DATE NULL,
  celular          VARCHAR(15) NULL,
  email            VARCHAR(100) NULL,
  ativo            TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_cargo) REFERENCES cargos(id_cargo),
  FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento),
  FOREIGN KEY (id_escolaridade) REFERENCES escolaridades(id_escolaridade)
) ENGINE=InnoDB CHARSET=utf8mb4;

-- =====================================================================
-- 3) INSTITUCIONAL / LEGISLATIVO
-- =====================================================================

CREATE TABLE IF NOT EXISTS mandatos_eletivos (
  id_mandato INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao  VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS funcoes_legislativas (
  id_funcao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(60) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tipos_comissao (
  id_tipo_comissao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao        VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS historia_municipio (
  id_historia      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ano_fundacao     VARCHAR(10) NULL,
  dia_aniversario  TINYINT UNSIGNED NULL,
  mes_aniversario  VARCHAR(20) NULL,
  populacao        INT UNSIGNED NULL,
  area_km2         DECIMAL(10,2) NULL,
  conteudo         TEXT NULL,
  imagem_cidade    VARCHAR(150) NULL,
  imagem_brasao    VARCHAR(150) NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contato_site (
  id_contato     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome_completo  VARCHAR(150) NOT NULL,
  email          VARCHAR(100) NOT NULL,
  assunto        VARCHAR(150) NULL,
  mensagem       TEXT NOT NULL,
  criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS legislaturas_bienio (
  id_legislatura INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_mandato     INT UNSIGNED NOT NULL,
  descricao      VARCHAR(20) NOT NULL,
  FOREIGN KEY (id_mandato) REFERENCES mandatos_eletivos(id_mandato)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vereadores (
  id_vereador  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_servidor  INT UNSIGNED NOT NULL,
  apelido      VARCHAR(100) NULL,
  partido      VARCHAR(50) NULL,
  ocupacao     VARCHAR(100) NULL,
  id_mandato   INT UNSIGNED NULL,
  ativo        TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_vereadores_servidor (id_servidor),
  FOREIGN KEY (id_servidor) REFERENCES servidores(id_servidor),
  FOREIGN KEY (id_mandato) REFERENCES mandatos_eletivos(id_mandato)
) ENGINE=InnoDB CHARSET=utf8mb4;

-- ON DELETE CASCADE confirmado pelo comentário em gerenciador/vereador-excluir.php
CREATE TABLE IF NOT EXISTS vereadores_mandatos_anteriores (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_vereador INT UNSIGNED NOT NULL,
  id_mandato  INT UNSIGNED NOT NULL,
  ano_inicio  YEAR NOT NULL,
  ano_fim     YEAR NOT NULL,
  FOREIGN KEY (id_vereador) REFERENCES vereadores(id_vereador) ON DELETE CASCADE,
  FOREIGN KEY (id_mandato) REFERENCES mandatos_eletivos(id_mandato)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mesa_diretora (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_vereador     INT UNSIGNED NOT NULL,
  id_legislatura  INT UNSIGNED NOT NULL,
  id_funcao       INT UNSIGNED NULL,
  membro_mesa     TINYINT(1) NOT NULL DEFAULT 1,
  observacao      VARCHAR(200) NULL,
  ativo           TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_vereador) REFERENCES vereadores(id_vereador) ON DELETE CASCADE,
  FOREIGN KEY (id_legislatura) REFERENCES legislaturas_bienio(id_legislatura),
  FOREIGN KEY (id_funcao) REFERENCES funcoes_legislativas(id_funcao)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comissoes_membros (
  id_comissao       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_vereador       INT UNSIGNED NOT NULL,
  id_tipo_comissao  INT UNSIGNED NOT NULL,
  id_legislatura    INT UNSIGNED NOT NULL,
  id_funcao         INT UNSIGNED NULL,
  observacao        VARCHAR(200) NULL,
  ativo             TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_vereador) REFERENCES vereadores(id_vereador) ON DELETE CASCADE,
  FOREIGN KEY (id_tipo_comissao) REFERENCES tipos_comissao(id_tipo_comissao),
  FOREIGN KEY (id_legislatura) REFERENCES legislaturas_bienio(id_legislatura),
  FOREIGN KEY (id_funcao) REFERENCES funcoes_legislativas(id_funcao)
) ENGINE=InnoDB CHARSET=utf8mb4;

-- =====================================================================
-- 4) DOCUMENTOS / NOTÍCIAS
-- =====================================================================

CREATE TABLE IF NOT EXISTS categorias_documentos (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao    VARCHAR(150) NOT NULL,
  slug         VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documentos (
  id_documento       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_categoria       INT UNSIGNED NOT NULL,
  numero_documento   VARCHAR(20) NULL,
  titulo             VARCHAR(250) NULL,
  descricao          TEXT NULL,
  data_publicacao    DATE NULL,
  id_autor_vereador  INT UNSIGNED NULL,
  arquivo            VARCHAR(150) NULL,
  status             TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_categoria) REFERENCES categorias_documentos(id_categoria),
  FOREIGN KEY (id_autor_vereador) REFERENCES vereadores(id_vereador) ON DELETE SET NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS noticias (
  id_noticia    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo        VARCHAR(255) NOT NULL,
  subtitulo     VARCHAR(255) NULL,
  conteudo      TEXT NOT NULL,
  imagem        VARCHAR(150) NULL,
  autor         VARCHAR(100) NULL,
  destaque      TINYINT(1) NOT NULL DEFAULT 0,
  status        TINYINT(1) NOT NULL DEFAULT 1,
  publicado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

-- =====================================================================
-- 5) COMPRAS / LICITAÇÕES / CONTRATOS / FORNECEDORES
-- =====================================================================

CREATE TABLE IF NOT EXISTS orgaos (
  id_orgao    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao   VARCHAR(150) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO orgaos (descricao) VALUES
('Câmara Municipal de Ananás'),
('Prefeitura Municipal de Ananás');

CREATE TABLE IF NOT EXISTS fornecedores (
  id_fornecedor       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  razao_social        VARCHAR(150) NOT NULL,
  nome_fantasia       VARCHAR(150) NULL,
  cnpj_cpf            VARCHAR(20) NULL,
  ativo               TINYINT(1) NOT NULL DEFAULT 1,
  motivo_inativacao   VARCHAR(255) NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS licitacao_modalidades (
  id_modalidade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao     VARCHAR(60) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO licitacao_modalidades (descricao) VALUES
('Pregão Eletrônico'),
('Pregão Presencial'),
('Concorrência'),
('Concurso'),
('Leilão'),
('Dispensa de Licitação'),
('Inexigibilidade de Licitação'),
('Convite'),
('Tomada de Preços'),
('Adesão a Ata de Registro de Preços');

CREATE TABLE IF NOT EXISTS licitacao_procedimentos (
  id_procedimento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao       VARCHAR(60) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO licitacao_procedimentos (descricao) VALUES
('Eletrônico'),
('Presencial');

CREATE TABLE IF NOT EXISTS licitacao_tipos (
  id_tipo_licitacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao         VARCHAR(60) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO licitacao_tipos (descricao) VALUES
('Menor Preço'),
('Melhor Técnica'),
('Técnica e Preço'),
('Maior Lance ou Oferta'),
('Maior Desconto');

CREATE TABLE IF NOT EXISTS licitacao_finalidades (
  id_finalidade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao     VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO licitacao_finalidades (descricao) VALUES
('Aquisição de Bens'),
('Prestação de Serviços'),
('Obras e Serviços de Engenharia'),
('Registro de Preços'),
('Locação de Bens');

CREATE TABLE IF NOT EXISTS licitacao_regimes_execucao (
  id_regime_execucao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao          VARCHAR(60) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO licitacao_regimes_execucao (descricao) VALUES
('Empreitada por Preço Global'),
('Empreitada por Preço Unitário'),
('Tarefa'),
('Empreitada Integral'),
('Contratação por Escopo'),
('Contratação por Tarefa Específica'),
('Fornecimento e Prestação de Serviço Associado');

-- Lista provisória: será apagada e recriada pelo padrão TCE-TO em
-- gerenciador/database/migration_licitacoes_tce.sql — só precisa
-- existir para as licitações poderem ser cadastradas antes disso.
CREATE TABLE IF NOT EXISTS licitacao_situacoes (
  id_situacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao   VARCHAR(30) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO licitacao_situacoes (descricao) VALUES
('Em Andamento'),
('Homologada'),
('Adjudicada'),
('Finalizada'),
('Fracassada'),
('Deserta'),
('Revogada'),
('Anulada'),
('Suspensa');

CREATE TABLE IF NOT EXISTS tipos_contratacao (
  id_tipo_contratacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao           VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO tipos_contratacao (descricao) VALUES
('Contrato Administrativo'),
('Ata de Registro de Preços'),
('Contratação Direta - Dispensa'),
('Contratação Direta - Inexigibilidade'),
('Termo de Fornecimento'),
('Convênio');

-- licitacoes (base — sem numero_licitacao, ano_exercicio, id_pregoeiro,
-- que são adicionados por gerenciador/database/migration_licitacoes_tce.sql)
CREATE TABLE IF NOT EXISTS licitacoes (
  id_licitacao        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_orgao            INT UNSIGNED NULL,
  id_procedimento     INT UNSIGNED NULL,
  id_modalidade       INT UNSIGNED NULL,
  id_tipo_licitacao   INT UNSIGNED NULL,
  id_finalidade       INT UNSIGNED NULL,
  id_regime_execucao  INT UNSIGNED NULL,
  id_situacao         INT UNSIGNED NULL,
  numero_processo     VARCHAR(50) NULL,
  data_abertura       DATE NULL,
  data_homologacao    DATE NULL,
  data_publicacao     DATE NULL,
  valor_estimado      DECIMAL(15,2) NULL,
  valor_despesa       DECIMAL(15,2) NULL,
  objeto              TEXT NULL,
  arquivo             VARCHAR(150) NULL,
  status              TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_orgao) REFERENCES orgaos(id_orgao),
  FOREIGN KEY (id_procedimento) REFERENCES licitacao_procedimentos(id_procedimento),
  FOREIGN KEY (id_modalidade) REFERENCES licitacao_modalidades(id_modalidade),
  FOREIGN KEY (id_tipo_licitacao) REFERENCES licitacao_tipos(id_tipo_licitacao),
  FOREIGN KEY (id_finalidade) REFERENCES licitacao_finalidades(id_finalidade),
  FOREIGN KEY (id_regime_execucao) REFERENCES licitacao_regimes_execucao(id_regime_execucao),
  FOREIGN KEY (id_situacao) REFERENCES licitacao_situacoes(id_situacao)
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS compras (
  id_compra       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_modalidade   INT UNSIGNED NULL,
  id_licitacao    INT UNSIGNED NULL,
  id_fornecedor   INT UNSIGNED NULL,
  data_compra     DATE NULL,
  tipo_compra     VARCHAR(200) NOT NULL,
  descricao       TEXT NULL,
  valor_total     DECIMAL(15,2) NULL,
  valor_desconto  DECIMAL(15,2) NOT NULL DEFAULT 0,
  valor_final     DECIMAL(15,2) NULL,
  status          TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_modalidade) REFERENCES licitacao_modalidades(id_modalidade),
  FOREIGN KEY (id_licitacao) REFERENCES licitacoes(id_licitacao),
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor)
) ENGINE=InnoDB CHARSET=utf8mb4;

-- contratos (base — sem ano_exercicio, id_finalidade, data_publicacao_extrato,
-- id_gestor, id_situacao, que são adicionados por migration_contratos.sql;
-- a coluna "descricao" é renomeada para "objeto" só depois, por
-- migration_fix_contratos_objeto.sql)
CREATE TABLE IF NOT EXISTS contratos (
  id_contrato           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_licitacao          INT UNSIGNED NULL,
  id_tipo_contratacao   INT UNSIGNED NULL,
  numero_contrato       VARCHAR(20) NOT NULL,
  inicio_vigencia       DATE NULL,
  fim_vigencia          DATE NULL,
  valor_estimado        DECIMAL(15,2) NULL,
  id_fornecedor         INT UNSIGNED NULL,
  id_fiscal             INT UNSIGNED NULL,
  descricao             TEXT NULL,
  arquivo               VARCHAR(150) NULL,
  status                TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (id_licitacao) REFERENCES licitacoes(id_licitacao),
  FOREIGN KEY (id_tipo_contratacao) REFERENCES tipos_contratacao(id_tipo_contratacao),
  FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor),
  FOREIGN KEY (id_fiscal) REFERENCES servidores(id_servidor)
) ENGINE=InnoDB CHARSET=utf8mb4;

-- =====================================================================
-- 6) ATENDIMENTO AO CIDADÃO (e-SIC / OUVIDORIA)
-- =====================================================================

CREATE TABLE IF NOT EXISTS tipos_esic (
  id_tipo_esic INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao    VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO tipos_esic (descricao) VALUES
('Solicitação de Acesso à Informação');

CREATE TABLE IF NOT EXISTS esic_solicitacoes (
  id_esic            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tipo_esic       INT UNSIGNED NOT NULL,
  anonimo            TINYINT(1) NOT NULL DEFAULT 0,
  nome               VARCHAR(150) NULL,
  cpf                VARCHAR(14) NULL,
  data_nascimento    DATE NULL,
  sexo               CHAR(1) NULL,
  id_escolaridade    INT UNSIGNED NULL,
  telefone           VARCHAR(20) NULL,
  forma_recebimento  VARCHAR(20) NOT NULL DEFAULT 'E-mail',
  email              VARCHAR(100) NULL,
  descricao          TEXT NOT NULL,
  anexo              VARCHAR(150) NULL,
  protocolo          VARCHAR(20) NOT NULL,
  resposta           TEXT NULL,
  respondida         TINYINT(1) NOT NULL DEFAULT 0,
  respondido_por     INT UNSIGNED NULL,
  respondido_em      DATETIME NULL,
  criado_em          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_esic_protocolo (protocolo),
  FOREIGN KEY (id_tipo_esic) REFERENCES tipos_esic(id_tipo_esic),
  FOREIGN KEY (id_escolaridade) REFERENCES escolaridades(id_escolaridade),
  FOREIGN KEY (respondido_por) REFERENCES servidores(id_servidor) ON DELETE SET NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tipos_manifestacao (
  id_tipo_manifestacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao             VARCHAR(100) NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO tipos_manifestacao (descricao) VALUES
('Reclamação'), ('Denúncia'), ('Elogio'), ('Sugestão');

CREATE TABLE IF NOT EXISTS manifestacoes (
  id_manifestacao      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tipo_manifestacao INT UNSIGNED NOT NULL,
  anonimo              TINYINT(1) NOT NULL DEFAULT 0,
  forma_recebimento    VARCHAR(20) NOT NULL DEFAULT 'Portal',
  nome                 VARCHAR(150) NULL,
  cpf                  VARCHAR(14) NULL,
  email                VARCHAR(100) NULL,
  telefone             VARCHAR(20) NULL,
  anexo                VARCHAR(150) NULL,
  texto                TEXT NOT NULL,
  protocolo            VARCHAR(20) NOT NULL,
  resposta             TEXT NULL,
  respondida           TINYINT(1) NOT NULL DEFAULT 0,
  respondido_por       INT UNSIGNED NULL,
  respondido_em        DATETIME NULL,
  criado_em            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_manifestacao_protocolo (protocolo),
  FOREIGN KEY (id_tipo_manifestacao) REFERENCES tipos_manifestacao(id_tipo_manifestacao),
  FOREIGN KEY (respondido_por) REFERENCES servidores(id_servidor) ON DELETE SET NULL
) ENGINE=InnoDB CHARSET=utf8mb4;
