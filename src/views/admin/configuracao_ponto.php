<?php
/**
 * View: Configuração de Ponto Avançado
 * Path: src/views/admin/configuracao_ponto.php
 * 
 * Interface para administradores configurarem:
 * - Permissão e limites de horas extras
 * - Cálculo de DSR (Descanso Semanal Remunerado)
 * - Tolerâncias de entrada/saída
 * - Configurações de feriados
 * 
 * Requer: $_SESSION['user_nivel'] === 'admin' ou 'rh'
 */

// Garantir autenticação e autorização
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_nivel'] ?? '', ['admin', 'rh'])) {
    header('Location: /login');
    exit;
}

// Em produção, estes dados viriam do controlador via $_POST ou API
$configuracao = [
    'permite_horas_extras' => true,
    'limite_horas_extras_diarias' => 2.0,
    'limite_horas_extras_mensais' => 20.0,
    'percentual_hora_extra_50' => 50.0,
    'percentual_hora_extra_100' => 100.0,
    'calcula_dsr' => true,
    'dsr_dias_compensacao' => 1,
    'desconta_feriado_nao_trabalhado' => false,
    'aplicar_dsr_compensado_feriado' => true,
    'tolerancia_entrada_minutos' => 5,
    'tolerancia_saida_minutos' => 5,
    'considerar_lunch_automatico' => false,
    'duracao_lunch_minutos' => 60,
    'regra_incompleto_fim_dia' => true,
    'batidas_padrao_dia' => 4,
    'dias_ativos' => ['seg' => true, 'ter' => true, 'qua' => true, 'qui' => true, 'sex' => true, 'sab' => false, 'dom' => false],
    'batidas_por_dia' => ['seg' => 4, 'ter' => 4, 'qua' => 4, 'qui' => 4, 'sex' => 4, 'sab' => 2, 'dom' => 0]
];

$mensagem = '';
$tipo_alerta = ''; // 'sucesso', 'erro', 'aviso'

