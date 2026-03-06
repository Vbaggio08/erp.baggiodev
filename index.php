<?php
// --- 0. CARREGAR VARIÁVEIS DE AMBIENTE ---
require_once 'src/config/env.php';

// --- 1. CONFIGURAÇÕES GERAIS ---
// Define o fuso horário
date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));

// Modo debug baseado na variável de ambiente
$appDebug = env('APP_DEBUG', false);

if ($appDebug) {
    // Modo DESENVOLVIMENTO - mostra erros
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Modo PRODUÇÃO - oculta erros
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    
    // Log de erros em arquivo
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 2. VERIFICAÇÃO DE SEGURANÇA ---
$rota = $_GET['rota'] ?? 'dashboard'; // Se não tiver rota, vai pro dashboard

// Rotas que NÃO precisam de login (Públicas)
$rotasPublicas = ['login', 'autenticar'];

// Se não estiver logado e tentar acessar página protegida, manda pro login
if (!isset($_SESSION['user_id']) && !in_array($rota, $rotasPublicas)) {
    header('Location: index.php?rota=login');
    exit;
}

// --- 3. ROTEADOR (O Cérebro do Sistema) ---
switch ($rota) {

    // --- 🔐 LOGIN & LOGOUT ---
    case 'login':
        require_once 'src/views/geral/login.php';
        break;

    case 'autenticar':
        require_once 'src/controllers/LoginController.php';
        (new LoginController())->autenticar();
        break;

    case 'logout':
        require_once 'src/controllers/LoginController.php';
        (new LoginController())->sair();
        break;

    // --- 📊 DASHBOARD ---
    case 'dashboard':
        require_once 'src/controllers/DashboardController.php';
        (new DashboardController())->index();
        break;


    // --- 📦 ESTOQUE (NOVO) ---
    case 'estoque_saldo':
        require_once 'src/controllers/EstoqueController.php';
        (new EstoqueController())->index();
        break;

    case 'entrada': // Tela de Movimentação
        require_once 'src/controllers/EstoqueController.php';
        (new EstoqueController())->telaEntrada();
        break;

    case 'salvar_estoque':
        require_once 'src/controllers/EstoqueController.php';
        (new EstoqueController())->salvarEntrada();
        break;

    case 'estoque_historico':
        require_once 'src/controllers/EstoqueController.php';
        (new EstoqueController())->historico();
        break;

    case 'relatorio_perdas':
        require_once 'src/controllers/EstoqueController.php';
        (new EstoqueController())->relatorioPerdas();
        break;


    // --- 👕 CADASTRO DE PRODUTOS ---
    case 'produtos':
        require_once 'src/controllers/ProdutoController.php';
        (new ProdutoController())->index();
        break;

    case 'novo_produto':
        require_once 'src/controllers/ProdutoController.php';
        (new ProdutoController())->criar();
        break;

    case 'salvar_produto':
        require_once 'src/controllers/ProdutoController.php';
        (new ProdutoController())->salvar();
        break;

    case 'excluir_produto':
        require_once 'src/controllers/ProdutoController.php';
        (new ProdutoController())->excluir();
        break;


    // --- 📝 GABARITOS / FICHAS TÉCNICAS ---
    case 'listar_gabaritos':
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->listar();
        break;

    case 'mudar_status_gabarito':
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->mudarStatus();
        break;

    case 'novo_gabarito':
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->novo();
        break;
        
    case 'editar_gabarito': 
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->editar();
        break;

    case 'salvar_gabarito': 
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->salvar();
        break;

    case 'visualizar_gabarito':
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->visualizar();
        break;

    case 'imprimir_gabarito':
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->imprimir();
        break;
    
    case 'excluir_gabarito':
        require_once 'src/controllers/GabaritoController.php';
        (new GabaritoController())->excluir();
        break;


    // --- 🧵 PRODUÇÃO / PEDIDOS ---
    case 'pedidos':
        require_once 'src/controllers/PedidoController.php';
        (new PedidoController())->index();
        break;

    case 'novo_pedido':
        require_once 'src/controllers/PedidoController.php';
        (new PedidoController())->novo();
        break;

    case 'salvar_pedido':
        require_once 'src/controllers/PedidoController.php';
        (new PedidoController())->salvar();
        break;
        
    case 'excluir_pedido':
        require_once 'src/controllers/PedidoController.php';
        (new PedidoController())->excluir();
        break;

    case 'novo_dtf':
        require_once 'src/controllers/PedidoController.php';
        (new PedidoController())->novo_dtf();
        break;

    case 'salvar_dtf':
        require_once 'src/controllers/PedidoController.php';
        (new PedidoController())->salvar_dtf();
        break;


    // --- 🛒 COMPRAS (NOVAS ROTAS) ---
    case 'compras':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->index();
        break;

    case 'nova_compra':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->nova();
        break;

    case 'compra_adicionar':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->adicionarItem();
        break;

    case 'compra_remover':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->removerItem();
        break;
        
    case 'compra_limpar':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->limparLista();
        break;

    case 'salvar_compra':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->salvar();
        break;

    case 'compra_mudar_status':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->mudarStatus();
        break;
        
    case 'compra_excluir':
        require_once 'src/controllers/CompraController.php';
        (new CompraController())->excluir();
        break;
    // --- 🛠️ SERVIÇOS / OS (CARRINHO E LISTA) ---
    case 'servicos':
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->index();
        break;

    case 'nova_os':
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->nova();
        break;

    // Ações da Lista de Serviços (Importante para a tela funcionar!)
    case 'os_adicionar':
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->adicionarItem(); // Certifique-se de ter criado esse método
        break;

    case 'os_remover':
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->removerItem(); // Certifique-se de ter criado esse método
        break;
        // ... dentro do switch ...

    // ROTA PARA MUDAR STATUS
    case 'os_mudar_status':
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->mudarStatus();
        break;

    // ROTA PARA EXCLUIR O.S.
    case 'os_excluir':
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->excluir();
        break;
        
    // ...

    case 'os_limpar':
         require_once 'src/controllers/ServicoController.php';
         // Se não tiver método, podemos limpar direto aqui ou criar um método limparLista()
         if(session_status() === PHP_SESSION_NONE) session_start();
         $_SESSION['lista_os'] = [];
         header('Location: index.php?rota=nova_os');
         break;

    case 'os_salvar': // Nome usado no form da OS
    case 'salvar_servico': // Nome alternativo
        require_once 'src/controllers/ServicoController.php';
        (new ServicoController())->salvar();
        break;


    // --- 👥 CADASTROS GERAIS ---

    // Clientes
    case 'clientes':
        require_once 'src/controllers/ClienteController.php';
        (new ClienteController())->index();
        break;
        case 'editar_cliente':
        require_once 'src/controllers/ClienteController.php';
        (new ClienteController())->editar();
        break;
    case 'novo_cliente':
        require_once 'src/controllers/ClienteController.php';
        (new ClienteController())->novo();
        break;
    case 'salvar_cliente':
        require_once 'src/controllers/ClienteController.php';
        (new ClienteController())->salvar();
        break;
    case 'excluir_cliente':
        require_once 'src/controllers/ClienteController.php';
        (new ClienteController())->excluir();
        break;

    // Fornecedores
    // --- FORNECEDORES ---
    // --- FORNECEDORES ---
    case 'fornecedores':
        require_once 'src/controllers/FornecedorController.php';
        (new FornecedorController())->listar();
        break;
        
    case 'novo_fornecedor':
        require_once 'src/controllers/FornecedorController.php';
        (new FornecedorController())->novo();
        break;
        
    case 'editar_fornecedor':
        require_once 'src/controllers/FornecedorController.php';
        (new FornecedorController())->editar();
        break;
        
    case 'salvar_fornecedor':
        require_once 'src/controllers/FornecedorController.php';
        (new FornecedorController())->salvar();
        break;
        
    case 'excluir_fornecedor':
        require_once 'src/controllers/FornecedorController.php';
        (new FornecedorController())->excluir();
        break;

    // Empresas
    case 'empresas':
        require_once 'src/controllers/EmpresaController.php';
        (new EmpresaController())->index();
        break;
    case 'salvar_empresa':
        require_once 'src/controllers/EmpresaController.php';
        (new EmpresaController())->salvar();
        break;
    case 'excluir_empresa':
        require_once 'src/controllers/EmpresaController.php';
        (new EmpresaController())->excluir();
        break;


    // --- 👤 ADMINISTRAÇÃO ---
    case 'gerenciar_usuarios':
        require_once 'src/controllers/LoginController.php';
        (new LoginController())->listarUsuarios();
        break;

    case 'editar_usuario':
        require_once 'src/controllers/LoginController.php';
        (new LoginController())->editarUsuario();
        break;

    case 'salvar_usuario':
        require_once 'src/controllers/LoginController.php';
        (new LoginController())->salvarUsuario();
        break;

    case 'excluir_usuario':
        require_once 'src/controllers/LoginController.php';
        (new LoginController())->excluirUsuario();
        break;


    // --- 🚫 ROTA PADRÃO (404) ---
    default:
        header('Location: index.php?rota=dashboard');
        exit;
}
?>