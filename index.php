<?php
declare(strict_types=1);

// Ponto de entrada do projeto. Quem acessa a raiz (ex.: http://localhost/almoxarifado/)
// cai aqui, e é encaminhado para o painel real dentro de public/.
// O próprio public/index.php já decide entre mostrar o painel ou mandar para o login,
// dependendo de existir ou não uma sessão ativa (AuthMiddleware::check()).
header('Location: /almoxarifado/public/index.php');
exit;
