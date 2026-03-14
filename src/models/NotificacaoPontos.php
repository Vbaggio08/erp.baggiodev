<?php

namespace Src\Models;

use Src\Config\Database;
use PDO;

/**
 * NotificacaoPontos Model
 * 
 * Gerencia sistema de notificações do módulo FASE 3:
 * - Alertas de limites de horas extras
 * - Confirmação de aprovação/rejeição
 * - Lembretes de compensação DSR
 * - Avisos de feriados
 * 
 * Implementa padrão de notificações com status de leitura
 * e integração com sistema de auditoria
 */
class NotificacaoPontos
{
    private static $db = null;

    // Constantes de tipo de notificação
    const TIPO_ALERTA = 'alerta';
    const TIPO_INFO = 'info';
    const TIPO_ERRO = 'erro';
    const TIPO_SUCESSO = 'sucesso';

    // Tópicos de notificação
    const TOPICO_HORAS_EXTRAS = 'horas_extras';
    const TOPICO_DSR = 'dsr';
    const TOPICO_FERIADO = 'feriado';
    const TOPICO_PONTO = 'ponto';
    const TOPICO_CONFIG = 'config';

    public function __construct()
    {
        if (self::$db === null) {
            self::$db = Database::getConnection();
        }
    }

    /**
     * Criar nova notificação
     * 
     * @param int $usuario_id ID do usuário destinatário
     * @param string $tipo Tipo: alerta|info|erro|sucesso
     * @param string $titulo Título da notificação
     * @param string $mensagem Mensagem completa
     * @param string $topico Tópico: horas_extras|dsr|feriado|ponto|config
     * @param string|null $link URL para ação (ex: /horas-extras/aprovar)
     * @param array $dados_adicionais Dados estruturados em JSON
     * 
     * @return int ID da notificação criada
     * @throws \Exception
     */
    public static function criarNotificacao(
        int $usuario_id,
        string $tipo,
        string $titulo,
        string $mensagem,
        string $topico,
        ?string $link = null,
        array $dados_adicionais = []
    ): int {
        try {
            $db = Database::getConnection();

            // Validar tipo
            if (!in_array($tipo, [self::TIPO_ALERTA, self::TIPO_INFO, self::TIPO_ERRO, self::TIPO_SUCESSO])) {
                throw new \Exception("Tipo de notificação inválido: $tipo");
            }

            // Validar topico
            $topicos_validos = [
                self::TOPICO_HORAS_EXTRAS,
                self::TOPICO_DSR,
                self::TOPICO_FERIADO,
                self::TOPICO_PONTO,
                self::TOPICO_CONFIG
            ];
            if (!in_array($topico, $topicos_validos)) {
                throw new \Exception("Tópico de notificação inválido: $topico");
            }

            $sql = "
                INSERT INTO notificacoes_ponto (
                    usuario_id,
                    tipo,
                    topico,
                    titulo,
                    mensagem,
                    link,
                    dados_adicionais,
                    lida,
                    criada_em
                ) VALUES (
                    :usuario_id,
                    :tipo,
                    :topico,
                    :titulo,
                    :mensagem,
                    :link,
                    :dados_adicionais,
                    FALSE,
                    NOW()
                )
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'tipo' => $tipo,
                'topico' => $topico,
                'titulo' => $titulo,
                'mensagem' => $mensagem,
                'link' => $link,
                'dados_adicionais' => !empty($dados_adicionais) ? json_encode($dados_adicionais) : null
            ]);

            // Registrar auditoria
            if (class_exists('AuditoriaAlteracao')) {
                AuditoriaAlteracao::registrarAlteracao(
                    tabela: 'notificacoes_ponto',
                    registro_id: $db->lastInsertId(),
                    acao: 'INSERT',
                    usuario_id_modificador: $usuario_id,
                    detalhes: "Notificação criada: $topico - $titulo"
                );
            }

