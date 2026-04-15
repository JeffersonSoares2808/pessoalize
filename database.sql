-- Pessoalize - Sistema de Gestão de Departamento Pessoal, RH e Financeiro
-- Banco de dados MySQL
--
-- IMPORTANTE: Antes de importar este arquivo, crie o banco de dados e selecione-o.
-- No phpMyAdmin (Hostinger), selecione o banco de dados no painel lateral antes de importar.
-- Exemplo local: CREATE DATABASE pessoalize CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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

-- Tabela de treinamentos
CREATE TABLE IF NOT EXISTS treinamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    instrutor VARCHAR(150),
    instituicao VARCHAR(200),
    carga_horaria DECIMAL(6,1) NOT NULL DEFAULT 0.0,
    data_inicio DATE,
    data_fim DATE,
    local_treinamento VARCHAR(200),
    modalidade ENUM('presencial','online','hibrido') DEFAULT 'presencial',
    status ENUM('planejado','em_andamento','concluido','cancelado') DEFAULT 'planejado',
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de participantes de treinamento
CREATE TABLE IF NOT EXISTS treinamento_participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treinamento_id INT NOT NULL,
    funcionario_id INT NOT NULL,
    status ENUM('inscrito','em_andamento','concluido','reprovado','desistente') DEFAULT 'inscrito',
    nota DECIMAL(5,2),
    certificado_arquivo VARCHAR(255),
    certificado_nome_original VARCHAR(255),
    data_inscricao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_conclusao DATE,
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (treinamento_id) REFERENCES treinamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participante (treinamento_id, funcionario_id)
) ENGINE=InnoDB;

-- Tabela de logs da IA
CREATE TABLE IF NOT EXISTS ia_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    pergunta TEXT NOT NULL,
    resposta TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabela de notificações internas do sistema (avisos automáticos)
CREATE TABLE IF NOT EXISTS notificacoes_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('vencimentos','pagamentos','avisos','rh','aniversarios') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensagem TEXT,
    nivel ENUM('info','success','warning','danger','primary') DEFAULT 'info',
    referencia_id INT,
    referencia_tipo VARCHAR(50),
    lida TINYINT(1) DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de lembretes / agenda de avisos
CREATE TABLE IF NOT EXISTS lembretes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    tipo ENUM('servico','pagamento','reuniao','prazo','outro') NOT NULL DEFAULT 'outro',
    data_lembrete DATE NOT NULL,
    hora_lembrete TIME,
    recorrencia ENUM('nenhuma','diaria','semanal','mensal','anual') DEFAULT 'nenhuma',
    prioridade ENUM('baixa','media','alta','urgente') DEFAULT 'media',
    status ENUM('pendente','concluido','cancelado') DEFAULT 'pendente',
    funcionario_id INT,
    conta_id INT,
    observacoes TEXT,
    criado_por INT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_id) REFERENCES contas(id) ON DELETE SET NULL,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Dados iniciais

-- Usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, cargo) VALUES
('Administrador', 'admin@pessoalize.com', '$2y$10$B8q.hTzgW25wXtfeKyYw1uwVKyzNgzxkiHnVy5JeTn5Sxv21fH0Vy', 'admin');

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

-- ============================================================
-- Módulo RDC - Conformidade Regulatória (ANVISA / MAPA)
-- ============================================================

