DROP TABLE IF EXISTS USUARIOS;
CREATE TABLE USUARIOS(
    id INT AUTO_INCREMENT PRIMARY KEY,

    NOME VARCHAR(100) NOT NULL,
    TELEFONE VARCHAR(20) NOT NULL,
    EMAIL VARCHAR(150) NOT NULL UNIQUE,
    SENHA VARCHAR(255) NOT NULL,
    
    -- endereço
    CEP VARCHAR(9) NOT NULL,
    LOGRADOURO VARCHAR(255) NOT NULL,
    NUMERO VARCHAR(20) NOT NULL,
    COMPLEMENTO VARCHAR(100) DEFAULT NULL,
    BAIRRO VARCHAR(100) NOT NULL,
    CIDADE VARCHAR(100) NOT NULL,
    UF CHAR(2) NOT NULL,

    -- Tipo de usuario criado / foi setado cliente como inicial
    TIPO ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente'
)ENGINE=InnoDB;

-- Criou a categoria da qual os produtos vão se ingressar
DROP TABLE IF EXISTS CATEGORIAS;
CREATE TABLE CATEGORIAS (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    NOME VARCHAR(70) NOT NULL,
    -- Slug é o nome de forma simples para poder ser utilizado em filtros e urls por isso tem que se unico
    SLUG VARCHAR(70) NOT NULL UNIQUE
)ENGINE=innoDB;

INSERT INTO CATEGORIAS (NOME, SLUG) VALUES
('Brincos', 'brincos'),
('Cerâmica', 'ceramica'),
('Croche', 'croche');



