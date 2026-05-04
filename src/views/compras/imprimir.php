<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ordem de Compra #<?= $compra['id'] ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #000; margin: 0; padding: 20px; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 3px solid #009999; padding-bottom: 10px; }
        .logo-img { max-height: 60px; max-width: 200px; object-fit: contain; }
        .company-info { text-align: right; font-size: 11px; line-height: 1.4; color: #555; }
        .section-title { font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; margin-bottom: 5px; padding-bottom: 2px; font-size: 11px; margin-top: 20px; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .info-col { width: 48%; }
        .info-line { margin-bottom: 4px; }
        .label { font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th { background-color: #ddd; border: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; }
        td { border: 1px solid #000; padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .totals-area { text-align: right; margin-top: 10px; font-size: 12px; }
        .final-total { font-weight: bold; font-size: 14px; margin-top: 5px; }
        .payment-box { border: 1px solid #ccc; margin-top: 15px; }
        .payment-header { display: flex; background: #eee; font-weight: bold; border-bottom: 1px solid #ccc; }
        .payment-row { display: flex; }
        .col-pay { flex: 1; padding: 4px; border-right: 1px solid #ccc; text-align: center; }
        .col-pay:last-child { border-right: none; }
        .signatures { margin-top: 80px; display: flex; justify-content: space-between; }
        .sig-line { width: 40%; text-align: center; border-top: 1px solid #000; padding-top: 5px; font-size: 11px; }
        .btn-print { position: fixed; top: 10px; right: 10px; background: #e6b800; color: #000; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 5px; }
        @media print { .btn-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

<a href="#" onclick="window.print()" class="btn-print">🖨️ Imprimir</a>

<div class="container">
    <div class="header">
        <div><img src="assets/img/logo_rip.png" alt="Ripfire" class="logo-img"></div>
        <div class="company-info">
            <strong><?= htmlspecialchars($compra['empresa']) ?></strong><br>
            CNPJ: <?= htmlspecialchars($empresaDados['cnpj'] ?? 'Não informado') ?><br>
            <?= htmlspecialchars($empresaDados['endereco'] ?? '') ?><br>
            <?= htmlspecialchars($empresaDados['cidade'] ?? '') ?>
        </div>
    </div>

    <div class="section-title">ORDEM DE COMPRA</div>
    <div class="info-grid">
        <div class="info-col">
            <div class="info-line"><span class="label">Pedido:</span> <?= str_pad($compra['id'], 5, '0', STR_PAD_LEFT) ?></div>
            <div class="info-line"><span class="label">Data:</span> <?= date('d/m/Y', strtotime($compra['data_pedido'])) ?></div>
            
            <div style="margin-top: 10px; border:1px solid #ddd; padding:5px;">
                <span class="label">DADOS DO FORNECEDOR</span><br>
                <?= htmlspecialchars($compra['fornecedor']) ?><br>
                <?= htmlspecialchars($fornecedorDados['endereco'] ?? '') ?><br>
                <?= htmlspecialchars($fornecedorDados['cidade'] ?? '') ?> - CEP: <?= htmlspecialchars($fornecedorDados['cep'] ?? '') ?><br>
                Contato: <?= htmlspecialchars($fornecedorDados['contato'] ?? '') ?>
            </div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-line"><span class="label">Entrega Prevista:</span> <?= date('d/m/Y', strtotime($compra['data_pedido'] . ' + 7 days')) ?></div>
            <div class="info-line"><span class="label">Email:</span> <?= htmlspecialchars($empresaDados['email'] ?? 'compras@ripfire.com') ?></div>
        </div>
    </div>

    <div class="section-title">ENDEREÇO DE ENTREGA</div>
    <div class="info-grid">
        <div class="info-col" style="width:100%">
            <?= htmlspecialchars($empresaDados['endereco'] ?? 'Retirar no Local') ?> - 
            <?= htmlspecialchars($empresaDados['cidade'] ?? '') ?> - 
            CEP: <?= htmlspecialchars($empresaDados['cep'] ?? '') ?>
        </div>
    </div>

    <div class="section-title">ITENS DO PEDIDO</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Item</th>
                <th>Descrição</th>
                <th style="width: 50px;">Un</th>
                <th style="width: 60px;">Qtde</th>
                <th style="width: 90px;">Preço</th>
                <th style="width: 90px;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalGeral = 0;
            $itens = json_decode($compra['itens_json'], true);
            $count = 1;
            if(is_array($itens)): foreach($itens as $item): 
                $qtd = (float)$item['qtd'];
                $valor = (float)str_replace(',', '.', $item['valor'] ?? 0);
                $subtotal = $qtd * $valor;
                $totalGeral += $subtotal;
            ?>
            <tr>
                <td class="text-center"><?= $count++ ?></td>
                <td><?= htmlspecialchars($item['produto']) ?> <small>(<?= htmlspecialchars($item['cor']) ?>)</small></td>
                <td class="text-center"><?= htmlspecialchars($item['tamanho']) ?></td>
                <td class="text-center"><?= $qtd ?></td>
                <td class="text-right">R$ <?= number_format($valor, 2, ',', '.') ?></td>
                <td class="text-right">R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="totals-area">
        <div class="final-total">TOTAL: R$ <?= number_format($totalGeral, 2, ',', '.') ?></div>
    </div>

    <div class="section-title">PAGAMENTO</div>
    <div class="payment-box">
        <div class="payment-header">
            <div class="col-pay">Condição</div>
            <div class="col-pay">Vencimento</div>
            <div class="col-pay">Valor</div>
        </div>
        <div class="payment-row">
            <div class="col-pay">A Combinar</div>
            <div class="col-pay"><?= date('d/m/Y', strtotime($compra['data_pedido'] . ' + 30 days')) ?></div>
            <div class="col-pay">R$ <?= number_format($totalGeral, 2, ',', '.') ?></div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-line">Assinatura do Comprador</div>
        <div class="sig-line">Assinatura do Recebedor</div>
    </div>
</div>
<script>window.onload = function() { window.print(); }</script>
</body>
</html>