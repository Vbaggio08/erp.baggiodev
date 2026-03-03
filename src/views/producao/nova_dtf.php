<?php include __DIR__ . '/../geral/header.php'; ?>

<?php
    // Mantém os dados do cliente e do pedido, se vierem via GET
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
            <?= isset($ficha['id']) ? "✏️ Editando Pedido DTF #$num" : '✨ Novo Pedido DTF' ?>
        </h1>
        <a href="index.php?rota=fila_producao" class="btn-red" style="text-decoration:none;">Cancelar</a>
    </div>

    <div style="display: flex; justify-content: center;">
        <div style="flex: 1; max-width: 900px; background: var(--bg-surface-2); padding: 30px; border-radius: 8px; border: 1px solid #444;">
            <form action="index.php?rota=salvar_dtf" method="POST" enctype="multipart/form-data">
                
                <?php if(isset($ficha['id'])): ?>
                    <input type="hidden" name="id" value="<?= $ficha['id'] ?>">
                    <input type="hidden" name="comprovante_atual" value="<?= $ficha['caminho_comprovante'] ?? '' ?>">
                <?php endif; ?>

                <!-- Informações do Cliente e Pedido -->
                <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:20px; margin-bottom:20px;">
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
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; color:#e6b800; margin-bottom:5px;">Nº Pedido</label>
                        <input type="text" name="numero_pedido" value="<?= htmlspecialchars($num) ?>" style="width:100%; padding:10px; background:#222; border:1px solid #555; color:#fff;">
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

                <!-- Campos específicos para DTF -->
                <div style="background:#222; padding:20px; border-radius:6px; border:1px solid #555; margin-bottom:20px;">
                    <label style="color:#e6b800; font-weight:bold; display:block; margin-bottom:15px;">Detalhes do Serviço DTF</label>
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px;">
                        <div>
                            <label style="display:block; color:#aaa; margin-bottom:5px;">Metros</label>
                            <input type="number" step="0.01" name="metros" id="metros" oninput="calcularValorFinal()" value="<?= $ficha['metros'] ?? '' ?>" style="width:100%; padding:10px; background:#111; border:1px solid #555; color:#fff; font-weight:bold;">
                        </div>
                        <div>
                            <label style="display:block; color:#aaa; margin-bottom:5px;">Valor p/ Metro (R$)</label>
                            <input type="text" name="valor_metro" id="valorMetro" oninput="calcularValorFinal()" value="<?= $ficha['valor_metro'] ?? '' ?>" style="width:100%; padding:10px; background:#111; border:1px solid #555; color:#fff;">
                        </div>
                        <div>
                            <label style="display:block; color:#aaa; margin-bottom:5px;">Valor Final (R$)</label>
                            <input type="text" name="valor_final" id="valorFinal" readonly value="<?= $ficha['valor_final'] ?? '' ?>" style="width:100%; padding:10px; background:#333; border:1px solid #555; color:#2ecc71; font-weight:bold;">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; color:#aaa; margin-bottom:5px;">Observações</label>
                    <textarea name="obs" style="width:100%; height:80px; background:#222; border:1px solid #555; color:#fff; padding:10px;"><?= htmlspecialchars($ficha['observacoes'] ?? '') ?></textarea>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; padding: 20px; background: #222; border: 1px solid #555; border-radius: 6px;">
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Arquivo para Impressão</label>
                        <input type="file" name="arquivo_impressao" style="color:#aaa;">
                        <?php if (!empty($ficha['arquivo_impressao'])): ?>
                            <div style="font-size:12px; margin-top:5px;">
                                <a href="assets/uploads/<?= $ficha['arquivo_impressao'] ?>" target="_blank" style="color:#2ecc71;">Ver arquivo atual</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label style="display:block; color:#aaa; margin-bottom:5px;">Anexar Comprovante</label>
                        <input type="file" name="arquivo_comprovante" style="color:#aaa;">
                        <?php if (!empty($ficha['caminho_comprovante'])): ?>
                            <div style="font-size:12px; margin-top:5px;">
                                <a href="assets/uploads/comprovantes/<?= $ficha['caminho_comprovante'] ?>" target="_blank" style="color:#2ecc71;">Ver comprovante atual</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:15px; margin-top:30px;">
                    <button type="submit" class="btn-green" style="padding: 12px 25px; font-weight:bold;">💾 Salvar Pedido DTF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calcularValorFinal() {
    const metrosInput = document.getElementById('metros');
    const valorMetroInput = document.getElementById('valorMetro');
    const valorFinalInput = document.getElementById('valorFinal');

    // Usa parseFloat para lidar com decimais, substitui vírgula por ponto
    const metros = parseFloat(metrosInput.value.replace(',', '.')) || 0;
    const valorMetro = parseFloat(valorMetroInput.value.replace(',', '.')) || 0;

    if (metros > 0 && valorMetro > 0) {
        const final = (metros * valorMetro).toFixed(2);
        // Formata para o padrão brasileiro (com vírgula) para exibição
        valorFinalInput.value = final.replace('.', ',');
    } else {
        valorFinalInput.value = '';
    }
}
</script>

</body>
</html>