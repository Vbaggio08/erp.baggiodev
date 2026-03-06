<div class="box-relatorio">
    <h1 class="login-title">Importar Vendas (CSV)</h1>
    <p class="login-subtitle">Carregue sua planilha original da Shopee/Mercado Livre.</p>

    <div style="background: var(--bg-surface-2); padding: 30px; border-radius: 12px; border: 1px dashed #555; max-width: 600px;">
        
        <form action="index.php?rota=processar_csv_producao" method="POST" enctype="multipart/form-data">
            
            <div style="margin-bottom: 20px; background: #333; padding: 15px; border-radius: 8px; border-left: 4px solid #e6b800;">
                <label style="display:block; margin-bottom:5px; color:#fff; font-weight:bold;">🏢 Para qual empresa são estas vendas?</label>
                <select name="empresa_destino" required style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; font-weight:bold; font-size:14px;">
                    <option value="Ripfire">Ripfire Streetwear</option>
                    <option value="Empresa 2">Segunda Empresa</option>
                    <option value="Empresa 3">Terceira Empresa</option>
                </select>
            </div>

            <label style="display:block; margin-bottom:10px; color:#aaa;">2. Selecione o arquivo CSV:</label>
            <input type="file" name="arquivo_csv" accept=".csv" required 
                   style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff; border-radius:4px; margin-bottom:20px;">

            <div style="background:#333; padding:15px; border-radius:5px; margin-bottom:20px; font-size:12px; color:#ccc;">
                <strong>💡 Como funciona:</strong><br>
                O sistema vai ler o arquivo e lançar TODOS os pedidos para a empresa selecionada acima.<br>
                <br>
                <em>Colunas esperadas no CSV (separadas por ponto-e-vírgula):</em><br>
                Canal; Produto; Cor; Tamanho
            </div>

            <div style="display:flex; justify-content:space-between;">
                <a href="index.php?rota=fila_producao" class="login-button btn-red" style="text-decoration:none;">Cancelar</a>
                <button type="submit" class="login-button btn-green">📂 Processar Arquivo</button>
            </div>
        </form>
    </div>
</div>