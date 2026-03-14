<?php

namespace Src\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * NotificadorEmail - FASE 5
 * 
 * Gerencia envio de emails:
 * - Notificações de aprovação/rejeição de horas extras
 * - Alertas de saldo de horas
 * - Relatorios periódicos
 * - Convites de eventos
 * 
 * Usa PHPMailer com SMTP (compatível com qualquer provider)
 * 
 * Configuração em: src/config/email.php
 */
class NotificadorEmail
{
    private static $mailer = null;
    private static $config = null;

    /**
     * Inicializar e retornar instância do PHPMailer
     */
    private static function getMailer(): PHPMailer
    {
        if (self::$mailer === null) {
            self::$mailer = new PHPMailer(true);

            // Carregar configuração
            if (self::$config === null) {
                self::$config = self::carregarConfiguracao();
            }

            // Configurar SMTP
            if (self::$config['ativo']) {
                self::$mailer->isSMTP();
                self::$mailer->Host = self::$config['smtp_host'];
                self::$mailer->Port = self::$config['smtp_port'];
                self::$mailer->SMTPAuth = true;
                self::$mailer->Username = self::$config['smtp_usuario'];
                self::$mailer->Password = self::$config['smtp_senha'];
                self::$mailer->SMTPSecure = self::$config['smtp_seguranca']; // PHPMailer::ENCRYPTION_STARTTLS

                self::$mailer->setFrom(self::$config['email_origem'], self::$config['nome_origem']);
                self::$mailer->CharSet = 'UTF-8';
                self::$mailer->isHTML(true);
            }
        }

        return self::$mailer;
    }

    /**
     * Carregar configuração de email
     */
    private static function carregarConfiguracao(): array
    {
        // Valores padrão
        $config = [
            'ativo' => false,
            'smtp_host' => $_ENV['SMTP_HOST'] ?? 'smtp.seuprovider.com',
            'smtp_port' => intval($_ENV['SMTP_PORT'] ?? 587),
            'smtp_usuario' => $_ENV['SMTP_USUARIO'] ?? '',
            'smtp_senha' => $_ENV['SMTP_SENHA'] ?? '',
            'smtp_seguranca' => $_ENV['SMTP_SEGURANCA'] ?? 'tls',
            'email_origem' => $_ENV['EMAIL_ORIGEM'] ?? 'noreply@empresa.com',
            'nome_origem' => $_ENV['NOME_ORIGEM'] ?? 'Ripfire ERP'
        ];

        // Se houver config em banco de dados, utilizar
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT * FROM configuracao_email LIMIT 1");
            $dbConfig = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($dbConfig) {
                $config = array_merge($config, $dbConfig);
                $config['ativo'] = (bool)$config['ativo'];
            }
        } catch (\Exception $e) {
            // Ignorar erros de banco
        }

