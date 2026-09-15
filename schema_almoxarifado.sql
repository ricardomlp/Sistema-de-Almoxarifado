-- =====================================================================
-- SISTEMA DE ALMOXARIFADO - Gestão de Estoque e Suprimentos Internos
-- Banco: MySQL / MariaDB (InnoDB)
-- Ambiente: XAMPP
-- v2: saldo de estoque controlado POR SETOR (estoque_setor)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS almoxarifado
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE almoxarifado;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 1. CONTROLE DE ACESSO (RBAC)
-- =====================================================================

CREATE TABLE papeis (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(50) NOT NULL UNIQUE, -- Administrador | Almoxarife | Solicitante
    descricao       VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE setores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100) NOT NULL,
    eh_almoxarifado_central TINYINT(1) NOT NULL DEFAULT 0, -- marca o(s) setor(es) que funcionam como depósito/origem de recebimento
    responsavel     VARCHAR(150) NULL,
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(150) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    senha_hash      VARCHAR(255) NOT NULL,
    papel_id        INT UNSIGNED NOT NULL,
    setor_id        INT UNSIGNED NULL,
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_papel  FOREIGN KEY (papel_id) REFERENCES papeis(id),
    CONSTRAINT fk_usuarios_setor  FOREIGN KEY (setor_id) REFERENCES setores(id)
) ENGINE=InnoDB;

-- =====================================================================
-- 2. CADASTRO / CATÁLOGO
-- =====================================================================

CREATE TABLE categorias (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100) NOT NULL UNIQUE,
    descricao       VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE fornecedores (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    razao_social    VARCHAR(150) NOT NULL,
    cnpj            VARCHAR(18) NOT NULL UNIQUE,
    contato         VARCHAR(100) NULL,
    telefone        VARCHAR(20) NULL,
    email           VARCHAR(150) NULL,
    ativo           TINYINT(1) NOT NULL DEFAULT 1,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE produtos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku                 VARCHAR(50) NOT NULL UNIQUE,
    nome                VARCHAR(150) NOT NULL,
    descricao           VARCHAR(255) NULL,
    categoria_id        INT UNSIGNED NOT NULL,
    unidade_medida      VARCHAR(10) NOT NULL DEFAULT 'UN', -- UN, CX, KG, L...
    estoque_minimo      DECIMAL(12,2) NOT NULL DEFAULT 0,
    estoque_maximo      DECIMAL(12,2) NOT NULL DEFAULT 0,
    estoque_atual       DECIMAL(12,2) NOT NULL DEFAULT 0,  -- TOTAL AGREGADO (soma de estoque_setor), mantido via trigger
    controla_validade   TINYINT(1) NOT NULL DEFAULT 0,
    custo_medio         DECIMAL(12,2) NOT NULL DEFAULT 0,
    ativo               TINYINT(1) NOT NULL DEFAULT 1,
    criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    CONSTRAINT chk_estoque_min_max CHECK (estoque_maximo >= estoque_minimo)
) ENGINE=InnoDB;

CREATE TABLE lotes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id      INT UNSIGNED NOT NULL,
    numero_lote     VARCHAR(50) NOT NULL,
    data_validade   DATE NULL,
    quantidade_atual DECIMAL(12,2) NOT NULL DEFAULT 0,
    fornecedor_id   INT UNSIGNED NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lotes_produto     FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_lotes_fornecedor  FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    UNIQUE KEY uq_lote_produto (produto_id, numero_lote)
) ENGINE=InnoDB;

-- =====================================================================
-- 2.1 SALDO DE ESTOQUE POR SETOR (nova tabela central desta versão)
-- =====================================================================
-- Cada linha representa quanto de um produto está fisicamente
-- armazenado em um determinado setor (incluindo o Almoxarifado Central,
-- que é apenas um "setor" com eh_almoxarifado_central = 1).

CREATE TABLE estoque_setor (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id      INT UNSIGNED NOT NULL,
    setor_id        INT UNSIGNED NOT NULL,
    quantidade      DECIMAL(12,2) NOT NULL DEFAULT 0,
    atualizado_em   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_estsetor_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_estsetor_setor   FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT chk_estsetor_qtd CHECK (quantidade >= 0),
    UNIQUE KEY uq_produto_setor (produto_id, setor_id)
) ENGINE=InnoDB;

CREATE INDEX idx_estsetor_setor ON estoque_setor(setor_id);