$dias = [
    'seg' => 'Segunda-feira',
    'ter' => 'Terça-feira',
    'qua' => 'Quarta-feira',
    'qui' => 'Quinta-feira',
    'sex' => 'Sexta-feira',
    'sab' => 'Sábado',
    'dom' => 'Domingo'
];
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="fas fa-cog"></i> Configuração de Ponto - FASE 3
            </h2>
            <small class="text-muted">Gerenciar parâmetros avançados do sistema de ponto</small>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-primary" onclick="salvarConfiguracao()">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </div>

    <?php if (!empty($mensagem)): ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <strong><?php echo ucfirst($tipo_alerta); ?>!</strong> <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Seção 1: Horas Extras -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle"></i> Horas Extras
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Permitir Horas Extras -->
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="permite_horas_extras"
                                   <?php echo $configuracao['permite_horas_extras'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="permite_horas_extras">
                                Permitir Horas Extras
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Se desativado, usuários não poderão registrar horas extras
                        </small>
                    </div>

                    <!-- Limite Diário -->
                    <div class="form-group mt-3">
                        <label for="limite_diario">
                            Limite Diário (horas)
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Máximo de horas extras permitidas por dia"></i>
                        </label>
                        <input type="number" class="form-control" id="limite_diario"
                               value="<?php echo $configuracao['limite_horas_extras_diarias']; ?>"
                               min="0" max="12" step="0.5">
                    </div>

                    <!-- Limite Mensal -->
                    <div class="form-group">
                        <label for="limite_mensal">
                            Limite Mensal (horas)
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Máximo de horas extras permitidas por mês"></i>
                        </label>
                        <input type="number" class="form-control" id="limite_mensal"
                               value="<?php echo $configuracao['limite_horas_extras_mensais']; ?>"
                               min="0" max="100" step="0.5">
                    </div>

                    <!-- Percentual 50% -->
                    <div class="form-group">
                        <label for="perc_50">
                            Percentual Hora Extra 50%
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Adicional de 50% quando extra até 2 horas/dia"></i>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="perc_50"
                                   value="<?php echo $configuracao['percentual_hora_extra_50']; ?>"
                                   min="0" max="100" step="5">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <!-- Percentual 100% -->
                    <div class="form-group">
                        <label for="perc_100">
                            Percentual Hora Extra 100%
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Adicional de 100% quando extra acima de 2 horas/dia ou noturna"></i>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="perc_100"
                                   value="<?php echo $configuracao['percentual_hora_extra_100']; ?>"
                                   min="0" max="200" step="5">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 2: DSR e Feriados -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> DSR e Feriados
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Calcular DSR -->
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="calcula_dsr"
                                   <?php echo $configuracao['calcula_dsr'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="calcula_dsr">
                                Calcular DSR (Lei 605/49)
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Descanso Semanal Remunerado: compensação por trabalhar aos domingos
                        </small>
                    </div>

                    <!-- Dias de Compensação DSR -->
                    <div class="form-group mt-3">
                        <label for="dsr_dias">
                            Dias de Compensação DSR
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Quantos dias de descanso/folga o DSR vai gerar"></i>
                        </label>
                        <input type="number" class="form-control" id="dsr_dias"
                               value="<?php echo $configuracao['dsr_dias_compensacao']; ?>"
                               min="1" max="5" step="1">
                    </div>

                    <!-- Desconta Feriado Não Trabalhado -->
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="desconta_feriado"
                                   <?php echo $configuracao['desconta_feriado_nao_trabalhado'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="desconta_feriado">
                                Descontar Feriado Não Trabalhado
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Se ativado, feriados sem apontamento causam desconto
                        </small>
                    </div>

                    <!-- Aplicar DSR em Feriado Compensado -->
                    <div class="form-group mt-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="dsr_compensado"
                                   <?php echo $configuracao['aplicar_dsr_compensado_feriado'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="dsr_compensado">
                                Aplicar DSR quando Feriado for Compensado
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Se ativado, gera DSR mesmo quando feriado é compensado em outro dia
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda Linha: Tolerâncias e Outros -->
    <div class="row">
        <!-- Seção 3: Tolerâncias -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Tolerâncias
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Tolerância Entrada -->
                    <div class="form-group">
                        <label for="toler_entrada">
                            Tolerância Entrada (minutos)
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Minutos de atraso tolerados para entrada"></i>
                        </label>
                        <input type="number" class="form-control" id="toler_entrada"
                               value="<?php echo $configuracao['tolerancia_entrada_minutos']; ?>"
                               min="0" max="30" step="1">
                    </div>

                    <!-- Tolerância Saída -->
                    <div class="form-group">
                        <label for="toler_saida">
                            Tolerância Saída (minutos)
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Minutos de antecipação tolerados na saída"></i>
                        </label>
                        <input type="number" class="form-control" id="toler_saida"
                               value="<?php echo $configuracao['tolerancia_saida_minutos']; ?>"
                               min="0" max="30" step="1">
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção 4: Almoço -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-utensils"></i> Almoço
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Considerar Almoço Automático -->
                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="lunch_auto"
                                   <?php echo $configuracao['considerar_lunch_automatico'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="lunch_auto">
                                Considerar Almoço Automático
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Se ativado, desconta automaticamente duração do almoço dos apontamentos
                        </small>
                    </div>

                    <!-- Duração Almoço -->
                    <div class="form-group mt-3">
                        <label for="duracao_lunch">
                            Duração do Almoço (minutos)
                                     <i class="fas fa-info-circle" data-bs-toggle="tooltip" 
                               title="Tempo padrão de almoço a descontar"></i>
                        </label>
                        <input type="number" class="form-control" id="duracao_lunch"
                               value="<?php echo $configuracao['duracao_lunch_minutos']; ?>"
                               min="30" max="180" step="15">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terceira Linha: Configuração Individual por Usuário -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-cog"></i> Configuração Individual de Usuário</h5>
                    <button class="btn btn-light btn-sm" type="button" onclick="salvarConfiguracaoUsuario()">
                        <i class="fas fa-save"></i> Salvar Usuário
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="usuario_config_id" class="form-label">Usuário</label>
                            <select class="form-select" id="usuario_config_id" onchange="carregarConfiguracaoUsuarioSelecionado()">
                                <option value="">-- Selecione um usuário --</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="usr_batidas_padrao_dia" class="form-label">Batidas padrão/dia</label>
                            <select class="form-select" id="usr_batidas_padrao_dia">
                                <option value="2">2 batidas</option>
                                <option value="4">4 batidas</option>
                                <option value="6">6 batidas</option>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="usr_permite_horas_extras" checked>
                                <label class="form-check-label" for="usr_permite_horas_extras">Permitir horas extras para este usuário</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="usr_horario_entrada_1">Entrada 1</label>
                            <input type="time" class="form-control" id="usr_horario_entrada_1" value="08:00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="usr_horario_saida_1">Saída 1</label>
                            <input type="time" class="form-control" id="usr_horario_saida_1" value="12:00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="usr_horario_entrada_2">Entrada 2</label>
                            <input type="time" class="form-control" id="usr_horario_entrada_2" value="13:00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="usr_horario_saida_2">Saída 2</label>
                            <input type="time" class="form-control" id="usr_horario_saida_2" value="18:00">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Dia</th>
                                    <th>Ativo</th>
                                    <th>Batidas esperadas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dias as $chave => $nome): ?>
                                <tr>
                                    <td><?php echo $nome; ?></td>
                                    <td>
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" class="form-check-input usr-dia-ativo" data-dia="<?php echo $chave; ?>" id="usr_dia_<?php echo $chave; ?>" checked>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm usr-batidas-dia" data-dia="<?php echo $chave; ?>" id="usr_batidas_<?php echo $chave; ?>">
                                            <option value="0">0</option>
                                            <option value="2">2</option>
                                            <option value="4">4</option>
                                            <option value="6">6</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quarta Linha: Escalas e Batidas Globais -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-desktop"></i> Máquina Global para Bater Ponto por CPF</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm" type="button" onclick="autorizarMaquinaGlobalAtual()">
                            <i class="fas fa-shield-alt"></i> Autorizar Esta Máquina
                        </button>
                        <button class="btn btn-outline-light btn-sm" type="button" onclick="revogarMaquinaGlobal()">
                            <i class="fas fa-ban"></i> Revogar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-2">Somente esta máquina poderá bater ponto de todos os usuários via CPF na tela de login.</p>
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="nome_maquina_global" class="form-label">Nome da máquina autorizada</label>
                            <input type="text" class="form-control" id="nome_maquina_global" placeholder="Ex.: Recepção, RH, PCP">
                        </div>
                    </div>
                    <div id="status_maquina_global" class="alert alert-secondary mb-0">Carregando status da máquina autorizada...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-week"></i> Escalas e Batidas</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="batidas_padrao_dia" class="form-label">Batidas padrão por dia</label>
                            <select class="form-select" id="batidas_padrao_dia">
                                <option value="2" <?php echo ((int)$configuracao['batidas_padrao_dia'] === 2) ? 'selected' : ''; ?>>2 batidas (entrada/saída)</option>
                                <option value="4" <?php echo ((int)$configuracao['batidas_padrao_dia'] === 4) ? 'selected' : ''; ?>>4 batidas (entrada/saída + entrada/saída)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="regra_incompleto_fim_dia" <?php echo !empty($configuracao['regra_incompleto_fim_dia']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="regra_incompleto_fim_dia">Marcar como incompleto após 23:59:59</label>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Dia</th>
                                    <th>Ativo</th>
                                    <th>Batidas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dias as $chave => $nome): ?>
                                <tr>
                                    <td><?php echo $nome; ?></td>
                                    <td>
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" class="form-check-input dia-ativo" data-dia="<?php echo $chave; ?>" id="dia_<?php echo $chave; ?>" <?php echo !empty($configuracao['dias_ativos'][$chave]) ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm batidas-dia" data-dia="<?php echo $chave; ?>" id="batidas_<?php echo $chave; ?>">
                                            <option value="0" <?php echo ((int)$configuracao['batidas_por_dia'][$chave] === 0) ? 'selected' : ''; ?>>0</option>
                                            <option value="2" <?php echo ((int)$configuracao['batidas_por_dia'][$chave] === 2) ? 'selected' : ''; ?>>2</option>
                                            <option value="4" <?php echo ((int)$configuracao['batidas_por_dia'][$chave] === 4) ? 'selected' : ''; ?>>4</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botões de Ação -->
    <div class="row mt-4">
        <div class="col-md-12">
            <button class="btn btn-primary btn-lg btn-block" onclick="salvarConfiguracao()">
                <i class="fas fa-save"></i> Salvar Todas as Configurações
            </button>
            <hr>
            <h6 class="mt-4 text-muted">Ações Complementares:</h6>
            <div class="btn-group btn-group-sm" role="group">
                <button class="btn btn-outline-secondary" onclick="restaurarPadrao()">
                    <i class="fas fa-undo"></i> Restaurar Padrão
                </button>
                <button class="btn btn-outline-info" onclick="visualizarFeriados()">
                    <i class="fas fa-calendar"></i> Gerenciar Feriados
                </button>
                <button class="btn btn-outline-success" onclick="testarCalculos()">
                    <i class="fas fa-calculator"></i> Testar Cálculos
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Feriados -->
<div class="modal fade" id="modalFeriados" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gerenciar Feriados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Adicionar Feriado</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="data_feriado">
                        <input type="text" class="form-control" placeholder="Descrição" id="descr_feriado">
                        <button class="btn btn-outline-primary" type="button" onclick="adicionarFeriado()">
                            <i class="fas fa-plus"></i> Adicionar
                        </button>
                    </div>
                </div>
                <div id="lista_feriados" class="mt-3">
                    <p class="text-muted">Carregando feriados...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Teste de Cálculos -->
<div class="modal fade" id="modalTeste" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Testar Cálculos de Ponto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="teste_usuario">Selecione um Usuário</label>
                    <select class="form-control" id="teste_usuario">
                        <option value="">-- Carregando usuários --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="teste_mes">Mês/Ano</label>
                    <input type="month" class="form-control" id="teste_mes">
                </div>
                <div id="resultado_teste" class="alert alert-light" style="display:none;">
                    <pre id="teste_output"></pre>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="executarTeste()">
                    <i class="fas fa-play"></i> Executar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const ROTAS_PONTO = {
    config: 'index.php?rota=configuracao_ponto_json',
    salvarConfig: 'index.php?rota=salvar_configuracao_ponto',
    configUsuario: 'index.php?rota=configuracao_ponto_usuario_json',
    salvarConfigUsuario: 'index.php?rota=salvar_configuracao_ponto_usuario',
    autorizarMaquinaGlobal: 'index.php?rota=autorizar_maquina_global_ponto',
    statusMaquinaGlobal: 'index.php?rota=status_maquina_global_ponto',
    revogarMaquinaGlobal: 'index.php?rota=revogar_maquina_global_ponto',
    resetarConfig: 'index.php?rota=resetar_configuracao_ponto',
    listarFeriados: 'index.php?rota=listar_feriados_ponto',
    adicionarFeriado: 'index.php?rota=adicionar_feriado_ponto',
    removerFeriado: 'index.php?rota=remover_feriado_ponto',
    usuariosTeste: 'index.php?rota=usuarios_teste_json',
    calcularSaldoMensal: 'index.php?rota=calcular_saldo_mensal'
};

let DEVICE_ID_ATUAL = '';

function abrirModal(id) {
    const el = document.getElementById(id);
    if (!el || !window.bootstrap || !window.bootstrap.Modal) {
        return;
    }
    const modal = window.bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
}

// Ativar tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        if (window.bootstrap && window.bootstrap.Tooltip) {
            new window.bootstrap.Tooltip(el);
        }
    });

    carregarConfiguracaoAtual();
    carregarUsuariosConfiguracao();
    DEVICE_ID_ATUAL = gerarDeviceIdAtual();
    carregarStatusMaquinaGlobal();
});

