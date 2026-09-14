-- =====================================================================
-- CORREÇÃO — coluna "descricao" da tabela contratos renomeada para "objeto"
-- (o código do gerenciador já usa "objeto"; o banco ainda estava com o
-- nome antigo, causando o erro "Unknown column 'c.objeto'")
-- =====================================================================
USE portal_camara;

ALTER TABLE contratos CHANGE COLUMN descricao objeto TEXT NULL;
