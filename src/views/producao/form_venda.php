
<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <div>
            <h1 class="login-title" style="margin:0;">Nova Ordem de Produção</h1>
            <p class="login-subtitle" style="margin:0;">Adicione um item à fila.</p>
        </div>
        <a href="index.php?rota=fila_producao" class="login-button btn-red" style="width:auto; padding: 10px 20px; text-decoration:none;">
            Cancelar
        </a>
    </div>

    <form action="index.php?rota=salvar_venda_fila" method="POST" enctype="multipart/form-data" 
          style="background: var(--bg-surface-2); padding: 30px; border-radius: 12px; border: 1px solid #444;">
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            
            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; color:#e6b800; font-weight:bold;">🏭 Produzir para qual Empresa?</label>
                <select name="empresa" required style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px; font-weight:bold;">
                    <option value="Ripfire">Ripfire Streetwear</option>
                    <option value="Empresa 2">Segunda Empresa</option>
                    <option value="Empresa 3">Terceira Empresa</option>
                </select>
            </div>

            <div style="grid-column: span 2;">
                <label style="display:block; margin-bottom:5px; color:#aaa;">Canal de Venda / Origem</label>
                <select name="canal" required style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                    <option value="Shopee">Shopee</option>
                    <option value="Mercado Livre">Mercado Livre</option>
                    <option value="Loja Física">Loja Física</option>
                    <option value="Instagram">Instagram</option>
                    <option value="Estoque">Reposição de Estoque</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; color:#aaa;">Produto</label>
                <input type="text" name="tipo" required placeholder="Ex: Camiseta Básica" 
                       style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; color:#aaa;">Cor</label>
                <input type="text" name="cor" required placeholder="Ex: Branca" 
                       style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; color:#aaa;">Tamanho</label>
                <select name="tamanho" required style="width:100%; padding:12px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
                    <option value="P">P</option>
                    <option value="M">M</option>
                    <option value="G">G</option>
                    <option value="GG">GG</option>
                    <option value="XG">XG</option>
                    <option value="UN">UN</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; color:#aaa;">Foto do Produto (Opcional)</label>
                <input type="file" name="foto" accept="image/*" 
                       style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px;">
            </div>

        </div>

        <button type="submit" class="login-button btn-green" style="margin-top:20px; width:100%; height: 50px; font-size:16px;">
            🚀 Lançar para Produção
        </button>

    </form>
</div>
</body>
</html>