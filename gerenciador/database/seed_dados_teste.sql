-- =====================================================================
-- DADOS DE TESTE — para demonstração do sistema
--
-- ATENÇÃO: este script insere pessoas e registros FICTÍCIOS (9 vereadores,
-- servidores, licitações, contratos, notícias etc.) em um Portal da
-- Transparência de verdade. Sirva-se dele só em ambiente local/demo.
-- Antes de colocar o sistema em produção, APAGUE esses registros de teste
-- (todos marcados com nomes/observações fictícias abaixo) ou restaure o
-- banco a partir de um dump limpo — não deixe vereadores/servidores
-- inventados visíveis no site público de uma Câmara real.
--
-- Idempotente: pode rodar mais de uma vez sem duplicar nada (cada INSERT
-- é condicionado por WHERE NOT EXISTS). Cria as tabelas que ainda não
-- existirem (mesmo padrão dos outros hotfixes) e depois insere pelo
-- menos 2 registros em cada uma.
-- =====================================================================
USE portal_camara;

-- ---------------------------------------------------------------------
-- 1) Lookups de servidores (Dados funcionais do formulário)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cargos (
  id_cargo  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO cargos (descricao)
SELECT d FROM (
  SELECT 'Vereador' AS d UNION ALL SELECT 'Diretor Administrativo'
  UNION ALL SELECT 'Assessor Jurídico' UNION ALL SELECT 'Contador'
  UNION ALL SELECT 'Assistente Administrativo' UNION ALL SELECT 'Recepcionista'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM cargos c WHERE c.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS departamentos (
  id_departamento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao        VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO departamentos (descricao)
SELECT d FROM (
  SELECT 'Diretoria Geral' AS d UNION ALL SELECT 'Departamento Jurídico'
  UNION ALL SELECT 'Departamento Financeiro' UNION ALL SELECT 'Departamento de Recursos Humanos'
  UNION ALL SELECT 'Departamento de Comunicação'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM departamentos d2 WHERE d2.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS escolaridades (
  id_escolaridade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao        VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO escolaridades (descricao)
SELECT d FROM (
  SELECT 'Ensino Fundamental' AS d UNION ALL SELECT 'Ensino Médio'
  UNION ALL SELECT 'Ensino Superior' UNION ALL SELECT 'Pós-graduação'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM escolaridades e WHERE e.descricao = novo.d COLLATE utf8mb4_unicode_ci);

-- ---------------------------------------------------------------------
-- 2) Lookups de vereadores / comissões / mesa diretora
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mandatos_eletivos (
  id_mandato INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO mandatos_eletivos (descricao)
SELECT d FROM (SELECT '2021/2024' AS d UNION ALL SELECT '2025/2028') AS novo
WHERE NOT EXISTS (SELECT 1 FROM mandatos_eletivos m WHERE m.descricao = novo.d COLLATE utf8mb4_unicode_ci);

SET @id_mandato_atual := (
  SELECT id_mandato FROM mandatos_eletivos WHERE descricao = '2025/2028' COLLATE utf8mb4_unicode_ci LIMIT 1
);

CREATE TABLE IF NOT EXISTS legislaturas_bienio (
  id_legislatura INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao       VARCHAR(20) NOT NULL UNIQUE,
  id_mandato      INT UNSIGNED NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO legislaturas_bienio (descricao, id_mandato)
SELECT '2025/2026', @id_mandato_atual
WHERE NOT EXISTS (SELECT 1 FROM legislaturas_bienio WHERE descricao = '2025/2026' COLLATE utf8mb4_unicode_ci);

SET @id_legislatura_atual := (
  SELECT id_legislatura FROM legislaturas_bienio WHERE descricao = '2025/2026' COLLATE utf8mb4_unicode_ci LIMIT 1
);

CREATE TABLE IF NOT EXISTS funcoes_legislativas (
  id_funcao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao  VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO funcoes_legislativas (descricao)
SELECT d FROM (
  SELECT 'Presidente' AS d UNION ALL SELECT 'Vice-Presidente'
  UNION ALL SELECT '1º Secretário' UNION ALL SELECT '2º Secretário'
  UNION ALL SELECT 'Relator' UNION ALL SELECT 'Membro'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM funcoes_legislativas f WHERE f.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS tipos_comissao (
  id_tipo_comissao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao         VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO tipos_comissao (descricao)
SELECT d FROM (
  SELECT 'Comissão de Finanças e Orçamento' AS d
  UNION ALL SELECT 'Comissão de Justiça e Redação'
  UNION ALL SELECT 'Comissão de Educação, Saúde e Assistência Social'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM tipos_comissao t WHERE t.descricao = novo.d COLLATE utf8mb4_unicode_ci);

-- ---------------------------------------------------------------------
-- 3) Servidores que NÃO são vereadores (equipe administrativa)
-- ---------------------------------------------------------------------
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, id_departamento, id_escolaridade, matricula, data_admissao, celular, email, ativo)
SELECT '20000000001', 'Patrícia Almeida Rocha', '1985-03-12', 'F',
       (SELECT id_cargo FROM cargos WHERE descricao = 'Diretor Administrativo' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_departamento FROM departamentos WHERE descricao = 'Diretoria Geral' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_escolaridade FROM escolaridades WHERE descricao = 'Ensino Superior' COLLATE utf8mb4_unicode_ci LIMIT 1),
       'MAT-0001', '2018-02-01', '(63) 99101-0001', 'patricia.rocha@teste.local', 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '20000000001');

INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, id_departamento, id_escolaridade, matricula, data_admissao, celular, email, ativo)
SELECT '20000000002', 'Marcos Vinícius Teixeira', '1979-07-22', 'M',
       (SELECT id_cargo FROM cargos WHERE descricao = 'Assessor Jurídico' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_departamento FROM departamentos WHERE descricao = 'Departamento Jurídico' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_escolaridade FROM escolaridades WHERE descricao = 'Ensino Superior' COLLATE utf8mb4_unicode_ci LIMIT 1),
       'MAT-0002', '2016-05-10', '(63) 99101-0002', 'marcos.teixeira@teste.local', 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '20000000002');

INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, id_departamento, id_escolaridade, matricula, data_admissao, celular, email, ativo)
SELECT '20000000003', 'Juliana Cristina Barbosa', '1990-11-05', 'F',
       (SELECT id_cargo FROM cargos WHERE descricao = 'Contador' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_departamento FROM departamentos WHERE descricao = 'Departamento Financeiro' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_escolaridade FROM escolaridades WHERE descricao = 'Ensino Superior' COLLATE utf8mb4_unicode_ci LIMIT 1),
       'MAT-0003', '2020-01-15', '(63) 99101-0003', 'juliana.barbosa@teste.local', 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '20000000003');

INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, id_departamento, id_escolaridade, matricula, data_admissao, celular, email, ativo)
SELECT '20000000004', 'Eduardo Henrique Nascimento', '1993-09-30', 'M',
       (SELECT id_cargo FROM cargos WHERE descricao = 'Assistente Administrativo' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_departamento FROM departamentos WHERE descricao = 'Departamento de Recursos Humanos' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_escolaridade FROM escolaridades WHERE descricao = 'Ensino Médio' COLLATE utf8mb4_unicode_ci LIMIT 1),
       'MAT-0004', '2021-08-01', '(63) 99101-0004', 'eduardo.nascimento@teste.local', 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '20000000004');

INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, id_departamento, id_escolaridade, matricula, data_admissao, celular, email, ativo)
SELECT '20000000005', 'Camila Fernanda Duarte', '1997-01-18', 'F',
       (SELECT id_cargo FROM cargos WHERE descricao = 'Recepcionista' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_departamento FROM departamentos WHERE descricao = 'Diretoria Geral' COLLATE utf8mb4_unicode_ci LIMIT 1),
       (SELECT id_escolaridade FROM escolaridades WHERE descricao = 'Ensino Médio' COLLATE utf8mb4_unicode_ci LIMIT 1),
       'MAT-0005', '2022-03-20', '(63) 99101-0005', 'camila.duarte@teste.local', 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '20000000005');

-- ---------------------------------------------------------------------
-- 4) 9 vereadores fictícios (cada um vira uma linha em `servidores`
--    + uma linha em `vereadores`, exatamente como o gerenciador faz)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vereadores (
  id_vereador INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_servidor INT UNSIGNED NOT NULL,
  apelido     VARCHAR(100) NULL,
  partido     VARCHAR(50) NULL,
  ocupacao    VARCHAR(100) NULL,
  id_mandato  INT UNSIGNED NULL,
  ativo       TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB CHARSET=utf8mb4;

SET @id_cargo_vereador := (SELECT id_cargo FROM cargos WHERE descricao = 'Vereador' COLLATE utf8mb4_unicode_ci LIMIT 1);

-- vereador 1
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000001', 'Antônio Carlos Ribeiro', '1975-04-11', 'M', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000001');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000001' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Antônio do Posto', 'PP', 'Comerciante', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 2
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000002', 'Maria de Fátima Souza', '1982-06-25', 'F', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000002');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000002' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Fátima Souza', 'PT', 'Professora', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 3
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000003', 'José Roberto Lima', '1970-01-30', 'M', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000003');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000003' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Zé Roberto', 'MDB', 'Produtor Rural', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 4
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000004', 'Francisca das Chagas Oliveira', '1988-09-14', 'F', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000004');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000004' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Chaguinha', 'PSD', 'Enfermeira', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 5
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000005', 'Raimundo Nonato Costa', '1965-12-02', 'M', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000005');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000005' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Raimundo do Sindicato', 'PL', 'Sindicalista', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 6
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000006', 'Ana Paula Ferreira', '1991-05-19', 'F', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000006');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000006' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Paula Ferreira', 'PDT', 'Advogada', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 7
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000007', 'Sebastião Pereira Alves', '1968-08-08', 'M', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000007');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000007' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Tião Alves', 'REPUBLICANOS', 'Pastor', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 8
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000008', 'Joana Rodrigues Martins', '1980-02-27', 'F', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000008');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000008' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Joaninha', 'PSDB', 'Comerciante', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- vereador 9
INSERT INTO servidores (cpf, nome_completo, data_nascimento, sexo, id_cargo, ativo)
SELECT '10000000009', 'Carlos Eduardo Santos', '1985-10-03', 'M', @id_cargo_vereador, 1
WHERE NOT EXISTS (SELECT 1 FROM servidores WHERE cpf = '10000000009');
SET @srv := (SELECT id_servidor FROM servidores WHERE cpf = '10000000009' LIMIT 1);
INSERT INTO vereadores (id_servidor, apelido, partido, ocupacao, id_mandato, ativo)
SELECT @srv, 'Cadu Santos', 'UNIÃO', 'Engenheiro', @id_mandato_atual, 1
WHERE NOT EXISTS (SELECT 1 FROM vereadores WHERE id_servidor = @srv);

