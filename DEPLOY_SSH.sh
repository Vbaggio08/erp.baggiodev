#!/bin/bash
# Script para deploy automático na HostGator via SSH

# INSTRUÇÕES:
# 1. Abra terminal/putty e conecte na HostGator:
#    ssh vin31871@viniciusbaggio1772594984234.linuxpro.com.br
#
# 2. Digite a senha
#
# 3. Navegue para a raiz:
#    cd ~
#
# 4. Limpe a pasta public_html (BACKUP ANTES!):
#    rm -rf public_html/*
#
# 5. Clone o repositório:
#    git clone https://SEUTOKEN@github.com/SEUUSER/SEUREPO.git public_html
#
# 6. Define permissões:
#    cd public_html
#    chmod 755 logs assets
#    chmod 644 .env .htaccess index.php
#
# 7. Acesse seu site:
#    https://viniciusbaggio1772594984234.0452201.meusitehostgator.com.br/

# Para gerar um token do GitHub:
# 1. GitHub → Settings → Developer settings → Personal access tokens
# 2. Gerar novo token com permissão "repo"
# 3. Copiar o token
# 4. Usar na URL: https://TOKEN@github.com/USUARIO/REPO.git