-- =====================================================================
-- 3. ENTRADA DE ESTOQUE (Recebimento / NF)
-- =====================================================================

CREATE TABLE notas_fiscais (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero_nf           VARCHAR(50) NOT NULL,
    fornecedor_id       INT UNSIGNED NOT NULL,
    data_emissao        DATE NOT NULL,
    data_recebimento    DATE NOT NULL,
    valor_total         DECIMAL(14,2) NOT NULL DEFAULT 0,
    arquivo_anexo       VARCHAR(255) NULL,  -- caminho do XML/PDF
    usuario_id          INT UNSIGNED NOT NULL, -- quem registrou o recebimento
    criado_em           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nf_fornecedor FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id),
    CONSTRAINT fk_nf_usuario    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY uq_nf_fornecedor (numero_nf, fornecedor_id)
) ENGINE=InnoDB;

CREATE TABLE movimentacoes_entrada (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nf_id               INT UNSIGNED NOT NULL,
    produto_id          INT UNSIGNED NOT NULL,
    lote_id             INT UNSIGNED NULL,
    setor_destino_id    INT UNSIGNED NOT NULL, -- local físico onde o material entra (normalmente o Almoxarifado Central)
    quantidade          DECIMAL(12,2) NOT NULL,
    custo_unitario      DECIMAL(12,2) NOT NULL,
    usuario_id          INT UNSIGNED NOT NULL,
    data_movimentacao   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mov_ent_nf       FOREIGN KEY (nf_id) REFERENCES notas_fiscais(id),
    CONSTRAINT fk_mov_ent_produto  FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_mov_ent_lote     FOREIGN KEY (lote_id) REFERENCES lotes(id),
    CONSTRAINT fk_mov_ent_setor    FOREIGN KEY (setor_destino_id) REFERENCES setores(id),
    CONSTRAINT fk_mov_ent_usuario  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT chk_mov_ent_qtd CHECK (quantidade > 0)
) ENGINE=InnoDB;

-- =====================================================================
-- 4. SAÍDA / CONSUMO INTERNO (Requisições por Setor)
-- =====================================================================

