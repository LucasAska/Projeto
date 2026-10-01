SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS milka_mics
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE milka_mics;

SET NAMES utf8mb4;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  celular VARCHAR(20) DEFAULT NULL,
  endereco VARCHAR(255) DEFAULT NULL,
  senha VARCHAR(255) NOT NULL,
  tipo ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO categorias (nome, slug) VALUES
('Brincos', 'brincos'),
('Cerâmica', 'ceramica'),
('Crochê', 'croche');

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NOT NULL,
  nome VARCHAR(150) NOT NULL,
  descricao TEXT,
  preco DECIMAL(10,2) NOT NULL,
  estoque INT NOT NULL DEFAULT 0,
  imagem VARCHAR(255) DEFAULT NULL,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_prod_cat FOREIGN KEY (categoria_id)
    REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE produto_categoria (
  produto_id INT NOT NULL,
  categoria_id INT NOT NULL,
  PRIMARY KEY (produto_id, categoria_id),
  CONSTRAINT fk_pc_produto FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
  CONSTRAINT fk_pc_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('pendente','pago','enviado','cancelado') NOT NULL DEFAULT 'pendente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ped_usu FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedido_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL,
  preco_unitario DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_item_ped FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_prod FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, destaque) VALUES
(1, 'Brinco Argola Trançada', 'Argola artesanal em fio de cobre banhado a ouro.', 59.90, 12, 1),
(1, 'Brinco de Cerâmica Floral', 'Brinco leve com pintura manual em cerâmica esmaltada.', 74.50, 8, 1),
(1, 'Brinco Cascata Oliva', 'Contas de vidro em tons de verde oliva.', 45.00, 20, 0),
(2, 'Caneca Rústica Oliva', 'Caneca em cerâmica queimada a alta temperatura, 300ml.', 89.00, 10, 1),
(2, 'Vaso Minimalista', 'Vaso decorativo esmaltado em tom terroso, 18cm.', 129.00, 5, 0),
(2, 'Prato Decorativo', 'Prato de parede com esmalte verde oliva e detalhes dourados.', 98.00, 7, 0),
(3, 'Blusa de Crochê Manga Longa', 'Blusa 100% algodão, fio natural, tamanho único.', 189.00, 6, 1),
(3, 'Top de Crochê Verão', 'Top de crochê em fio de algodão na cor cru.', 119.90, 9, 1),
(3, 'Cachecol de Crochê', 'Cachecol macio em lã merino, tons de oliva.', 139.00, 4, 0),
(1, 'Brinco de Coração em Crochê',
 'Brinco artesanal em crochê com fio vermelho, formato de coração. Alça em metal prateado.',
 39.90, 15, 1);

INSERT INTO produto_categoria (produto_id, categoria_id)
SELECT p.id, c.id FROM produtos p, categorias c
WHERE p.nome = 'Brinco de Coração em Crochê' AND c.slug IN ('brincos','croche');

INSERT IGNORE INTO produto_categoria (produto_id, categoria_id)
SELECT id, categoria_id FROM produtos WHERE categoria_id IS NOT NULL;

INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Administradora Milka Mics',
 'admin@milkamics.com',
 '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfQcEDFvJ9K9qZbXG6m1XwH3yF5aJu7O',
 'admin');