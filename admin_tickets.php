<?php
require_once 'dp.php';

// FIX: Безопасность сессий и CSRF-токен
session_start();


// Генерация CSRF-токена, если его нет
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка изменения статуса и ответа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FIX: Проверка CSRF-токена для всех POST-запросов
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности: неверный CSRF-токен');
    }

    $ticket_id = $_POST['ticket_id'] ?? '';
    
    // Изменение статуса
    if (isset($_POST['change_status'])) {
        $new_status = $_POST['new_status'];
        $stmt = $pdo->prepare("UPDATE support_tickets SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['status' => $new_status, 'id' => $ticket_id]);
        
        // Получаем тикет для проверки на возврат
        $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :id");
        $stmt->execute(['id' => $ticket_id]);
        $ticket = $stmt->fetch();
        
        // Если это заявка на возврат и статус закрыт - обновляем заказ
        if ($ticket && $new_status === 'closed' && stripos($ticket['subject'], 'возврат') !== false) {
            if (preg_match('/Заказ:\s*([A-Z]{2,3}-[A-Z0-9]+)/i', $ticket['message'], $matches)) {
                $order_id = $matches[1];
                $update_order = $pdo->prepare("UPDATE payments SET status = 'refunded', updated_at = NOW() WHERE payment_id = :payment_id");
                $update_order->execute(['payment_id' => $order_id]);
            }
        }
        
        $_SESSION['admin_msg'] = "✅ Статус тикета изменён";
        header('Location: admin_tickets.php');
        exit;
    }
    
    // Отправка ответа клиенту
    if (isset($_POST['send_reply'])) {
        $reply_message = trim($_POST['reply_message'] ?? '');
        
        if (!empty($reply_message)) {
            $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :id");
            $stmt->execute(['id' => $ticket_id]);
            $ticket = $stmt->fetch();
            
            if ($ticket) {
                $to = $ticket['user_email'];
                $subject = "Re: Тикет #$ticket[ticket_id] - Lizo4kaGameStore";
                
                $message = "
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <style>
                        body { font-family: Arial, sans-serif; background: #0f0f0f; color: #fff; margin: 0; padding: 0; }
                        .container { max-width: 600px; margin: 0 auto; background: #1a1a2e; padding: 30px; border-radius: 12px; }
                        .header { background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); padding: 25px; border-radius: 8px; margin-bottom: 25px; text-align: center; }
                        .header h2 { margin: 0; color: #fff; font-size: 22px; }
                        .ticket-info { background: #16213e; padding: 20px; border-radius: 8px; margin: 20px 0; }
                        .info-row { display: flex; justify-content: space-between; margin: 10px 0; }
                        .label { color: #aaa; font-size: 14px; }
                        .value { color: #fff; font-size: 15px; font-weight: 600; }
                        .reply-box { background: #0f0f0f; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #4361ee; }
                        .reply-label { color: #4361ee; font-size: 14px; margin-bottom: 10px; font-weight: 600; }
                        .reply-content { color: #fff; line-height: 1.8; font-size: 15px; }
                        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #0f3460; text-align: center; color: #666; font-size: 12px; }
                        .btn { display: inline-block; padding: 12px 30px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: 600; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>🎮 Ответ от поддержки Lizo4kaGameStore</h2>
                            <p style='margin:10px 0 0; color:#e0e0ff; font-size:14px;'>Тикет #$ticket[ticket_id]</p>
                        </div>
                        
                        <div class='ticket-info'>
                            <div class='info-row'>
                                <span class='label'>Номер тикета:</span>
                                <span class='value'>#$ticket[ticket_id]</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Тема:</span>
                                <span class='value'>$ticket[subject]</span>
                            </div>
                            <div class='info-row'>
                                <span class='label'>Статус:</span>
                                <span class='value' style='color:#2ed573;'>".ucfirst($ticket['status'])."</span>
                            </div>
                        </div>
                        
                        <div class='reply-box'>
                            <div class='reply-label'>📧 Ответ поддержки:</div>
                            <div class='reply-content'>" . nl2br(htmlspecialchars($reply_message)) . "</div>
                        </div>
                        
                        <div style='text-align:center;'>
                            <a href='".SITE_URL."/order_status.php' class='btn'>Проверить статус заказа</a>
                        </div>
                        
                        <div class='footer'>
                            <p>Это автоматическое уведомление от системы поддержки Lizo4kaGameStore</p>
                            <p>📧 242185@edu.fa.ru | 💬 @lizo4ka_support</p>
                            <p>Дата: " . date('d.m.Y H:i') . "</p>
                            <p style='margin-top:15px; color:#444;'>Не отвечайте на это письмо — используйте форму на сайте</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Lizo4kaGameStore Support <242185@edu.fa.ru>\r\n";
                $headers .= "Reply-To: 242185@edu.fa.ru\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion();
                $headers .= "X-Priority: 3\r\n";
                
                $mail_sent = @mail($to, $subject, $message, $headers);
                
                $log_entry = date('Y-m-d H:i:s') . " | Ticket Reply: $ticket[ticket_id] | To: $to | Status: " . ($mail_sent ? 'SENT' : 'FAILED') . "\n";
                file_put_contents('email_logs.txt', $log_entry, FILE_APPEND);
                
                if ($mail_sent) {
                    $_SESSION['reply_success'] = "✅ Ответ отправлен клиенту на $to";
                } else {
                    $_SESSION['reply_error'] = "⚠️ Письмо не отправлено. Проверьте настройки сервера.";
                }
            }
        }
        
        header('Location: admin_tickets.php');
        exit;
    }
}

// Получение всех тикетов
$stmt = $pdo->query("SELECT * FROM support_tickets ORDER BY created_at DESC");
$tickets = $stmt->fetchAll();

// Статистика
$total_tickets = count($tickets);
$open_tickets = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='open'")->fetchColumn();
$resolved_tickets = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='resolved'")->fetchColumn();

// Перевод статусов
$status_labels = [
    'open' => 'Открыт',
    'in_progress' => 'В работе',
    'resolved' => 'Решён',
    'closed' => 'Закрыт'
];

$status_colors = [
    'open' => '#ff4757',
    'in_progress' => '#ffa502',
    'resolved' => '#2ed573',
    'closed' => '#57606f'
];

$priority_labels = [
    'low' => 'Низкий',
    'normal' => 'Средний',
    'high' => 'Высокий'
];

$priority_colors = [
    'low' => '#57606f',
    'normal' => '#4361ee',
    'high' => '#ff4757'
];

$reply_success = $_SESSION['reply_success'] ?? '';
$reply_error = $_SESSION['reply_error'] ?? '';
$admin_msg = $_SESSION['admin_msg'] ?? '';
unset($_SESSION['reply_success'], $_SESSION['reply_error'], $_SESSION['admin_msg']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Тикеты поддержки</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #0f0f1a; color: #fff; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #4361ee; margin-bottom: 30px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #16213e; padding: 25px; border-radius: 12px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: 700; color: #4361ee; }
        .stat-label { color: #aaa; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #16213e; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #0f3460; }
        th { background: #4361ee; font-weight: 600; }
        tr:hover { background: #1a1a2e; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .priority-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .btn-back { display: inline-block; padding: 10px 20px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 8px; margin-bottom: 20px; }
        .status-select { padding: 5px 10px; background: #1a1a2e; border: 1px solid #0f3460; border-radius: 6px; color: #fff; font-size: 13px; }
        .btn-change { padding: 5px 10px; background: #2ed573; border: none; border-radius: 6px; color: #fff; cursor: pointer; font-size: 13px; }
        .btn-view { padding: 5px 10px; background: #4361ee; border: none; border-radius: 6px; color: #fff; cursor: pointer; font-size: 13px; margin-right: 5px; }
        .btn-reply { padding: 5px 10px; background: #ffa502; border: none; border-radius: 6px; color: #fff; cursor: pointer; font-size: 13px; }
        .ticket-message { background: #1a1a2e; padding: 15px; border-radius: 8px; margin: 10px 0; max-height: 150px; overflow-y: auto; font-size: 13px; color: #aaa; }
        .modal { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: #16213e; padding: 30px; border-radius: 12px; max-width: 700px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-close { background: none; border: none; color: #fff; font-size: 28px; cursor: pointer; }
        .detail-row { margin: 15px 0; }
        .detail-label { color: #aaa; font-size: 14px; margin-bottom: 5px; }
        .detail-value { color: #fff; font-size: 16px; }
        .reply-form { background: #1a1a2e; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .reply-form textarea { width: 100%; padding: 12px; background: #0f0f0f; border: 1px solid #0f3460; border-radius: 8px; color: #fff; font-family: inherit; min-height: 120px; margin-bottom: 15px; box-sizing: border-box; }
        .reply-form textarea:focus { outline: none; border-color: #4361ee; }
        .btn-send { padding: 12px 30px; background: #4361ee; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-send:hover { background: #3a56d4; }
        .success-msg { background: #2ed573; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error-msg { background: #ff4757; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .info-msg { background: #4361ee; color: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .refund-notice { background: #ff4757; color: #fff; padding: 15px; border-radius: 8px; margin-top: 15px; font-size: 13px; }
        @media (max-width: 768px) { table { font-size: 12px; } th, td { padding: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">← На сайт</a>
        <h1>🎫 Панель управления тикетами</h1>
        
        <?php if ($reply_success): ?>
            <div class="success-msg"><?= htmlspecialchars($reply_success) ?></div>
        <?php endif; ?>
        
        <?php if ($reply_error): ?>
            <div class="error-msg"><?= htmlspecialchars($reply_error) ?></div>
        <?php endif; ?>

        <?php if ($admin_msg): ?>
            <div class="info-msg"><?= htmlspecialchars($admin_msg) ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_tickets ?></div>
                <div class="stat-label">Всего тикетов</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ff4757;"><?= $open_tickets ?></div>
                <div class="stat-label">Открыто</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ffa502;"><?= $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='in_progress'")->fetchColumn() ?></div>
                <div class="stat-label">В работе</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #2ed573;"><?= $resolved_tickets ?></div>
                <div class="stat-label">Решено</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Тикет ID</th>
                    <th>Email</th>
                    <th>Тема</th>
                    <th>Приоритет</th>
                    <th>Статус</th>
                    <th>Изменить статус</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><strong><?= htmlspecialchars($t['ticket_id']) ?></strong></td>
                    <td><?= htmlspecialchars($t['user_email']) ?></td>
                    <td><?= htmlspecialchars($t['subject']) ?></td>
                    <td>
                        <span class="priority-badge" style="background: <?= $priority_colors[$t['priority']] ?>; color: #fff;">
                            <?= $priority_labels[$t['priority']] ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge" style="background: <?= $status_colors[$t['status']] ?>; color: #fff;">
                            <?= $status_labels[$t['status']] ?>
                        </span>
                    </td>
                    <td>
                        <!-- FIX: добавлен CSRF-токен -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                            <select name="new_status" class="status-select">
                                <option value="open" <?= $t['status']=='open'?'selected':'' ?>>Открыт</option>
                                <option value="in_progress" <?= $t['status']=='in_progress'?'selected':'' ?>>В работе</option>
                                <option value="resolved" <?= $t['status']=='resolved'?'selected':'' ?>>Решён</option>
                                <option value="closed" <?= $t['status']=='closed'?'selected':'' ?>>Закрыт</option>
                            </select>
                            <button type="submit" name="change_status" class="btn-change">OK</button>
                        </form>
                    </td>
                    <td><?= date('d.m.Y H:i', strtotime($t['created_at'])) ?></td>
                    <td>
                        <button class="btn-view" onclick="viewTicket(<?= htmlspecialchars(json_encode($t, JSON_UNESCAPED_UNICODE)) ?>)">Просмотр</button>
                        <button class="btn-reply" onclick="openReply(<?= $t['id'] ?>, '<?= htmlspecialchars($t['ticket_id']) ?>', '<?= htmlspecialchars($t['user_email']) ?>')">Ответить</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Модальное окно просмотра тикета -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Тикет</h2>
                <button class="modal-close" onclick="closeModal('ticketModal')">&times;</button>
            </div>
            <div class="detail-row">
                <div class="detail-label">ID тикета</div>
                <div class="detail-value" id="modalTicketId"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email клиента</div>
                <div class="detail-value" id="modalEmail"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Тема</div>
                <div class="detail-value" id="modalSubject"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Статус</div>
                <div class="detail-value" id="modalStatus"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Приоритет</div>
                <div class="detail-value" id="modalPriority"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Дата создания</div>
                <div class="detail-value" id="modalDate"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Сообщение клиента</div>
                <div class="ticket-message" id="modalMessage"></div>
            </div>
            <div id="refundNotice" class="refund-notice" style="display:none;">
                ⚠️ При закрытии этого тикета статус заказа автоматически изменится на "Возврат средств"
            </div>
        </div>
    </div>

    <!-- Модальное окно ответа -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="replyTitle">Ответ на тикет</h2>
                <button class="modal-close" onclick="closeModal('replyModal')">&times;</button>
            </div>
            <!-- FIX: добавлен CSRF-токен -->
            <form method="POST" action="admin_tickets.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="ticket_id" id="replyTicketId">
                <div class="detail-row">
                    <div class="detail-label">Тикет</div>
                    <div class="detail-value" id="replyTicketNum"></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email клиента</div>
                    <div class="detail-value" id="replyEmail"></div>
                </div>
                <div class="reply-form">
                    <textarea name="reply_message" placeholder="Введите ваш ответ клиенту..." required></textarea>
                    <button type="submit" name="send_reply" class="btn-send">📧 Отправить ответ</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const statusLabels = <?= json_encode($status_labels, JSON_UNESCAPED_UNICODE) ?>;
        const priorityLabels = <?= json_encode($priority_labels, JSON_UNESCAPED_UNICODE) ?>;

        function viewTicket(ticket) {
            document.getElementById('modalTitle').textContent = ticket.ticket_id;
            document.getElementById('modalTicketId').textContent = ticket.ticket_id;
            document.getElementById('modalEmail').textContent = ticket.user_email;
            document.getElementById('modalSubject').textContent = ticket.subject;
            document.getElementById('modalStatus').textContent = statusLabels[ticket.status];
            document.getElementById('modalPriority').textContent = priorityLabels[ticket.priority];
            document.getElementById('modalDate').textContent = new Date(ticket.created_at).toLocaleString('ru-RU');
            document.getElementById('modalMessage').textContent = ticket.message;
            
            const isRefund = ticket.subject.toLowerCase().includes('возврат');
            document.getElementById('refundNotice').style.display = isRefund ? 'block' : 'none';
            
            document.getElementById('ticketModal').classList.add('active');
        }

        function openReply(id, ticketNum, email) {
            document.getElementById('replyTicketId').value = id;
            document.getElementById('replyTicketNum').textContent = ticketNum;
            document.getElementById('replyEmail').textContent = email;
            document.getElementById('replyTitle').textContent = 'Ответ на тикет ' + ticketNum;
            document.getElementById('replyModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>