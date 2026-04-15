-- Pessoalize - Sistema de Gestão de Departamento Pessoal, RH e Financeiro
-- Banco de dados MySQL

CREATE DATABASE IF NOT EXISTS pessoalize CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pessoalize;

-- Tabela de usuários do sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo VARCHAR(50) DEFAULT 'operador',
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    rg VARCHAR(20),
    data_nascimento DATE,
    sexo ENUM('M','F','O') DEFAULT 'M',
    estado_civil VARCHAR(20),
    email VARCHAR(150),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    endereco VARCHAR(255),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    cargo VARCHAR(100),
    departamento_id INT,
    salario DECIMAL(10,2) DEFAULT 0.00,
    data_admissao DATE,
    data_demissao DATE,
    ctps VARCHAR(30),
    pis VARCHAR(20),
    status ENUM('ativo','inativo','ferias','afastado') DEFAULT 'ativo',
    observacoes TEXT,
    foto VARCHAR(255),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabela de currículos (candidatos)
CREATE TABLE IF NOT EXISTS curriculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    cpf VARCHAR(14),
    data_nascimento DATE,
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cargo_pretendido VARCHAR(100),
    pretensao_salarial DECIMAL(10,2),
    escolaridade VARCHAR(50),
    curso VARCHAR(100),
    instituicao VARCHAR(150),
    experiencia TEXT,
    habilidades TEXT,
    arquivo_cv VARCHAR(255),
    status ENUM('recebido','em_analise','aprovado','reprovado','contratado') DEFAULT 'recebido',
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de vagas para seleção
CREATE TABLE IF NOT EXISTS vagas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    departamento_id INT,
    descricao TEXT,
    requisitos TEXT,
    salario_min DECIMAL(10,2),
    salario_max DECIMAL(10,2),
    quantidade INT DEFAULT 1,
    status ENUM('aberta','em_selecao','fechada','cancelada') DEFAULT 'aberta',
    data_abertura DATE,
    data_encerramento DATE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabela de candidaturas (relaciona currículo com vaga)
CREATE TABLE IF NOT EXISTS candidaturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    curriculo_id INT NOT NULL,
    vaga_id INT NOT NULL,
    status ENUM('inscrito','em_analise','entrevista','aprovado','reprovado') DEFAULT 'inscrito',
    nota DECIMAL(5,2),
    parecer TEXT,
    data_entrevista DATETIME,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (curriculo_id) REFERENCES curriculos(id) ON DELETE CASCADE,
    FOREIGN KEY (vaga_id) REFERENCES vagas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de categorias financeiras
CREATE TABLE IF NOT EXISTS categorias_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('receita','despesa') NOT NULL,
    descricao TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de contas a pagar/receber (boletos e financeiro)
CREATE TABLE IF NOT EXISTS contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('pagar','receber') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    categoria_id INT,
    fornecedor_cliente VARCHAR(200),
    valor DECIMAL(12,2) NOT NULL,
    valor_pago DECIMAL(12,2) DEFAULT 0.00,
    data_emissao DATE,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    status ENUM('pendente','pago','vencido','cancelado') DEFAULT 'pendente',
    forma_pagamento VARCHAR(50),
    numero_documento VARCHAR(50),
    codigo_barras VARCHAR(100),
    observacoes TEXT,
    funcionario_id INT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_financeiras(id) ON DELETE SET NULL,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabela de contatos para notificações (WhatsApp e SMS)
CREATE TABLE IF NOT EXISTS notificacao_contatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    whatsapp VARCHAR(20),
    receber_whatsapp TINYINT(1) DEFAULT 1,
    receber_sms TINYINT(1) DEFAULT 1,
    tipos_notificacao SET('vencimentos','pagamentos','avisos','rh','aniversarios') DEFAULT 'vencimentos,avisos',
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de log de notificações enviadas
CREATE TABLE IF NOT EXISTS notificacao_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contato_id INT,
    tipo ENUM('whatsapp','sms') NOT NULL,
    assunto VARCHAR(200),
    mensagem TEXT,
    status ENUM('enviado','falha','pendente') DEFAULT 'pendente',
    enviado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contato_id) REFERENCES notificacao_contatos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Dados iniciais

-- Usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, cargo) VALUES
('Administrador', 'admin@pessoalize.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Departamentos iniciais
INSERT INTO departamentos (nome, descricao) VALUES
('Administrativo', 'Departamento administrativo'),
('Recursos Humanos', 'Departamento de RH'),
('Financeiro', 'Departamento financeiro'),
('Operacional', 'Departamento operacional'),
('Comercial', 'Departamento comercial');

-- Categorias financeiras iniciais
INSERT INTO categorias_financeiras (nome, tipo, descricao) VALUES
('Salários', 'despesa', 'Pagamento de salários'),
('Benefícios', 'despesa', 'Vale transporte, alimentação, etc'),
('Aluguel', 'despesa', 'Aluguel de imóvel'),
('Energia', 'despesa', 'Conta de energia elétrica'),
('Água', 'despesa', 'Conta de água'),
('Internet/Telefone', 'despesa', 'Telecomunicações'),
('Material de Escritório', 'despesa', 'Suprimentos e materiais'),
('Impostos', 'despesa', 'Impostos e taxas'),
('Serviços Prestados', 'receita', 'Receita de serviços'),
('Vendas', 'receita', 'Receita de vendas'),
('Outros', 'despesa', 'Outras despesas');
