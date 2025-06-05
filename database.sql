CREATE DATABASE IF NOT EXISTS mini_erp;
USE mini_erp;

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE produto_variacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    valor_adicional DECIMAL(10,2) DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

CREATE TABLE estoque (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    variacao_id INT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    quantidade_minima INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (variacao_id) REFERENCES produto_variacoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_estoque (produto_id, variacao_id)
);

CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    tipo ENUM('percentual', 'valor_fixo') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    valor_minimo DECIMAL(10,2) DEFAULT 0,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    limite_uso INT DEFAULT NULL,
    usado INT DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subtotal DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) DEFAULT 0,
    frete DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    cupom_id INT NULL,
    status ENUM('pendente', 'confirmado', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
    cliente_nome VARCHAR(255) NOT NULL,
    cliente_email VARCHAR(255) NOT NULL,
    cliente_telefone VARCHAR(20),
    endereco_cep VARCHAR(10) NOT NULL,
    endereco_logradouro VARCHAR(255) NOT NULL,
    endereco_numero VARCHAR(20) NOT NULL,
    endereco_complemento VARCHAR(255),
    endereco_bairro VARCHAR(255) NOT NULL,
    endereco_cidade VARCHAR(255) NOT NULL,
    endereco_uf VARCHAR(2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cupom_id) REFERENCES cupons(id)
);

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    variacao_id INT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id),
    FOREIGN KEY (variacao_id) REFERENCES produto_variacoes(id)
);

INSERT INTO produtos (nome, preco, descricao) VALUES
('Camiseta Básica', 29.90, 'Camiseta 100% algodão'),
('Calça Jeans', 89.90, 'Calça jeans tradicional'),
('Tênis Esportivo', 159.90, 'Tênis para corrida e caminhada');

INSERT INTO produto_variacoes (produto_id, nome, valor_adicional) VALUES
(1, 'P', 0),
(1, 'M', 0),
(1, 'G', 5.00),
(1, 'GG', 10.00),
(2, '36', 0),
(2, '38', 0),
(2, '40', 0),
(2, '42', 5.00),
(3, '37', 0),
(3, '38', 0),
(3, '39', 0),
(3, '40', 0),
(3, '41', 0),
(3, '42', 0);

INSERT INTO estoque (produto_id, variacao_id, quantidade, quantidade_minima) VALUES
(1, 1, 50, 10),
(1, 2, 75, 10),
(1, 3, 30, 10),
(1, 4, 20, 10),
(2, 5, 25, 5),
(2, 6, 30, 5),
(2, 7, 20, 5),
(2, 8, 15, 5),
(3, 9, 10, 3),
(3, 10, 15, 3),
(3, 11, 12, 3),
(3, 12, 8, 3),
(3, 13, 6, 3),
(3, 14, 4, 3);

INSERT INTO cupons (codigo, tipo, valor, valor_minimo, data_inicio, data_fim, limite_uso) VALUES
('DESCONTO10', 'percentual', 10.00, 50.00, '2024-01-01', '2024-12-31', 100),
('FRETE15', 'valor_fixo', 15.00, 100.00, '2024-01-01', '2024-12-31', 50),
('BEMVINDO', 'percentual', 15.00, 80.00, '2024-01-01', '2024-12-31', NULL);
