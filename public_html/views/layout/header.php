<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ConfiguracaoModel.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

// Reload role and username if missing from session
if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    require_once __DIR__ . '/../../db.php';
    $db = getDB();
    $stmt = $db->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['role'] = $user['role'] ?: 'user';
        $_SESSION['username'] = $user['username'] ?: 'Usuário';
    }
}

$configModel = new ConfiguracaoModel();
$empresaConfig = $configModel->getConfig() ?: [];
$empresaNome = !empty($empresaConfig['nome_fantasia']) ? $empresaConfig['nome_fantasia'] : (!empty($empresaConfig['razao_social']) ? $empresaConfig['razao_social'] : APP_NAME);
$empresaLogo = !empty($empresaConfig['logotipo']) ? APP_URL . ltrim($empresaConfig['logotipo'], '/') : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    $primaryColor = $empresaConfig['primary_color'] ?? '#1e293b';
    $secondaryColor = $empresaConfig['secondary_color'] ?? '#334155';
    ?>
    <style>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
        }
        .bg-primary { background-color: var(--primary-color) !important; }
        .bg-secondary { background-color: var(--secondary-color) !important; }
        .text-primary { color: var(--primary-color) !important; }
        .border-primary { border-color: var(--primary-color) !important; }

        html { font-size: 16px; } 
        @media (min-width: 1024px) {
            html { font-size: 14.5px; }
        }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php if (!isset($_GET['modal'])): ?>
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-30 hidden lg:hidden transition-opacity duration-300"></div>
    <?php endif; ?>

    <style>
        <?php if (isset($_GET['modal'])): ?>
            nav, aside, #sidebarOverlay { display: none !important; }
            main { margin-left: 0 !important; margin-top: 0 !important; padding: 1rem !important; }
            body { background-color: white !important; }
        <?php endif; ?>
    </style>

    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 text-slate-700 shadow-sm fixed w-full top-0 z-50 transition-all duration-300">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="lg:hidden text-slate-600 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center space-x-3">
                    <?php if ($empresaLogo): ?>
                        <div class="bg-white p-1 rounded-lg shadow-sm">
                            <img src="<?php echo $empresaLogo; ?>" alt="Logo" class="h-8 w-auto object-contain">
                        </div>
                    <?php else: ?>
                        <div class="bg-indigo-600 p-2 rounded-lg">
                            <i class="fas fa-rocket text-white"></i>
                        </div>
                    <?php endif; ?>
                    <h1 class="text-xl font-bold tracking-tight text-slate-800"><?php echo htmlspecialchars($empresaNome); ?></h1>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="hidden md:inline text-slate-500 font-medium">Olá, <span class="text-slate-800"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuário'); ?></span></span>
                <a href="<?php echo APP_URL; ?>logout.php" class="bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-200 hover:border-red-200 px-4 py-2 rounded-lg transition-all duration-300 flex items-center text-sm font-semibold">
                    <i class="fas fa-sign-out-alt mr-2"></i>Sair
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-gray-50 to-white shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 mt-14">
        <style>
            .menu-icon {
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.3s ease;
            }
            
            .menu-item {
                transition: all 0.2s ease;
            }
            
            .menu-item:hover {
                background-color: #f3f4f6;
                padding-left: 1.25rem;
            }
            
            .group-header {
                cursor: pointer;
                user-select: none;
            }

            .group-content {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-out;
            }

            .group-active .group-content {
                max-height: 500px;
                transition: max-height 0.5s ease-in;
            }

            .chevron-icon {
                transition: transform 0.3s ease;
            }

            .group-active .chevron-icon {
                transform: rotate(180deg);
            }
            
            .icon-dashboard { background: var(--primary-color); color: white; opacity: 0.9; }
            .icon-usuarios { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; }
            .icon-superadmin { background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%); color: white; }
            
            .group-title-icon {
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                font-size: 16px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            /* Dynamic Hover Effect using Primary Color */
            .menu-item:hover .menu-icon {
                transform: scale(1.1);
            }
            .text-primary-hover:hover {
                color: var(--primary-color) !important;
            }
        </style>
        
        <nav class="p-4 space-y-2 overflow-y-auto h-full pb-20">
            <!-- Dashboard (Single Item) -->
            <a href="<?php echo APP_URL; ?>views/dashboard.php" class="flex items-center space-x-3 px-3 py-3 rounded-xl hover:bg-white text-gray-700 transition group">
                <div class="group-title-icon icon-dashboard" style="background: var(--primary-color)">
                    <i class="fas fa-home"></i>
                </div>
                <span class="font-bold text-gray-800">Dashboard</span>
            </a>
            
            <!-- Group: Cadastros -->
            <div class="menu-group" id="group-cadastros">
                <div class="group-header flex items-center justify-between px-3 py-3 rounded-xl hover:bg-gray-100 transition" onclick="toggleGroup('group-cadastros')">
                    <div class="flex items-center space-x-3">
                        <div class="group-title-icon" style="background-color: color-mix(in srgb, var(--primary-color), white 90%); color: var(--primary-color);">
                            <i class="fas fa-edit"></i>
                        </div>
                        <span class="font-bold text-gray-800">Cadastros</span>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs chevron-icon"></i>
                </div>
                <div class="group-content space-y-1 mt-1 ml-4 border-l-2 border-gray-100">
                    <a href="<?php echo APP_URL; ?>views/clientes/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-clientes">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="text-sm font-medium">Clientes</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/dependentes/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-dependentes">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <span class="text-sm font-medium">Dependentes</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/products/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-produtos">
                            <i class="fas fa-box"></i>
                        </div>
                        <span class="text-sm font-medium">Produtos/Serviços</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/objetos/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-objetos">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <span class="text-sm font-medium">Objetos</span>
                    </a>
                </div>
            </div>
            
            <!-- Group: Financeiro -->
            <div class="menu-group" id="group-financeiro">
                <div class="group-header flex items-center justify-between px-3 py-3 rounded-xl hover:bg-gray-100 transition" onclick="toggleGroup('group-financeiro')">
                    <div class="flex items-center space-x-3">
                        <div class="group-title-icon bg-emerald-100 text-emerald-600">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <span class="font-bold text-gray-800">Financeiro</span>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs chevron-icon"></i>
                </div>
                <div class="group-content space-y-1 mt-1 ml-4 border-l-2 border-gray-100">
                    <a href="<?php echo APP_URL; ?>views/portadores/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-portadores">
                            <i class="fas fa-university"></i>
                        </div>
                        <span class="text-sm font-medium">Portadores</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/contas/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-contas">
                            <i class="fas fa-book"></i>
                        </div>
                        <span class="text-sm font-medium">Contas</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/tipos_pagamento/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-tipos-pagamento">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <span class="text-sm font-medium">Tipos de Pagamento</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/financeiro/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-financeiro">
                            <i class="fas fa-coins"></i>
                        </div>
                        <span class="text-sm font-medium">Movimentações</span>
                    </a>
                </div>
            </div>
            
            <!-- Group: Contratos -->
            <div class="menu-group" id="group-contratos">
                <div class="group-header flex items-center justify-between px-3 py-3 rounded-xl hover:bg-gray-100 transition" onclick="toggleGroup('group-contratos')">
                    <div class="flex items-center space-x-3">
                        <div class="group-title-icon bg-amber-100 text-amber-600">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <span class="font-bold text-gray-800">Contratos</span>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs chevron-icon"></i>
                </div>
                <div class="group-content space-y-1 mt-1 ml-4 border-l-2 border-gray-100">
                    <a href="<?php echo APP_URL; ?>views/contratos/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-contratos">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <span class="text-sm font-medium">Gerenciar Contratos</span>
                    </a>
                </div>
            </div>

            <!-- Group: Gestão (Roles) -->
            <?php if (isAdmin()): ?>
            <div class="menu-group" id="group-gestao">
                <div class="group-header flex items-center justify-between px-3 py-3 rounded-xl hover:bg-gray-100 transition" onclick="toggleGroup('group-gestao')">
                    <div class="flex items-center space-x-3">
                        <div class="group-title-icon bg-slate-100 text-slate-600">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="font-bold text-gray-800">Gestão</span>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs chevron-icon"></i>
                </div>
                <div class="group-content space-y-1 mt-1 ml-4 border-l-2 border-gray-100">
                    <a href="<?php echo APP_URL; ?>views/usuarios/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-usuarios">
                            <i class="fas fa-user-lock"></i>
                        </div>
                        <span class="text-sm font-medium">Usuários</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/configuracoes/index.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-config">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="text-sm font-medium">Configurações</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/configuracoes/api.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-api" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                            <i class="fas fa-key"></i>
                        </div>
                        <span class="text-sm font-medium">Integrações (API)</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>views/contrato_modelos/list.php" class="menu-item flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-600 text-primary-hover">
                        <div class="menu-icon icon-contratos" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <span class="text-sm font-medium">Modelos de Contrato</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Super Admin Section -->
            <?php if (isSuperAdmin()): ?>
            <div class="pt-4 mt-4 border-t border-gray-100">
                <p class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administração do Sistema</p>
                <a href="<?php echo APP_URL; ?>views/superadmin/companies.php" class="flex items-center space-x-3 px-3 py-3 rounded-xl hover:bg-white text-gray-700 transition group">
                    <div class="group-title-icon icon-superadmin">
                        <i class="fas fa-server"></i>
                    </div>
                    <span class="font-bold text-gray-800">Empresas & Planos</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>

        <script>
            function toggleGroup(groupId) {
                const group = document.getElementById(groupId);
                const isActive = group.classList.contains('group-active');
                
                // Close other groups optionally, but user might want multiple open
                // For a true accordion, uncomment the next lines:
                // document.querySelectorAll('.menu-group').forEach(g => {
                //    if(g.id !== groupId) g.classList.remove('group-active');
                // });

                if (isActive) {
                    group.classList.remove('group-active');
                    localStorage.setItem(groupId, 'closed');
                } else {
                    group.classList.add('group-active');
                    localStorage.setItem(groupId, 'open');
                }
            }

            // Persistence
            document.addEventListener('DOMContentLoaded', () => {
                const groups = ['group-cadastros', 'group-financeiro', 'group-contratos', 'group-gestao'];
                groups.forEach(id => {
                    const state = localStorage.getItem(id);
                    const el = document.getElementById(id);
                    if (el) {
                        if (state === 'open') el.classList.add('group-active');
                        else if (state === 'closed') el.classList.remove('group-active');
                    }
                });
            });
        </script>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 mt-14 p-6">
