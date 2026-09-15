# Sistema de Almoxarifado

(By Ricardo Maranhão - (81)98682-9927 / cranioscaner@gmail.com)

Sistema web de gestão de estoque e suprimentos internos, desenvolvido em PHP 8.x
(PDO) + MySQL/MariaDB, para rodar em ambiente XAMPP/LAMPP.

Cobre quatro áreas principais: **Cadastro e Catálogo**, **Movimentação de
Estoque**, **Controle e Alertas** e **Relatórios**, com controle de acesso
por papel (RBAC), log de auditoria completo e triggers de banco garantindo a
integridade do saldo de estoque por setor.

---

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Frontend | HTML5, CSS3 customizado (sem framework), JavaScript Vanilla |
| Backend | PHP 8.x com PDO (prepared statements) |
| Banco de dados | MySQL / MariaDB (InnoDB, com triggers) |
| Autenticação | Sessão PHP + `password_hash()` / `password_verify()` (bcrypt) |
| Ambiente | XAMPP / LAMPP |

---

## Papéis de Acesso (RBAC)

| Papel | Acesso |
|---|---|
| **Administrador** | Acesso total: usuários, setores, log de auditoria, exclusões/inativações |
| **Almoxarife** | Cadastro/catálogo, movimentações de estoque, inventário, relatórios |
| **Solicitante** | Cria e acompanha requisições do próprio setor; vê alertas do próprio setor |

Não existe autocadastro público — usuários são criados exclusivamente pelo Administrador.

---

## Árvore Completa do Projeto

