# Guia de Implantação - Hostinger

## Pré-requisitos
- Conta Hostinger ativa
- Acesso ao painel de controle (hPanel)
- Acesso FTP ou File Manager

## Passo a Passo

### 1. Preparar o Banco de Dados

1. Acesse o hPanel da Hostinger
2. Vá em **Bancos de Dados MySQL**
3. Clique em **Criar Novo Banco de Dados**
4. Preencha:
   - Nome do banco: `smu_db` (ou outro nome de sua preferência)
   - Usuário: crie um novo usuário
   - Senha: defina uma senha forte
5. Anote as credenciais criadas
6. Clique em **phpMyAdmin**
7. Selecione o banco criado
8. Clique em **Importar**
9. Selecione o arquivo `database.sql`
10. Clique em **Executar**

### 2. Configurar o Sistema

1. Abra o arquivo `public_html/config.php`
2. Atualize com as credenciais do banco:

```php
define('DB_HOST', 'localhost');           // Geralmente é localhost
define('DB_NAME', 'nome_do_seu_banco');   // Nome que você criou
define('DB_USER', 'seu_usuario');         // Usuário criado
define('DB_PASS', 'sua_senha');           // Senha definida
```

3. Se necessário, ajuste a URL do aplicativo:

```php
define('APP_URL', 'https://seudominio.com');
```

### 3. Upload dos Arquivos

#### Opção A: Via File Manager (Recomendado)

1. No hPanel, vá em **Arquivos** → **Gerenciador de Arquivos**
2. Navegue até a pasta `public_html`
3. Delete todos os arquivos padrão (index.html, etc)
4. Clique em **Upload**
5. Selecione TODOS os arquivos da pasta `public_html` do projeto
6. Aguarde o upload completar

#### Opção B: Via FTP

1. Use um cliente FTP (FileZilla, WinSCP, etc)
2. Conecte usando as credenciais FTP da Hostinger
3. Navegue até a pasta `public_html`
4. Delete arquivos padrão
5. Faça upload de todos os arquivos do projeto

### 4. Verificar Permissões

1. No File Manager, verifique as permissões:
   - Pastas: 755
   - Arquivos PHP: 644
2. Se necessário, ajuste clicando com botão direito → **Permissões**

### 5. Testar o Sistema

1. Acesse seu domínio no navegador
2. Você deve ver a tela de login
3. Entre com:
   - Usuário: `admin`
   - Senha: `admin123`
4. Teste as funcionalidades principais:
   - Criar um cliente
   - Criar um produto
   - Criar um contrato
   - Gerar PDF

### 6. Segurança Pós-Instalação

1. **Altere a senha do admin:**
   - Acesse o banco de dados via phpMyAdmin
   - Vá na tabela `users`
   - Edite o usuário admin
   - Gere uma nova senha com:
   ```php
   <?php echo password_hash('sua_nova_senha', PASSWORD_DEFAULT); ?>
   ```

2. **Desabilite exibição de erros em produção:**
   - Edite `config.php`
   - Altere para:
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Configure SSL (HTTPS):**
   - No hPanel, vá em **Segurança** → **SSL**
   - Ative o SSL gratuito da Hostinger
   - Force HTTPS criando um arquivo `.htaccess` na raiz:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### 7. Backup Regular

Configure backups automáticos:
1. No hPanel, vá em **Arquivos** → **Backups**
2. Configure backups automáticos semanais
3. Faça backup manual do banco de dados mensalmente

## Solução de Problemas

### Erro 500 - Internal Server Error
- Verifique as permissões dos arquivos
- Verifique se há erros de sintaxe no código
- Consulte os logs de erro no hPanel

### Página em branco
- Verifique as configurações do banco em `config.php`
- Ative temporariamente `display_errors` para ver o erro
- Verifique os logs de erro do PHP

### PDF não gera
- Verifique se a pasta `pdf/` tem permissão de escrita
- Verifique se o arquivo `fpdf.php` foi enviado corretamente

### CEP não funciona
- Verifique se o servidor permite conexões externas (cURL)
- Teste manualmente a API: https://viacep.com.br/ws/01310100/json/

## Contato Suporte Hostinger

- Chat ao vivo: disponível 24/7
- Email: support@hostinger.com
- Base de conhecimento: https://support.hostinger.com

---

**Sistema pronto para uso em produção!** 🚀
