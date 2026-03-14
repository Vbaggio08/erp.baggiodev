<?php
// Dados disponíveis:
// $apontamentos - array com todos os apontamentos filtrados
?>

<div class="container-fluid py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">👥 Dashboard de Pontos (RH)</h1>
            
            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="rota" value="ponto_todos">
                        
                        <div class="col-md-4">
                            <label class="form-label">Departamento</label>
                            <select name="departamento" class="form-select">
                                <option value="">Todos</option>
                                <option value="Geral" <?php echo ($_GET['departamento'] ?? '') === 'Geral' ? 'selected' : ''; ?>>Geral</option>
                                <option value="RH" <?php echo ($_GET['departamento'] ?? '') === 'RH' ? 'selected' : ''; ?>>RH</option>
                                <option value="Produção" <?php echo ($_GET['departamento'] ?? '') === 'Produção' ? 'selected' : ''; ?>>Produção</option>
                                <option value="Administrativo" <?php echo ($_GET['departamento'] ?? '') === 'Administrativo' ? 'selected' : ''; ?>>Administrativo</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Período</label>
                            <select name="periodo" class="form-select">
                                <option value="7" <?php echo ($_GET['periodo'] ?? '30') === '7' ? 'selected' : ''; ?>>Últimos 7 dias</option>
                                <option value="30" <?php echo ($_GET['periodo'] ?? '30') === '30' ? 'selected' : ''; ?>>Últimos 30 dias</option>
                                <option value="90" <?php echo ($_GET['periodo'] ?? '30') === '90' ? 'selected' : ''; ?>>Últimos 90 dias</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">🔍 Filtrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabela de Apontamentos -->
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Departamento</th>
                        <th>Entrada 1</th>
                        <th>Saída 1</th>
                        <th>Status</th>
                        <th>Horas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apontamentos as $apt): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('d/m/Y', strtotime($apt['data'])); ?></strong>
                            </td>
                            <td><?php echo $apt['nome']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $apt['departamento']; ?></span></td>
                            <td><?php echo $apt['hora_entrada_1'] ?? '---'; ?></td>
                            <td><?php echo $apt['hora_saida_1'] ?? '---'; ?></td>
                            <td>
                                <?php 
                                    $status_class = 'badge-secondary';
                                    if ($apt['status'] === 'presente') $status_class = 'badge-success';
                                    elseif ($apt['status'] === 'falta') $status_class = 'badge-danger';
                                    elseif ($apt['status'] === 'atestado') $status_class = 'badge-info';
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $apt['status']; ?></span>
                            </td>
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
                                <a href="index.php?rota=editar_ponto&id=<?php echo $apt['id']; ?>" class="btn btn-sm btn-warning" title="Editar">✏️</a>
                                <a href="index.php?rota=auditoria_apontamento&id=<?php echo $apt['id']; ?>" class="btn btn-sm btn-info" title="Auditoria">🔍</a>
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
            <div class="card bg-light text-center">
                <div class="card-body">
                    <h5>Total de Registros</h5>
                    <p class="fs-4 fw-bold"><?php echo count($apontamentos); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light text-center">
                <div class="card-body">
                    <h5>Presentes</h5>
                    <p class="fs-4 fw-bold text-success">
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
            <div class="card bg-light text-center">
                <div class="card-body">
                    <h5>Faltas</h5>
                    <p class="fs-4 fw-bold text-danger">
                        <?php 
                            echo count(array_filter($apontamentos, function($a) { 
                                return $a['status'] === 'falta'; 
                            })); 
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