function gerarDeviceIdAtual() {
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText(navigator.userAgent, 2, 2);
        return canvas.toDataURL().slice(-32);
    } catch (e) {
        return 'fallback-' + navigator.userAgent.slice(0, 20);
    }
}

function carregarStatusMaquinaGlobal() {
    $.ajax({
        url: ROTAS_PONTO.statusMaquinaGlobal,
        type: 'GET',
        success: function(response) {
            const box = $('#status_maquina_global');
            if (!response || !response.sucesso || !response.dados) {
                box.removeClass('alert-secondary alert-success').addClass('alert-warning');
                box.text('Não foi possível consultar máquina autorizada.');
                return;
            }

            const d = response.dados;
            if (!d.device_id) {
                box.removeClass('alert-secondary alert-success').addClass('alert-warning');
                box.text('Nenhuma máquina global autorizada no momento.');
                $('#nome_maquina_global').val('');
                return;
            }

            const fim = d.device_id.slice(-8);
            const nome = (d.nome_maquina || '').trim();
            const atualizado = d.atualizado_em ? (' | Atualizado em: ' + d.atualizado_em) : '';
            box.removeClass('alert-secondary alert-warning').addClass('alert-success');
            box.text('Máquina autorizada' + (nome ? ' [' + nome + ']' : '') + ' (ID final: ' + fim + ')' + atualizado);
            $('#nome_maquina_global').val(nome);
        },
        error: function() {
            const box = $('#status_maquina_global');
            box.removeClass('alert-secondary alert-success').addClass('alert-warning');
            box.text('Falha ao consultar máquina autorizada.');
        }
    });
}