        return $config;
    }

    /**
     * Notificar aprovação de hora extra
     * 
     * @param string $email_usuario
     * @param string $nome_usuario
     * @param float $horas
     * @param string $observacao
     */
    public static function notificarHoraExtraAprovada(
        string $email_usuario,
        string $nome_usuario,
        float $horas,
        string $observacao = ''
    ): bool {
        try {
            $mailer = self::getMailer();

            $mailer->clearAddresses();
            $mailer->addAddress($email_usuario, $nome_usuario);

            $mailer->Subject = '✓ Sua Hora Extra foi Aprovada!';

            $html = self::template('hora_extra_aprovada', [
                'nome' => $nome_usuario,
                'horas' => number_format($horas, 2, ',', '.'),
                'observacao' => $observacao
            ]);

            $mailer->Body = $html;
            $mailer->AltBody = "Sua solicitação de $horas horas extras foi aprovada.";

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Erro ao enviar email de aprovação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar rejeição de hora extra
     */
    public static function notificarHoraExtraRejeitada(
        string $email_usuario,
        string $nome_usuario,
        float $horas,
        string $motivo
    ): bool {
        try {
            $mailer = self::getMailer();

            $mailer->clearAddresses();
            $mailer->addAddress($email_usuario, $nome_usuario);

            $mailer->Subject = '✗ Sua Hora Extra foi Rejeitada';

            $html = self::template('hora_extra_rejeitada', [
                'nome' => $nome_usuario,
                'horas' => number_format($horas, 2, ',', '.'),
                'motivo' => $motivo
            ]);

            $mailer->Body = $html;
            $mailer->AltBody = "Infelizmente sua solicitação de $horas horas extras foi rejeitada.\nMotivo: $motivo";

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Erro ao enviar email de rejeição: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar DSR disponível
     */
    public static function notificarDSRDisponivel(
        string $email_usuario,
        string $nome_usuario,
        int $semana_numero,
        float $valor_dsr
    ): bool {
        try {
            $mailer = self::getMailer();

            $mailer->clearAddresses();
            $mailer->addAddress($email_usuario, $nome_usuario);

            $mailer->Subject = '📅 Descanso Semanal Remunerado Disponível';

            $html = self::template('dsr_disponivel', [
                'nome' => $nome_usuario,
                'semana' => $semana_numero,
                'valor' => number_format($valor_dsr, 2, ',', '.')
            ]);

            $mailer->Body = $html;
            $mailer->AltBody = "Você tem direito a DSR da semana $semana_numero com valor de $valor_dsr horas.";

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Erro ao enviar email de DSR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notificar alerta de limite de horas extras próximo
     */
    public static function notificarLimiteMensalProximo(
        string $email_usuario,
        string $nome_usuario,
        float $limite_mensal,
        float $atual,
        float $percentual
    ): bool {
        try {
            $mailer = self::getMailer();

            $mailer->clearAddresses();
            $mailer->addAddress($email_usuario, $nome_usuario);

            $tipo = $percentual >= 100 ? 'ATINGIDO' : 'PRÓXIMO';
            $mailer->Subject = "⚠️ Limite de Horas Extras $tipo";

            $html = self::template('alerta_limite', [
                'nome' => $nome_usuario,
                'atual' => number_format($atual, 2, ',', '.'),
                'limite' => number_format($limite_mensal, 2, ',', '.'),
                'percentual' => round($percentual, 1)
            ]);

            $mailer->Body = $html;
            $mailer->AltBody = "Você atingiu $atual de $limite_mensal horas extras neste mês.";

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Erro ao enviar alerta de limite: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar relatório mensal de ponto
     */
    public static function enviarRelatorioPonto(
        string $email_usuario,
        string $nome_usuario,
        array $relatorio,
        string $mes_ano
    ): bool {
        try {
            $mailer = self::getMailer();

            $mailer->clearAddresses();
            $mailer->addAddress($email_usuario, $nome_usuario);

            $mailer->Subject = "📊 Relatório de Ponto - $mes_ano";

            $html = self::template('relatorio_mensal', [
                'nome' => $nome_usuario,
                'mes_ano' => $mes_ano,
                'saldo' => number_format($relatorio['saldo_final'] ?? 0, 2, ',', '.'),
                'dias_trabalhados' => $relatorio['dias_trabalhados'] ?? 0,
                'faltas' => $relatorio['faltas'] ?? 0,
                'horas_extras' => number_format($relatorio['horas_extras_aprovadas'] ?? 0, 2, ',', '.')
            ]);

            $mailer->Body = $html;

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Erro ao enviar relatório de ponto: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar email genérico para RH
     */
    public static function enviarParaRH(
        string $titulo,
        string $conteudo,
        array $anexos = []
    ): bool {
        try {
            $db = Database::getConnection();
            
            // Buscar todos os usuários com role RH
            $stmt = $db->query("
                SELECT email FROM usuarios 
                WHERE role IN ('rh', 'admin', 'gerente')
                AND email IS NOT NULL
            ");
            
            $emails_rh = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (empty($emails_rh)) {
                return false;
            }

            $mailer = self::getMailer();

            foreach ($emails_rh as $email) {
                $mailer->clearAddresses();
                $mailer->addAddress($email);
                $mailer->Subject = $titulo;
                $mailer->Body = $conteudo;

                try {
                    $mailer->send();
                } catch (MailException $e) {
                    error_log("Erro ao enviar para $email: " . $e->getMessage());
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log('Erro ao enviar para RH: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Testar configuração de email
     */
    public static function testar(string $email_teste): bool
    {
        try {
            $mailer = self::getMailer();

            $mailer->clearAddresses();
            $mailer->addAddress($email_teste);

            $mailer->Subject = '[TESTE] Sistema de Email - Ripfire ERP';
            $mailer->Body = '<p>Este é um email de teste do sistema Ripfire ERP.</p><p>Se você recebeu este email, a configuração está funcionando corretamente!</p>';
            $mailer->AltBody = 'Email de teste do sistema Ripfire ERP.';

            return $mailer->send();
        } catch (MailException $e) {
            error_log('Erro ao testar email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retornar template HTML para email
     */
    private static function template(string $nome, array $dados = []): string
    {
        $templates = [
            'hora_extra_aprovada' => <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;">
                    <h1 style="margin: 0;">✓ Aprovado!</h1>
                </div>
                <div style="padding: 20px;">
                    <p>Olá <strong>{$dados['nome']}</strong>,</p>
                    <p>Sua solicitação de <strong>{$dados['horas']}h</strong> de hora extra foi <strong>aprovada</strong>!</p>
                    {$dados['observacao'] ? "<p><strong>Observação:</strong> {$dados['observacao']}</p>" : ""}
                    <p>Este valor será considerado no seu próximo cálculo de saldo de horas.</p>
                    <hr>
                    <small style="color: #999;">Ripfire ERP - Sistema de Ponto Eletrônico</small>
                </div>
            </div>
            HTML,

            'hora_extra_rejeitada' => <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; text-align: center;">
                    <h1 style="margin: 0;">✗ Rejeitado</h1>
                </div>
                <div style="padding: 20px;">
                    <p>Olá <strong>{$dados['nome']}</strong>,</p>
                    <p>Infelizmente sua solicitação de <strong>{$dados['horas']}h</strong> de hora extra foi <strong>rejeitada</strong>.</p>
                    <p><strong>Motivo:</strong> {$dados['motivo']}</p>
                    <p>Se você tiver dúvidas, entre em contato com o departamento de RH.</p>
                    <hr>
                    <small style="color: #999;">Ripfire ERP - Sistema de Ponto Eletrônico</small>
                </div>
            </div>
            HTML,

            'dsr_disponivel' => <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; text-align: center;">
                    <h1 style="margin: 0;">📅 DSR Disponível</h1>
                </div>
                <div style="padding: 20px;">
                    <p>Olá <strong>{$dados['nome']}</strong>,</p>
                    <p>Você completou os requisitos da semana <strong>{$dados['semana']}</strong> e tem direito a:</p>
                    <p style="font-size: 24px; font-weight: bold; color: #4facfe;"><strong>{$dados['valor']}h</strong></p>
                    <p>De Descanso Semanal Remunerado (DSR - Lei 605/49)</p>
                    <p>Este tempo será creditado em sua comprovação de horas.</p>
                    <hr>
                    <small style="color: #999;">Ripfire ERP - Sistema de Ponto Eletrônico</small>
                </div>
            </div>
            HTML,

            'alerta_limite' => <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%); color: white; padding: 20px; text-align: center;">
                    <h1 style="margin: 0;">⚠️ Atenção!</h1>
                </div>
                <div style="padding: 20px;">
                    <p>Olá <strong>{$dados['nome']}</strong>,</p>
                    <p>Você atingiu <strong>{$dados['percentual']}%</strong> do seu limite mensal de horas extras.</p>
                    <div style="background: #f5f5f5; padding: 15px; border-left: 4px solid #ffa751;">
                        <p style="margin: 0;"><strong>Limite:</strong> {$dados['limite']}h</p>
                        <p style="margin: 0;"><strong>Atual:</strong> {$dados['atual']}h</p>
                    </div>
                    <p>Solicite novas horas extras com cuidado para não exceder o limite permitido.</p>
                    <hr>
                    <small style="color: #999;">Ripfire ERP - Sistema de Ponto Eletrônico</small>
                </div>
            </div>
            HTML,

            'relatorio_mensal' => <<<HTML
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;">
                    <h1 style="margin: 0;">📊 Relatório de Ponto</h1>
                    <p style="margin: 5px 0; font-size: 14px;">{$dados['mes_ano']}</p>
                </div>
                <div style="padding: 20px;">
                    <p>Olá <strong>{$dados['nome']}</strong>,</p>
                    <p>Segue resumo do seu ponto do mês:</p>
                    
                    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                        <tr style="background: #f5f5f5;">
                            <td style="padding: 10px; border: 1px solid #ddd;"><strong>Dias Trabalhados</strong></td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">{$dados['dias_trabalhados']}</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd;"><strong>Faltas</strong></td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">{$dados['faltas']}</td>
                        </tr>
                        <tr style="background: #f5f5f5;">
                            <td style="padding: 10px; border: 1px solid #ddd;"><strong>Horas Extras Aprovadas</strong></td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">{$dados['horas_extras']}h</td>
                        </tr>
                        <tr style="background: #f5f5f5; font-weight: bold;">
                            <td style="padding: 10px; border: 1px solid #ddd;"><strong>Saldo Total</strong></td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: right; color: #667eea;">{$dados['saldo']}h</td>
                        </tr>
                    </table>

                    <p>Para mais detalhes, acesse o sistema em: <a href="/">Ripfire ERP</a></p>
                    <hr>
                    <small style="color: #999;">Ripfire ERP - Sistema de Ponto Eletrônico</small>
                </div>
            </div>
            HTML
        ];

        return $templates[$nome] ?? '<p>Template não encontrado</p>';
    }
}
