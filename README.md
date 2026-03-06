# 🚀 Ripfire ERP System

Sistema de gestão empresarial completo.

## 🏭 Deploy na HostGator via Git (SSH)

### Método Recomendado: Clone Direto via SSH

**Pré-requisitos:**
- Acesso SSH disponível na HostGator
- Terminal/Putty instalado
- Token do GitHub (para repositório privado)

**Passos:**

1. **Conecte via SSH:**
   ```bash
   ssh vin31871@viniciusbaggio1772594984234.linuxpro.com.br
   # Digite sua senha
   ```

2. **Limpe a pasta public_html:**
   ```bash
   cd ~
   rm -rf public_html/*
   ```

3. **Clone o repositório:**
   ```bash
   # Para repositório público:
   git clone https://github.com/USUARIO/REPO.git public_html

   # Para repositório privado (use token):
   git clone https://SEU_TOKEN@github.com/USUARIO/REPO.git public_html
   ```

4. **Configure permissões:**
   ```bash
   cd public_html
   chmod 755 logs assets
   chmod 644 .env .htaccess index.php *.php
   ```

5. **Verifique o banco de dados:**
   - Acesse cPanel → phpMyAdmin
   - Importe `assets/ripfire_db.sql` se for primeira vez
   - Verifique as credenciais no `.env`

6. **Teste:**
   ```
   https://viniciusbaggio1772594984234.0452201.meusitehostgator.com.br/
   ```

---

## 🔑 Login Padrão

- **Usuário:** admin
- **Senha:** admin123

---

## 📁 Estrutura do Projeto

```
public_html/
├── .env                 # Configurações (TODOS os dados estão aqui!)
├── .htaccess            # Regras Apache
├── index.php            # Ponto de entrada
├── manifest.json        # PWA
├── sw.js                # Service Worker
├── src/
│   ├── config/          # Configuração do banco
│   ├── controllers/     # Lógica da aplicação
│   ├── models/          # Modelos de dados
│   └── views/           # Todas as páginas
├── assets/
│   ├── estilo.css       # Estilos
│   ├── ripfire_db.sql   # Script do banco
│   └── uploads/         # Arquivos subidos
└── logs/                # Logs da aplicação
```

---

## 🔒 Configurações do `.env`

```
DB_HOST=localhost              # Server MySQL
DB_NAME=vin31871_ripfire       # Nome do banco
DB_USER=vin31871_ripfire       # Usuário MySQL
DB_PASSWORD=Vb357753@          # Senha MySQL
APP_ENV=production             # Ambiente
APP_DEBUG=true                 # Mostra erros (false em produção)
APP_URL=https://seu-dominio/   # URL da aplicação
APP_TIMEZONE=America/Sao_Paulo # Timezone
```

---

## ⚙️ Troubleshooting

### Erro 500
- Verifique o `.env` - credenciais corretas?
- Verifique `APP_DEBUG=true` para ver o erro real
- Acesse `cPanel → Error Log` para ver logs do servidor

### Problema ao fazer push
```bash
# Se precisar forçar:
git push -u origin master --force

# Se a pasta estiver suja:
git status
git add .
git commit -m "sua mensagem"
git push
```

---

**⚠️ ATENÇÃO:** Este repositório contém `.env` com credenciais reais. Use apenas para testes!

**Desenvolvido por Vinicius Baggio**