# 🚀 Ripfire ERP System

Sistema de gestão empresarial completo.

## ⚠️ CONFIGURAÇÃO TEMPORÁRIA PARA DESENVOLVIMENTO

**ATENÇÃO:** Esta versão tem configurações de segurança reduzidas para facilitar o desenvolvimento. **NÃO USE EM PRODUÇÃO!**

### Como usar:

1. **Clone o repositório:**
   ```bash
   git clone <seu-repo-url>
   cd ERP
   ```

2. **Configure o ambiente:**
   - Copie `.env.example` para `.env`
   - Ajuste as configurações do banco de dados

3. **Inicie o servidor:**
   - Use XAMPP ou similar
   - Acesse: `http://localhost/ERP/`

4. **Login padrão:**
   - Usuário: admin
   - Senha: admin123

### 🔒 Para Produção (HostGator):

1. **Restaure as regras de segurança** no `.htaccess`
2. **Mude** `APP_DEBUG=false` no `.env`
3. **Configure** `APP_ENV=production`
4. **Ajuste** `APP_URL` para seu domínio
5. **Configure permissões** corretas (755 pastas, 644 arquivos)

### 📁 Estrutura:
- `src/` - Código fonte
- `assets/` - Arquivos estáticos
- `logs/` - Logs do sistema
- `.env` - Configurações (não commite!)

**Desenvolvido por Vinicius Baggio**