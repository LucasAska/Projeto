SET NAMES utf8mb4;

INSERT INTO CATEGORIAS (NOME, SLUG) VALUES
('Brincos', 'brincos'),
('Cerâmica', 'ceramica'),
('Crochê', 'croche');

INSERT INTO PRODUTOS (CATEGORIA_ID, NOME, DESCRICAO, PRECO, ESTOQUE, IMAGEM) 
VALUES 
(1, 'Brinco de Prata', 'Brinco de prata com pedras preciosas.', 150.00, 10, 'brinco_prata.jpg'),
(2, 'Vaso de Cerâmica', 'Vaso de cerâmica pintado à mão.', 80.00, 5, 'vaso_ceramica.jpg'),
(3, 'Cachecol de Crochê', 'Cachecol feito à mão em crochê.', 60.00, 15, 'cachecol_croche.jpg');

INSERT INTO USUARIOS (NOME, TELEFONE, EMAIL, SENHA, CEP, LOGRADOURO, NUMERO, BAIRRO, CIDADE, UF, TIPO) 
VALUES ('Administrador', '11999999999', 'admin@loja.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe/W.9hK6/2YqW3c0yKkU2J6W7x6Wk5mG', '00000000', 'Rua do Admin', '0', 'Centro', 'São Paulo', 'SP', 'admin');


