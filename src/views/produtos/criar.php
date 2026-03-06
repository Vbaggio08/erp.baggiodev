
<?php
    // Lista oficial de cores da Ripfire
    $lista_cores = ['Preta', 'Branca', 'Off-White', 'Azul-Marinho', 'Bordô', 'Verde', 'Marrom', 'Vermelha', 'Doce de Leite'];
?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">📦 Cadastrar Novo Produto</h1>
        <a href="index.php?rota=produtos" class="btn-red" style="text-decoration:none;">Voltar para Lista</a>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div style="background: var(--bg-surface-2); padding: 25px; border-radius: 8px; border: 1px solid #444; height: fit-content;">
            <h3 style="color:#e6b800; margin-top:0; font-size:16px; border-bottom:1px solid #444; padding-bottom:10px;">Preencha os Dados</h3>
            
            <form action="index.php?rota=salvar_produto" method="POST">
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Nome / Descrição do Produto</label>
                    <input type="text" name="nome" required placeholder="Ex: Camiseta Oversized" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <div style="grid-column: span 2; margin-bottom:15px;">
    
    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <label style="color:#aaa; margin:0;">Tamanhos da Grade</label>
        
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-sm btn-blue" onclick="marcarGrade('infantil')">👦 Infantil (2 ao 16)</button>
            <button type="button" class="btn btn-sm btn-gold" onclick="marcarGrade('adulto')">👨 Adulto (PP ao G3)</button>
            <button type="button" class="btn btn-sm" style="background:#444; color:#fff;" onclick="marcarGrade('limpar')">🧹 Limpar</button>
        </div>
    </div>
    
    <div style="display:flex; gap:15px; flex-wrap:wrap; background:#222; padding:15px; border:1px solid #555; border-radius:4px; color:#fff;">
        
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="UN"> UN</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="PP"> PP</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="P"> P</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="M"> M</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="G"> G</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="GG"> GG</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="G1"> G1</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="G2"> G2</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="G3"> G3</label> <div style="width: 100%; height: 1px; background: #444; margin: 5px 0;"></div> <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="2"> 2</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="4"> 4</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="6"> 6</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="8"> 8</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="10"> 10</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="12"> 12</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="14"> 14</label>
        <label style="cursor:pointer;"><input type="checkbox" name="tamanhos[]" value="16"> 16</label>
    </div>
</div>

<script>
function marcarGrade(tipo) {
    // 1. Pega todas as caixinhas de tamanho da tela
    const checkboxes = document.querySelectorAll('input[name="tamanhos[]"]');
    
    // 2. Define quais valores pertencem a cada grade
    const gradeAdulto = ['PP', 'P', 'M', 'G', 'GG', 'G1', 'G2', 'G3'];
    const gradeInfantil = ['2', '4', '6', '8', '10', '12', '14', '16'];

    // 3. Desmarca tudo primeiro para evitar misturar adulto com infantil sem querer
    checkboxes.forEach(box => box.checked = false);

    // 4. Marca apenas os itens da grade que o usuário clicou
    if (tipo === 'adulto') {
        checkboxes.forEach(box => {
            if (gradeAdulto.includes(box.value)) box.checked = true;
        });
    } else if (tipo === 'infantil') {
        checkboxes.forEach(box => {
            if (gradeInfantil.includes(box.value)) box.checked = true;
        });
    }
    // Se o tipo for 'limpar', ele só roda o passo 3 e deixa tudo vazio.
}
</script>
       <div style="display:flex; gap:15px; flex-wrap:wrap; background:#222; padding:15px; border:1px solid #555; border-radius:4px; color:#fff;">
    <?php foreach($lista_cores as $cor_nome): ?>
        <label style="cursor:pointer;">
            <input type="checkbox" name="cores[]" value="<?= $cor_nome ?>"> <?= $cor_nome ?>
        </label>
    <?php endforeach; ?>
</div>
</div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Preço de Custo (R$)</label>
                        <input type="text" name="custo" placeholder="0,00" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Preço de Venda (R$)</label>
                        <input type="text" name="preco" placeholder="0,00" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                </div>

                <div style="margin-bottom:25px;">
                    <label style="display:block; color:#aaa; margin-bottom:5px;">SKU / Código (Opcional)</label>
                    <input type="text" name="sku" placeholder="Deixe em branco para gerar automático" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                </div>

                <button type="submit" class="btn-green" style="width:100%; padding:15px; font-size:16px;">
                    💾 Salvar Produto
                </button>
            </form>
        </div>

        <div style="background: var(--bg-surface-2); padding: 25px; border-radius: 8px; border: 1px solid #444;">
            <h3 style="color:#e6b800; margin-top:0; font-size:16px; border-bottom:1px solid #444; padding-bottom:10px;">Já Cadastrados</h3>
            
            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #333; border-radius: 4px; background: #222;">
                <table style="width:100%; border-collapse: collapse; font-size:13px; color:#ddd;">
                    <thead style="position: sticky; top: 0; background: #111; z-index: 1;">
                        <tr>
                            <th style="padding:10px; text-align:left; border-bottom:1px solid #555;">Produto</th>
                            <th style="padding:10px; text-align:center; border-bottom:1px solid #555;">Tam / Cor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($produtos)): ?>
                            <?php foreach($produtos as $p): ?>
                            <tr style="border-bottom:1px solid #333;">
                                <td style="padding:10px;">
                                    <strong><?= htmlspecialchars($p['nome'] ?? '') ?></strong><br>
                                    <span style="font-size:11px; color:#777;">Cód: <?= htmlspecialchars($p['sku'] ?? '-') ?></span>
                                </td>
                                <td style="padding:10px; text-align:center; color:#aaa;">
                                    <?= htmlspecialchars($p['tamanho'] ?? 'UN') ?> <br> 
                                    <?= htmlspecialchars($p['cor'] ?? '-') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" style="text-align:center; padding:30px; color:#666;">
                                    Nenhum produto cadastrado ainda.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top:10px; font-size:12px; color:#777; text-align:right;">
                Total de itens: <?= count($produtos ?? []) ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>