```
almoxarifado/
├── .htaccess                          # Options -Indexes (desativa listagem de pastas)
├── index.php                          # Redireciona para public/index.php
│
├── config/
│   ├── .htaccess                      # Bloqueia acesso HTTP direto (Require all denied)
│   ├── config.php                     # Constantes de ambiente (DB_HOST, DB_NAME, etc.)
│   └── Database.php                   # Conexão PDO (singleton)
│
├── src/
│   ├── .htaccess                      # Bloqueia acesso HTTP direto (Require all denied)
│   ├── Middlewares/
│   │   ├── AuthMiddleware.php         # Exige sessão ativa
│   │   └── RoleMiddleware.php         # Exige papel (RBAC) correto
│   └── Models/
│       ├── AlertaModel.php            # Alertas de estoque crítico e vencimento
│       ├── AuditoriaModel.php         # Consulta ao log de auditoria (filtros + paginação)
│       ├── CategoriaModel.php         # CRUD de categorias de produto
│       ├── EntradaModel.php           # Lançamento de itens recebidos (NF)
│       ├── FornecedorModel.php        # CRUD de fornecedores
│       ├── InventarioModel.php        # Inventário cíclico (contagem física)
│       ├── NotaFiscalModel.php        # Cadastro de notas fiscais (+ anexo)
│       ├── ProdutoModel.php           # CRUD de produtos (SKU, min/máx, custo)
│       ├── RelatorioModel.php         # Giro de estoque, curva ABC, custo por setor
│       ├── RequisicaoModel.php        # Requisições: criar, aprovar, atender, recusar
│       ├── SetorModel.php             # CRUD de setores da empresa
│       ├── TransferenciaModel.php     # Transferência de estoque entre setores
│       └── UsuarioModel.php           # CRUD de usuários + troca de senha
│
├── uploads/
│   ├── .htaccess                      # Bloqueia acesso HTTP direto (Require all denied)
│   └── notas_fiscais/                 # Anexos de NF (PDF/XML/JPG/PNG), servidos só via download_anexo.php
│
├── public/                            # Front controllers (uma pasta por módulo)
│   ├── index.php                      # Dashboard / painel principal
│   ├── login_process.php              # Processa o formulário de login
│   ├── logout.php                     # Encerra a sessão
│   │
│   ├── assets/css/
│   │   └── style.css                  # Folha de estilos única do sistema
│   │
│   ├── usuarios/                      # CRUD de usuários (Administrador)
│   │   ├── index.php
│   │   ├── criar.php
│   │   ├── editar.php
│   │   └── inativar.php
│   │
│   ├── categorias/                    # CRUD de categorias (Admin + Almoxarife)
│   │   ├── index.php
│   │   ├── criar.php
│   │   ├── editar.php
│   │   └── excluir.php
│   │
│   ├── setores/                       # CRUD de setores (Administrador)
│   │   ├── index.php
│   │   ├── criar.php
│   │   ├── editar.php
│   │   └── alternar_status.php
│   │
│   ├── fornecedores/                  # CRUD de fornecedores (Admin + Almoxarife)
│   │   ├── index.php
│   │   ├── criar.php
│   │   ├── editar.php
│   │   └── alternar_status.php
│   │
│   ├── produtos/                      # CRUD de produtos (Admin + Almoxarife)
│   │   ├── index.php
│   │   ├── criar.php
│   │   ├── editar.php
│   │   └── alternar_status.php
│   │
│   ├── notas_fiscais/                 # Cadastro de NF + anexo (Admin + Almoxarife)
│   │   ├── index.php
│   │   ├── criar.php
│   │   └── download_anexo.php
│   │
│   ├── entradas/                      # Lançamento de itens recebidos de uma NF
│   │   └── criar.php
│   │
│   ├── requisicoes/                   # Fluxo de requisição em duas etapas
│   │   ├── index.php                  # Lista (escopo por papel)
│   │   ├── criar.php                  # Solicitante cria a requisição
│   │   ├── aprovar.php                # Etapa 1: aprova ou recusa (não mexe em estoque)
│   │   └── atender.php                # Etapa 2: baixa o estoque de fato
│   │
│   ├── transferencias/                # Transferência de estoque entre setores
│   │   ├── index.php
│   │   └── criar.php
│   │
│   ├── inventario/                    # Inventário cíclico
│   │   ├── index.php                  # Contagem por setor
│   │   └── historico.php              # Histórico de contagens/divergências
│   │
│   ├── alertas/                       # Alertas de estoque crítico e vencimento
│   │   ├── index.php
│   │   └── resolver.php
│   │
│   ├── relatorios/                    # Indicadores (Admin + Almoxarife)
│   │   ├── index.php                  # Menu
│   │   ├── giro.php                   # Giro de estoque
│   │   ├── curva_abc.php              # Curva ABC de consumo
│   │   └── custo_setor.php            # Custo por setor
│   │
│   ├── auditoria/                     # Log de auditoria (Administrador)
│   │   └── index.php
│   │
│   └── conta/                         # Autoatendimento do próprio usuário
│       └── alterar_senha.php
│
└── views/                             # Espelha a estrutura de public/ (HTML/PHP de apresentação)
    ├── login.php
    ├── dashboard.php
    ├── erro_403.php
    │
    ├── usuarios/{listar,formulario}.php
    ├── categorias/{listar,formulario}.php
    ├── setores/{listar,formulario}.php
    ├── fornecedores/{listar,formulario}.php
    ├── produtos/{listar,formulario}.php
    ├── notas_fiscais/{listar,formulario}.php
    ├── entradas/formulario.php
    ├── requisicoes/{listar,criar,aprovar,atender}.php
    ├── transferencias/{listar,formulario}.php
    ├── inventario/{formulario,historico}.php
    ├── alertas/listar.php
    ├── relatorios/{menu,giro,curva_abc,custo_setor}.php
    ├── auditoria/listar.php
    └── conta/alterar_senha.php
```

---

## Módulos e Funcionalidades

### Cadastro e Catálogo
- **Categorias**, **Setores**, **Fornecedores**, **Produtos** (SKU único,
  estoque mínimo/máximo, controle de validade opcional, custo médio).
- Setores e Fornecedores usam inativação (soft delete) em vez de exclusão real,
  preservando o histórico de movimentações.

### Movimentação de Estoque
- **Entrada**: cadastro de Nota Fiscal (com anexo opcional) → lançamento dos
  itens recebidos, com criação automática de lote quando o produto controla
  validade.