function autorizarMaquinaGlobalAtual() {
    if (!DEVICE_ID_ATUAL) {
        Swal.fire('Erro', 'Não foi possível identificar este dispositivo', 'error');
        return;
    }

    const nomeMaquina = ($('#nome_maquina_global').val() || '').trim();

    $.ajax({
        url: ROTAS_PONTO.autorizarMaquinaGlobal,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ device_id: DEVICE_ID_ATUAL, nome_maquina: nomeMaquina }),
        success: function(response) {
            if (response && response.sucesso) {
                Swal.fire('Sucesso', 'Máquina global autorizada com sucesso', 'success');
                carregarStatusMaquinaGlobal();
                return;
            }
            Swal.fire('Erro', (response && response.erro) || 'Não foi possível autorizar a máquina', 'error');
        },
        error: function() {
            Swal.fire('Erro', 'Não foi possível autorizar a máquina', 'error');
        }
    });
}

function revogarMaquinaGlobal() {
    Swal.fire({
        title: 'Revogar máquina autorizada?',
        text: 'Após revogar, nenhuma máquina poderá bater ponto até nova autorização.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, revogar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: ROTAS_PONTO.revogarMaquinaGlobal,
            type: 'POST',
            success: function(response) {
                if (response && response.sucesso) {
                    Swal.fire('Revogada', 'A máquina global foi revogada com sucesso.', 'success');
                    carregarStatusMaquinaGlobal();
                    return;
                }
                Swal.fire('Erro', (response && response.erro) || 'Não foi possível revogar a máquina', 'error');
            },
            error: function() {
                Swal.fire('Erro', 'Não foi possível revogar a máquina', 'error');
            }
        });
    });
}

