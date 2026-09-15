-- =====================================================================
-- DADOS DE TESTE — PARTE 2: Legislação, Documentos, Diário Oficial e Notícias
--
-- Complementa o seed_dados_teste.sql: aquele script só tinha colocado
-- documento de teste em 3 das 15 categorias (Leis, Atas e Diário
-- Oficial). Este aqui garante pelo menos 2 documentos em TODAS as
-- categorias usadas pelo site (Legislação completa: decretos, leis,
-- lei orgânica, portarias, resoluções, projetos de lei, pautas,
-- regimento interno, PCA, PPA, LDO, LOA, atas de preços), mais edições
-- extras do Diário Oficial e mais notícias.
--
-- Mesmo aviso do script anterior: dados 100% FICTÍCIOS para demonstração
-- local — apague antes de colocar o sistema em produção.
--
-- Idempotente (WHERE NOT EXISTS). Requer que o seed_dados_teste.sql já
-- tenha rodado antes (ele que cria as categorias_documentos que faltam).
-- =====================================================================
USE portal_camara;

-- ---------------------------------------------------------------------
-- Função auxiliar "manual": para cada categoria, insere 2 documentos de
-- teste se a categoria ainda não tiver nenhum documento com aquele
-- numero_documento. Repetido categoria a categoria (SQL puro, sem loop).
-- ---------------------------------------------------------------------

-- Atas de Sessões (mais 1, já existe 1 do script anterior)
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'atas-de-sessoes' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Ata de Sessão Ordinária de teste nº 2', 'Documento de teste para demonstração do sistema.', '2026-02-10', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- Atas de Registro de Preços
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'atas-de-precos' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '001/2026', 'Ata de Registro de Preços de teste nº 1', 'Documento de teste para demonstração do sistema.', '2026-03-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Ata de Registro de Preços de teste nº 2', 'Documento de teste para demonstração do sistema.', '2026-03-20', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- Decretos
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'decretos' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '001/2026', 'Decreto Legislativo de teste nº 001/2026', 'Documento de teste para demonstração do sistema.', '2026-01-10', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Decreto Legislativo de teste nº 002/2026', 'Documento de teste para demonstração do sistema.', '2026-02-05', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- Lei Orgânica
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'lei-organica' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'Lei Orgânica do Município de Ananás (texto consolidado)', 'Documento de teste para demonstração do sistema.', '2026-01-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'Lei Orgânica do Município de Ananás (texto consolidado)' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '01/2026', 'Emenda à Lei Orgânica de teste nº 01/2026', 'Documento de teste para demonstração do sistema.', '2026-02-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '01/2026' AND id_categoria = @cat);

-- Leis Municipais (mais 1, já existe 1 do script anterior)
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'leis-municipais' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Lei Municipal de teste nº 002/2026', 'Documento de teste para demonstração do sistema.', '2026-02-20', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- Pautas de Sessões
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'pautas-de-sessoes' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '001/2026', 'Pauta da Sessão Ordinária de teste nº 1', 'Documento de teste para demonstração do sistema.', '2026-03-05', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Pauta da Sessão Ordinária de teste nº 2', 'Documento de teste para demonstração do sistema.', '2026-03-12', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- PCA — Plano de Contratações Anual
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'pca' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'Plano de Contratações Anual de teste - 2026', 'Documento de teste para demonstração do sistema.', '2026-01-05', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'Plano de Contratações Anual de teste - 2026' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'Revisão do PCA de teste - 1º semestre 2026', 'Documento de teste para demonstração do sistema.', '2026-06-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'Revisão do PCA de teste - 1º semestre 2026' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);

-- Portarias
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'portarias' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '001/2026', 'Portaria de teste nº 001/2026', 'Documento de teste para demonstração do sistema.', '2026-01-12', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Portaria de teste nº 002/2026', 'Documento de teste para demonstração do sistema.', '2026-02-18', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- Projetos de Lei
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'projetos-de-lei' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, id_autor_vereador, status)
SELECT @cat, '001/2026', 'Projeto de Lei de teste nº 001/2026',
    'Dispõe sobre assunto de teste para demonstração do sistema.', '2026-01-25',
    (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000002' LIMIT 1), 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, id_autor_vereador, status)
SELECT @cat, '002/2026', 'Projeto de Lei de teste nº 002/2026',
    'Dispõe sobre outro assunto de teste para demonstração do sistema.', '2026-03-02',
    (SELECT id_vereador FROM vereadores v JOIN servidores s ON s.id_servidor = v.id_servidor WHERE s.cpf = '10000000006' LIMIT 1), 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- Regimento Interno
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'regimento-interno' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'Regimento Interno da Câmara Municipal (texto consolidado)', 'Documento de teste para demonstração do sistema.', '2026-01-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'Regimento Interno da Câmara Municipal (texto consolidado)' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '01/2026', 'Alteração ao Regimento Interno de teste nº 01/2026', 'Documento de teste para demonstração do sistema.', '2026-04-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '01/2026' AND id_categoria = @cat);