            return intval($db->lastInsertId());
        } catch (\Exception $e) {
            throw new \Exception("Erro ao criar notificação: " . $e->getMessage());
        }
    }

    /**
     * Listar notificações não lidas do usuário
     * 
     * @param int $usuario_id
     * @param int $limite Quantidade máxima a retornar
     * @param array $topicos Filtrar por tópicos específicos (vazio = todos)
     * 
     * @return array
     */
    public static function listarNaoLidas(int $usuario_id, int $limite = 20, array $topicos = []): array
    {
        try {
            $db = Database::getConnection();

            $sql = "
                SELECT *
                FROM notificacoes_ponto
                WHERE usuario_id = :usuario_id
                AND lida = FALSE
            ";

            $params = ['usuario_id' => $usuario_id];

            if (!empty($topicos)) {
                $placeholders = implode(',', array_fill(0, count($topicos), '?'));
                $sql .= " AND topico IN ($placeholders)";
                $params = array_merge($params, $topicos);
            }

            $sql .= " ORDER BY criada_em DESC LIMIT :limite";

            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge($params, ['limite' => $limite]));

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Listar todas as notificações do usuário com paginação
     * 
     * @param int $usuario_id
     * @param int $pagina Página (começa em 1)
     * @param int $por_pagina Quantidade por página
     * @param string $topico Filtro opcional por tópico
     * 
     * @return array ['notificacoes' => [], 'total' => int, 'pagina' => int, 'total_paginas' => int]
     */
    public static function listarPaginado(
        int $usuario_id,
        int $pagina = 1,
        int $por_pagina = 15,
        string $topico = ''
    ): array {
        try {
            $db = Database::getConnection();

            // Contar total
            $sql_count = "SELECT COUNT(*) as total FROM notificacoes_ponto WHERE usuario_id = :usuario_id";
            $params = ['usuario_id' => $usuario_id];

            if ($topico) {
                $sql_count .= " AND topico = :topico";
                $params['topico'] = $topico;
            }

            $stmt = $db->prepare($sql_count);
            $stmt->execute($params);
            $total = intval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

            // Buscar página
            $offset = ($pagina - 1) * $por_pagina;
            $sql = "
                SELECT *
                FROM notificacoes_ponto
                WHERE usuario_id = :usuario_id
            ";

            if ($topico) {
                $sql .= " AND topico = :topico";
            }

            $sql .= " ORDER BY criada_em DESC LIMIT :offset, :limite";

            $params['offset'] = $offset;
            $params['limite'] = $por_pagina;

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total_paginas = ceil($total / $por_pagina);

            return [
                'notificacoes' => $notificacoes,
                'total' => $total,
                'pagina' => $pagina,
                'total_paginas' => $total_paginas,
                'por_pagina' => $por_pagina
            ];
        } catch (\Exception $e) {
            return ['notificacoes' => [], 'total' => 0, 'pagina' => 1, 'total_paginas' => 0, 'por_pagina' => 0];
        }
    }

    /**
     * Marcar notificação como lida
     * 
     * @param int $id ID da notificação
     * @return bool
     */
    public static function marcarComoLida(int $id): bool
    {
        try {
            $db = Database::getConnection();

            $sql = "UPDATE notificacoes_ponto SET lida = TRUE, lida_em = NOW() WHERE id = :id";
            $stmt = $db->prepare($sql);

            return $stmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Marcar todas as notificações como lidas
     * 
     * @param int $usuario_id
     * @return int Quantidade atualizada
     */
    public static function marcarTodasComoLidas(int $usuario_id): int
    {
        try {
            $db = Database::getConnection();

            $sql = "UPDATE notificacoes_ponto SET lida = TRUE, lida_em = NOW() 
                   WHERE usuario_id = :usuario_id AND lida = FALSE";
            $stmt = $db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuario_id]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obter notificação por ID
     * 
     * @param int $id
     * @return array|null
     */
    public static function obterPorId(int $id): ?array
    {
        try {
            $db = Database::getConnection();

            $sql = "SELECT * FROM notificacoes_ponto WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['dados_adicionais']) {
                $result['dados_adicionais'] = json_decode($result['dados_adicionais'], true);
            }

            return $result ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Deletar notificação
     * 
     * @param int $id
     * @return bool
     */
    public static function deletar(int $id): bool
    {
        try {
            $db = Database::getConnection();

            $sql = "DELETE FROM notificacoes_ponto WHERE id = :id";
            $stmt = $db->prepare($sql);

            return $stmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Deletar notificações antigas (mais de N dias)
     * 
     * @param int $dias Notificações com mais de N dias
     * @return int Quantidade deletada
     */
    public static function limparAntigas(int $dias = 30): int
    {
        try {
            $db = Database::getConnection();

            $sql = "DELETE FROM notificacoes_ponto WHERE criada_em < DATE_SUB(NOW(), INTERVAL :dias DAY)";
            $stmt = $db->prepare($sql);
            $stmt->execute(['dias' => $dias]);

            return $stmt->rowCount();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Contar notificações não lidas
     * 
     * @param int $usuario_id
     * @return int
     */
    public static function contarNaoLidas(int $usuario_id): int
    {
        try {
            $db = Database::getConnection();

            $sql = "SELECT COUNT(*) as total FROM notificacoes_ponto 
                   WHERE usuario_id = :usuario_id AND lida = FALSE";
            $stmt = $db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuario_id]);

            return intval($stmt->fetch(PDO::FETCH_ASSOC)['total']);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper: Criar notificação de hora extra aprovada
     * 
     * @param int $usuario_id
     * @param float $horas
     * @param string $aprovado_por
     */
    public static function notificarHoraExtraAprovada(int $usuario_id, float $horas, string $aprovado_por)
    {
        self::criarNotificacao(
            usuario_id: $usuario_id,
            tipo: self::TIPO_SUCESSO,
            titulo: 'Hora Extra Aprovada ✓',
            mensagem: "Sua solicitação de $horas horas extras foi aprovada por $aprovado_por",
            topico: self::TOPICO_HORAS_EXTRAS,
            link: '/dashboard/horas-extras',
            ['horas' => $horas, 'aprovado_por' => $aprovado_por]
        );
    }

    /**
     * Helper: Criar notificação de hora extra rejeitada
     * 
     * @param int $usuario_id
     * @param float $horas
     * @param string $motivo
     */
    public static function notificarHoraExtraRejeitada(int $usuario_id, float $horas, string $motivo)
    {
        self::criarNotificacao(
            usuario_id: $usuario_id,
            tipo: self::TIPO_ALERTA,
            titulo: 'Hora Extra Rejeitada',
            mensagem: "Sua solicitação de $horas horas extras foi rejeitada. Motivo: $motivo",
            topico: self::TOPICO_HORAS_EXTRAS,
            link: '/dashboard/horas-extras',
            ['horas' => $horas, 'motivo' => $motivo]
        );
    }

    /**
     * Helper: Criar notificação de limite de horas extras atingido
     * 
     * @param int $usuario_id
     * @param float $limite_mes
     * @param float $atual
     */
    public static function notificarLimiteMensalProximo(int $usuario_id, float $limite_mes, float $atual)
    {
        $percentual = ($atual / $limite_mes) * 100;

        if ($percentual >= 100) {
            $titulo = 'Limite Mensal Atingido!';
            $tipo = self::TIPO_ERRO;
        } else if ($percentual >= 80) {
            $titulo = 'Alerta: Limite Próximo';
            $tipo = self::TIPO_ALERTA;
        } else {
            return; // Não notificar se ainda há espaço
        }

        self::criarNotificacao(
            usuario_id: $usuario_id,
            tipo: $tipo,
            titulo: $titulo,
            mensagem: "Você atingiu $atual de $limite_mes horas extras permitidas neste mês",
            topico: self::TOPICO_HORAS_EXTRAS,
            link: '/dashboard/horas-extras',
            ['atual' => $atual, 'limite' => $limite_mes, 'percentual' => $percentual]
        );
    }

    /**
     * Helper: Criar notificação de DSR disponível
     * 
     * @param int $usuario_id
     * @param int $semana_numero
     * @param float $valor_dsr
     */
    public static function notificarDSRDisponivel(int $usuario_id, int $semana_numero, float $valor_dsr)
    {
        self::criarNotificacao(
            usuario_id: $usuario_id,
            tipo: self::TIPO_INFO,
            titulo: 'Descanso Semanal Remunerado Disponível',
            mensagem: "Você tem direito a DSR da semana $semana_numero com valor de $valor_dsr horas",
            topico: self::TOPICO_DSR,
            link: '/dashboard/dsr',
            ['semana' => $semana_numero, 'valor' => $valor_dsr]
        );
    }

    /**
     * Enviar notificação por email (integração futura)
     * 
     * @param int $notificacao_id
     * @param string $email_destino
     * @return bool
     */
    public static function enviarPorEmail(int $notificacao_id, string $email_destino): bool
    {
        try {
            $notificacao = self::obterPorId($notificacao_id);
            if (!$notificacao) {
                return false;
            }

            // TODO: Integrar com sistema de email da aplicação
            // Exemplo: enviar notificacao via SMTP

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