-- ---------------------------------------------------------------------
-- 5) Mandato anterior de exemplo (2 registros, mesma tela do vereador)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vereadores_mandatos_anteriores (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_vereador  INT UNSIGNED NOT NULL,
  id_mandato   INT UNSIGNED NOT NULL,
  ano_inicio   SMALLINT UNSIGNED NOT NULL,
  ano_fim      SMALLINT UNSIGNED NOT NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

SET @id_mandato_anterior := (
  SELECT id_mandato FROM mandatos_eletivos WHERE descricao = '2021/2024' COLLATE utf8mb4_unicode_ci LIMIT 1
);
SET @id_ver1 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000001' LIMIT 1);
SET @id_ver2 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000003' LIMIT 1);

INSERT INTO vereadores_mandatos_anteriores (id_vereador, id_mandato, ano_inicio, ano_fim)
SELECT @id_ver1, @id_mandato_anterior, 2021, 2024
WHERE NOT EXISTS (SELECT 1 FROM vereadores_mandatos_anteriores WHERE id_vereador = @id_ver1 AND ano_inicio = 2021);

INSERT INTO vereadores_mandatos_anteriores (id_vereador, id_mandato, ano_inicio, ano_fim)
SELECT @id_ver2, @id_mandato_anterior, 2021, 2024
WHERE NOT EXISTS (SELECT 1 FROM vereadores_mandatos_anteriores WHERE id_vereador = @id_ver2 AND ano_inicio = 2021);

-- ---------------------------------------------------------------------
-- 6) Mesa Diretora da legislatura atual (Presidente, Vice, 1º e 2º Secretário)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS mesa_diretora (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_vereador    INT UNSIGNED NOT NULL,
  id_legislatura INT UNSIGNED NOT NULL,
  id_funcao      INT UNSIGNED NULL,
  membro_mesa    TINYINT(1) NOT NULL DEFAULT 1,
  observacao     VARCHAR(255) NULL,
  ativo          TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB CHARSET=utf8mb4;

SET @f_presidente := (SELECT id_funcao FROM funcoes_legislativas WHERE descricao = 'Presidente' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @f_vice        := (SELECT id_funcao FROM funcoes_legislativas WHERE descricao = 'Vice-Presidente' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @f_1sec         := (SELECT id_funcao FROM funcoes_legislativas WHERE descricao = '1º Secretário' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @f_2sec         := (SELECT id_funcao FROM funcoes_legislativas WHERE descricao = '2º Secretário' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_ver3 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000002' LIMIT 1);
SET @id_ver4 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000004' LIMIT 1);

INSERT INTO mesa_diretora (id_vereador, id_legislatura, id_funcao, membro_mesa, ativo)
SELECT @id_ver1, @id_legislatura_atual, @f_presidente, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM mesa_diretora WHERE id_legislatura = @id_legislatura_atual AND id_vereador = @id_ver1 AND ativo = 1);

INSERT INTO mesa_diretora (id_vereador, id_legislatura, id_funcao, membro_mesa, ativo)
SELECT @id_ver2, @id_legislatura_atual, @f_vice, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM mesa_diretora WHERE id_legislatura = @id_legislatura_atual AND id_vereador = @id_ver2 AND ativo = 1);

INSERT INTO mesa_diretora (id_vereador, id_legislatura, id_funcao, membro_mesa, ativo)
SELECT @id_ver3, @id_legislatura_atual, @f_1sec, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM mesa_diretora WHERE id_legislatura = @id_legislatura_atual AND id_vereador = @id_ver3 AND ativo = 1);

INSERT INTO mesa_diretora (id_vereador, id_legislatura, id_funcao, membro_mesa, ativo)
SELECT @id_ver4, @id_legislatura_atual, @f_2sec, 1, 1
WHERE NOT EXISTS (SELECT 1 FROM mesa_diretora WHERE id_legislatura = @id_legislatura_atual AND id_vereador = @id_ver4 AND ativo = 1);

-- ---------------------------------------------------------------------
-- 7) Comissões permanentes (2 comissões, alguns membros)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS comissoes_membros (
  id_comissao       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_vereador       INT UNSIGNED NOT NULL,
  id_funcao         INT UNSIGNED NULL,
  id_legislatura    INT UNSIGNED NOT NULL,
  id_tipo_comissao  INT UNSIGNED NOT NULL,
  observacao        VARCHAR(255) NULL,
  ativo             TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB CHARSET=utf8mb4;

SET @tc_financas := (SELECT id_tipo_comissao FROM tipos_comissao WHERE descricao = 'Comissão de Finanças e Orçamento' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @tc_justica   := (SELECT id_tipo_comissao FROM tipos_comissao WHERE descricao = 'Comissão de Justiça e Redação' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @f_relator    := (SELECT id_funcao FROM funcoes_legislativas WHERE descricao = 'Relator' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @f_membro     := (SELECT id_funcao FROM funcoes_legislativas WHERE descricao = 'Membro' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_ver5 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000005' LIMIT 1);
SET @id_ver6 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000006' LIMIT 1);

INSERT INTO comissoes_membros (id_vereador, id_funcao, id_legislatura, id_tipo_comissao, ativo)
SELECT @id_ver5, @f_relator, @id_legislatura_atual, @tc_financas, 1
WHERE NOT EXISTS (
  SELECT 1 FROM comissoes_membros
  WHERE id_legislatura = @id_legislatura_atual AND id_tipo_comissao = @tc_financas AND id_vereador = @id_ver5 AND ativo = 1
);

INSERT INTO comissoes_membros (id_vereador, id_funcao, id_legislatura, id_tipo_comissao, ativo)
SELECT @id_ver6, @f_membro, @id_legislatura_atual, @tc_financas, 1
WHERE NOT EXISTS (
  SELECT 1 FROM comissoes_membros
  WHERE id_legislatura = @id_legislatura_atual AND id_tipo_comissao = @tc_financas AND id_vereador = @id_ver6 AND ativo = 1
);

SET @id_ver7 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000007' LIMIT 1);
SET @id_ver8 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000008' LIMIT 1);

INSERT INTO comissoes_membros (id_vereador, id_funcao, id_legislatura, id_tipo_comissao, ativo)
SELECT @id_ver7, @f_relator, @id_legislatura_atual, @tc_justica, 1
WHERE NOT EXISTS (
  SELECT 1 FROM comissoes_membros
  WHERE id_legislatura = @id_legislatura_atual AND id_tipo_comissao = @tc_justica AND id_vereador = @id_ver7 AND ativo = 1
);

INSERT INTO comissoes_membros (id_vereador, id_funcao, id_legislatura, id_tipo_comissao, ativo)
SELECT @id_ver8, @f_membro, @id_legislatura_atual, @tc_justica, 1
WHERE NOT EXISTS (
  SELECT 1 FROM comissoes_membros
  WHERE id_legislatura = @id_legislatura_atual AND id_tipo_comissao = @tc_justica AND id_vereador = @id_ver8 AND ativo = 1
);

-- ---------------------------------------------------------------------
-- 8) Fornecedores
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fornecedores (
  id_fornecedor      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  razao_social       VARCHAR(150) NOT NULL,
  nome_fantasia      VARCHAR(150) NULL,
  cnpj_cpf           VARCHAR(20) NULL,
  ativo              TINYINT(1) NOT NULL DEFAULT 1,
  motivo_inativacao  VARCHAR(255) NULL
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj_cpf, ativo)
SELECT 'Papelaria Ananás Comércio de Materiais LTDA', 'Papelaria Ananás', '11.111.111/0001-11', 1
WHERE NOT EXISTS (SELECT 1 FROM fornecedores WHERE cnpj_cpf = '11.111.111/0001-11');

INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj_cpf, ativo)
SELECT 'Construtora Rio Formoso Serviços de Engenharia LTDA', 'Construtora Rio Formoso', '22.222.222/0001-22', 1
WHERE NOT EXISTS (SELECT 1 FROM fornecedores WHERE cnpj_cpf = '22.222.222/0001-22');

-- ---------------------------------------------------------------------
-- 9) Lookups de Licitações (para o formulário do gerenciador)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orgaos (
  id_orgao  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;
INSERT INTO orgaos (descricao)
SELECT 'Câmara Municipal de Ananás'
WHERE NOT EXISTS (SELECT 1 FROM orgaos WHERE descricao = 'Câmara Municipal de Ananás' COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS licitacao_procedimentos (
  id_procedimento INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao        VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;
INSERT INTO licitacao_procedimentos (descricao)
SELECT d FROM (SELECT 'Eletrônico' AS d UNION ALL SELECT 'Presencial') AS novo
WHERE NOT EXISTS (SELECT 1 FROM licitacao_procedimentos p WHERE p.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS licitacao_tipos (
  id_tipo_licitacao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao          VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;
INSERT INTO licitacao_tipos (descricao)
SELECT d FROM (SELECT 'Menor Preço' AS d UNION ALL SELECT 'Melhor Técnica') AS novo
WHERE NOT EXISTS (SELECT 1 FROM licitacao_tipos t WHERE t.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS licitacao_finalidades (
  id_finalidade INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao      VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;
INSERT INTO licitacao_finalidades (descricao)
SELECT d FROM (
  SELECT 'Aquisição de Bens' AS d UNION ALL SELECT 'Contratação de Serviços'
  UNION ALL SELECT 'Obras e Serviços de Engenharia'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM licitacao_finalidades f WHERE f.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS licitacao_regimes_execucao (
  id_regime_execucao INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao            VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;
INSERT INTO licitacao_regimes_execucao (descricao)
SELECT d FROM (SELECT 'Empreitada por Preço Global' AS d UNION ALL SELECT 'Empreitada por Preço Unitário') AS novo
WHERE NOT EXISTS (SELECT 1 FROM licitacao_regimes_execucao r WHERE r.descricao = novo.d COLLATE utf8mb4_unicode_ci);

-- licitacao_modalidades / licitacao_situacoes já existem no seu banco
-- (usadas por licitacoes.php sem erro); garantimos só que tenham pelo
-- menos estes registros de teste, sem recriar a tabela.
INSERT INTO licitacao_modalidades (descricao)
SELECT d FROM (SELECT 'Pregão Eletrônico' AS d UNION ALL SELECT 'Dispensa de Licitação') AS novo
WHERE NOT EXISTS (SELECT 1 FROM licitacao_modalidades m WHERE m.descricao = novo.d COLLATE utf8mb4_unicode_ci);

INSERT INTO licitacao_situacoes (descricao)
SELECT d FROM (SELECT 'Andamento' AS d UNION ALL SELECT 'Realizada(o)') AS novo
WHERE NOT EXISTS (SELECT 1 FROM licitacao_situacoes s WHERE s.descricao = novo.d COLLATE utf8mb4_unicode_ci);

-- ---------------------------------------------------------------------
-- 10) Licitações (2 registros de teste)
-- ---------------------------------------------------------------------
SET @id_orgao          := (SELECT id_orgao FROM orgaos WHERE descricao = 'Câmara Municipal de Ananás' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_procedimento   := (SELECT id_procedimento FROM licitacao_procedimentos WHERE descricao = 'Eletrônico' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_modalidade_peg := (SELECT id_modalidade FROM licitacao_modalidades WHERE descricao = 'Pregão Eletrônico' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_tipo_lic       := (SELECT id_tipo_licitacao FROM licitacao_tipos WHERE descricao = 'Menor Preço' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_finalidade_bens := (SELECT id_finalidade FROM licitacao_finalidades WHERE descricao = 'Aquisição de Bens' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_regime         := (SELECT id_regime_execucao FROM licitacao_regimes_execucao WHERE descricao = 'Empreitada por Preço Global' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_situacao_and    := (SELECT id_situacao FROM licitacao_situacoes WHERE descricao = 'Andamento' COLLATE utf8mb4_unicode_ci LIMIT 1);

INSERT INTO licitacoes (id_orgao, id_procedimento, id_modalidade, id_tipo_licitacao, id_finalidade, id_regime_execucao,
    id_situacao, numero_processo, numero_licitacao, ano_exercicio, data_abertura, valor_estimado, objeto, status)
SELECT @id_orgao, @id_procedimento, @id_modalidade_peg, @id_tipo_lic, @id_finalidade_bens, @id_regime,
    @id_situacao_and, 'PROC-TESTE-001/2026', '001', 2026, '2026-03-10', 85000.00,
    'Aquisição de material de expediente e informática (dado de teste)', 1
WHERE NOT EXISTS (SELECT 1 FROM licitacoes WHERE numero_processo = 'PROC-TESTE-001/2026');

INSERT INTO licitacoes (id_orgao, id_procedimento, id_modalidade, id_tipo_licitacao, id_finalidade, id_regime_execucao,
    id_situacao, numero_processo, numero_licitacao, ano_exercicio, data_abertura, valor_estimado, objeto, status)
SELECT @id_orgao, @id_procedimento, @id_modalidade_peg, @id_tipo_lic, @id_finalidade_bens, @id_regime,
    @id_situacao_and, 'PROC-TESTE-002/2026', '002', 2026, '2026-04-05', 152300.50,
    'Contratação de serviços de limpeza e conservação (dado de teste)', 1
WHERE NOT EXISTS (SELECT 1 FROM licitacoes WHERE numero_processo = 'PROC-TESTE-002/2026');

-- ---------------------------------------------------------------------
-- 11) Contratos (2 registros de teste) — tipos_contratacao e
--     contrato_situacoes já foram semeados pelo hotfix anterior
-- ---------------------------------------------------------------------
SET @id_licitacao1 := (SELECT id_licitacao FROM licitacoes WHERE numero_processo = 'PROC-TESTE-001/2026' LIMIT 1);
SET @id_fornecedor1 := (SELECT id_fornecedor FROM fornecedores WHERE cnpj_cpf = '11.111.111/0001-11' LIMIT 1);
SET @id_tipo_contratacao := (SELECT id_tipo_contratacao FROM tipos_contratacao WHERE descricao = 'Licitação' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_situacao_vigente := (SELECT id_situacao FROM contrato_situacoes WHERE descricao = 'Vigente' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_fiscal := (SELECT id_servidor FROM servidores WHERE cpf = '20000000002' LIMIT 1);

INSERT INTO contratos (id_licitacao, id_tipo_contratacao, id_finalidade, numero_contrato, ano_exercicio,
    inicio_vigencia, fim_vigencia, valor_estimado, id_fornecedor, id_fiscal, id_situacao, objeto, status)
SELECT @id_licitacao1, @id_tipo_contratacao, @id_finalidade_bens, 'CONTR-TESTE-001', 2026,
    '2026-04-01', '2027-03-31', 85000.00, @id_fornecedor1, @id_fiscal, @id_situacao_vigente,
    'Fornecimento de material de expediente e informática (dado de teste)', 1
WHERE NOT EXISTS (SELECT 1 FROM contratos WHERE numero_contrato = 'CONTR-TESTE-001');

SET @id_fornecedor2 := (SELECT id_fornecedor FROM fornecedores WHERE cnpj_cpf = '22.222.222/0001-22' LIMIT 1);
INSERT INTO contratos (id_licitacao, id_tipo_contratacao, id_finalidade, numero_contrato, ano_exercicio,
    inicio_vigencia, fim_vigencia, valor_estimado, id_fornecedor, id_fiscal, id_situacao, objeto, status)
SELECT NULL, @id_tipo_contratacao, @id_finalidade_bens, 'CONTR-TESTE-002', 2026,
    '2026-05-01', '2027-04-30', 152300.50, @id_fornecedor2, @id_fiscal, @id_situacao_vigente,
    'Serviços de reforma predial (dado de teste)', 1
WHERE NOT EXISTS (SELECT 1 FROM contratos WHERE numero_contrato = 'CONTR-TESTE-002');

-- ---------------------------------------------------------------------
-- 12) Compras (2 registros de teste)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS compras (
  id_compra       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_modalidade   INT UNSIGNED NULL,
  id_licitacao    INT UNSIGNED NULL,
  id_fornecedor   INT UNSIGNED NULL,
  data_compra     DATE NULL,
  tipo_compra     VARCHAR(200) NOT NULL,
  descricao       TEXT NULL,
  valor_total     DECIMAL(15,2) NOT NULL,
  valor_desconto  DECIMAL(15,2) NOT NULL DEFAULT 0,
  valor_final     DECIMAL(15,2) NULL,
  status          TINYINT(1) NOT NULL DEFAULT 1,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO compras (id_modalidade, id_fornecedor, data_compra, tipo_compra, descricao, valor_total, valor_desconto, valor_final, status)
SELECT @id_modalidade_peg, @id_fornecedor1, '2026-02-20', 'Material de expediente', 'Compra de teste', 3200.00, 200.00, 3000.00, 1
WHERE NOT EXISTS (SELECT 1 FROM compras WHERE tipo_compra = 'Material de expediente' COLLATE utf8mb4_unicode_ci AND descricao = 'Compra de teste' COLLATE utf8mb4_unicode_ci);

INSERT INTO compras (id_modalidade, id_fornecedor, data_compra, tipo_compra, descricao, valor_total, valor_desconto, valor_final, status)
SELECT @id_modalidade_peg, @id_fornecedor2, '2026-03-15', 'Material de construção', 'Compra de teste', 8900.00, 0, 8900.00, 1
WHERE NOT EXISTS (SELECT 1 FROM compras WHERE tipo_compra = 'Material de construção' COLLATE utf8mb4_unicode_ci AND descricao = 'Compra de teste' COLLATE utf8mb4_unicode_ci);

-- ---------------------------------------------------------------------
-- 13) Categorias de documentos (garante todos os slugs usados pelo site)
--     + 4 documentos de teste
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias_documentos (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao     VARCHAR(100) NOT NULL,
  slug          VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO categorias_documentos (descricao, slug)
SELECT * FROM (SELECT 'Atas de Sessões' AS d, 'atas-de-sessoes' AS s
  UNION ALL SELECT 'Atas de Registro de Preços', 'atas-de-precos'
  UNION ALL SELECT 'Decretos', 'decretos'
  UNION ALL SELECT 'Lei Orgânica', 'lei-organica'
  UNION ALL SELECT 'Leis Municipais', 'leis-municipais'
  UNION ALL SELECT 'Pautas de Sessões', 'pautas-de-sessoes'
  UNION ALL SELECT 'PCA - Plano de Contratações Anual', 'pca'
  UNION ALL SELECT 'Portarias', 'portarias'
  UNION ALL SELECT 'Projetos de Lei', 'projetos-de-lei'
  UNION ALL SELECT 'Regimento Interno', 'regimento-interno'
  UNION ALL SELECT 'Resoluções', 'resolucoes'
  UNION ALL SELECT 'Diário Oficial', 'diario-oficial'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM categorias_documentos c WHERE c.slug = novo.s COLLATE utf8mb4_unicode_ci);

SET @cat_leis := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'leis-municipais' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @cat_atas := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'atas-de-sessoes' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @cat_diario := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'diario-oficial' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @id_autor_ver1 := (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000001' LIMIT 1);

INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, id_autor_vereador, status)
SELECT @cat_leis, '001/2026', 'Lei Municipal de teste nº 001/2026', 'Documento de teste para demonstração do sistema.', '2026-01-15', @id_autor_ver1, 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat_leis);

INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat_atas, '001/2026', 'Ata de Sessão Ordinária de teste', 'Documento de teste para demonstração do sistema.', '2026-01-20', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat_atas);

INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat_diario, '001/2026', 'Diário Oficial de teste - Edição 001', 'Documento de teste para demonstração do sistema.', '2026-02-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat_diario);

INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat_diario, '002/2026', 'Diário Oficial de teste - Edição 002', 'Documento de teste para demonstração do sistema.', '2026-02-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat_diario);

-- ---------------------------------------------------------------------
-- 14) Notícias (2 registros de teste)
-- ---------------------------------------------------------------------
INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Câmara realiza sessão ordinária de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 1, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Câmara realiza sessão ordinária de teste' COLLATE utf8mb4_unicode_ci);

INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Vereadores aprovam projeto de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 0, 1, NOW()
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Vereadores aprovam projeto de teste' COLLATE utf8mb4_unicode_ci);

-- ---------------------------------------------------------------------
-- 15) e-SIC (2 solicitações de teste)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tipos_esic (
  id_tipo_esic INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao     VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;
INSERT INTO tipos_esic (descricao)
SELECT d FROM (
  SELECT 'Informação Geral' AS d UNION ALL SELECT 'Documentos e Processos'
  UNION ALL SELECT 'Dados Orçamentários e Financeiros' UNION ALL SELECT 'Outros assuntos'
) AS novo
WHERE NOT EXISTS (SELECT 1 FROM tipos_esic t WHERE t.descricao = novo.d COLLATE utf8mb4_unicode_ci);

CREATE TABLE IF NOT EXISTS esic_solicitacoes (
  id_esic            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tipo_esic       INT UNSIGNED NULL,
  protocolo          VARCHAR(30) NOT NULL UNIQUE,
  anonimo            TINYINT(1) NOT NULL DEFAULT 0,
  nome               VARCHAR(150) NULL,
  cpf                VARCHAR(14) NULL,
  data_nascimento    DATE NULL,
  sexo               CHAR(1) NULL,
  id_escolaridade    INT UNSIGNED NULL,
  telefone           VARCHAR(20) NULL,
  forma_recebimento  VARCHAR(30) NULL,
  email              VARCHAR(150) NULL,
  descricao          TEXT NOT NULL,
  anexo              VARCHAR(150) NULL,
  respondida         TINYINT(1) NOT NULL DEFAULT 0,
  resposta           TEXT NULL,
  respondido_por     INT UNSIGNED NULL,
  respondido_em      DATETIME NULL,
  criado_em          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8mb4;

SET @tipo_esic1 := (SELECT id_tipo_esic FROM tipos_esic WHERE descricao = 'Informação Geral' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @tipo_esic2 := (SELECT id_tipo_esic FROM tipos_esic WHERE descricao = 'Dados Orçamentários e Financeiros' COLLATE utf8mb4_unicode_ci LIMIT 1);

INSERT INTO esic_solicitacoes (id_tipo_esic, protocolo, anonimo, nome, email, forma_recebimento, descricao)
SELECT @tipo_esic1, 'SIC-TESTE-0001', 0, 'Cidadão de Teste', 'cidadao.teste@teste.local', 'E-mail', 'Solicitação de teste para demonstração do sistema.'
WHERE NOT EXISTS (SELECT 1 FROM esic_solicitacoes WHERE protocolo = 'SIC-TESTE-0001');

INSERT INTO esic_solicitacoes (id_tipo_esic, protocolo, anonimo, descricao)
SELECT @tipo_esic2, 'SIC-TESTE-0002', 1, 'Solicitação anônima de teste para demonstração do sistema.'
WHERE NOT EXISTS (SELECT 1 FROM esic_solicitacoes WHERE protocolo = 'SIC-TESTE-0002');

-- ---------------------------------------------------------------------
-- 16) Ouvidoria (2 manifestações a mais, além dos tipos já semeados)
-- ---------------------------------------------------------------------
SET @tipo_man1 := (SELECT id_tipo_manifestacao FROM tipos_manifestacao WHERE descricao = 'Sugestão' COLLATE utf8mb4_unicode_ci LIMIT 1);
SET @tipo_man2 := (SELECT id_tipo_manifestacao FROM tipos_manifestacao WHERE descricao = 'Elogio' COLLATE utf8mb4_unicode_ci LIMIT 1);

INSERT INTO manifestacoes (id_tipo_manifestacao, protocolo, anonimo, forma_recebimento, nome, texto)
SELECT @tipo_man1, 'OUV-TESTE-0001', 0, 'Portal', 'Cidadão de Teste', 'Manifestação de teste para demonstração do sistema.'
WHERE NOT EXISTS (SELECT 1 FROM manifestacoes WHERE protocolo = 'OUV-TESTE-0001');

INSERT INTO manifestacoes (id_tipo_manifestacao, protocolo, anonimo, forma_recebimento, texto)
SELECT @tipo_man2, 'OUV-TESTE-0002', 1, 'Portal', 'Manifestação anônima de teste para demonstração do sistema.'
WHERE NOT EXISTS (SELECT 1 FROM manifestacoes WHERE protocolo = 'OUV-TESTE-0002');
