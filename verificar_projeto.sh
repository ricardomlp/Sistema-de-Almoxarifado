#!/bin/bash
# =====================================================================
# Script de verificação do projeto Sistema de Almoxarifado
# Uso: bash verificar_projeto.sh /opt/lampp/htdocs/almoxarifado
# =====================================================================

BASE="${1:-/opt/lampp/htdocs/almoxarifado}"

ARQUIVOS_ESPERADOS=(
"config/Database.php"
"config/config.php"
"public/alertas/index.php"
"public/alertas/resolver.php"
"public/assets/css/style.css"
"public/categorias/criar.php"
"public/categorias/editar.php"
"public/categorias/excluir.php"
"public/categorias/index.php"
"public/entradas/criar.php"
"public/fornecedores/alternar_status.php"
"public/fornecedores/criar.php"
"public/fornecedores/editar.php"
"public/fornecedores/index.php"
"public/index.php"
"public/inventario/historico.php"
"public/inventario/index.php"
"public/login_process.php"
"public/logout.php"
"public/notas_fiscais/criar.php"
"public/notas_fiscais/index.php"
"public/produtos/alternar_status.php"
"public/produtos/criar.php"
"public/produtos/editar.php"
"public/produtos/index.php"
"public/relatorios/curva_abc.php"
"public/relatorios/custo_setor.php"
"public/relatorios/giro.php"
"public/relatorios/index.php"
"public/requisicoes/atender.php"
"public/requisicoes/criar.php"
"public/requisicoes/index.php"
"public/setores/alternar_status.php"
"public/setores/criar.php"
"public/setores/editar.php"
"public/setores/index.php"
"public/transferencias/criar.php"
"public/transferencias/index.php"
"public/usuarios/criar.php"
"public/usuarios/editar.php"
"public/usuarios/inativar.php"
"public/usuarios/index.php"
"src/Middlewares/AuthMiddleware.php"
"src/Middlewares/RoleMiddleware.php"
"src/Models/AlertaModel.php"
"src/Models/CategoriaModel.php"
"src/Models/EntradaModel.php"
"src/Models/FornecedorModel.php"
"src/Models/InventarioModel.php"
"src/Models/NotaFiscalModel.php"
"src/Models/ProdutoModel.php"
"src/Models/RelatorioModel.php"
"src/Models/RequisicaoModel.php"
"src/Models/SetorModel.php"
"src/Models/TransferenciaModel.php"
"src/Models/UsuarioModel.php"
"views/alertas/listar.php"
"views/categorias/formulario.php"
"views/categorias/listar.php"
"views/dashboard.php"
"views/entradas/formulario.php"
"views/erro_403.php"
"views/fornecedores/formulario.php"
"views/fornecedores/listar.php"
"views/inventario/formulario.php"
"views/inventario/historico.php"
"views/login.php"
"views/notas_fiscais/formulario.php"
"views/notas_fiscais/listar.php"
"views/produtos/formulario.php"
"views/produtos/listar.php"
"views/relatorios/curva_abc.php"
"views/relatorios/custo_setor.php"
"views/relatorios/giro.php"
"views/relatorios/menu.php"
"views/requisicoes/atender.php"
"views/requisicoes/criar.php"
"views/requisicoes/listar.php"
"views/setores/formulario.php"
"views/setores/listar.php"
"views/transferencias/formulario.php"
"views/transferencias/listar.php"
"views/usuarios/formulario.php"
"views/usuarios/listar.php"
)

faltando=0
vazios=0
ok=0

echo "Verificando projeto em: $BASE"
echo "========================================================"

for arquivo in "${ARQUIVOS_ESPERADOS[@]}"; do
    caminho="$BASE/$arquivo"
    if [ ! -f "$caminho" ]; then
        echo "FALTANDO   : $arquivo"
        faltando=$((faltando+1))
    elif [ ! -s "$caminho" ]; then
        echo "VAZIO (0kb): $arquivo"
        vazios=$((vazios+1))
    else
        ok=$((ok+1))
    fi
done

echo "========================================================"
echo "OK: $ok | Vazios: $vazios | Faltando: $faltando (de ${#ARQUIVOS_ESPERADOS[@]} esperados)"

if [ $faltando -eq 0 ] && [ $vazios -eq 0 ]; then
    echo "Projeto completo — todos os arquivos esperados existem e têm conteúdo."
fi
