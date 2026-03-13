<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedido #<?= htmlspecialchars($lista_fichas[0]['numero_pedido'] ?? 'Produção') ?></title>
    <style>
        /* CONFIGURAÇÃO GERAL E RESET */
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } 
        body { font-family: 'Arial', sans-serif; background: #555; margin: 0; padding: 10px; }
        
        /* ESTILO DA FOLHA A4 - OTIMIZADO PARA 1 PÁGINA */
        .folha-a4 {
            background: #fff; width: 210mm; margin: 0 auto 10px auto;
            padding: 8mm; box-shadow: 0 0 10px rgba(0,0,0,0.5); color: #000;
            position: relative;
            page-break-inside: avoid;
            height: 297mm;
            display: flex;
            flex-direction: column;
        }
        
        /* QUEBRA DE PÁGINA NA IMPRESSÃO */
        .quebra-pagina { page-break-after: always; }

        h1 { text-align: center; border-bottom: 2px solid #000; padding-bottom: 6px; margin: 0 0 10px 0; font-size: 20px; }
        .campo { margin-bottom: 4px; font-size: 12px; border-bottom: 1px dotted #ccc; padding-bottom: 2px; }
        .campo strong { font-weight: bold; font-size: 13px; margin-right: 3px; }
        
        .grid-topo { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 8px; margin-bottom: 8px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        
        /* BOX DO PRODUTO (MODELO, COR E GRADE) */
        .box-produto { border: 1px solid #000; padding: 10px; margin: 8px 0; background: #f4f4f4; }
        
        /* CONTAINER DO MOCKUP (SUPORTA IMG E PDF) */
        .box-imagem {
            border: 1px solid #000; margin: 6px 0;
            display: flex; align-items: center; justify-content: center;
            background: #f9f9f9;
            flex: 1;
            min-height: 120px;
            max-height: 160px;
        }

        .aviso { font-weight: bold; background: #eee; padding: 5px; border: 1px solid #000; margin-top: 5px; font-size: 9px; }

        /* AJUSTES PARA IMPRESSORA */
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .folha-a4 { box-shadow: none; width: 100%; margin: 0; padding: 5mm; border: none; height: auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align:center; margin-bottom:20px;">
        <button onclick="window.print()" style="padding:12px 25px; background:#2ecc71; color:white; border:none; cursor:pointer; font-size:16px; font-weight:bold; border-radius:5px;">🖨️ IMPRIMIR PEDIDO COMPLETO</button>
        <a href="index.php?rota=listar_gabaritos" style="margin-left:20px; color:white; font-size:16px; text-decoration:none;">⬅️ Voltar para Lista</a>
    </div>

    <?php 
        // Detecta a URL base para as imagens aparecerem no PDF/Impressão
        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        
        // Detecta se está em raiz ou em pasta
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $path = substr($script, 0, strrpos($script, '/index.php'));
        if (empty($path) || $path === '') {
            $path = '';
        }
        
        $url_base = $protocolo . "://" . $_SERVER['HTTP_HOST'] . $path . "/";
    ?>

    <?php if(!empty($lista_fichas)): foreach($lista_fichas as $index => $ficha): ?>
    
    <div class="folha-a4 <?= ($index < count($lista_fichas) - 1) ? 'quebra-pagina' : '' ?>">
        
        <div style="position:absolute; top:15mm; right:15mm; font-size:12px; color:#555; font-weight:bold;">
            ITEM <?= $index + 1 ?> DE <?= count($lista_fichas) ?>
        </div>

        <h1>FICHA TÉCNICA / GABARITO</h1>
        
        <div class="grid-topo">
            <div class="campo"><strong>Cliente:</strong> <?= htmlspecialchars($ficha['cliente'] ?? '') ?></div>
            <div class="campo"><strong>Pedido:</strong> <?= htmlspecialchars($ficha['numero_pedido'] ?? '-') ?></div>
            <div class="campo"><strong>Plataforma:</strong> <?= htmlspecialchars($ficha['plataforma'] ?? 'WhatsApp') ?></div>
            <div class="campo"><strong>Vendedor:</strong> <?= htmlspecialchars($ficha['vendedor_nome'] ?? 'N/A') ?></div>
        </div>

        <div class="box-produto">
            <?php $grade = json_decode($ficha['itens_json'] ?? '[]', true); ?>
            
            <div style="display:grid; grid-template-columns: 2.5fr 2fr 1fr; gap: 5px; align-items: center;">
                
                <div style="border-right: 1px dashed #999; padding-right:5px;">
                    <span style="font-size: 10px; text-transform: uppercase; color:#555;">Modelo:</span><br>
                    <strong style="font-size: 14px; text-transform: uppercase; display:block; margin-bottom:5px;">
                        <?= htmlspecialchars($ficha['modelo'] ?? '') ?>
                    </strong>
                    <span style="font-size: 10px; text-transform: uppercase; color:#555;">Cor:</span><br>
                    <strong style="font-size: 13px; text-transform: uppercase;">
                        <?= htmlspecialchars($ficha['cor'] ?? 'Padrão') ?>
                    </strong>
                </div>

                <div style="text-align:center; border-right: 1px dashed #999; padding-right:5px;">
                    <span style="font-size: 10px; text-transform: uppercase; color:#555;">Distribuição:</span><br>
                    <?php if(!empty($grade) && is_array($grade)): ?>
                        <table style="width:100%; margin-top:2px; border-collapse:collapse; font-size:11px;">
                            <tr style="border-bottom:1px solid #ccc;">
                                <?php foreach($grade as $tam => $qtd): ?>
                                    <th style="padding:1px; color:#555; font-size:9px;"><?= $tam ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach($grade as $tam => $qtd): ?>
                                    <td style="font-weight:bold; font-size:13px; padding:1px;"><?= $qtd ?></td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    <?php else: ?>
                        <strong style="font-size: 18px; display:block;"><?= htmlspecialchars($ficha['tamanho'] ?? 'UN') ?></strong>
                    <?php endif; ?>
                </div>

                <div style="text-align:center;">
                    <span style="font-size: 10px; text-transform: uppercase; color:#555;">Qtd:</span><br>
                    <strong style="font-size: 24px;"><?= htmlspecialchars($ficha['quantidade'] ?? '0') ?></strong>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:5px; margin:5px 0;">
            <div class="campo"><strong>Contato:</strong> <?= htmlspecialchars($ficha['contato'] ?? '-') ?></div>
            <div class="campo"><strong>Data Ped:</strong> <?= !empty($ficha['data_pedido']) ? date('d/m/Y', strtotime($ficha['data_pedido'])) : '-' ?></div>
            <div class="campo"><strong>Entrega:</strong> <?= !empty($ficha['data_entrega']) ? date('d/m/Y', strtotime($ficha['data_entrega'])) : 'A DEFINIR' ?></div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:5px; margin:4px 0;">
            <div class="campo"><strong>Valor Unit:</strong> R$ <?= number_format($ficha['valor_unit'] ?? 0, 2, ',', '.') ?></div>
            <div class="campo"><strong>Valor Total:</strong> R$ <?= number_format($ficha['valor_total'] ?? 0, 2, ',', '.') ?></div>
        </div>

        <div class="campo" style="background: #f4f4f4; padding: 5px 6px; border-left: 3px solid #3498db; margin-top: 4px; font-size:11px;">
            <strong>Pagamento:</strong> <?= htmlspecialchars($ficha['meio_pagamento'] ?? 'Não informado') ?>
        </div>

        <h3 style="margin-bottom:4px; margin-top:4px; font-size:13px; text-transform: uppercase;">Mockup / Referência Visual</h3>
        <div class="box-imagem">
            <?php if (!empty($ficha['imagem_mockup'])): ?>
                <?php 
                    $extensao = strtolower(pathinfo($ficha['imagem_mockup'], PATHINFO_EXTENSION)); 
                    $arquivo = $url_base . "assets/uploads/" . $ficha['imagem_mockup'];
                ?>

                <?php if ($extensao === 'pdf'): ?>
                    <iframe src="<?= $arquivo ?>#toolbar=0&navpanes=0&scrollbar=0" width="100%" height="160px" style="border:1px solid #000;"></iframe>
                <?php else: ?>
                    <img src="<?= $arquivo ?>" style="max-width:100%; max-height:160px; object-fit: contain; display: block; border:1px solid #000; padding:5px; background:#fff;">
                <?php endif; ?>

            <?php else: ?>
                <p style="color:#aaa; font-style:italic; padding:10px; margin:0; font-size:9px;">Nenhuma arte ou mockup anexado a este item.</p>
            <?php endif; ?>
        </div>

        <?php if(!empty($ficha['observacoes'])): ?>
            <div class="campo" style="background:#fef9e7; padding:4px; border:1px solid #f39c12; font-size:10px; margin:3px 0 0 0;">
                <strong>Obs:</strong> <?= htmlspecialchars(substr($ficha['observacoes'], 0, 100)) ?>
            </div>
        <?php endif; ?>

        <div class="aviso" style="padding:4px; font-size:8px; margin:3px 0 0 0;">
            ⚠️ <strong>Verificar:</strong> COR, MODELO e GRADE antes de produzir. Pedido #<?= htmlspecialchars($ficha['numero_pedido'] ?? '') ?> (<?= htmlspecialchars($ficha['plataforma'] ?? '') ?>)
        </div>
    </div>

    <?php 
        // Lógica para adicionar página de comprovante
        if (!empty($ficha['caminho_comprovante'])) {
            // Adiciona quebra de página apenas se não for o último item da lista total
            // ou se o item atual já for o último mas tem um comprovante
            $isLastItem = ($index === count($lista_fichas) - 1);
            $quebraPaginaExtra = !$isLastItem ? 'quebra-pagina' : '';

            $arquivoComp = $url_base . "assets/uploads/comprovantes/" . $ficha['caminho_comprovante'];
            $extComp = strtolower(pathinfo($ficha['caminho_comprovante'], PATHINFO_EXTENSION));
    ?>
    <div class="folha-a4 <?= $quebraPaginaExtra ?>">
        <h1>COMPROVANTE DE PAGAMENTO</h1>
        <p style="text-align:center; font-size:14px; margin-bottom: 20px;">Referente ao pedido <strong>#<?= htmlspecialchars($ficha['numero_pedido'] ?? '') ?></strong>, item <strong><?= htmlspecialchars($ficha['modelo'] ?? '') ?></strong>.</p>
        
        <div class="box-imagem" style="height: 650px; border: 2px dashed #ccc;">
            <?php if ($extComp === 'pdf'): ?>
                <iframe src="<?= $arquivoComp ?>" width="100%" height="100%" style="border:none;"></iframe>
            <?php else: ?>
                <img src="<?= $arquivoComp ?>" style="max-width:100%; max-height:100%; object-fit: contain; display: block;">
            <?php endif; ?>
        </div>
    </div>
    <?php 
        } // Fim do if
    ?>
    
    <?php endforeach; endif; ?>

</body>
</html>