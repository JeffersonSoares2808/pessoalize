# Pessoalize

**Sistema de Gestão de Departamento Pessoal, RH e Financeiro**

by JS Sistemas Inteligentes

![Pessoalize](https://github.com/user-attachments/assets/b94d193c-ecec-4a0c-ae2e-dc5c3154a3c3)

---

## 📋 Funcionalidades

### 👥 Gestão de Funcionários
- Cadastro completo de funcionários (dados pessoais, endereço, profissionais)
- Controle de status (ativo, inativo, férias, afastado)
- Busca e filtros avançados
- Vinculação com departamentos

### 📄 Currículos
- Cadastro de currículos de candidatos
- Upload de arquivos (PDF, DOC, DOCX)
- Controle de status (recebido, em análise, aprovado, reprovado, contratado)
- Dados de formação, experiência e habilidades

### 🔍 Seleção de Funcionários
- Criação e gestão de vagas
- Inscrição de candidatos (currículos) nas vagas
- Avaliação com notas e pareceres
- Agendamento de entrevistas
- Controle de status do processo seletivo

### 💰 Financeiro Completo
- Contas a pagar e a receber
- Registro de boletos com código de barras
- Controle de pagamentos
- Categorias financeiras
- Resumo financeiro mensal no dashboard
- Alerta de contas vencidas
- Filtros por mês, tipo e status

### 🔔 Notificações WhatsApp & SMS
- Cadastro de funcionários para receber avisos
- Seleção de canais: WhatsApp e/ou SMS
- Tipos de aviso configuráveis (vencimentos, pagamentos, avisos, RH, aniversários)
- Histórico de notificações enviadas
- Status por contato (ativo/inativo)

### 📊 Relatórios Completos
- **Funcionários**: listagem por status, departamento, com totais de folha
- **Financeiro**: fluxo de caixa, por categoria, resumo detalhado mensal
- **Folha de Pagamento**: custo por departamento, média salarial, maior/menor salário
- **Recrutamento**: funil de seleção, vagas abertas/fechadas, taxa de conversão
- **Aniversariantes**: do mês e próximos 30 dias
- **Notificações**: contatos ativos, histórico de envios
- Todos os relatórios são **imprimíveis** (Ctrl+P)

### 🌓 Design Profissional
- Interface moderna com design system completo
- Dark Mode (alternância com 1 clique)
- Animações suaves e transições
- Tipografia profissional (Google Fonts Inter)
- Cards com gradientes e efeitos hover
- 100% Responsivo (mobile-first)

## 🚀 Instalação (Hostinger)

### Requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Módulo PDO habilitado

### Passo a Passo

1. **Faça upload** de todos os arquivos para o diretório `public_html` da Hostinger

2. **Crie o banco de dados** no painel da Hostinger (hPanel > Banco de Dados MySQL)

3. **Importe o SQL**: No phpMyAdmin, importe o arquivo `database.sql`

4. **Configure a conexão**: Edite o arquivo `config/config.php` com os dados do seu banco:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'seu_banco');
   define('DB_USER', 'seu_usuario');
   define('DB_PASS', 'sua_senha');
   define('APP_URL', 'https://seudominio.com');
   ```

5. **Verifique as permissões** da pasta `uploads/` (chmod 755)

6. **Acesse o sistema**:
   - URL: `https://seudominio.com`
   - E-mail: `admin@pessoalize.com`
   - Senha: `admin123`

> ⚠️ **Importante**: Altere a senha padrão após o primeiro acesso.

## 🏗️ Estrutura do Projeto

```
pessoalize/
├── config/
│   └── config.php          # Configurações do sistema
├── core/
│   ├── Database.php         # Classe de banco de dados (PDO)
│   └── helpers.php          # Funções auxiliares
├── modules/
│   ├── auth/                # Login e logout
│   ├── dashboard/           # Painel principal
│   ├── funcionarios/        # Gestão de funcionários
│   ├── curriculos/          # Gestão de currículos
│   ├── selecao/             # Seleção e recrutamento
│   ├── financeiro/          # Financeiro e boletos
│   ├── notificacoes/        # WhatsApp & SMS
│   └── relatorios/          # Relatórios completos
├── templates/
│   ├── header.php           # Cabeçalho e menu
│   └── footer.php           # Rodapé
├── assets/
│   ├── css/style.css        # Design system profissional
│   └── js/app.js            # JavaScript com dark mode
├── uploads/
│   └── curriculos/          # Arquivos de CV enviados
├── database.sql             # Script do banco de dados
├── index.php                # Ponto de entrada
└── .htaccess                # Configuração Apache
```

## 🔒 Segurança
- Senhas criptografadas com bcrypt
- Proteção CSRF em todos os formulários
- Escape de saída HTML (XSS)
- Prepared statements (SQL Injection)
- Upload seguro com validação de tipo

## 💡 Tecnologias
- **Backend**: PHP puro (sem frameworks pesados)
- **Banco**: MySQL com PDO
- **Frontend**: Bootstrap 5 + Bootstrap Icons + Google Fonts
- **Design**: CSS Variables, Dark Mode, Animações CSS
- **Leve**: Otimizado para hospedagem compartilhada (Hostinger)