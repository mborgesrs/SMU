# SMU - Sistema de Controle de Maternidade
## Inventário de Funcionalidades do Sistema

Este documento descreve todas as funcionalidades implementadas no sistema SMU, organizado por módulos e camadas técnicas.

---

### 1. Autenticação e Segurança
- **Login Multi-empresa**: Acesso restrito por usuário, senha e seleção de empresa.
- **Controle de Sessão**: Gestão de tempo de expiração da sessão (1 hora).
- **Hierarquia de Permissões**:
  - `user`: Acesso básico aos módulos de cadastro e contratos.
  - `admin`: Acesso completo à gestão da empresa (usuários, configurações, integrações).
  - `super_admin`: Acesso total, incluindo gestão de empresas e planos do SaaS.
- **Bloqueio por Inadimplência**: Redirecionamento automático para tela de pagamento caso o status da empresa esteja bloqueado ou inativo.

**Arquivos Principais:**
- `index.php` (Login)
- `helpers.php` (`checkAuth`, `isAdmin`, `isSuperAdmin`, `checkBilling`)
- `models/UserModel.php`

---

### 2. Dashboard
- **Resumo Estatístico**: Visualização rápida de contratos Totais do Mês, Pendentes, Ativos e Encerrados.
- **Acesso Rápido**: Atalhos visuais para os principais módulos do sistema.
- **Branding Dinâmico**: Cores e logotipo personalizados de acordo com a configuração da empresa logada.

**Arquivos Principais:**
- `views/dashboard.php`
- `views/layout/header.php`

---

### 3. Cadastros (Módulo Cadastros)
- **Clientes**: Gestão completa de clientes (Nome, Documento, Endereço, Contato).
- **Dependentes**: Vinculação de dependentes aos clientes.
- **Produtos/Serviços**: Cadastro de itens que podem ser vendidos ou vinculados a contratos.
- **Objetos**: Cadastro de objetos específicos (provavelmente relacionados à assistência de maternidade).

**Arquivos Principais:**
- `models/ClienteModel.php`, `models/DependenteModel.php`, `models/ProductModel.php`, `models/ObjetoModel.php`
- `views/clientes/`, `views/dependentes/`, `views/products/`, `views/objetos/`

---

### 4. Financeiro (Módulo Financeiro)
- **Portadores**: Cadastro de bancos ou meios de recebimento.
- **Contas (Plano de Contas)**: Categorização de receitas e despesas.
- **Tipos de Pagamento**: Definição de métodos (Cartão, PIX, Boleto, etc.).
- **Movimentações**: Registro de entradas e saídas financeiras.
- **Integração com Asaas**:
  - Geração de cobranças automáticas.
  - Sincronização via Webhook.

**Arquivos Principais:**
- `models/FinanceiroModel.php`, `models/ContaModel.php`, `models/PortadorModel.php`, `models/TipoPagamentoModel.php`
- `models/AsaasService.php`
- `views/financeiro/`, `views/contas/`, `views/portadores/`, `views/tipos_pagamento/`
- `api/asaas_webhook.php`

---

### 5. Contratos (Módulo Contratos)
- **Modelos de Contrato**: Criação de templates dinâmicos usando tags de substituição.
- **Gestão de Contratos**:
  - Criação de novos contratos vinculando Clientes e Objetos.
  - Controle de status (Pendente, Ativo, Encerrado).
- **Assinatura Digital (ZapSign)**:
  - Integração para envio de contratos para assinatura eletrônica.

**Arquivos Principais:**
- `models/ContratoModel.php`, `models/ContratoModeloModel.php`
- `models/ZapSignService.php`
- `views/contratos/`, `views/contrato_modelos/`

---

### 6. Gestão Administrativa
- **Usuários**: Cadastro e edição de usuários da empresa.
- **Configurações**: Personalização visual (cores, logo) e dados da empresa.
- **Integrações (API)**: Configuração das chaves de API para Asaas e ZapSign.

**Arquivos Principais:**
- `views/usuarios/`
- `views/configuracoes/`
- `models/ConfiguracaoModel.php`

---

### 7. Super Admin (SaaS)
- **Gestão de Empresas**: Cadastro de novas empresas no sistema SaaS.
- **Planos e Cobrança**: Controle de status de pagamento das empresas clientes.

**Arquivos Principais:**
- `views/superadmin/companies.php`
- `models/CompanyModel.php`

---

### 8. Infraestrutura Técnica
- **Banco de Dados**: MySQL (utilizando PDO).
- **Frontend**: Tailwind CSS para estilização, SweetAlert2 para notificações, FontAwesome para ícones.
- **Arquitetura**: Baseada em MVC simplificado.
- **Geração de PDF**: Integração para emissão de documentos.

**Arquivos Principais:**
- `db.php` (Conexão)
- `config.php` (Configurações globais e constantes)
- `assets/` (CSS/JS)
- `pdf/` (Lógica de geração de PDF)
