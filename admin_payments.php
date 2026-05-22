<?php
require_once 'dp.php';

session_start();
// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Перевод статусов
$status_labels = [
    'pending' => 'Ожидает оплаты',
    'paid' => 'Оплачен',
    'failed' => 'Отклонён',
    'refunded' => 'Возврат средств'
];

// Обработка изменения статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Ошибка безопасности: неверный CSRF-токен');
}
    $payment_id = $_POST['payment_id'];
    $new_status = $_POST['new_status'];
    $stmt = $pdo->prepare("UPDATE payments SET status = :status, updated_at = NOW() WHERE payment_id = :payment_id");
    $stmt->execute(['status' => $new_status, 'payment_id' => $payment_id]);
    header('Location: admin_payments.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC");
$payments = $stmt->fetchAll();
$total_sum = $pdo->query("SELECT SUM(amount) FROM payments WHERE status='paid'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM payments WHERE status='paid'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Админ-панель - Платежи</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: 'Montserrat', sans-serif; background: #0f0f1a; color: #fff; padding: 20px; }
.container { max-width: 1400px; margin: 0 auto; }
h1 { color: #4361ee; }
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
.stat-card { background: #16213e; padding: 25px; border-radius: 12px; text-align: center; }
.stat-number { font-size: 32px; font-weight: 700; color: #4361ee; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #16213e; border-radius: 12px; overflow: hidden; }
th, td { padding: 15px; text-align: left; border-bottom: 1px solid #0f3460; }
th { background: #4361ee; font-weight: 600; }
tr:hover { background: #1a1a2e; }
.status-pending { color: #ffa502; font-weight: bold; }
.status-paid { color: #2ed573; font-weight: bold; }
.status-failed { color: #ff4757; font-weight: bold; }
.status-refunded { color: #00d2d3; font-weight: bold; }
.btn-back { display: inline-block; padding: 10px 20px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 8px; margin-bottom: 20px; }
.status-select { padding: 5px 10px; background: #1a1a2e; border: 1px solid #0f3460; border-radius: 6px; color: #fff; }
.btn-change { padding: 5px 10px; background: #2ed573; border: none; border-radius: 6px; color: #fff; cursor: pointer; }
</style>
</head>
<body>
<div class="container">
<a href="index.php" class="btn-back">← На сайт</a>
<h1>💰 Панель управления платежами</h1>
<div class="stats">
<div class="stat-card">
<div class="stat-number"><?= number_format($total_sum ?? 0, 0, '.', ' ') ?> ₽</div>
<div>Всего выручено</div>
</div>
<div class="stat-card">
<div class="stat-number"><?= $total_orders ?? 0 ?></div>
<div>Успешных заказов</div>
</div>
<div class="stat-card">
<div class="stat-number"><?= count($payments) ?></div>
<div>Всего транзакций</div>
</div>
</div>
<table>
<thead>
<tr>
<th>ID</th>
<th>Платёж ID</th>
<th>Email</th>
<th>Контакт</th>
<th>Сумма</th>
<th>Товары</th>
<th>Карта</th>
<th>Метод</th>
<th>Статус</th>
<th>Изменить</th>
<th>Дата</th>
</tr>
</thead>
<tbody>
<?php foreach ($payments as $p): ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= htmlspecialchars($p['payment_id']) ?></td>
<td><?= htmlspecialchars($p['user_email']) ?></td>
<td><?= htmlspecialchars($p['user_contact']) ?></td>
<td><?= number_format($p['amount'], 0, '.', ' ') ?> ₽</td>
<td><?= htmlspecialchars($p['products']) ?></td>
<td><?= htmlspecialchars($p['card_mask'] ?? '—') ?></td>
<td><?= htmlspecialchars($p['payment_method']) ?></td>
<td class="status-<?= $p['status'] ?>"><?= $status_labels[$p['status']] ?? $p['status'] ?></td>
<td>
<form method="POST" style="display:inline;">
<input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<select name="new_status" class="status-select">
<option value="pending" <?= $p['status']=='pending'?'selected':'' ?>>Ожидает оплаты</option>
<option value="paid" <?= $p['status']=='paid'?'selected':'' ?>>Оплачен</option>
<option value="failed" <?= $p['status']=='failed'?'selected':'' ?>>Отклонён</option>
<option value="refunded" <?= $p['status']=='refunded'?'selected':'' ?>>Возврат средств</option>
</select>
<button type="submit" name="change_status" class="btn-change">OK</button>
</form>
</td>
<td><?= $p['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>
