<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistema de Almoxarifado</title>
    <link rel="stylesheet" href="/almoxarifado/public/assets/css/style.css?v=3">
</head>
<body class="pagina-login">
    <div class="login-page">

        <!-- ============ COLUNA DA MARCA + MURAL ============ -->
        <aside class="login-visual">
            <div class="login-logo">
                <span>Sistema de Almoxarifado</span>
            </div>
            <p class="login-tagline">Gestão de estoque e suprimentos internos</p>

            <div class="mural-avisos">
                <h2>Mural de Avisos</h2>
                <ul>
                    <li>
                        <strong>Inspiração:</strong>
                        Do estoque ao destino final, o rastro que a gente deixa é a certeza do trabalho bem feito, vencendo a distância com a força do nosso braço.
                    </li>
                    <li>
                        <strong>Manutenção programada:</strong>
                        sistema pode ficar indisponível no sábado, a partir das 22h.
                    </li>
                    <li>
                        <strong>Novo fornecedor cadastrado:</strong>
                        confira o catálogo de EPIs atualizado nesta semana.
                    </li>
                </ul>
                <p class="ajuda-mural">Avisos fixos — para alterar, edite views/login.php.</p>
            </div>
        </aside>

        <!-- ============ COLUNA DO FORMULÁRIO (ao lado do mural) ============ -->
        <main class="login-form-panel">
            <div class="login-container">
                <h1>Acessar o Sistema</h1>

                <?php if (!empty($_SESSION['erro_login'])): ?>
                    <p class="erro"><?= htmlspecialchars($_SESSION['erro_login']) ?></p>
                    <?php unset($_SESSION['erro_login']); ?>
                <?php endif; ?>

                <form action="/almoxarifado/public/login_process.php" method="POST">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required autofocus>

                    <div class="linha-label-com-link">
                        <label for="senha">Senha</label>
                        <a href="#" class="link-esqueci-senha">Esqueci minha senha</a>
                    </div>
                    <input type="password" id="senha" name="senha" required>

                    <button type="submit" class="botao-pill">Acessar conta →</button>
                </form>

                <p class="login-nota-acesso">
                    Não tem uma conta? Fale com o Administrador do sistema para solicitar seu acesso.
                </p>
            </div>
        </main>
    </div>

    <!-- ============ RODAPÉ (abaixo das duas colunas) ============ -->
    <footer class="rodape-login">
        <p>Sistema de Almoxarifado &middot; Uso interno &middot; &copy; <?= date('Y') ?></p>
    </footer>
</body>
</html>