- **Saída / Requisição** (fluxo em duas etapas):
  1. Solicitante cria a requisição (herdando o setor da própria sessão).
  2. Administrador/Almoxarife **aprova** (ou recusa) — ainda sem mexer em estoque.
  3. Administrador/Almoxarife **atende** a requisição já aprovada — só aqui o
     estoque é de fato baixado, validando saldo disponível no setor de origem.
- **Transferências** entre setores, com validação de saldo antes de mover.

### Controle e Alertas
- **Inventário cíclico**: contagem física por setor, comparando com o saldo
  do sistema e registrando divergência.
- **Alertas automáticos**: estoque crítico (gerado por trigger na saída) e
  vencimento próximo (gerado sob demanda ao abrir a tela de Alertas).

### Relatórios
- Giro de estoque, Curva ABC de consumo, Custo por setor — todos com seletor
  de período (30/90/180/365 dias).

### Administração
- Log de auditoria completo (quem fez o quê, com antes/depois em JSON),
  com filtros e paginação.
- Troca de senha pelo próprio usuário (exige senha atual).

---

## Saldo de Estoque por Setor

O sistema **não** usa um saldo único agregado por produto — cada setor
(incluindo o Almoxarifado Central) tem seu próprio saldo, controlado pela
tabela `estoque_setor`. `produtos.estoque_atual` é apenas o total agregado
(soma de todos os setores), mantido automaticamente por triggers.

Fluxo de movimentação de saldo:
- **Entrada** → soma no setor de destino (normalmente o Almoxarifado Central).
- **Saída/Requisição** → subtrai do setor de origem físico; o setor
  consumidor (quem solicitou) não recebe saldo — o material é expensado.
- **Transferência** → move saldo de um setor para outro; o total da empresa
  não muda.
- **Inventário** → ajusta o saldo do setor contado e recalcula o total do
  produto pela soma de `estoque_setor`.

---

## Instalação

1. **Banco de dados**: execute o script `schema_almoxarifado.sql` no
   phpMyAdmin (cria banco, tabelas, triggers e o seed inicial dos papéis).
2. **Usuário Administrador inicial**: rode o `INSERT` fornecido separadamente
   (senha padrão `TrocarSenha@123` — troque após o primeiro login).
3. **Configuração**: ajuste `config/config.php` com as credenciais do seu
   MySQL local, se necessário (usuário/senha padrão do XAMPP costuma ser
   `root` sem senha).
4. **Pasta de uploads**: confirme que `uploads/notas_fiscais/` existe e tem
   permissão de escrita para o usuário do Apache:
   ```
   chmod -R 775 uploads/notas_fiscais/
   ```
5. **Limites de upload**: no `php.ini`, confirme:
   ```
   upload_max_filesize = 5M
   post_max_size = 6M
   ```
6. Acesse `http://localhost/almoxarifado/` — deve redirecionar para o login.

---

## Segurança

- Senhas com `password_hash()` (bcrypt) — nunca texto puro.
- Todas as queries usam *prepared statements* via PDO.
- `config/`, `src/` e `uploads/` têm `.htaccess` com `Require all denied` —
  só acessíveis via `require`/`include` do PHP, nunca diretamente pelo navegador.
- Anexos de NF são servidos por um script (`download_anexo.php`) que exige
  login e papel antes de entregar o arquivo — não ficam num caminho público.
- CSRF, sessão com `session_regenerate_id()` após login, mensagens de erro
  genéricas no login (não revelam se o e-mail existe).

---

## Pendências / Evoluções Futuras

- Custo histórico real em `movimentacoes_saida` (hoje os relatórios usam o
  custo médio **atual** como aproximação, não o custo vigente na data da saída).
- Trava para impedir dois setores marcados como "Almoxarifado Central"
  simultaneamente.
- Paginação nas listagens que podem crescer bastante (produtos, requisições) —
  hoje só o Log de Auditoria tem.