-- Resoluções
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'resolucoes' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '001/2026', 'Resolução de teste nº 001/2026', 'Documento de teste para demonstração do sistema.', '2026-01-30', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '001/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '002/2026', 'Resolução de teste nº 002/2026', 'Documento de teste para demonstração do sistema.', '2026-03-10', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '002/2026' AND id_categoria = @cat);

-- PPA — Plano Plurianual
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'ppa' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'PPA de teste 2026-2029', 'Documento de teste para demonstração do sistema.', '2025-12-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'PPA de teste 2026-2029' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'Revisão anual do PPA de teste - 2026', 'Documento de teste para demonstração do sistema.', '2026-06-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'Revisão anual do PPA de teste - 2026' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);

-- LDO — Lei de Diretrizes Orçamentárias
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'ldo' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'LDO de teste - 2026', 'Documento de teste para demonstração do sistema.', '2025-08-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'LDO de teste - 2026' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'LDO de teste - 2027', 'Documento de teste para demonstração do sistema.', '2026-08-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'LDO de teste - 2027' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);

-- LOA — Lei Orçamentária Anual
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'loa' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'LOA de teste - 2026', 'Documento de teste para demonstração do sistema.', '2025-12-20', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'LOA de teste - 2026' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, titulo, descricao, data_publicacao, status)
SELECT @cat, 'LOA de teste - 2027', 'Documento de teste para demonstração do sistema.', '2026-12-20', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE titulo = 'LOA de teste - 2027' COLLATE utf8mb4_unicode_ci AND id_categoria = @cat);

-- Diário Oficial: mais 4 edições (já existiam 2 do script anterior, total 6)
SET @cat := (SELECT id_categoria FROM categorias_documentos WHERE slug = 'diario-oficial' COLLATE utf8mb4_unicode_ci LIMIT 1);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '003/2026', 'Diário Oficial de teste - Edição 003', 'Documento de teste para demonstração do sistema.', '2026-03-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '003/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '004/2026', 'Diário Oficial de teste - Edição 004', 'Documento de teste para demonstração do sistema.', '2026-03-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '004/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '005/2026', 'Diário Oficial de teste - Edição 005', 'Documento de teste para demonstração do sistema.', '2026-04-01', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '005/2026' AND id_categoria = @cat);
INSERT INTO documentos (id_categoria, numero_documento, titulo, descricao, data_publicacao, status)
SELECT @cat, '006/2026', 'Diário Oficial de teste - Edição 006', 'Documento de teste para demonstração do sistema.', '2026-04-15', 1
WHERE NOT EXISTS (SELECT 1 FROM documentos WHERE numero_documento = '006/2026' AND id_categoria = @cat);

-- ---------------------------------------------------------------------
-- Notícias: mais 6 (já existiam 2 do script anterior, total 8)
-- ---------------------------------------------------------------------
INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Câmara promove audiência pública de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 1, 1, '2026-01-10 09:00:00'
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Câmara promove audiência pública de teste' COLLATE utf8mb4_unicode_ci);

INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Comissão de Finanças analisa contas de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 0, 1, '2026-01-22 10:30:00'
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Comissão de Finanças analisa contas de teste' COLLATE utf8mb4_unicode_ci);

INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Mesa Diretora define pauta de teste do semestre', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 1, 1, '2026-02-05 14:00:00'
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Mesa Diretora define pauta de teste do semestre' COLLATE utf8mb4_unicode_ci);

INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Câmara divulga edital de licitação de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 0, 1, '2026-02-18 11:15:00'
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Câmara divulga edital de licitação de teste' COLLATE utf8mb4_unicode_ci);

INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Vereadores participam de capacitação de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 0, 1, '2026-03-01 08:45:00'
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Vereadores participam de capacitação de teste' COLLATE utf8mb4_unicode_ci);

INSERT INTO noticias (titulo, subtitulo, conteudo, autor, destaque, status, publicado_em)
SELECT 'Ouvidoria registra aumento de manifestações de teste', 'Notícia de demonstração do sistema',
    'Este é um conteúdo de notícia gerado apenas para testar o portal. Substitua por conteúdo real antes de publicar.',
    'Assessoria de Comunicação', 0, 1, '2026-03-20 16:00:00'
WHERE NOT EXISTS (SELECT 1 FROM noticias WHERE titulo = 'Ouvidoria registra aumento de manifestações de teste' COLLATE utf8mb4_unicode_ci);
