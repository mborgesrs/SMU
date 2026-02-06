-- ============================================
-- SaaS Maternity Assistance Control System
-- Database Schema
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: smu_db
CREATE DATABASE IF NOT EXISTS `smu_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smu_db`;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default user: admin / admin123
INSERT INTO `users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@smu.com');

-- ============================================
-- Table: clientes
-- ============================================
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `fantasia` varchar(200) DEFAULT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `municipio` varchar(100) DEFAULT NULL,
  `uf` varchar(2) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `tipo_pessoa` enum('Fisica','Juridica') DEFAULT 'Fisica',
  `ie` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `dt_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `divisao` enum('clientes','fornecedores','colaboradores','representantes') DEFAULT 'clientes',
  `cd_pais` varchar(10) DEFAULT 'BR',
  `insc_mun` varchar(20) DEFAULT NULL,
  `perc_comissao` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_nome` (`nome`),
  KEY `idx_fantasia` (`fantasia`),
  KEY `idx_cpf_cnpj` (`cpf_cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: dependentes
-- ============================================
CREATE TABLE `dependentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `dt_nascto` date DEFAULT NULL,
  `matricula` varchar(50) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_dependente_cliente` (`id_cliente`),
  CONSTRAINT `fk_dependente_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: products
-- ============================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample products
INSERT INTO `products` (`nome`, `descricao`, `preco_unitario`) VALUES
('Consulta Pré-natal', 'Consulta médica de acompanhamento pré-natal', 150.00),
('Ultrassom Obstétrico', 'Exame de ultrassom para gestantes', 200.00),
('Curso de Gestantes', 'Curso preparatório para gestantes', 350.00);

-- ============================================
-- Table: portadores
-- ============================================
CREATE TABLE `portadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `conta` varchar(50) DEFAULT NULL,
  `agencia` varchar(20) DEFAULT NULL,
  `numero` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample portadores
INSERT INTO `portadores` (`nome`, `conta`, `agencia`, `numero`) VALUES
('Caixa Principal', '12345-6', '0001', '001'),
('Banco do Brasil', '98765-4', '1234-5', '002');

-- ============================================
-- Table: contas
-- ============================================
CREATE TABLE `contas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `descricao` varchar(200) NOT NULL,
  `tipo` enum('Analitica','Sintetica') DEFAULT 'Analitica',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample contas
INSERT INTO `contas` (`codigo`, `descricao`, `tipo`, `ativo`) VALUES
('01.01.01', 'Receitas de Serviços', 'Analitica', 1),
('02.01.01', 'Despesas Administrativas', 'Analitica', 1),
('02.02.01', 'Despesas com Pessoal', 'Sintetica', 1);

-- ============================================
-- Table: tipos_pagamento
-- ============================================
CREATE TABLE `tipos_pagamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample tipos_pagamento
INSERT INTO `tipos_pagamento` (`descricao`, `ativo`) VALUES
('Dinheiro', 1),
('Cartão de Crédito', 1),
('Cartão de Débito', 1),
('PIX', 1),
('Boleto Bancário', 1),
('Transferência Bancária', 1);

-- ============================================
-- Table: financeiro
-- ============================================
CREATE TABLE `financeiro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `id_cliente_forn` int(11) DEFAULT NULL,
  `observacao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tipo` enum('Pagar','Receber','Entrada','Saida') NOT NULL,
  `id_portador` int(11) DEFAULT NULL,
  `id_conta` int(11) DEFAULT NULL,
  `id_tipopgto` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_financeiro_cliente` (`id_cliente_forn`),
  KEY `fk_financeiro_portador` (`id_portador`),
  KEY `fk_financeiro_conta` (`id_conta`),
  KEY `fk_financeiro_tipopgto` (`id_tipopgto`),
  CONSTRAINT `fk_financeiro_cliente` FOREIGN KEY (`id_cliente_forn`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_financeiro_portador` FOREIGN KEY (`id_portador`) REFERENCES `portadores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_financeiro_conta` FOREIGN KEY (`id_conta`) REFERENCES `contas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_financeiro_tipopgto` FOREIGN KEY (`id_tipopgto`) REFERENCES `tipos_pagamento` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: objetos
-- ============================================
CREATE TABLE `objetos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(200) NOT NULL,
  `objeto` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample objetos
INSERT INTO `objetos` (`descricao`, `objeto`) VALUES
('Acompanhamento Gestacional', 'Prestação de serviços de acompanhamento médico durante a gestação'),
('Curso Preparatório', 'Ministrar curso preparatório para gestantes e familiares');

-- ============================================
-- Table: contratos
-- ============================================
CREATE TABLE `contratos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_objeto` int(11) DEFAULT NULL,
  `natureza` enum('cobranca','pagamento') DEFAULT 'cobranca',
  `tipo` enum('assessoria','consultoria','servico') DEFAULT 'servico',
  `modalidade` enum('Antecipado','Retroativo') DEFAULT 'Antecipado',
  `id_contratante` int(11) NOT NULL,
  `dt_inicio` date NOT NULL,
  `dt_termino` date DEFAULT NULL,
  `id_conta` int(11) DEFAULT NULL,
  `id_portador` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `status` enum('criado','atendido','cancelado') DEFAULT 'criado',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_contrato_objeto` (`id_objeto`),
  KEY `fk_contrato_contratante` (`id_contratante`),
  KEY `fk_contrato_conta` (`id_conta`),
  KEY `fk_contrato_portador` (`id_portador`),
  CONSTRAINT `fk_contrato_objeto` FOREIGN KEY (`id_objeto`) REFERENCES `objetos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contrato_contratante` FOREIGN KEY (`id_contratante`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contrato_conta` FOREIGN KEY (`id_conta`) REFERENCES `contas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contrato_portador` FOREIGN KEY (`id_portador`) REFERENCES `portadores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: contrato_items
-- ============================================
CREATE TABLE `contrato_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contrato` int(11) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_item_contrato` (`id_contrato`),
  KEY `fk_item_produto` (`id_produto`),
  CONSTRAINT `fk_item_contrato` FOREIGN KEY (`id_contrato`) REFERENCES `contratos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_produto` FOREIGN KEY (`id_produto`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: configuracoes
-- ============================================
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(200) NOT NULL,
  `nome_fantasia` varchar(200) DEFAULT NULL,
  `cnpj` varchar(20) DEFAULT NULL,
  `responsavel` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `uf` varchar(2) DEFAULT NULL,
  `logotipo` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial empty config record if not exists
INSERT INTO `configuracoes` (id, razao_social) VALUES (1, 'Minha Empresa') ON DUPLICATE KEY UPDATE id=id;

COMMIT;
