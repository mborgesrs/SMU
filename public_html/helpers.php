<?php
/**
 * Global Helper Functions
 */

if (!function_exists('formatarTelefone')) {
    function formatarTelefone($fone) {
        $fone = preg_replace('/[^0-9]/', '', $fone);
        $length = strlen($fone);

        if ($length == 11) {
            return '(' . substr($fone, 0, 2) . ') ' . substr($fone, 2, 5) . '-' . substr($fone, 7);
        } elseif ($length == 10) {
            return '(' . substr($fone, 0, 2) . ') ' . substr($fone, 2, 4) . '-' . substr($fone, 6);
        }

        return $fone;
    }
}

if (!function_exists('formatarCPF_CNPJ')) {
    function formatarCPF_CNPJ($documento) {
        $doc = preg_replace('/[^0-9]/', '', $documento);
        $length = strlen($doc);

        if ($length == 11) {
            return substr($doc, 0, 3) . '.' . substr($doc, 3, 3) . '.' . substr($doc, 6, 3) . '-' . substr($doc, 9);
        } elseif ($length == 14) {
            return substr($doc, 0, 2) . '.' . substr($doc, 2, 3) . '.' . substr($doc, 5, 3) . '/' . substr($doc, 8, 4) . '-' . substr($doc, 12);
        }

        return $documento;
    }
}
if (!function_exists('checkBilling')) {
    function checkBilling() {
        if (!isset($_SESSION['company_id'])) return true;
        
        // Skip check for super admin if needed, or check always
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') return true;

        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT billing_status, status FROM companies WHERE id = ?");
            $stmt->execute([$_SESSION['company_id']]);
            $company = $stmt->fetch();

            if (!$company) return true;

            if ($company['status'] === 'inactive' || $company['billing_status'] === 'blocked' || $company['billing_status'] === 'overdue') {
                $depth = 0;
                $phpSelf = $_SERVER['PHP_SELF'] ?? '';
                if (strpos($phpSelf, 'views/') !== false) $depth = 1;
                if (strpos($phpSelf, 'api/') !== false) $depth = 1;
                
                $prefix = str_repeat('../', $depth);
                
                // If already on billing page, don't redirect
                if (strpos($phpSelf, 'billing.php') === false) {
                    header('Location: ' . $prefix . 'views/billing/status.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            return true;
        }
        return true;
    }
}

if (!function_exists('checkAuth')) {
    function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            $depth = 0;
            $phpSelf = $_SERVER['PHP_SELF'] ?? '';
            if (strpos($phpSelf, 'views/') !== false) $depth = 1;
            if (strpos($phpSelf, 'api/') !== false) $depth = 1;
            
            $prefix = str_repeat('../', $depth);
            header('Location: ' . $prefix . 'index.php');
            exit;
        }
        
        // Also check billing
        checkBilling();
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
    }
}

if (!function_exists('getCompanyColors')) {
    function getCompanyColors() {
        if (!isset($_SESSION['company_id'])) {
            return ['primary' => '#1e293b', 'secondary' => '#334155'];
        }
        
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT primary_color, secondary_color FROM companies WHERE id = ?");
            $stmt->execute([$_SESSION['company_id']]);
            $colors = $stmt->fetch();
            
            return [
                'primary' => $colors['primary_color'] ?? '#1e293b',
                'secondary' => $colors['secondary_color'] ?? '#334155'
            ];
        } catch (Exception $e) {
            return ['primary' => '#1e293b', 'secondary' => '#334155'];
        }
    }
}
