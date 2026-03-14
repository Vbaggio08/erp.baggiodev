<?php
// Dados disponíveis:
// $saldo - saldo de horas (positivo ou negativo)
// $usuario - dados do usuário (carga_horaria_diaria, tipo_contrato, cargo, departamento)
// $mes_atual - mês atual
// $ano_atual - ano atual
?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Card Principal de Saldo -->
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-body p-5">
                    <h1 class="card-title text-center mb-4">⏳ Saldo de Horas</h1>
                    
                    <!-- Mês/Ano -->
                    <div class="text-center mb-4">
                        <p class="text-muted">Período: <?php echo strftime('%B de %Y', mktime(0, 0, 0, $mes_atual, 1, $ano_atual)); ?></p>
                    </div>
                    
                    <!-- Saldo Grande -->
                    <div class="text-center mb-5">
                        <?php 
                            $classe_saldo = 'text-success';
                            $icone_saldo = '✅';
                            if ($saldo < 0) {
                                $classe_saldo = 'text-danger';
                                $icone_saldo = '⚠️';
                            }
                        ?>
                        <div class="fs-1 fw-bold <?php echo $classe_saldo; ?>">
                            <?php echo $icone_saldo; ?> 
                            <?php echo number_format(abs($saldo), 2, ',', '.'); ?>h
                        </div>
                        <p class="text-muted mt-2">
                            <?php if ($saldo > 0): ?>
                                Você tem saldo positivo (deve receber)
                            <?php elseif ($saldo < 0): ?>
                                Você está devendo (compensar no próximo mês)
                            <?php else: ?>
                                Saldo equilibrado
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Informações -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Carga Horária Diária</small>
                                <p class="fw-bold"><?php echo number_format($usuario['carga_horaria_diaria'], 2, ',', '.'); ?>h</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Tipo de Contrato</small>
                                <p class="fw-bold"><?php echo $usuario['tipo_contrato']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Cargo</small>
                                <p class="fw-bold"><?php echo $usuario['cargo']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Departamento</small>
                                <p class="fw-bold"><?php echo $usuario['departamento']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Tendência (placeholder) -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📊 Evolução do Mês</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted text-center py-5">
                        Gráfico será implementado na próxima versão
                    </p>
                </div>
            </div>
            
            <!-- Links -->
            <div class="text-center">
                <a href="index.php?rota=meu_ponto" class="btn btn-outline-primary me-2">📋 Voltar ao Ponto</a>
                <a href="index.php?rota=relatorio_ponto_mes" class="btn btn-outline-info">📄 Relatório da Folha</a>
            </div>
        </div>
    </div>
</div>
