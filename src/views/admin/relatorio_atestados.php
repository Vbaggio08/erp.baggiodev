<?php
// Relatório de atestados para RH
?>

<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">📊 Relatório de Atestados (RH)</h1>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="rota" value="relatorio_atestados">
                
                <div class="col-md-3">
                    <label class="form-label">Mês</label>
                    <select name="mes" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" 
                                    <?php echo ($_GET['mes'] ?? date('m')) === str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                <?php echo strftime('%B', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Ano</label>
                    <input type="number" name="ano" class="form-control" value="<?php echo $_GET['ano'] ?? date('Y'); ?>" min="2020">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendente" <?php echo ($_GET['status'] ?? '') === 'pendente' ? 'selected' : ''; ?>>Pendentes</option>
                        <option value="aprovado" <?php echo ($_GET['status'] ?? '') === 'aprovado' ? 'selected' : ''; ?>>Aprovados</option>
                        <option value="rejeitado" <?php echo ($_GET['status'] ?? '') === 'rejeitado' ? 'selected' : ''; ?>>Rejeitados</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Departamento</label>
                    <select name="departamento" class="form-select">
                        <option value="">Todos</option>
                        <option value="Produção" <?php echo ($_GET['departamento'] ?? '') === 'Produção' ? 'selected' : ''; ?>>Produção</option>
                        <option value="RH" <?php echo ($_GET['departamento'] ?? '') === 'RH' ? 'selected' : ''; ?>>RH</option>
                        <option value="Administrativo" <?php echo ($_GET['departamento'] ?? '') === 'Administrativo' ? 'selected' : ''; ?>>Administrativo</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">🔍 Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Resumo Estatístico -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Total de Atestados</h6>
                    <p class="fs-4 fw-bold"><?php echo count($atestados); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Dias Abonados</h6>
                    <p class="fs-4 fw-bold">
                        <?php 
                            $dias_total = 0;
                            foreach ($atestados as $a) {
                                if ($a['status'] === 'aprovado') {
                                    $inicio = new DateTime($a['data_inicio']);
                                    $fim = new DateTime($a['data_fim']);
                                    $dias_total += $fim->diff($inicio)->days + 1;
                                }
                            }
                            echo $dias_total;
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Aprovados</h6>
                    <p class="fs-4 fw-bold text-success">
                        <?php 
                            echo count(array_filter($atestados, function($a) { 
                                return $a['status'] === 'aprovado'; 
                            })); 
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6>Rejeitados</h6>
                    <p class="fs-4 fw-bold text-danger">
                        <?php 
                            echo count(array_filter($atestados, function($a) { 
                                return $a['status'] === 'rejeitado'; 
                            })); 
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Atestados -->
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Funcionário</th>
                        <th>Departamento</th>
                        <th>Tipo</th>
                        <th>Início</th>
                        <th>Término</th>
                        <th>Dias</th>
                        <th>Status</th>
                        <th>Data Proc.</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($atestados as $atestado): ?>
                        <tr>
                            <td><?php echo $atestado['nome']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $atestado['departamento']; ?></span></td>
                            <td><?php echo $atestado['tipo_afastamento']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($atestado['data_inicio'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($atestado['data_fim'])); ?></td>
                            <td>
                                <?php 
                                    $inicio = new DateTime($atestado['data_inicio']);
                                    $fim = new DateTime($atestado['data_fim']);
                                    echo $fim->diff($inicio)->days + 1;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $badge_class = $atestado['status'] === 'pendente' ? 'badge-warning' : 
                                                   ($atestado['status'] === 'aprovado' ? 'badge-success' : 'badge-danger');
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo strtoupper($atestado['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($atestado['data_processamento'])); ?></td>
                            <td>
                                <a href="assets/uploads/atestados/<?php echo $atestado['arquivo_comprovante']; ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-info"
                                   title="Visualizar arquivo">
                                    👁️
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Análise por Tipo de Afastamento -->
    <div class="card shadow mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">📈 Análise por Tipo de Afastamento</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo de Afastamento</th>
                            <th>Quantidade</th>
                            <th>Dias Totais</th>
                            <th>% do Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $tipos = array_values(array_unique(array_column($atestados, 'tipo_afastamento')));
                            $total_atestados = count($atestados);
                            
                            foreach ($tipos as $tipo):
                                $atestados_tipo = array_filter($atestados, function($a) use ($tipo) {
                                    return $a['tipo_afastamento'] === $tipo;
                                });
                                $qtd = count($atestados_tipo);
                                $dias = 0;
                                foreach ($atestados_tipo as $a) {
                                    $inicio = new DateTime($a['data_inicio']);
                                    $fim = new DateTime($a['data_fim']);
                                    $dias += $fim->diff($inicio)->days + 1;
                                }
                                $percentual = ($qtd / $total_atestados * 100);
                        ?>
                        <tr>
                            <td><?php echo ucfirst(str_replace('_', ' ', $tipo)); ?></td>
                            <td><?php echo $qtd; ?></td>
                            <td><?php echo $dias; ?></td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $percentual; ?>%">
                                        <?php echo round($percentual, 1); ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Exportar -->
    <div class="mt-4 text-center">
        <form method="POST" style="display:inline;">
            <button type="submit" class="btn btn-success">
                📥 Exportar para Excel
            </button>
        </form>
    </div>
</div>
