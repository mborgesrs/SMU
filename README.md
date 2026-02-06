# SMU - Sistema de Controle de Maternidade

Sistema SaaS completo para controle e auxílio de maternidade desenvolvido em PHP puro, MySQL e Tailwind CSS.

## 📋 Características

- ✅ Sistema de autenticação com sessões PHP
- ✅ Dashboard com estatísticas mensais de contratos
- ✅ Gestão completa de clientes com integração CEP (ViaCEP)
- ✅ Cadastro de dependentes vinculados a clientes
- ✅ Catálogo de produtos e serviços
- ✅ Gestão financeira completa
- ✅ Sistema de contratos com múltiplos produtos
- ✅ Geração de PDF para contratos
- ✅ Integração com WhatsApp para alertas
- ✅ Design moderno e responsivo com Tailwind CSS
- ✅ Máscaras automáticas para CPF, CNPJ, telefone e CEP

## 🗂️ Estrutura do Projeto

```
SMU/
├── database.sql                 # Script SQL para criação do banco
├── public_html/                 # Raiz do projeto (compatível com Hostinger)
│   ├── index.php               # Página de login
│   ├── logout.php              # Logout
│   ├── config.php              # Configurações do sistema
│   ├── db.php                  # Conexão com banco de dados
│   ├── assets/
│   │   └── js/
│   │       └── main.js         # JavaScript (máscaras, CEP, etc)
│   ├── models/                 # Modelos de dados
│   │   ├── ClienteModel.php
│   │   ├── DependenteModel.php
│   │   ├── ProductModel.php
│   │   ├── PortadorModel.php
│   │   ├── ContaModel.php
│   │   ├── TipoPagamentoModel.php
│   │   ├── FinanceiroModel.php
│   │   ├── ObjetoModel.php
│   │   └── ContratoModel.php
│   ├── controllers/            # Controladores
│   │   └── ClienteController.php
│   ├── views/                  # Views
│   │   ├── layout/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── dashboard.php
│   │   ├── clientes/
│   │   ├── dependentes/
│   │   ├── products/
│   │   ├── portadores/
│   │   ├── contas/
│   │   ├── tipos_pagamento/
│   │   ├── financeiro/
│   │   ├── objetos/
│   │   └── contratos/
│   └── pdf/
│       ├── fpdf.php            # Biblioteca FPDF
│       └── contrato_pdf.php    # Geração de PDF de contratos
```

## 🚀 Instalação

### Passo 1: Criar o Banco de Dados

1. Acesse o phpMyAdmin ou painel de controle do MySQL
2. Crie um novo banco de dados chamado `smu_db`
3. Importe o arquivo `database.sql`

```sql
-- Ou execute via linha de comando:
mysql -u seu_usuario -p smu_db < database.sql
```

### Passo 2: Configurar o Sistema

1. Edite o arquivo `public_html/config.php`
2. Atualize as credenciais do banco de dados:

```php
define('DB_HOST', 'localhost');        // Host do banco
define('DB_NAME', 'smu_db');           // Nome do banco
define('DB_USER', 'seu_usuario');      // Usuário do banco
define('DB_PASS', 'sua_senha');        // Senha do banco
```

### Passo 3: Upload para Hostinger

1. Faça upload de todo o conteúdo da pasta `public_html` para a raiz do seu domínio
2. Certifique-se de que os arquivos estão na pasta correta (geralmente `public_html` na Hostinger)
3. Ajuste as permissões se necessário (755 para pastas, 644 para arquivos)

### Passo 4: Acessar o Sistema

1. Acesse seu domínio no navegador
2. Use as credenciais padrão:
   - **Usuário:** admin
   - **Senha:** admin123

⚠️ **IMPORTANTE:** Altere a senha padrão após o primeiro acesso!

## 📦 Módulos do Sistema

### 1. Dashboard
- Visualização de estatísticas do mês corrente
- Contratos criados, atendidos e cancelados
- Acesso rápido aos módulos

### 2. Clientes
- Cadastro completo com dados pessoais e empresariais
- Integração automática com ViaCEP
- Suporte para pessoa física e jurídica
- Busca por nome, fantasia, CPF ou CNPJ

### 3. Dependentes
- Vinculação com clientes
- Dados pessoais e documentação
- Busca em todos os campos

### 4. Produtos/Serviços
- Catálogo de produtos e serviços
- Preço unitário
- Descrição detalhada

### 5. Financeiro
- Lançamentos de contas a pagar e receber
- Entradas e saídas
- Vinculação com clientes, portadores e contas

### 6. Contratos
- Criação de contratos com múltiplos produtos
- Cálculo automático de valores
- Geração de PDF
- Envio de alerta via WhatsApp
- Controle de status (criado, atendido, cancelado)

## 🔧 Funcionalidades Especiais

### Integração CEP
O sistema utiliza a API ViaCEP para preenchimento automático de endereços. Ao digitar um CEP válido, os campos de endereço, bairro, município e UF são preenchidos automaticamente.

### Máscaras de Input
- **CPF:** 000.000.000-00
- **CNPJ:** 00.000.000/0000-00
- **Telefone:** (00) 0000-0000
- **Celular:** (00) 00000-0000
- **CEP:** 00000-000

### Geração de PDF
Os contratos podem ser exportados em PDF com todas as informações:
- Dados do contratante
- Detalhes do contrato
- Lista de produtos/serviços
- Valores e totais

### WhatsApp Integration
Ao criar um contrato, é possível enviar um alerta automático via WhatsApp para o cliente informando sobre a criação do contrato.

## 🔐 Segurança

- Senhas armazenadas com hash bcrypt
- Proteção contra SQL Injection (PDO com prepared statements)
- Validação de sessões
- Escape de dados de saída (XSS protection)

## 🎨 Design

- Interface moderna com Tailwind CSS
- Totalmente responsivo (mobile, tablet, desktop)
- Ícones Font Awesome
- Feedback visual para ações do usuário
- Alertas de sucesso e erro

## 📊 Banco de Dados

O sistema utiliza 11 tabelas:
- `users` - Usuários do sistema
- `clientes` - Cadastro de clientes
- `dependentes` - Dependentes dos clientes
- `products` - Produtos e serviços
- `portadores` - Portadores de pagamento
- `contas` - Plano de contas
- `tipos_pagamento` - Tipos de pagamento
- `financeiro` - Lançamentos financeiros
- `objetos` - Objetos de contrato
- `contratos` - Contratos
- `contrato_items` - Itens dos contratos

## 🆘 Suporte

### Problemas Comuns

**Erro de conexão com banco de dados:**
- Verifique as credenciais em `config.php`
- Certifique-se de que o banco de dados foi criado
- Verifique se o usuário tem permissões adequadas

**CEP não preenche automaticamente:**
- Verifique sua conexão com a internet
- A API ViaCEP pode estar temporariamente indisponível
- Verifique se o CEP é válido

**PDF não é gerado:**
- Verifique as permissões da pasta `pdf/`
- Certifique-se de que o arquivo `fpdf.php` existe

## 📝 Licença

Este sistema foi desenvolvido para uso interno. Todos os direitos reservados.

## 🔄 Atualizações Futuras

- [ ] Relatórios avançados
- [ ] Gráficos de dashboard
- [ ] Exportação para Excel
- [ ] API REST
- [ ] Notificações por e-mail
- [ ] Backup automático

---

**Desenvolvido com ❤️ para SMU - Sistema de Controle de Maternidade**
