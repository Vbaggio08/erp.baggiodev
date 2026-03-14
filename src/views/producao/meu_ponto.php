<?php
// Dados disponíveis:
// $apontamentos - array de apontamentos dos últimos 30 dias
?>

<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">📋 Meu Ponto - Últimos 30 Dias</h1>
            
            <div class="card shadow">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Data</th>
                                <th>Entrada 1</th>
                                <th>Saída 1</th>
                                <th>Entrada 2</th>
                                <th>Saída 2</th>
                                <th>Horas</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apontamentos as $apt): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo date('d/m/Y', strtotime($apt['data'])); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo date('l', strtotime($apt['data'])); ?></small>
                                    </td>
                                    <td><?php echo $apt['hora_entrada_1'] ?? '---'; ?></td>
                                    <td><?php echo $apt['hora_saida_1'] ?? '---'; ?></td>
                                    <td><?php echo $apt['hora_entrada_2'] ?? '---'; ?></td>
                                    <td><?php echo $apt['hora_saida_2'] ?? '---'; ?></td>
                                    <td>
                                        <?php 
                                            $horas = 0;
                                            if ($apt['hora_entrada_1'] && $apt['hora_saida_1']) {
                                                $entrada = strtotime($apt['hora_entrada_1']);
                                                $saida = strtotime($apt['hora_saida_1']);
                                                $horas = ($saida - $entrada) / 3600;
                                            }
                                            echo number_format($horas, 2, ',', '.');
                                        ?>h
                                    </td>
                                    <td>
                                        <?php 
                                            $status_badge = 'badge-secondary';
                                            $status_texto = $apt['status'];
                                            
                                            if ($apt['status'] === 'presente') $status_badge = 'badge-success';
                                            elseif ($apt['status'] === 'falta') $status_badge = 'badge-danger';
                                            elseif ($apt['status'] === 'atestado') $status_badge = 'badge-info';
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>"><?php echo $status_texto; ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="Ver detalhes">👁️</button>
                                        <button class="btn btn-sm btn-outline-warning" title="Solicitar alteração">✏️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Resumo -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total de Dias</h5>
                            <p class="fs-4 fw-bold"><?php echo count($apontamentos); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Dias Presentes</h5>
                            <p class="fs-4 fw-bold">
                                <?php 
                                    echo count(array_filter($apontamentos, function($a) { 
                                        return $a['status'] === 'presente'; 
                                    })); 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total de Horas</h5>
                            <p class="fs-4 fw-bold">
                                <?php 
                                    $total_horas = 0;
                                    foreach ($apontamentos as $apt) {
                                        if ($apt['hora_entrada_1'] && $apt['hora_saida_1']) {
                                            $entrada = strtotime($apt['hora_entrada_1']);
                                            $saida = strtotime($apt['hora_saida_1']);
                                            $total_horas += ($saida - $entrada) / 3600;
                                        }
                                    }
                                    echo number_format($total_horas, 2, ',', '.');
                                ?>h
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
