-- =====================================================================
-- MIGRAÇÃO — AUTENTICAÇÃO E PERMISSÕES (ETAPA 2: GERENCIADOR)
-- Execute depois do portal_camara_mysql.sql
-- =====================================================================
USE portal_camara;

-- Login passa a ser feito pela própria tabela de servidores (como já
-- era no sistema antigo). Senha com hash bcrypt (password_hash do PHP),
-- nunca em texto puro nem MD5.
ALTER TABLE servidores
  ADD COLUMN senha_hash   VARCHAR(255) NULL AFTER email,
  ADD COLUMN nivel_acesso TINYINT UNSIGNED NOT NULL DEFAULT 3
    COMMENT '1=Admin Master, 2=Admin, 3=Operador' AFTER senha_hash;

-- Módulos do sistema (para controle de permissão por servidor)
CREATE TABLE modulos_sistema (
  id_modulo    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao    VARCHAR(60) NOT NULL,
  slug         VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

CREATE TABLE permissoes_acesso (
  id_permissao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_servidor  INT UNSIGNED NOT NULL,
  id_modulo    INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_servidor_modulo (id_servidor, id_modulo),
  FOREIGN KEY (id_servidor) REFERENCES servidores(id_servidor) ON DELETE CASCADE,
  FOREIGN KEY (id_modulo) REFERENCES modulos_sistema(id_modulo) ON DELETE CASCADE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO modulos_sistema (descricao, slug) VALUES
('A Câmara', 'a-camara'),
('Diário Oficial', 'diario-oficial'),
('Notícias', 'noticias'),
('Licitações e Contratos', 'licitacoes'),
('Compras e Suprimentos', 'compras'),
('Legislação', 'legislacao'),
('Recursos Humanos', 'rh'),
('Atendimento ao Cidadão', 'atendimento');

-- Usuário inicial para o primeiro acesso ao gerenciador.
-- CPF: 00000000000  |  Senha: trocar123
-- (o hash abaixo foi gerado com password_hash('trocar123', PASSWORD_BCRYPT))
INSERT INTO cargos (descricao) VALUES ('Administrador do Sistema');

INSERT INTO servidores (cpf, nome_completo, id_cargo, ativo, senha_hash, nivel_acesso)
VALUES (
  '00000000000',
  'Administrador',
  (SELECT id_cargo FROM cargos WHERE descricao = 'Administrador do Sistema'),
  1,
  '$2b$10$fLHE9EVpydXosa.g/gkWsuwXN2YZMlB5QiYx6DlMUxleR0PyKwvH.',
  1
);
