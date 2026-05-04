# 🚀 Ripfire System - Guia de Deploy na HostGator

## ⚙️ Configuração antes do Upload

### 1. **Preparar o arquivo .env**
   
```bash
# Na sua máquina local, copie o arquivo de exemplo
cp .env.example .env
```

### 2. **Editar o .env com suas informações**

```
DB_HOST=localhost
DB_NAME=seu_banco_hostgator
DB_USER=seu_usuario_hostgator
DB_PASSWORD=sua_senha_hostgator
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com.br
APP_TIMEZONE=America/Sao_Paulo
```

### 3. **IMPORTANTE: Não fazer commit do .env**

O arquivo `.env` já está no `.gitignore`, então não será commitado. Apenas o `.env.example` vai para o Git.

---

## 📤 Upload para HostGator

### Pela FTP/SFTP:

1. **Conecte via FTP/SFTP** (FileZilla recomendado)
   - Use as credenciais da HostGator
   - Acesse a pasta `public_html` ou `www`

2. **Upload dos arquivos:**
   ```
   /public_html/
   ├── index.php
   ├── .env           ⚠️ IMPORTANTE: faça upload manualmente
   ├── .htaccess      ✅ Já configurado com segurança
   ├── .gitignore     ✅ Já configurado
   ├── src/
   ├── assets/
   ├── manifest.json
   └── sw.js
   ```

3. **Configure as permissões:**
   - `index.php`: 644
   - `logs/`: 755
   - Demais diretórios: 755

### Pela Terminal (Git/SSH):

```bash
# 1. Acesse via SSH na HostGator
ssh seu_usuario@seu_dominio.com

# 2. Clone o repositório
git clone https://seu-repo.git projeto

# 3. Entre no diretório
cd projeto

# 4. Copie e configure o .env
cp .env.example .env

# 5. Edite o .env com as credenciais
nano .env

# 6. Configure permissões
chmod 755 logs/
chmod 755 assets/uploads/
```

---

## 🔐 Segurança - Checklist

- ✅ `.env` contém credenciais (não é commitado)
- ✅ `.htaccess` bloqueia acesso a arquivos sensíveis
- ✅ Debug desativado em produção
- ✅ Headers de segurança configurados
- ✅ Compressão GZIP habilitada
- ✅ Cache de browser ativado

---

## 🗄️ Banco de Dados

### Criando o banco na HostGator:

1. Acesse **cPanel** > **PhpMyAdmin**
2. Crie um novo banco com o nome configurado no `.env`
3. Importe o arquivo SQL:
   - Localização: `assets/ripfire_db.sql`
   - Importe no PhpMyAdmin

```bash
# Ou pela terminal:
mysql -u seu_usuario -p seu_banco < assets/ripfire_db.sql
```

---

## 🔍 Verificações Após Deploy

```bash
# Teste a conexão com o banco
curl https://seu-dominio.com.br

# Verifique se os logs estão funcionando
# Teste uma ação que gere erro para confirmar que os logs são criados em /logs

# Teste os formulários (login, cadastro, etc)
```

---

## 📝 Variáveis de Ambiente Explicadas

| Variável | Descrição | Exemplo |
|----------|-----------|---------|
| `DB_HOST` | Host do banco de dados | localhost |
| `DB_NAME` | Nome do banco de dados | vin31871_ripfire |
| `DB_USER` | Usuário do banco | vin31871_ripfire |
| `DB_PASSWORD` | Senha do banco | Vb357753@ |
| `APP_ENV` | Ambiente (development/production) | production |
| `APP_DEBUG` | Modo debug (true/false) | false |
| `APP_URL` | URL da aplicação | https://seu-dominio.com.br |
| `APP_TIMEZONE` | Fuso horário | America/Sao_Paulo |

---

## ⚡ Otimizações Adicionais

### 1. **Ativar HTTPS (recomendado):**

Descomente a following linhas no `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 2. **Aumentar limite de upload:**

Na HostGator, edite `.user.ini` (raiz) ou `.htaccess`:
```
upload_max_filesize = 50M
post_max_size = 50M
```

### 3. **Ativar mod_rewrite:**

Na HostGator, já vem ativado por padrão no `.htaccess`.

---

## 🆘 Troubleshooting

### "Error na conexão com banco"
- Verifique as credenciais do `.env`
- Confirme que o banco foi criado
- Teste via PhpMyAdmin

### "Página em branco / Error 500"
- Verifique os logs em `/logs/error.log`
- Confirme a versão do PHP (recomendado: 7.4+)

### "Menu não funciona"
- Limpe cache: Ctrl+Shift+Del
- Verifique se o `.htaccess` está sendo interpretado

---

## 📞 Suporte HostGator

- **Chat**: https://www.hostgator.com.br/suporte
- **Email**: suporte@hostgator.com.br
- **Telefone**: 1150-3803

---

**Versão:** 1.0 | **Data:** Março 2026 | **Status:** ✅ Pronto para Produção
