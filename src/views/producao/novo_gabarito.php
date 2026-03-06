
<?php
    $cli = $_GET['cliente'] ?? ($ficha['cliente'] ?? '');
    $tel = $_GET['contato'] ?? ($ficha['contato'] ?? '');
    $num = $_GET['numero_pedido'] ?? ($num ?? ($ficha['numero_pedido'] ?? '01'));
    $plat = $_GET['plataforma'] ?? ($ficha['plataforma'] ?? '');
    $dtPed = $_GET['data_pedido'] ?? ($ficha['data_pedido'] ?? date('Y-m-d'));
    $dtEnt = $_GET['data_entrega'] ?? ($ficha['data_entrega'] ?? '');
?>

<div class="box-relatorio">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 class="login-title">
            <?= isset($ficha['id']) ? "✏️ Editando Pedido #$num" : '✨ Novo Pedido' ?>
        </h1>
        <a href="index.php?rota=listar_gabaritos" class="btn-red" style="text-decoration:none;">Cancelar</a>
    </div>

    <div style="display: flex; gap: 25px; align-items: flex-start;">
        
        <?php if (!empty($itens_pedido)): ?>
        <div style="width: 280px; background: #222; border-radius: 8px; border: 1px solid #444; padding: 15px; position: sticky; top: 20px;">
            <h3 style="color: #e6b800; font-size: 14px; margin-bottom: 15px; border-bottom: 1px solid #444; padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <span class="material-icons" style="font-size: 18px;">format_list_numbered</span>
                Itens do Pedido #<?= htmlspecialchars($num) ?>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 450px; overflow-y: auto;">
                <?php foreach($itens_pedido as $index => $item): ?>
                    <?php 
                        $ativo = (isset($ficha['id']) && $item['id'] == $ficha['id']);
                        $numeroSequencial = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    ?>
                    <a href="index.php?rota=editar_gabarito&id=<?= $item['id'] ?>" 
                       style="text-decoration: none; padding: 12px; border-radius: 6px; font-size: 13px;
                              background: <?= $ativo ? 'rgba(230, 184, 0, 0.1)' : '#111' ?>; 
                              border: 1px solid <?= $ativo ? '#e6b800' : '#333' ?>;
                              color: <?= $ativo ? '#fff' : '#999' ?>;
                              display: flex; flex-direction: column; gap: 3px;">
                        <span style="font-weight: bold; color: <?= $ativo ? '#e6b800' : '#666' ?>;">ITEM <?= $numeroSequencial ?></span>
                        <span style="font-weight: <?= $ativo ? 'bold' : 'normal' ?>;"><?= htmlspecialchars($item['modelo'] ?? '') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #333;">
                <a href="index.php?rota=novo_gabarito&cliente=<?= urlencode($cli) ?>&numero_pedido=<?= urlencode($num) ?>&contato=<?= urlencode($tel) ?>&plataforma=<?= urlencode($plat) ?>&data_pedido=<?= urlencode($dtPed) ?>&data_entrega=<?= urlencode($dtEnt) ?>" 
                   class="btn-green" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 12px; padding: 10px; background: #2ecc71;">
                    <span class="material-icons" style="font-size: 16px;">add_circle</span> Novo Item neste Pedido
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div style="flex: 1; background: var(--bg-surface-2); padding: 30px; border-radius: 8px; border: 1px solid #444;">
            <form action="index.php?rota=salvar_gabarito" method="POST" enctype="multipart/form-data">
                
                <?php if(isset($ficha['id'])): ?>
                    <input type="hidden" name="id" value="<?= $ficha['id'] ?>">
                    <input type="hidden" name="imagem_atual" value="<?= $ficha['imagem_mockup'] ?? '' ?>">
                    <input type="hidden" name="comprovante_atual" value="<?= $ficha['caminho_comprovante'] ?? '' ?>">
                <?php endif; ?>

                <div style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Cliente</label>
                        <input type="text" name="cliente" required value="<?= htmlspecialchars($cli) ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Telefone</label>
                        <input type="text" name="contato" value="<?= htmlspecialchars($tel) ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Plataforma</label>
                        <select name="plataforma" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                            <option value="WhatsApp" <?= $plat == 'WhatsApp' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="Balcão" <?= $plat == 'Balcão' ? 'selected' : '' ?>>Balcão / Loja</option>
                            <option value="Instagram" <?= $plat == 'Instagram' ? 'selected' : '' ?>>Instagram</option>
                            <option value="Mercado Livre" <?= $plat == 'Mercado Livre' ? 'selected' : '' ?>>Mercado Livre</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Vendedor</label>
                        <select name="vendedor_id" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                            <option value="">-- Selecione --</option>
                            <?php foreach($usuarios as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= (isset($ficha['vendedor_id']) && $ficha['vendedor_id'] == $user['id']) ? 'selected' : '' ?>><?= htmlspecialchars($user['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; color:#e6b800; margin-bottom:5px;">Nº Pedido (Automático)</label>
                        <input type="text" 
                               name="numero_pedido" 
                               value="<?= htmlspecialchars($num) ?>" 
                               readonly 
                               tabindex="-1"
                               onfocus="this.blur()"
                               style="width:100%; padding:10px; background:#1a1a1a; border:1px solid #e6b800; color:#e6b800; font-weight:bold; cursor:not-allowed; outline:none; pointer-events: none;"
                               title="Gerado automaticamente pelo sistema">
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Data Pedido</label>
                        <input type="date" name="data_pedido" value="<?= $dtPed ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Previsão Entrega</label>
                        <input type="date" name="data_entrega" value="<?= $dtEnt ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Modelo / Produto</label>
                        <select name="modelo" id="modelo" required style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                            <option value="">-- Selecione o Modelo --</option>
                            <?php foreach($modelos_unicos as $modelo): ?>
                                <option value="<?= htmlspecialchars($modelo) ?>" <?= (isset($ficha['modelo']) && $ficha['modelo'] == $modelo) ? 'selected' : '' ?>><?= htmlspecialchars($modelo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Cor da Peça</label>
                        <input type="text" name="cor" required value="<?= htmlspecialchars($ficha['cor'] ?? '') ?>" placeholder="Ex: Preto" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
                    </div>
                </div>

                <div style="background:#222; padding:15px; border-radius:6px; border:1px solid #555; margin-bottom:20px;">
                    <label style="color:#e6b800; font-weight:bold; display:block; margin-bottom:10px;">Grade de Tamanhos</label>
                    <?php 
                        $gradeArr = isset($ficha['itens_json']) ? json_decode($ficha['itens_json'], true) : [];
                        $tamanhos_infantil = ['2', '4', '6', '8', '10', '12', '14', '16'];
                        $tamanhos_adulto = ['PP', 'P', 'M', 'G', 'GG', 'G1', 'G2', 'G3', 'UNIC'];
                    ?>
                    
                    <div id="grade-infantil" style="margin-bottom:15px;">
                        <span style="color:#aaa; font-size:12px; font-weight:bold;">Infantil</span>
                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:5px;">
                            <?php foreach($tamanhos_infantil as $tam): ?>
                                <div class="tamanho-input" data-tamanho="<?= $tam ?>" style="text-align:center;">
                                    <span style="font-size:12px; color:#aaa;"><?= $tam ?></span><br>
                                    <input type="number" name="grade[<?= $tam ?>]" value="<?= $gradeArr[$tam] ?? '' ?>" 
                                           class="input-grade" min="0" oninput="somarTotal()"
                                           style="width:55px; padding:8px; text-align:center; background:#111; border:1px solid #444; color:#fff; font-weight:bold;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div id="grade-adulto">
                        <span style="color:#aaa; font-size:12px; font-weight:bold;">Adulto</span>
                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:5px;">
                            <?php foreach($tamanhos_adulto as $tam): ?>
                                <div class="tamanho-input" data-tamanho="<?= $tam ?>" style="text-align:center;">
                                    <span style="font-size:12px; color:#aaa;"><?= $tam ?></span><br>
                                    <input type="number" name="grade[<?= $tam ?>]" value="<?= $gradeArr[$tam] ?? '' ?>" 
                                           class="input-grade" min="0" oninput="somarTotal()"
                                           style="width:55px; padding:8px; text-align:center; background:#111; border:1px solid #444; color:#fff; font-weight:bold;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div><label style="display:block; color:#aaa; margin-bottom:5px;">Qtd Total</label><input type="number" name="quantidade" id="totalQtd" readonly value="<?= $ficha['quantidade'] ?? '0' ?>" style="width:100%; padding:10px; background:#333; border:1px solid #555; color:#fff; font-weight:bold;"></div>
                    <div><label style="display:block; color:#aaa; margin-bottom:5px;">Valor Unit.</label><input type="text" name="valor_unit" id="vlrUnit" oninput="somarTotal()" value="<?= $ficha['valor_unit'] ?? '' ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;"></div>
                    <div><label style="display:block; color:#aaa; margin-bottom:5px;">Valor Total</label><input type="text" name="valor_total" id="vlrTotal" readonly value="<?= $ficha['valor_total'] ?? '' ?>" style="width:100%; padding:10px; background:#333; border:1px solid #555; color:#2ecc71; font-weight:bold;"></div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Observações</label>
                    <textarea name="obs" style="width:100%; height:80px; background:#222; border:1px solid #555; color:#fff; padding:10px;"><?= htmlspecialchars($ficha['observacoes'] ?? '') ?></textarea>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; padding: 20px; background: #222; border: 1px solid #555; border-radius: 6px;">
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Meio de Pagamento</label>
                        <select name="meio_pagamento" style="width:100%; padding:10px; background:#111; border:1px solid #555; color:#fff;">
                            <option value="">-- Selecione --</option>
                            <option value="Pix" <?= (isset($ficha['meio_pagamento']) && $ficha['meio_pagamento'] == 'Pix') ? 'selected' : '' ?>>Pix</option>
                            <option value="Débito" <?= (isset($ficha['meio_pagamento']) && $ficha['meio_pagamento'] == 'Débito') ? 'selected' : '' ?>>Cartão de Débito</option>
                            <option value="Crédito" <?= (isset($ficha['meio_pagamento']) && $ficha['meio_pagamento'] == 'Crédito') ? 'selected' : '' ?>>Cartão de Crédito</option>
                            <option value="Dinheiro" <?= (isset($ficha['meio_pagamento']) && $ficha['meio_pagamento'] == 'Dinheiro') ? 'selected' : '' ?>>Dinheiro</option>
                            <option value="Pago em Loja" <?= (isset($ficha['meio_pagamento']) && $ficha['meio_pagamento'] == 'Pago em Loja') ? 'selected' : '' ?>>Pago em Loja</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Anexar Comprovante</label>
                        <input type="file" name="caminho_comprovante" style="color:#aaa;">
                        <?php if (!empty($ficha['caminho_comprovante'])): ?>
                            <div style="font-size:12px; margin-top:5px;">
                                <a href="assets/uploads/comprovantes/<?= $ficha['caminho_comprovante'] ?>" target="_blank" style="color:#2ecc71;">Ver comprovante atual</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Arquivo Mockup</label>
                    <input type="file" name="mockup" style="color:#aaa;">
                    <?php if (!empty($ficha['imagem_mockup'])): ?>
                        <div style="font-size:12px; margin-top:5px;">
                            <a href="assets/uploads/<?= $ficha['imagem_mockup'] ?>" target="_blank" style="color:#2ecc71;">Ver mockup atual</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                    <button type="submit" name="acao" value="continuar" class="btn-blue" style="padding:15px; font-weight:bold;">💾 Atualizar e Próximo Item</button>
                    <button type="submit" name="acao" value="finalizar" class="btn-green" style="padding:15px; font-weight:bold;">✅ Atualizar e Sair</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function somarTotal() {
    let inputs = document.querySelectorAll('.input-grade');
    let total = 0;
    inputs.forEach(item => { if(item.value) total += parseInt(item.value); });
    document.getElementById('totalQtd').value = total;
    let unit = document.getElementById('vlrUnit').value.replace(',', '.');
    if(unit) {
        let final = (total * parseFloat(unit)).toFixed(2);
        document.getElementById('vlrTotal').value = final.replace('.', ',');
    }
}

window.tamanhosPorModelo = <?php echo json_encode($tamanhos_por_modelo); ?>;

document.getElementById('modelo').addEventListener('change', function() {
    const modelo = this.value;
    const tamanhosDisponiveis = window.tamanhosPorModelo[modelo] || [];
    const inputs = document.querySelectorAll('.tamanho-input');
    inputs.forEach(div => {
        const tam = div.getAttribute('data-tamanho');
        if (tamanhosDisponiveis.includes(tam)) {
            div.style.display = 'block';
        } else {
            div.style.display = 'none';
            const input = div.querySelector('input');
            if (input) input.value = '';
        }
    });
    somarTotal();
});

document.addEventListener('DOMContentLoaded', function() {
    const modeloSelect = document.getElementById('modelo');
    if (modeloSelect.value) {
        modeloSelect.dispatchEvent(new Event('change'));
    }
});
</script>

</body>
</html>