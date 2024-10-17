CREATE DATABASE soletrando;

CREATE TABLE avatarcrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    nome varchar(50) NOT NULL,
    arquivo varchar(50) NOT NULL,
    PRIMARY KEY (id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE emojicrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    nome varchar(50) NOT NULL,
    tipo varchar(10) NOT NULL,
    arquivo varchar(50) NOT NULL,
    PRIMARY KEY (id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE palavracrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    palavra varchar(20) NOT NULL,
    nivel int(11) NOT NULL,
    serie_ano varchar(50) NOT NULL,
    arquivo varchar(50) NOT NULL,
    PRIMARY KEY (id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE perfilcrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    nome varchar(30) NOT NULL,
    PRIMARY KEY (id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE seriecrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    ano int(11) NOT NULL,
    turma varchar(3) NOT NULL,
    PRIMARY KEY (id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pessoacrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    nome varchar(200) NOT NULL,
    data_nascimento DATE NOT NULL,
    acerto int(11) NOT NULL DEFAULT 0,
    erro int(11) NOT NULL DEFAULT 0,
    token varchar(50) NOT NULL,
    termo varchar(100) NOT NULL,
    status varchar(10) NOT NULL DEFAULT 'ativo',
    id_perfil int(11) NOT NULL,
    id_serie int(11) DEFAULT NULL,
    id_avatar int(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    CONSTRAINT FK_pessoa_perfil FOREIGN KEY (id_perfil) REFERENCES perfilcrf(id),
    CONSTRAINT FK_pessoa_serie FOREIGN KEY (id_serie) REFERENCES seriecrf(id),
    CONSTRAINT FK_pessoa_avatar FOREIGN KEY (id_avatar) REFERENCES avatarcrf(id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE contagemcrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    data date NOT NULL,
    jogadas int(11) DEFAULT NULL,
    usadas varchar(500) DEFAULT NULL,
    id_pessoa int(11) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT FK_contagem_pessoa FOREIGN KEY(id_pessoa) REFERENCES pessoacrf(id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE logincrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    usuario varchar(50) NOT NULL,
    senha varchar(50) NOT NULL,
    id_pessoa int(11) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT FK_login_pessoa FOREIGN KEY (id_pessoa) REFERENCES pessoacrf(id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE precadastrocrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    nome varchar(200) NOT NULL,
    data_nascimento DATE NOT NULL,
    id_serie int(11) DEFAULT NULL,
    status varchar(10) NOT NULL DEFAULT 'ativo',
    chave varchar(50) NOT NULL,    
    id_perfil int(11) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT FK_precadastro_serie FOREIGN KEY (id_serie) REFERENCES seriecrf(id),
    CONSTRAINT FK_precadastro_perfil FOREIGN KEY (id_perfil) REFERENCES perfilcrf(id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE rankingcrf(
    id int(11) NOT NULL AUTO_INCREMENT,
    resultado varchar(10) NOT NULL,
    palavra_escrita varchar(20) DEFAULT NULL,
    data datetime NOT NULL DEFAULT current_timestamp(),
    id_palavra int(11) NOT NULL,
    id_pessoa int(11) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT FK_ranking_palavra FOREIGN KEY (id_palavra) REFERENCES palavracrf(id),
    CONSTRAINT FK_ranking_pessoa FOREIGN KEY (id_pessoa) REFERENCES pessoacrf(id)
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO perfilcrf (nome) VALUES ('aluno');
INSERT INTO perfilcrf (nome) VALUES ('professor');
INSERT INTO perfilcrf (nome) VALUES ('gestor');
INSERT INTO perfilcrf (nome) VALUES ('administrador');


INSERT INTO seriecrf (ano, turma) VALUES (1,'a');
INSERT INTO seriecrf (ano, turma) VALUES (1,'b');
INSERT INTO seriecrf (ano, turma) VALUES (1,'c');
INSERT INTO seriecrf (ano, turma) VALUES (1,'d');
INSERT INTO seriecrf (ano, turma) VALUES (1,'e');
INSERT INTO seriecrf (ano, turma) VALUES (1,'f');
INSERT INTO seriecrf (ano, turma) VALUES (2,'a'),(2,'b'),(2,'c'),(2,'d'),(2,'e'),(2,'f');
INSERT INTO seriecrf (ano, turma) VALUES (3,'a'),(3,'b'),(3,'c'),(3,'d'),(3,'e');
INSERT INTO seriecrf (ano, turma) VALUES (4,'a'),(4,'b'),(4,'c'),(4,'d'),(4,'e'),(4,'f'),(4,'g');
INSERT INTO seriecrf (ano, turma) VALUES (5,'a'),(5,'b'),(5,'c'),(5,'d'),(5,'e'),(5,'f');


INSERT INTO avatarcrf (nome, arquivo) VALUES ('avatar (padrão)', 'avatar.png');


INSERT INTO precadastrocrf(nome, data_nascimento, chave, id_perfil) VALUES ('Jhonatan Barbosa Mendes', '1993-01-11', 'abcde12345', '4');