-- Tabela de normas RDC
CREATE TABLE IF NOT EXISTS rdc_normas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) NOT NULL,
    titulo VARCHAR(300) NOT NULL,
    orgao ENUM('ANVISA','MAPA','INMETRO','OUTRO') DEFAULT 'ANVISA',
    data_publicacao DATE,
    data_vigencia DATE,
    descricao TEXT,
    categoria VARCHAR(100),
    status ENUM('vigente','revogada','alterada') DEFAULT 'vigente',
    url_oficial VARCHAR(500),
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de itens de conformidade (checklist de cada norma)
CREATE TABLE IF NOT EXISTS rdc_itens_conformidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    norma_id INT NOT NULL,
    codigo VARCHAR(30),
    descricao TEXT NOT NULL,
    criticidade ENUM('critico','maior','menor','informativo') DEFAULT 'maior',
    evidencia_necessaria TEXT,
    ordem INT DEFAULT 0,
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (norma_id) REFERENCES rdc_normas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de verificações de conformidade
CREATE TABLE IF NOT EXISTS rdc_verificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    status ENUM('conforme','nao_conforme','parcial','nao_aplicavel','pendente') DEFAULT 'pendente',
    evidencia TEXT,
    responsavel VARCHAR(150),
    data_verificacao DATE,
    data_proxima_verificacao DATE,
    plano_acao TEXT,
    verificado_por INT,
    observacoes TEXT,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES rdc_itens_conformidade(id) ON DELETE CASCADE,
    FOREIGN KEY (verificado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Dados iniciais: Principais normas RDC para laboratórios
INSERT INTO rdc_normas (numero, titulo, orgao, data_publicacao, descricao, categoria, status) VALUES
('RDC 786/2023', 'Requisitos para Funcionamento de Laboratórios Clínicos', 'ANVISA', '2023-05-12',
 'Dispõe sobre os requisitos técnicos para o funcionamento de laboratórios clínicos e postos de coleta laboratorial públicos e privados. Substitui a RDC 302/2005.',
 'Laboratórios', 'vigente'),

('RDC 302/2005', 'Regulamento Técnico para Laboratórios Clínicos', 'ANVISA', '2005-10-13',
 'Dispõe sobre Regulamento Técnico para funcionamento de Laboratórios Clínicos. Revogada pela RDC 786/2023, mas ainda referência.',
 'Laboratórios', 'revogada'),

('RDC 222/2018', 'Boas Práticas de Gerenciamento de Resíduos de Serviços de Saúde', 'ANVISA', '2018-03-28',
 'Regulamenta as Boas Práticas de Gerenciamento dos Resíduos de Serviços de Saúde e dá outras providências.',
 'Resíduos', 'vigente'),

('RDC 665/2022', 'Boas Práticas de Fabricação de Produtos para Diagnóstico In Vitro', 'ANVISA', '2022-03-30',
 'Dispõe sobre as Boas Práticas de Fabricação e o controle de produtos para diagnóstico de uso in vitro.',
 'Fabricação', 'vigente'),

('RDC 430/2020', 'Boas Práticas de Distribuição, Armazenagem e Transporte de Produtos', 'ANVISA', '2020-10-08',
 'Estabelece as Boas Práticas de Distribuição, Armazenagem e de Transporte de Medicamentos.',
 'Distribuição', 'vigente'),

('IN 76/2018', 'Regulamento Técnico de Identidade e Qualidade do Leite Cru Refrigerado', 'MAPA', '2018-11-26',
 'Fixa a identidade e as características de qualidade que deve apresentar o leite cru refrigerado. Padrões de CBT, CCS, composição, temperatura e outros.',
 'Leite - Qualidade', 'vigente'),

('IN 77/2018', 'Regulamento Técnico para Coleta e Transporte de Leite Cru', 'MAPA', '2018-11-26',
 'Estabelece os critérios e procedimentos para a produção, acondicionamento, conservação, transporte, seleção e recepção do leite cru em estabelecimentos registrados.',
 'Leite - Transporte', 'vigente'),

('IN 58/2019', 'Amostragem Oficial para Análise Fiscal de Leite', 'MAPA', '2019-12-05',
 'Define os procedimentos de amostragem oficial para fins de análise fiscal de produtos de origem animal e seus derivados.',
 'Leite - Amostragem', 'vigente'),

('IN 59/2019', 'Limites Máximos para Contaminantes em Leite', 'MAPA', '2019-12-05',
 'Estabelece limites máximos para resíduos de medicamentos veterinários em alimentos de origem animal.',
 'Leite - Contaminantes', 'vigente'),

('RDC 331/2019', 'Padrões Microbiológicos de Alimentos', 'ANVISA', '2019-12-23',
 'Dispõe sobre os padrões microbiológicos de alimentos e sua aplicação, incluindo leite e derivados.',
 'Alimentos', 'vigente'),

('IN 60/2019', 'Listas de Padrões Microbiológicos para Alimentos', 'ANVISA', '2019-12-23',
 'Estabelece as listas de padrões microbiológicos para alimentos, complementando a RDC 331/2019.',
 'Alimentos', 'vigente'),

('RDC 275/2002', 'Boas Práticas de Fabricação em Estabelecimentos de Alimentos', 'ANVISA', '2002-10-21',
 'Dispõe sobre o Regulamento Técnico de Procedimentos Operacionais Padronizados aplicados aos Estabelecimentos Produtores/Industrializadores de Alimentos e a Lista de Verificação das Boas Práticas de Fabricação.',
 'BPF Alimentos', 'vigente'),

('RDC 216/2004', 'Boas Práticas para Serviços de Alimentação', 'ANVISA', '2004-09-15',
 'Dispõe sobre Regulamento Técnico de Boas Práticas para Serviços de Alimentação.',
 'BPF Serviços', 'vigente'),

('NR 32', 'Segurança e Saúde no Trabalho em Estabelecimentos de Saúde', 'OUTRO', '2005-11-11',
 'Estabelece as diretrizes básicas para a implementação de medidas de proteção à segurança e à saúde dos trabalhadores em serviços de saúde e laboratórios.',
 'Segurança Trabalho', 'vigente'),

('NR 6', 'Equipamento de Proteção Individual - EPI', 'OUTRO', '1978-06-08',
 'Estabelece as disposições sobre o uso de equipamentos de proteção individual (EPI) no ambiente de trabalho, incluindo laboratórios.',
 'EPI', 'vigente'),

('RDC 50/2002', 'Planejamento e Programação de Estabelecimentos de Saúde', 'ANVISA', '2002-02-21',
 'Dispõe sobre o Regulamento Técnico para planejamento, programação, elaboração e avaliação de projetos físicos de estabelecimentos assistenciais de saúde.',
 'Infraestrutura', 'vigente');

-- Itens de conformidade para RDC 786/2023 (norma_id = 1)
INSERT INTO rdc_itens_conformidade (norma_id, codigo, descricao, criticidade, evidencia_necessaria, ordem) VALUES
(1, '786-01', 'Laboratório possui Responsável Técnico habilitado e registrado no conselho de classe', 'critico', 'Documento de registro profissional e contrato/vínculo', 1),
(1, '786-02', 'Alvará sanitário válido e atualizado', 'critico', 'Cópia do alvará sanitário vigente', 2),
(1, '786-03', 'Procedimentos Operacionais Padrão (POP) documentados e acessíveis', 'critico', 'Lista de POPs, cópias e registros de treinamento', 3),
(1, '786-04', 'Programa de controle de qualidade interno implementado', 'critico', 'Registros de CQI, cartas de controle', 4),
(1, '786-05', 'Participação em programa de ensaio de proficiência (controle externo)', 'critico', 'Certificados de participação e relatórios', 5),
(1, '786-06', 'Rastreabilidade de amostras garantida do recebimento ao laudo', 'critico', 'Sistema de registro e identificação de amostras', 6),
(1, '786-07', 'Equipamentos calibrados e com manutenção preventiva', 'maior', 'Certificados de calibração e cronograma de manutenção', 7),
(1, '786-08', 'Registro de temperatura de equipamentos e ambiente', 'maior', 'Planilhas de controle de temperatura', 8),
(1, '786-09', 'Descarte adequado de resíduos conforme RDC 222/2018', 'maior', 'PGRSS e registros de coleta', 9),
(1, '786-10', 'Treinamento de pessoal registrado e atualizado', 'maior', 'Registros de treinamento com data e conteúdo', 10),
(1, '786-11', 'Laudos com informações mínimas obrigatórias', 'maior', 'Modelo de laudo conforme requisitos', 11),
(1, '786-12', 'Sistema de notificação de incidentes e eventos adversos', 'menor', 'Formulários e registros de notificação', 12),
(1, '786-13', 'Instalações físicas adequadas (área de recepção, técnica, lavagem)', 'maior', 'Planta baixa e registro fotográfico', 13),
(1, '786-14', 'Biossegurança: mapa de risco e uso de EPIs', 'critico', 'Mapa de risco atualizado, registros de entrega de EPIs', 14),
(1, '786-15', 'Manual da Qualidade do laboratório', 'maior', 'Cópia do manual com data de revisão', 15);

-- Itens de conformidade para IN 76/2018 (norma_id = 6)
INSERT INTO rdc_itens_conformidade (norma_id, codigo, descricao, criticidade, evidencia_necessaria, ordem) VALUES
(6, 'IN76-01', 'Leite refrigerado na propriedade a máx. 4°C em até 3h após ordenha', 'critico', 'Registros de temperatura de tanques', 1),
(6, 'IN76-02', 'Contagem Bacteriana Total (CBT) dentro do limite: máx. 300.000 UFC/mL', 'critico', 'Laudos de análise CBT', 2),
(6, 'IN76-03', 'Contagem de Células Somáticas (CCS) dentro do limite: máx. 500.000 cél/mL', 'critico', 'Laudos de análise CCS', 3),
(6, 'IN76-04', 'Gordura mínima: 3,0 g/100g', 'maior', 'Laudos de composição', 4),
(6, 'IN76-05', 'Proteína mínima: 2,9 g/100g', 'maior', 'Laudos de composição', 5),
(6, 'IN76-06', 'Extrato Seco Desengordurado (ESD) mínimo: 8,4 g/100g', 'maior', 'Laudos de composição', 6),
(6, 'IN76-07', 'Acidez titulável: 0,14 a 0,18 g ác. lático/100mL', 'maior', 'Laudos de análise', 7),
(6, 'IN76-08', 'Densidade a 15°C: 1,028 a 1,034 g/mL', 'maior', 'Laudos de análise', 8),
(6, 'IN76-09', 'Índice crioscópico: -0,530°H a -0,555°H', 'critico', 'Laudos de crioscopia', 9),
(6, 'IN76-10', 'Pesquisa de antibióticos/inibidores: NEGATIVO', 'critico', 'Laudos de pesquisa de resíduos', 10),
(6, 'IN76-11', 'Estabilidade ao alizarol 72% (v/v): estável', 'maior', 'Laudos de estabilidade', 11),
(6, 'IN76-12', 'Ausência de neutralizantes de acidez, reconstituintes ou conservantes', 'critico', 'Laudos de pesquisa de fraudes', 12);

-- ============================================================
-- Módulo Autolac - Integração com sistema local
-- ============================================================

-- Configuração de conexão com o Autolac
CREATE TABLE IF NOT EXISTS autolac_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    db_host VARCHAR(200) NOT NULL DEFAULT 'localhost',
    db_port INT DEFAULT 3306,
    db_name VARCHAR(100) NOT NULL DEFAULT '',
    db_user VARCHAR(100) NOT NULL DEFAULT '',
    db_pass VARCHAR(255) NOT NULL DEFAULT '',
    db_driver ENUM('mysql','pgsql','sqlsrv','firebird') DEFAULT 'mysql',
    tabela_pagamentos VARCHAR(100) DEFAULT 'pagamentos',
    campo_valor VARCHAR(100) DEFAULT 'valor',
    campo_data VARCHAR(100) DEFAULT 'data_pagamento',
    campo_descricao VARCHAR(100) DEFAULT 'descricao',
    campo_cliente VARCHAR(100) DEFAULT 'cliente',
    campo_status VARCHAR(100) DEFAULT 'status',
    campo_documento VARCHAR(100) DEFAULT 'numero_documento',
    ultima_sincronizacao DATETIME,
    data_inicio_integracao DATE DEFAULT NULL COMMENT 'Importar apenas pagamentos a partir desta data',
    ativo TINYINT(1) DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Pagamentos importados do Autolac
CREATE TABLE IF NOT EXISTS autolac_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    autolac_id VARCHAR(50),
    descricao VARCHAR(300),
    cliente VARCHAR(200),
    valor DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    data_pagamento DATE,
    status VARCHAR(50) DEFAULT 'importado',
    numero_documento VARCHAR(100),
    dados_extras TEXT,
    conta_id INT,
    importado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_id) REFERENCES contas(id) ON DELETE SET NULL,
    UNIQUE KEY unique_autolac_id (autolac_id)
) ENGINE=InnoDB;

-- Log de sincronizações
CREATE TABLE IF NOT EXISTS autolac_sync_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('importacao','teste_conexao','manual') NOT NULL DEFAULT 'importacao',
    registros_encontrados INT DEFAULT 0,
    registros_importados INT DEFAULT 0,
    registros_ignorados INT DEFAULT 0,
    status ENUM('sucesso','erro','parcial') DEFAULT 'sucesso',
    mensagem TEXT,
    executado_por INT,
    executado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (executado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