CREATE TABLE requisicoes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setor_id            INT UNSIGNED NOT NULL, -- setor SOLICITANTE (consumidor, usado no relatório de custo)
    solicitante_id      INT UNSIGNED NOT NULL,
    aprovador_id        INT UNSIGNED NULL,
    status              ENUM('pendente','aprovada','atendida','recusada') NOT NULL DEFAULT 'pendente',
    data_solicitacao    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atendimento    DATETIME NULL,
    observacao          VARCHAR(255) NULL,
    CONSTRAINT fk_req_setor       FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_req_solicitante FOREIGN KEY (solicitante_id) REFERENCES usuarios(id),
    CONSTRAINT fk_req_aprovador   FOREIGN KEY (aprovador_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE requisicoes_itens (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requisicao_id           INT UNSIGNED NOT NULL,
    produto_id              INT UNSIGNED NOT NULL,
    quantidade_solicitada   DECIMAL(12,2) NOT NULL,
    quantidade_atendida     DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_reqitem_requisicao FOREIGN KEY (requisicao_id) REFERENCES requisicoes(id) ON DELETE CASCADE,
    CONSTRAINT fk_reqitem_produto    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT chk_reqitem_qtd CHECK (quantidade_solicitada > 0)
) ENGINE=InnoDB;

CREATE TABLE movimentacoes_saida (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requisicao_id       INT UNSIGNED NULL, -- nullable p/ baixas administrativas sem requisição formal
    produto_id          INT UNSIGNED NOT NULL,
    lote_id             INT UNSIGNED NULL,
    setor_origem_id     INT UNSIGNED NOT NULL, -- de onde o estoque FISICAMENTE sai (ex.: Almoxarifado Central)
    setor_id            INT UNSIGNED NOT NULL, -- setor CONSUMIDOR (para o relatório de custo por setor)
    quantidade          DECIMAL(12,2) NOT NULL,
    usuario_id          INT UNSIGNED NOT NULL, -- almoxarife que atendeu
    data_movimentacao   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mov_sai_requisicao FOREIGN KEY (requisicao_id) REFERENCES requisicoes(id),
    CONSTRAINT fk_mov_sai_produto    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_mov_sai_lote       FOREIGN KEY (lote_id) REFERENCES lotes(id),
    CONSTRAINT fk_mov_sai_origem     FOREIGN KEY (setor_origem_id) REFERENCES setores(id),
    CONSTRAINT fk_mov_sai_setor      FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_mov_sai_usuario    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT chk_mov_sai_qtd CHECK (quantidade > 0)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. TRANSFERÊNCIAS ENTRE SETORES (agora movimenta saldo real de ambos)
-- =====================================================================

CREATE TABLE transferencias (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id          INT UNSIGNED NOT NULL,
    setor_origem_id     INT UNSIGNED NOT NULL,
    setor_destino_id    INT UNSIGNED NOT NULL,
    quantidade          DECIMAL(12,2) NOT NULL,
    usuario_id          INT UNSIGNED NOT NULL,
    motivo              VARCHAR(255) NULL,
    data_transferencia  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transf_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_transf_origem  FOREIGN KEY (setor_origem_id) REFERENCES setores(id),
    CONSTRAINT fk_transf_destino FOREIGN KEY (setor_destino_id) REFERENCES setores(id),
    CONSTRAINT fk_transf_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT chk_transf_qtd CHECK (quantidade > 0),
    CONSTRAINT chk_transf_setores CHECK (setor_origem_id <> setor_destino_id)
) ENGINE=InnoDB;

-- =====================================================================
-- 6. INVENTÁRIO CÍCLICO E ALERTAS (contagem agora é POR SETOR)
-- =====================================================================

CREATE TABLE inventarios_ciclicos (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produto_id          INT UNSIGNED NOT NULL,
    setor_id            INT UNSIGNED NOT NULL, -- local onde a contagem física foi feita
    quantidade_sistema  DECIMAL(12,2) NOT NULL,
    quantidade_contada  DECIMAL(12,2) NOT NULL,
    divergencia         DECIMAL(12,2) GENERATED ALWAYS AS (quantidade_contada - quantidade_sistema) STORED,
    usuario_id          INT UNSIGNED NOT NULL,
    observacao          VARCHAR(255) NULL,
    data_contagem       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inv_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_inv_setor   FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_inv_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE alertas (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo            ENUM('estoque_critico','vencimento_proximo') NOT NULL,
    produto_id      INT UNSIGNED NOT NULL,
    setor_id        INT UNSIGNED NULL, -- setor onde o estoque crítico foi detectado (NULL = alerta agregado da empresa)
    lote_id         INT UNSIGNED NULL,
    data_gerado     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolvido       TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_alerta_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
    CONSTRAINT fk_alerta_setor   FOREIGN KEY (setor_id) REFERENCES setores(id),
    CONSTRAINT fk_alerta_lote    FOREIGN KEY (lote_id) REFERENCES lotes(id)
) ENGINE=InnoDB;

-- =====================================================================
-- 7. LOG DE AUDITORIA
-- =====================================================================

CREATE TABLE log_auditoria (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED NULL,
    acao            ENUM('CREATE','UPDATE','DELETE') NOT NULL,
    tabela_afetada  VARCHAR(60) NOT NULL,
    registro_id     INT UNSIGNED NOT NULL,
    dados_anteriores JSON NULL,
    dados_novos      JSON NULL,
    data_hora       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- 8. ÍNDICES DE APOIO (consultas de dashboard/relatórios)
-- =====================================================================

CREATE INDEX idx_produtos_categoria      ON produtos(categoria_id);
CREATE INDEX idx_produtos_estoque_atual  ON produtos(estoque_atual);
CREATE INDEX idx_lotes_validade          ON lotes(data_validade);
CREATE INDEX idx_mov_entrada_produto_dt  ON movimentacoes_entrada(produto_id, data_movimentacao);
CREATE INDEX idx_mov_saida_produto_dt    ON movimentacoes_saida(produto_id, data_movimentacao);
CREATE INDEX idx_mov_saida_setor_dt      ON movimentacoes_saida(setor_id, data_movimentacao);
CREATE INDEX idx_requisicoes_status      ON requisicoes(status);
CREATE INDEX idx_alertas_resolvido       ON alertas(resolvido, tipo);
CREATE INDEX idx_log_tabela_registro     ON log_auditoria(tabela_afetada, registro_id);

-- =====================================================================
-- 9. TRIGGERS - Manutenção automática de estoque_setor e estoque_atual
-- =====================================================================

DELIMITER $$

-- ---- ENTRADA: soma no saldo do setor de destino, no lote e no total do produto
CREATE TRIGGER trg_after_insert_entrada
AFTER INSERT ON movimentacoes_entrada
FOR EACH ROW
BEGIN
    INSERT INTO estoque_setor (produto_id, setor_id, quantidade)
    VALUES (NEW.produto_id, NEW.setor_destino_id, NEW.quantidade)
    ON DUPLICATE KEY UPDATE quantidade = quantidade + NEW.quantidade;

    UPDATE produtos
       SET estoque_atual = estoque_atual + NEW.quantidade
     WHERE id = NEW.produto_id;

    IF NEW.lote_id IS NOT NULL THEN
        UPDATE lotes
           SET quantidade_atual = quantidade_atual + NEW.quantidade
         WHERE id = NEW.lote_id;
    END IF;
END$$

-- ---- SAÍDA/CONSUMO: subtrai do saldo do setor de ORIGEM (físico) e do total do produto
--      (o setor consumidor, setor_id, NÃO recebe saldo — o material é expensado)
CREATE TRIGGER trg_after_insert_saida
AFTER INSERT ON movimentacoes_saida
FOR EACH ROW
BEGIN
    UPDATE estoque_setor
       SET quantidade = quantidade - NEW.quantidade
     WHERE produto_id = NEW.produto_id
       AND setor_id = NEW.setor_origem_id;

    UPDATE produtos
       SET estoque_atual = estoque_atual - NEW.quantidade
     WHERE id = NEW.produto_id;

    IF NEW.lote_id IS NOT NULL THEN
        UPDATE lotes
           SET quantidade_atual = quantidade_atual - NEW.quantidade
         WHERE id = NEW.lote_id;
    END IF;

    -- Alerta de estoque crítico no setor de origem (físico)
    INSERT INTO alertas (tipo, produto_id, setor_id, data_gerado, resolvido)
    SELECT 'estoque_critico', p.id, NEW.setor_origem_id, NOW(), 0
      FROM produtos p
     WHERE p.id = NEW.produto_id
       AND p.estoque_atual <= p.estoque_minimo
       AND NOT EXISTS (
            SELECT 1 FROM alertas a
             WHERE a.produto_id = p.id
               AND a.setor_id = NEW.setor_origem_id
               AND a.tipo = 'estoque_critico'
               AND a.resolvido = 0
       );
END$$

-- ---- TRANSFERÊNCIA: move saldo de um setor para outro; o TOTAL do produto não muda
CREATE TRIGGER trg_after_insert_transferencia
AFTER INSERT ON transferencias
FOR EACH ROW
BEGIN
    UPDATE estoque_setor
       SET quantidade = quantidade - NEW.quantidade
     WHERE produto_id = NEW.produto_id
       AND setor_id = NEW.setor_origem_id;

    INSERT INTO estoque_setor (produto_id, setor_id, quantidade)
    VALUES (NEW.produto_id, NEW.setor_destino_id, NEW.quantidade)
    ON DUPLICATE KEY UPDATE quantidade = quantidade + NEW.quantidade;
END$$

-- ---- INVENTÁRIO CÍCLICO: ajusta o saldo do setor contado e recalcula o total do produto
CREATE TRIGGER trg_after_insert_inventario
AFTER INSERT ON inventarios_ciclicos
FOR EACH ROW
BEGIN
    INSERT INTO estoque_setor (produto_id, setor_id, quantidade)
    VALUES (NEW.produto_id, NEW.setor_id, NEW.quantidade_contada)
    ON DUPLICATE KEY UPDATE quantidade = NEW.quantidade_contada;

    UPDATE produtos p
       SET estoque_atual = (
            SELECT COALESCE(SUM(es.quantidade), 0)
              FROM estoque_setor es
             WHERE es.produto_id = NEW.produto_id
       )
     WHERE p.id = NEW.produto_id;
END$$

DELIMITER ;

-- =====================================================================
-- 10. DADOS INICIAIS (seed) - Papéis padrão do RBAC
-- =====================================================================

INSERT INTO papeis (nome, descricao) VALUES
    ('Administrador', 'Acesso total ao sistema, incluindo exclusões e configurações'),
    ('Almoxarife',    'Opera entradas, saídas, transferências e inventário'),
    ('Solicitante',   'Cria e acompanha requisições do próprio setor');

-- Exemplo: setor que funciona como Almoxarifado Central (origem de todo recebimento)
INSERT INTO setores (nome, eh_almoxarifado_central, ativo) VALUES
    ('Almoxarifado Central', 1, 1);
