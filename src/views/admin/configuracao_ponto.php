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
 * Requer: $_SESSION['role'] === 'admin' ou 'RH'
 */

// Garantir autenticação e autorização
if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'RH'])) {
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
    'duracao_lunch_minutos' => 60
];

$mensagem = '';
$tipo_alerta = ''; // 'sucesso', 'erro', 'aviso'
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
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
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
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="permite_horas_extras"
                                   <?php echo $configuracao['permite_horas_extras'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="permite_horas_extras">
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
                               title="Adicional de 50% quando extra até 2 horas/dia"></i>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="perc_50"
                                   value="<?php echo $configuracao['percentual_hora_extra_50']; ?>"
                                   min="0" max="100" step="5">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Percentual 100% -->
                    <div class="form-group">
                        <label for="perc_100">
                            Percentual Hora Extra 100%
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
                               title="Adicional de 100% quando extra acima de 2 horas/dia ou noturna"></i>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="perc_100"
                                   value="<?php echo $configuracao['percentual_hora_extra_100']; ?}"
                                   min="0" max="200" step="5">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
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
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="calcula_dsr"
                                   <?php echo $configuracao['calcula_dsr'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="calcula_dsr">
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
                               title="Quantos dias de descanso/folga o DSR vai gerar"></i>
                        </label>
                        <input type="number" class="form-control" id="dsr_dias"
                               value="<?php echo $configuracao['dsr_dias_compensacao']; ?>"
                               min="1" max="5" step="1">
                    </div>

                    <!-- Desconta Feriado Não Trabalhado -->
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="desconta_feriado"
                                   <?php echo $configuracao['desconta_feriado_nao_trabalhado'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="desconta_feriado">
                                Descontar Feriado Não Trabalhado
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Se ativado, feriados sem apontamento causam desconto
                        </small>
                    </div>

                    <!-- Aplicar DSR em Feriado Compensado -->
                    <div class="form-group mt-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="dsr_compensado"
                                   <?php echo $configuracao['aplicar_dsr_compensado_feriado'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="dsr_compensado">
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
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
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="lunch_auto"
                                   <?php echo $configuracao['considerar_lunch_automatico'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="lunch_auto">
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
                            <i class="fas fa-info-circle" data-toggle="tooltip" 
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
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Adicionar Feriado</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="data_feriado">
                        <input type="text" class="form-control" placeholder="Descrição" id="descr_feriado">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" onclick="adicionarFeriado()">
                                <i class="fas fa-plus"></i> Adicionar
                            </button>
                        </div>
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
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
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
// Ativar tooltips Bootstrap
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});

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
        duracao_lunch_minutos: parseInt($('#duracao_lunch').val())
    };

    $.ajax({
        url: '/api/configuracao-ponto/atualizar',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(dados),
        success: function(response) {
            // Mostrar sucesso
            Swal.fire('Sucesso!', 'Configurações salvas com sucesso', 'success');
        },
        error: function(xhr) {
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
                url: '/api/configuracao-ponto/resetar',
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
    $('#modalFeriados').modal('show');
    carregarFeriados();
}

/**
 * Carregar e exibir feriados
 */
function carregarFeriados() {
    $.ajax({
        url: '/api/feriados/listar',
        type: 'GET',
        success: function(response) {
            let html = '<table class="table table-sm">';
            response.dados.forEach(f => {
                html += `<tr>
                    <td>${new Date(f.data).toLocaleDateString('pt-BR')}</td>
                    <td>${f.descricao}</td>
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

/**
 * Abrir modal de testes
 */
function testarCalculos() {
    $('#modalTeste').modal('show');
    carregarUsuariosTeste();
    document.getElementById('teste_mes').valueAsDate = new Date();
}

/**
 * Carregar usuários para teste
 */
function carregarUsuariosTeste() {
    $.ajax({
        url: '/api/usuarios/listar',
        success: function(response) {
            let html = '<option value="">-- Selecione --</option>';
            response.data.forEach(u => {
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
        url: `/api/ponto/teste-calculo?usuario_id=${usuario_id}&mes=${mes}`,
        success: function(response) {
            $('#resultado_teste').show();
            $('#teste_output').text(JSON.stringify(response, null, 2));
        }
    });
}
</script>
