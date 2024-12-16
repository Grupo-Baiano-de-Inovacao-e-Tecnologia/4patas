CREATE DATABASE quatro_patas;
USE quatro_patas;

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_nascimento DATE NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    cep VARCHAR(10),
    rua VARCHAR(255),
    numero VARCHAR(10),
    complemento VARCHAR(255),
    bairro VARCHAR(255),
    cidade VARCHAR(255),
    estado VARCHAR(2),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE abrigo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cnpj_cpf VARCHAR(18) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    cep VARCHAR(10) NOT NULL,
    rua VARCHAR(255) NOT NULL,
    numero VARCHAR(10) NOT NULL,
    complemento VARCHAR(255),
    bairro VARCHAR(255) NOT NULL,
    cidade VARCHAR(255) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    site VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE animal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cao', 'gato') NOT NULL,
    nome VARCHAR(255) NOT NULL,
    data_nascimento DATE NOT NULL,
    sexo ENUM('macho', 'femea') NOT NULL,
    castrado ENUM('sim', 'nao') NOT NULL,
    porte ENUM('pequeno', 'medio', 'grande') NOT NULL,
    descricao TEXT NOT NULL,
    foto VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    abrigo_id INT NOT NULL,
    FOREIGN KEY (abrigo_id) REFERENCES abrigo(id) ON DELETE CASCADE
);

CREATE TABLE solicitacao_adocao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    animal_id INT NOT NULL,
    nome_usuario VARCHAR(255) NOT NULL,
    email_usuario VARCHAR(255) NOT NULL,
    telefone_usuario VARCHAR(20) NOT NULL,
    cpf_usuario VARCHAR(14) NOT NULL,
    cep_usuario VARCHAR(9) NOT NULL,
    idade_usuario INT NOT NULL,
    nome_animal VARCHAR(255) NOT NULL,
    idade_animal INT NOT NULL,
    sexo_animal ENUM('macho', 'femea') NOT NULL,
    formulario TEXT NOT NULL,
    status ENUM('analise', 'aprovado', 'recusado') DEFAULT 'analise',
    data_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_fim TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id),
    FOREIGN KEY (animal_id) REFERENCES animal(id)  ON DELETE CASCADE
);