<?php
// "Ver todas as edições" na home aponta pra cá; o arquivo completo com busca
// e paginação já vive em diario-oficial.php, então só redirecionamos pra lá
// em vez de manter a mesma lógica duplicada em dois arquivos.
header('Location: diario-oficial.php');
exit;