function estadoDiaPadraoUsuario() {
    return {
        dias_ativos: { seg: true, ter: true, qua: true, qui: true, sex: true, sab: false, dom: false },
        batidas_por_dia: { seg: 4, ter: 4, qua: 4, qui: 4, sex: 4, sab: 0, dom: 0 }
    };
}

function carregarUsuariosConfiguracao() {
    $.ajax({
        url: ROTAS_PONTO.usuariosTeste,
        type: 'GET',
        success: function(response) {
            let html = '<option value="">-- Selecione um usuário --</option>';
            const lista = (response && Array.isArray(response.data)) ? response.data : [];
            lista.forEach(function(u) {
                html += `<option value="${u.id}">${u.nome}</option>`;
            });
            $('#usuario_config_id').html(html);

            if (lista.length > 0) {
                $('#usuario_config_id').val(lista[0].id);
                carregarConfiguracaoUsuarioSelecionado();
            }
        },
        error: function() {
            Swal.fire('Erro', 'Não foi possível carregar usuários para configuração individual', 'error');
        }
    });
}

function carregarConfiguracaoUsuarioSelecionado() {
    const usuarioId = parseInt($('#usuario_config_id').val(), 10);
    if (!usuarioId) {
        return;
    }

    $.ajax({
        url: `${ROTAS_PONTO.configUsuario}&usuario_id=${usuarioId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response || !response.sucesso || !response.configuracao) {
                return;
            }
            popularFormularioUsuario(response.configuracao);
        },
        error: function() {
            Swal.fire('Erro', 'Não foi possível carregar configuração do usuário', 'error');
        }
    });
}

function popularFormularioUsuario(cfg) {
    const padrao = estadoDiaPadraoUsuario();

    $('#usr_permite_horas_extras').prop('checked', !!cfg.permite_horas_extras);
    $('#usr_batidas_padrao_dia').val(parseInt(cfg.batidas_padrao_dia ?? 4, 10));

    $('#usr_horario_entrada_1').val((cfg.horario_entrada_1 || '08:00').slice(0, 5));
    $('#usr_horario_saida_1').val((cfg.horario_saida_1 || '12:00').slice(0, 5));
    $('#usr_horario_entrada_2').val((cfg.horario_entrada_2 || '13:00').slice(0, 5));
    $('#usr_horario_saida_2').val((cfg.horario_saida_2 || '18:00').slice(0, 5));

    const diasAtivos = (cfg.dias_ativos && typeof cfg.dias_ativos === 'object') ? cfg.dias_ativos : padrao.dias_ativos;
    Object.keys(padrao.dias_ativos).forEach(function(dia) {
        $('#usr_dia_' + dia).prop('checked', !!diasAtivos[dia]);
    });

    const batidasPorDia = (cfg.batidas_por_dia && typeof cfg.batidas_por_dia === 'object') ? cfg.batidas_por_dia : padrao.batidas_por_dia;
    Object.keys(padrao.batidas_por_dia).forEach(function(dia) {
        const valor = parseInt((batidasPorDia[dia] ?? padrao.batidas_por_dia[dia]), 10);
        $('#usr_batidas_' + dia).val(valor);
    });
}

function salvarConfiguracaoUsuario() {
    const usuarioId = parseInt($('#usuario_config_id').val(), 10);
    if (!usuarioId) {
        Swal.fire('Aviso', 'Selecione um usuário para salvar', 'warning');
        return;
    }

    const dados = {
        usuario_id: usuarioId,
        permite_horas_extras: $('#usr_permite_horas_extras').is(':checked'),
        batidas_padrao_dia: parseInt($('#usr_batidas_padrao_dia').val(), 10),
        horario_entrada_1: ($('#usr_horario_entrada_1').val() || '08:00'),
        horario_saida_1: ($('#usr_horario_saida_1').val() || '12:00'),
        horario_entrada_2: ($('#usr_horario_entrada_2').val() || '13:00'),
        horario_saida_2: ($('#usr_horario_saida_2').val() || '18:00'),
        dias_ativos: {},
        batidas_por_dia: {}
    };

    $('.usr-dia-ativo').each(function() {
        dados.dias_ativos[$(this).data('dia')] = $(this).is(':checked');
    });

    $('.usr-batidas-dia').each(function() {
        dados.batidas_por_dia[$(this).data('dia')] = parseInt($(this).val(), 10);
    });

    $.ajax({
        url: ROTAS_PONTO.salvarConfigUsuario,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(dados),
        success: function(response) {
            if (response && response.sucesso) {
                Swal.fire('Sucesso!', 'Configuração individual salva com sucesso', 'success');
                return;
            }
            Swal.fire('Erro!', (response && response.erro) || 'Não foi possível salvar configuração individual', 'error');
        },
        error: function() {
            Swal.fire('Erro!', 'Não foi possível salvar configuração individual', 'error');
        }
    });
}

function carregarConfiguracaoAtual() {
    $.ajax({
        url: ROTAS_PONTO.config,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (!response || !response.sucesso || !response.configuracao) {
                return;
            }
            popularFormularioConfiguracao(response.configuracao);
        }
    });
}

function popularFormularioConfiguracao(cfg) {
    $('#permite_horas_extras').prop('checked', !!cfg.permite_horas_extras);
    $('#limite_diario').val(cfg.limite_horas_extras_diarias ?? 2);
    $('#limite_mensal').val(cfg.limite_horas_extras_mensais ?? 20);
    $('#perc_50').val(cfg.percentual_hora_extra_50 ?? 50);
    $('#perc_100').val(cfg.percentual_hora_extra_100 ?? 100);
    $('#calcula_dsr').prop('checked', !!cfg.calcula_dsr);
    $('#dsr_dias').val(cfg.dsr_dias_compensacao ?? 1);
    $('#desconta_feriado').prop('checked', !!cfg.desconta_feriado_nao_trabalhado);
    $('#dsr_compensado').prop('checked', !!cfg.aplicar_dsr_compensado_feriado);
    $('#toler_entrada').val(cfg.tolerancia_entrada_minutos ?? 5);
    $('#toler_saida').val(cfg.tolerancia_saida_minutos ?? 5);
    $('#lunch_auto').prop('checked', !!cfg.considerar_lunch_automatico);
    $('#duracao_lunch').val(cfg.duracao_lunch_minutos ?? 60);

    $('#regra_incompleto_fim_dia').prop('checked', !!cfg.regra_incompleto_fim_dia);
    $('#batidas_padrao_dia').val(parseInt(cfg.batidas_padrao_dia ?? 4, 10));

    if (cfg.dias_ativos && typeof cfg.dias_ativos === 'object') {
        Object.keys(cfg.dias_ativos).forEach(function(dia) {
            $('#dia_' + dia).prop('checked', !!cfg.dias_ativos[dia]);
        });
    }

    if (cfg.batidas_por_dia && typeof cfg.batidas_por_dia === 'object') {
        Object.keys(cfg.batidas_por_dia).forEach(function(dia) {
            $('#batidas_' + dia).val(parseInt(cfg.batidas_por_dia[dia], 10));
        });
    }
}

/**
 * Salvar configuração de ponto
 */
function salvarConfiguracao() {
    const dados = {
        permite_horas_extras: $('#permite_horas_extras').is(':checked'),
        limite_horas_extras_diarias: parseFloat($('#limite_diario').val()),
        limite_horas_extras_mensais: parseFloat($('#limite_mensal').val()),
        percentual_hora_extra_50: parseFloat($('#perc_50').val()),
        percentual_hora_extra_100: parseFloat($('#perc_100').val()),
        calcula_dsr: $('#calcula_dsr').is(':checked'),
        dsr_dias_compensacao: parseInt($('#dsr_dias').val()),
        desconta_feriado_nao_trabalhado: $('#desconta_feriado').is(':checked'),
        aplicar_dsr_compensado_feriado: $('#dsr_compensado').is(':checked'),
        tolerancia_entrada_minutos: parseInt($('#toler_entrada').val()),
        tolerancia_saida_minutos: parseInt($('#toler_saida').val()),
        considerar_lunch_automatico: $('#lunch_auto').is(':checked'),
        duracao_lunch_minutos: parseInt($('#duracao_lunch').val()),
        regra_incompleto_fim_dia: $('#regra_incompleto_fim_dia').is(':checked'),
        batidas_padrao_dia: parseInt($('#batidas_padrao_dia').val()),
        dias_ativos: {},
        batidas_por_dia: {}
    };

    $('.dia-ativo').each(function() {
        dados.dias_ativos[$(this).data('dia')] = $(this).is(':checked');
    });
    $('.batidas-dia').each(function() {
        dados.batidas_por_dia[$(this).data('dia')] = parseInt($(this).val());
    });

    $.ajax({
        url: ROTAS_PONTO.salvarConfig,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(dados),
        success: function(response) {
            if (response && response.sucesso) {
                Swal.fire('Sucesso!', 'Configurações salvas com sucesso', 'success');
                return;
            }
            Swal.fire('Erro!', response.erro || 'Não foi possível salvar configurações', 'error');
        },
        error: function() {
            Swal.fire('Erro!', 'Não foi possível salvar configurações', 'error');
        }
    });
}

/**
 * Restaurar valores padrão
 */
function restaurarPadrao() {
    Swal.fire({
        title: 'Restaurar Padrão?',
        text: 'Todas as configurações serão resetadas para os valores padrão',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, restaurar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: ROTAS_PONTO.resetarConfig,
                type: 'POST',
                success: function() {
                    window.location.reload();
                }
            });
        }
    });
}

/**
 * Abrir modal de feriados
 */
function visualizarFeriados() {
    abrirModal('modalFeriados');
    carregarFeriados();
}

/**
 * Carregar e exibir feriados
 */
function carregarFeriados() {
    $.ajax({
        url: ROTAS_PONTO.listarFeriados,
        type: 'GET',
        success: function(response) {
            if (!response || !response.sucesso || !Array.isArray(response.dados)) {
                $('#lista_feriados').html('<p class="text-muted">Nenhum feriado encontrado.</p>');
                return;
            }

            let html = '<table class="table table-sm">';
            response.dados.forEach(f => {
                html += `<tr>
                    <td>${new Date(f.data).toLocaleDateString('pt-BR')}</td>
                    <td>${f.descricao || '-'}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="removerFeriado(${f.id})">
                        <i class="fas fa-trash"></i>
                    </button></td>
                </tr>`;
            });
            html += '</table>';
            $('#lista_feriados').html(html);
        }
    });
}

function adicionarFeriado() {
    const data = ($('#data_feriado').val() || '').trim();
    const descricao = ($('#descr_feriado').val() || '').trim();

    if (!data) {
        Swal.fire('Aviso', 'Informe a data do feriado', 'warning');
        return;
    }

    $.ajax({
        url: ROTAS_PONTO.adicionarFeriado,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            data: data,
            descricao: descricao || 'Feriado',
            tipo: 'personalizado'
        }),
        success: function(response) {
            if (response && response.sucesso) {
                $('#data_feriado').val('');
                $('#descr_feriado').val('');
                carregarFeriados();
                return;
            }
            Swal.fire('Erro', (response && response.erro) || 'Não foi possível adicionar', 'error');
        },
        error: function() {
            Swal.fire('Erro', 'Não foi possível adicionar', 'error');
        }
    });
}

function removerFeriado(id) {
    $.ajax({
        url: ROTAS_PONTO.removerFeriado,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ id: id }),
        success: function(response) {
            if (response && response.sucesso) {
                carregarFeriados();
                return;
            }
            Swal.fire('Erro', (response && response.erro) || 'Não foi possível remover', 'error');
        },
        error: function() {
            Swal.fire('Erro', 'Não foi possível remover', 'error');
        }
    });
}

/**
 * Abrir modal de testes
 */
function testarCalculos() {
    abrirModal('modalTeste');
    carregarUsuariosTeste();
    document.getElementById('teste_mes').valueAsDate = new Date();
}

/**
 * Carregar usuários para teste
 */
function carregarUsuariosTeste() {
    $.ajax({
        url: ROTAS_PONTO.usuariosTeste,
        success: function(response) {
            let html = '<option value="">-- Selecione --</option>';
            const lista = (response && Array.isArray(response.data)) ? response.data : [];
            lista.forEach(u => {
                html += `<option value="${u.id}">${u.nome}</option>`;
            });
            $('#teste_usuario').html(html);
        }
    });
}

/**
 * Executar teste de cálculos
 */
function executarTeste() {
    const usuario_id = $('#teste_usuario').val();
    const mes = $('#teste_mes').val();

    if (!usuario_id) {
        Swal.fire('Aviso', 'Selecione um usuário', 'warning');
        return;
    }

    $.ajax({
        url: `${ROTAS_PONTO.calcularSaldoMensal}&usuario_id=${usuario_id}&mes=${mes}`,
        success: function(response) {
            $('#resultado_teste').show();
            $('#teste_output').text(JSON.stringify(response, null, 2));
        }
    });
}
</script>
