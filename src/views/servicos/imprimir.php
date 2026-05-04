<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>OS #<?= $os['id'] ?></title>
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
        .desc-box { border: 1px solid #000; padding: 10px; min-height: 100px; margin-top: 5px; }
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
            <strong><?= htmlspecialchars($os['empresa']) ?></strong><br>
            CNPJ: <?= htmlspecialchars($empresaDados['cnpj'] ?? '') ?><br>
            <?= htmlspecialchars($empresaDados['endereco'] ?? '') ?><br>
            <?= htmlspecialchars($empresaDados['cidade'] ?? '') ?>
        </div>
    </div>

    <div class="section-title">ORDEM DE SERVIÇO</div>
    <div class="info-grid">
        <div class="info-col">
            <div class="info-line"><span class="label">OS Número:</span> <?= str_pad($os['id'], 5, '0', STR_PAD_LEFT) ?></div>
            <div class="info-line"><span class="label">Data Abertura:</span> <?= date('d/m/Y', strtotime($os['data_abertura'])) ?></div>
        </div>
        <div class="info-col" style="text-align: right;">
            <div class="info-line"><span class="label">Status:</span> <?= strtoupper($os['status']) ?></div>
        </div>
    </div>
    
    <div style="margin-top: 15px; border:1px solid #ccc; padding:10px;">
        <span class="label">DADOS DO PRESTADOR / CLIENTE</span><br>
        <strong><?= htmlspecialchars($os['cliente']) ?></strong><br>
        <?= htmlspecialchars($prestadorDados['endereco'] ?? 'Endereço não cadastrado') ?><br>
        <?= htmlspecialchars($prestadorDados['cidade'] ?? '') ?> - CEP: <?= htmlspecialchars($prestadorDados['cep'] ?? '') ?><br>
        Contato: <?= htmlspecialchars($prestadorDados['contato'] ?? $prestadorDados['telefone'] ?? '') ?>
    </div>

    <div class="section-title">DESCRIÇÃO DOS SERVIÇOS</div>
    <div class="desc-box">
        <?= nl2br(htmlspecialchars($os['descricao'])) ?>
    </div>

    <div class="section-title">VALORES</div>
    <div style="text-align: right; font-size: 14px;">
        Valor Acordado: <strong>R$ <?= number_format($os['valor'], 2, ',', '.') ?></strong>
    </div>

    <div class="signatures">
        <div class="sig-line">Assinatura do Solicitante</div>
        <div class="sig-line">Assinatura do Prestador</div>
    </div>
</div>
<script>window.onload = function() { window.print(); }</script>
</body>
</html>