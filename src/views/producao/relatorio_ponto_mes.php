<?php
// Relatório mensal de ponto - view
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Mensal de Ponto</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
        .footer { margin-top: 40px; display: flex; justify-content: space-around; }
        .assinatura { border-top: 1px solid #000; margin-top: 40px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container" style="padding: 20px;">
        <!-- Cabeçalho -->
        <div class="header">
            <h2>RELATÓRIO MENSAL DE APONTAMENTO DE PONTO</h2>
            <p>Período: <?php echo strftime('%B de %Y', strtotime($ano . '-' . $mes . '-01')); ?></p>
        </div>
        
        <!-- Info Funcionário -->
        <table style="margin-bottom: 30px;">
            <tr>
                <td><strong>Nome:</strong> <?php echo $usuario['nome']; ?></td>
                <td><strong>Matrícula:</strong> <?php echo $usuario['id']; ?></td>
            </tr>
            <tr>
                <td><strong>Cargo:</strong> <?php echo $usuario['cargo']; ?></td>
                <td><strong>Departamento:</strong> <?php echo $usuario['departamento']; ?></td>
            </tr>
            <tr>
                <td><strong>Carga Horária Diária:</strong> <?php echo $usuario['carga_horaria_diaria']; ?>h</td>
                <td><strong>Tipo de Contrato:</strong> <?php echo $usuario['tipo_contrato']; ?></td>
            </tr>
        </table>
        
        <!-- Tabela de Pontos -->
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Entrada 1</th>
                    <th>Saída 1</th>
                    <th>Entrada 2</th>
                    <th>Saída 2</th>
                    <th>Horas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $total_horas = 0;
                    foreach ($apontamentos as $apt): 
                        $horas = 0;
                        if ($apt['hora_entrada_1'] && $apt['hora_saida_1']) {
                            $entrada = strtotime($apt['hora_entrada_1']);
                            $saida = strtotime($apt['hora_saida_1']);
                            $horas = ($saida - $entrada) / 3600;
                        }
                        $total_horas += $horas;
                ?>
                <tr>
                    <td><?php echo date('d/m/Y (D)', strtotime($apt['data'])); ?></td>
                    <td><?php echo substr($apt['hora_entrada_1'], 0, 5) ?? '---'; ?></td>
                    <td><?php echo substr($apt['hora_saida_1'], 0, 5) ?? '---'; ?></td>
                    <td><?php echo substr($apt['hora_entrada_2'], 0, 5) ?? '---'; ?></td>
                    <td><?php echo substr($apt['hora_saida_2'], 0, 5) ?? '---'; ?></td>
                    <td><?php echo number_format($horas, 2, ',', '.'); ?></td>
                    <td>
                        <?php 
                            echo $apt['status'] === 'presente' ? '✅ Presente' :
                                 ($apt['status'] === 'falta' ? '❌ Falta' :
                                  '📋 Atestado');
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Resumo -->
        <table style="margin-top: 30px;">
            <tr>
                <th>Total de Horas</th>
                <th>Horas Esperadas</th>
                <th>Saldo</th>
                <th>Dias Presentes</th>
                <th>Faltas</th>
            </tr>
            <tr>
                <td><?php echo number_format($total_horas, 2, ',', '.'); ?>h</td>
                <td><?php 
                    $carga = floatval(str_replace(',', '.', $usuario['carga_horaria_diaria']));
                    $dias_uteis = count($apontamentos);
                    $horas_esperadas = $carga * $dias_uteis;
                    echo number_format($horas_esperadas, 2, ',', '.'); 
                ?>h</td>
                <td><?php 
                    $saldo = $total_horas - $horas_esperadas;
                    echo number_format($saldo, 2, ',', '.'); 
                ?>h</td>
                <td>
                    <?php 
                        echo count(array_filter($apontamentos, function($a) { 
                            return $a['status'] === 'presente'; 
                        })); 
                    ?>
                </td>
                <td>
                    <?php 
                        echo count(array_filter($apontamentos, function($a) { 
                            return $a['status'] === 'falta'; 
                        })); 
                    ?>
                </td>
            </tr>
        </table>
        
        <!-- Assinaturas -->
        <div class="footer" style="margin-top: 60px;">
            <div class="assinatura">
                <p>Funcionário</p>
                ___________________________________________<br>
                <?php echo $usuario['nome']; ?> / <?php echo date('d/m/Y'); ?>
            </div>
            <div class="assinatura">
                <p>Departamento RH</p>
                ___________________________________________<br>
                Data: <?php echo date('d/m/Y'); ?>
            </div>
        </div>
        
        <!-- Aviso Legal -->
        <div style="margin-top: 40px; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px; color: #666;">
            <p>
                Este relatório é um documento comprobatório de apontamento de ponto de acordo com a CLT 
                (Consolidação das Leis do Trabalho) e Lei 10.820/2003. 
                Alterações não autorizadas neste documento constituem falsificação.
            </p>
        </div>
    </div>
    
    <!-- Botões de Ação (não aparecem na impressão) -->
    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px; gap: 10px; display: flex;">
        <button onclick="window.print()" class="btn btn-primary" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ Imprimir
        </button>
        <button onclick="window.history.back()" class="btn btn-secondary" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
            ← Voltar
        </button>
    </div>
</body>
</html>
