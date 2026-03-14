<?php
/**
 * FASE 5 - Rotas necessárias para Email, Relatórios e Dashboard
 * 
 * Este arquivo documenta as rotas que devem ser adicionadas ao router/index.php
 * para integrar a FASE 5 completa ao sistema
 */

/**
 * ROTAS JSON (API) - Dashboard do Usuário
 */

// GET /index.php?rota=dashboard_ponto_json
// Retorna: { metricas, proximos_eventos, anomalias }
// Descrição: Dados do dashboard pessoal de ponto

// GET /index.php?rota=dashboard_graficos_json
// Retorna: { grafico_horas_30dias }
// Descrição: Dados para gráfico de horas (últimos 30 dias)

// GET /index.php?rota=dashboard_saldo_json
// Retorna: { grafico_saldo_6meses }
// Descrição: Dados para gráfico de saldo (últimos 6 meses)

/**
 * ROTAS JSON (API) - Dashboard RH/Admin
 */

// GET /index.php?rota=dashboard_rh_json&mes=MM&ano=YYYY
// Requer: admin ou RH
// Retorna: { totais, top_horas_extras, usuarios_com_faltas, usuarios_com_offline }
// Descrição: Dados completos do dashboard de RH

/**
 * ROTAS DE VIEWS - Dashboard
 */

// GET /index.php?rota=dashboard_ponto
// View: src/views/producao/dashboard_ponto.php
// Descrição: Dashboard pessoal de ponto do funcionário

// GET /index.php?rota=dashboard_rh
// View: src/views/admin/dashboard_rh.php
// Requer: admin ou RH
// Descrição: Dashboard consolidado de RH

/**
 * ROTAS DE EXPORTAÇÃO - Relatórios
 */

// GET /index.php?rota=exportar_ponto&mes_ano=YYYY-MM&formato=pdf|excel
// Controlador: PontoController::exportarRelatorioPonto()
// Descrição: Exporta relatório de ponto do usuário

// GET /index.php?rota=exportar_recibo&batida_id=ID
// Controlador: PontoController::exportarReciboPonto()
// Descrição: Exporta recibo individual de ponto

/**
 * EXEMPLO DE INTEGRAÇÃO NO INDEX.PHP ou ROUTER
 */

// Adicionar ao switch/case do router:

/*

case 'dashboard_ponto':
    require_exists(__DIR__ . '/src/views/producao/dashboard_ponto.php');
    break;

case 'dashboard_ponto_json':
    require_once __DIR__ . '/src/models/GeradorRelatorioPDF.php';
    require_once __DIR__ . '/src/controllers/DashboardController.php';
    $controller = new DashboardController();
    echo json_encode($controller->getDadosUsuario());
    break;

case 'dashboard_graficos_json':
    require_once __DIR__ . '/src/controllers/DashboardController.php';
    $controller = new DashboardController();
    echo json_encode($controller->getGraficosHoras());
    break;

case 'dashboard_saldo_json':
    require_once __DIR__ . '/src/controllers/DashboardController.php';
    $controller = new DashboardController();
    echo json_encode($controller->getGraficoSaldoMensal());
    break;

case 'dashboard_rh':
    // Verificar permissão admin/RH
    if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1) {
        require_exists(__DIR__ . '/src/views/admin/dashboard_rh.php');
    } else {
        header('HTTP/1.1 403 Forbidden');
        echo 'Acesso negado';
    }
    break;

case 'dashboard_rh_json':
    // Verificar permissão admin/RH
    if (isset($_SESSION['usuario_admin']) && $_SESSION['usuario_admin'] == 1) {
        require_once __DIR__ . '/src/controllers/DashboardController.php';
        $controller = new DashboardController();
        echo json_encode($controller->getDadosRH());
    } else {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Acesso negado']);
    }
    break;

case 'exportar_ponto':
    require_once __DIR__ . '/src/models/GeradorRelatorioPDF.php';
    require_once __DIR__ . '/src/controllers/PontoController.php';
    $controller = new PontoController();
    $controller->exportarRelatorioPonto();
    break;

case 'exportar_recibo':
    require_once __DIR__ . '/src/models/GeradorRelatorioPDF.php';
    require_once __DIR__ . '/src/controllers/PontoController.php';
    $controller = new PontoController();
    $controller->exportarReciboPonto();
    break;

*/

/**
 * DEPENDÊNCIAS NECESSÁRIAS - composer.json
 */

/*

Adicionar ao arquivo composer.json:

{
    "require": {
        "phpmailer/phpmailer": "^6.8",
        "setasign/fpdf": "^1.9",
        "phpoffice/phpspreadsheet": "^1.28",
        "tcpdf/tcpdf": "^6.6"
    }
}

Então rodar: composer install

*/

/**
 * INTEGRAÇÃO COM NOTIFICADOR EMAIL
 */

/*

No HorasExtrasController::aprovar():

    $resultado = $this->model->aprovar($id, $this->usuario_id);
    
    if ($resultado) {
        $hora_extra = HorasExtras::buscarPorId($id);
        $usuario = Usuario::buscarPorId($hora_extra['usuario_id']);
        
        // Enviar email
        require_once __DIR__ . '/../models/NotificadorEmail.php';
        NotificadorEmail::notificarHoraExtraAprovada(
            email: $usuario['email'],
            nome: $usuario['nome'],
            horas: $hora_extra['horas_extras'],
            observacao: $_POST['observacao'] ?? ''
        );
    }

No HorasExtrasController::rejeitar():

    $resultado = $this->model->rejeitar($id, $_POST['motivo'], $this->usuario_id);
    
    if ($resultado) {
        $hora_extra = HorasExtras::buscarPorId($id);
        $usuario = Usuario::buscarPorId($hora_extra['usuario_id']);
        
        // Enviar email
        require_once __DIR__ . '/../models/NotificadorEmail.php';
        NotificadorEmail::notificarHoraExtraRejeitada(
            email: $usuario['email'],
            nome: $usuario['nome'],
            horas: $hora_extra['horas_extras'],
            motivo: $_POST['motivo']
        );
    }

*/

/**
 * RECURSOS ADICIONAIS
 */

// Chart.js CDN: https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
// FontAwesome CDN: https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css

/**
 * ESTRUTURA DE DIRETÓRIOS CRIADA
 */

/*

/src
  /models
    ├── GeradorRelatorioPDF.php (NOVO - FASE 5)
    ├── NotificadorEmail.php (NOVO - FASE 5)
    └── [outros modelos...]
  
  /controllers
    ├── DashboardController.php (ATUALIZADO - adicionar métodos JSON)
    └── [outros controllers...]
  
  /views
    /producao
    ├── dashboard_ponto.php (NOVO - FASE 5)
    └── [outras views...]
    
    /admin
    ├── dashboard_rh.php (NOVO - FASE 5)
    └── [outras views...]
  
  /config
  └── database.php (existente)

/assets
  /uploads
  └── relatorios/ (CRIADO AUTOMATICAMENTE)

*/

echo "FASE 5 - Rotas e Integração documentadas";
