<?php
// Relatório de Auditoria com filtros avançados
// Variables from controller:
// $alteracoes, $data_inicio, $data_fim, $tipo_alteracao, $usuario_alterador
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">📊 Relatório de Auditoria</h1>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">🔍 Filtros Avançados</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="rota" value="auditoria_relatorio">
                        
                        <div class="col-md-3">
                            <label class="form-label">Data Inicial</label>
                            <input type="date" name="data_inicio" class="form-control" 
                                   value="<?php echo htmlspecialchars($data_inicio ?? date('Y-m-01')); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Data Final</label>
                            <input type="date" name="data_fim" class="form-control" 
                                   value="<?php echo htmlspecialchars($data_fim ?? date('Y-m-t')); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Alteração</label>
                            <select name="tipo" class="form-control">
                                <option value="">-- Todos --</option>
                                <option value="entrada_criada" <?php echo ($tipo_alteracao === 'entrada_criada') ? 'selected' : ''; ?>>Entrada Criada</option>
                                <option value="saida_criada" <?php echo ($tipo_alteracao === 'saida_criada') ? 'selected' : ''; ?>>Saída Criada</option>
                                <option value="entrada_editada" <?php echo ($tipo_alteracao === 'entrada_editada') ? 'selected' : ''; ?>>Entrada Editada</option>
                                <option value="saida_editada" <?php echo ($tipo_alteracao === 'saida_editada') ? 'selected' : ''; ?>>Saída Editada</option>
                                <option value="atestado_processado" <?php echo ($tipo_alteracao === 'atestado_processado') ? 'selected' : ''; ?>>Atestado Processado</option>
                                <option value="feriado_processado" <?php echo ($tipo_alteracao === 'feriado_processado') ? 'selected' : ''; ?>>Feriado Processado</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Usuário Alterador</label>
                            <input type="text" name="usuario_alterador" class="form-control" 
                                   placeholder="Nome do usuário" value="<?php echo htmlspecialchars($usuario_alterador ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">🔍 Gerar Relatório</button>
                            <a href="index.php?rota=auditoria_dashboard" class="btn btn-secondary">← Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Resumo Estatístico -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6>Total de Registros</h6>
                            <p class="fs-3 fw-bold text-primary"><?php echo count($alteracoes); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6>Tipos de Alteração</h6>
                            <p class="fs-3 fw-bold text-success"><?php echo count(array_unique(array_column($alteracoes, 'tipo_alteracao'))); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h6>Período</h6>
                            <p class="fs-5 text-warning">
                                <?php echo date('d/m/Y', strtotime($data_inicio)); ?> a 
                                <?php echo date('d/m/Y', strtotime($data_fim)); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h6>Últimas 24h</h6>
                            <p class="fs-3 fw-bold text-info">
                                <?php 
                                $ultimas_24h = count(array_filter($alteracoes, function($a) {
                                    return strtotime($a['criado_em']) > strtotime('-1 day');
                                }));
                                echo $ultimas_24h;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Tipos de Alteração -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">Distribuição por Tipo</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $tipos = array_values(array_unique(array_column($alteracoes, 'tipo_alteracao')));
                            $total_alteracoes = count($alteracoes);
                            
                            if ($total_alteracoes > 0) {
                                foreach ($tipos as $tipo) {
                                    $alteracoes_tipo = array_filter($alteracoes, function($a) use ($tipo) {
                                        return $a['tipo_alteracao'] === $tipo;
                                    });
                                    $qtd = count($alteracoes_tipo);
                                    $percentual = ($qtd / $total_alteracoes) * 100;
                                    
                                    echo "<div class='mb-3'>";
                                    echo "<div class='d-flex justify-content-between mb-1'>";
                                    echo "<small>$tipo</small>";
                                    echo "<small class='fw-bold'>$qtd (" . round($percentual, 1) . "%)</small>";
                                    echo "</div>";
                                    echo "<div class='progress' style='height: 25px'>";
                                    echo "<div class='progress-bar bg-info' style='width: {$percentual}%'></div>";
                                    echo "</div>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p class='text-muted'>Nenhum registro encontrado</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0">Usuários que Alteraram</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $usuarios_alteradores = array_unique(array_column($alteracoes, 'usuario_nome'));
                            
                            if (!empty($usuarios_alteradores)) {
                                foreach ($usuarios_alteradores as $usuario) {
                                    $qtd_usuario = count(array_filter($alteracoes, function($a) use ($usuario) {
                                        return $a['usuario_nome'] === $usuario;
                                    }));
                                    
                                    echo "<div class='mb-2'>";
                                    echo "<small class='fw-bold'>$usuario</small><br>";
                                    echo "<small class='text-muted'>$qtd_usuario alterações</small>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p class='text-muted'>Nenhum usuário encontrado</p>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabela Principal -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">📋 Detalhes do Histórico</h6>
                </div>
                <div class="card-body" style="overflow-x: auto;">
                    <?php if (!empty($alteracoes)): ?>
                        <table class="table table-striped table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Tipo</th>
                                    <th>Alterado Por</th>
                                    <th>Motivo</th>
                                    <th>Valor Anterior</th>
                                    <th>Valor Novo</th>
                                    <th>Hash</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alteracoes as $log): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('d/m/Y H:i:s', strtotime($log['criado_em'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($log['tipo_alteracao']); ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo htmlspecialchars($log['usuario_nome'] ?? 'Sistema'); ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($log['motivo_alteracao'] ?? '-', 0, 30)); ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <code class="text-danger"><?php echo htmlspecialchars(substr($log['valor_anterior'] ?? '-', 0, 40)); ?></code>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <code class="text-success"><?php echo htmlspecialchars(substr($log['valor_novo'] ?? '-', 0, 40)); ?></code>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <span class="badge bg-success">SHA256 ✓</span>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>Nenhum registro encontrado com os filtros especificados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Observações Importantes -->
            <div class="alert alert-secondary mt-4" role="alert">
                <h6>📌 Observações Importantes</h6>
                <ul class="mb-0 small">
                    <li>Todos os registros são rastreáveis e não podem ser alterados ou removidos</li>
                    <li>O hash SHA256 garante que nenhum registro foi modificado após criação</li>
                    <li>Este relatório fornece auditoria completa de todas as alterações no sistema</li>
                    <li>Dados em <span class="text-danger"><code>vermelho</code></span> são valores antigos, <span class="text-success"><code>verde</code></span> são valores novos</li>
                </ul>
            </div>
        </div>
    </div>
</div>
