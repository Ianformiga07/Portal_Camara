-- =====================================================================
-- FIX — garante todas as categorias de Documentos/Legislação
--
-- Script isolado e independente (não depende de vereadores, servidores
-- nem de nenhum outro script rodar antes) — só para garantir que o
-- dropdown "Categoria" do formulário "Novo documento" venha
-- preenchido. Idempotente: pode rodar quantas vezes quiser.
-- =====================================================================
USE portal_camara;

CREATE TABLE IF NOT EXISTS categorias_documentos (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  descricao     VARCHAR(100) NOT NULL,
  slug          VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4;

INSERT INTO categorias_documentos (descricao, slug)
SELECT * FROM (
  SELECT 'PPA - Plano Plurianual' AS d, 'ppa' AS s
  UNION ALL SELECT 'LDO - Lei de Diretrizes Orçamentárias', 'ldo'
  UNION ALL SELECT 'LOA - Lei Orçamentária Anual', 'loa'
  UNION ALL SELECT 'Atas de Sessões', 'atas-de-sessoes'
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
WHERE NOT EXISTS (
  SELECT 1 FROM categorias_documentos c WHERE c.slug = novo.s COLLATE utf8mb4_unicode_ci
);

-- confira o resultado: deve listar as 15 categorias acima
SELECT id_categoria, descricao, slug FROM categorias_documentos ORDER BY descricao;
