<?php
include('db.php');
$has_additional_info = $con->query("SHOW COLUMNS FROM request LIKE 'additional_info'");
if ($has_additional_info && $has_additional_info->num_rows === 0) {
    $con->query("ALTER TABLE request ADD COLUMN additional_info TEXT NULL");
}
$has_is_completed = $con->query("SHOW COLUMNS FROM request LIKE 'is_completed'");
if ($has_is_completed && $has_is_completed->num_rows === 0) {
    $con->query("ALTER TABLE request ADD COLUMN is_completed TINYINT(1) NOT NULL DEFAULT 0");
}
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$valid_statuses = ['Новая', 'Мероприятие назначено', 'Мероприятие завершено'];
$status_updated = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'] ?? '';

    if (!in_array($status, $valid_statuses, true)) {
        die('Недопустимый статус заявки');
    }

    $completed_status = $valid_statuses[2];
    $is_completed = ($status === $completed_status) ? 1 : 0;
    $stmt = $con->prepare("UPDATE request SET status = ?, is_completed = ? WHERE id = ?");
    $stmt->bind_param('sii', $status, $is_completed, $request_id);

    if (!$stmt->execute()) {
        die('Ошибка обновления: ' . $con->error);
    } else {
        $status_updated = true;
    }
}

$page = (int)($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$query = $con->query("
    SELECT request.*, users.login, users.fullname,
           COUNT(*) OVER() as total_count
    FROM request
    INNER JOIN users ON request.user_id = users.id
    ORDER BY request.date DESC
    LIMIT $limit OFFSET $offset
");

if (!$query) die('Ошибка запроса: ' . $con->error);

$stats_query = $con->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new_requests,
        SUM(CASE WHEN status = 'Мероприятие назначено' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Мероприятие завершено' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Конференции.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --gray-dark: #343A40;   
            --gray-light: #CED4DA;  
            --green: #28A745;       
            --white: #FFFFFF;       
            --gray-bg: #F4F6F8;     
            --text-muted: #6c757d;
            --border-radius: 16px;
            --shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, var(--gray-bg) 0%, #eef2f5 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            background: var(--white);
            padding: 28px 32px;
            border-bottom: 3px solid var(--green);
        }

        .header h1 {
            color: var(--gray-dark);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }

        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 32px;
            background: var(--gray-bg);
            border-bottom: 1px solid var(--gray-light);
        }

        .btn {
            padding: 8px 24px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--green);
            color: var(--white);
        }

        .btn-outline {
            background: transparent;
            color: var(--green);
            border: 1.5px solid var(--green);
        }

        .btn-outline:hover {
            background: var(--green);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 28px 32px;
            background: var(--gray-bg);
        }

        .stat-card {
            background: var(--white);
            padding: 22px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-left: 4px solid var(--green);
            transition: all 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }

        .stat-number {
            font-size: 34px;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .requests-container {
            padding: 0 32px 32px;
        }

        .request-item {
            background: var(--white);
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid var(--gray-light);
            transition: all 0.25s ease;
        }

        .request-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
            border-color: var(--green);
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .user-info h3 {
            color: var(--gray-dark);
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 18px;
        }

        .user-info p {
            color: var(--text-muted);
            font-weight: 400;
            font-size: 14px;
        }

        .request-id {
            background: var(--gray-bg);
            padding: 4px 14px;
            border-radius: 40px;
            font-weight: 600;
            color: var(--gray-dark);
            font-size: 13px;
        }

        .status-badge {
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-left: 10px;
        }

        .status-new {
            background: #fff3cd;
            color: #856404;
        }

        .status-assigned {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .detail-item {
            padding: 12px 16px;
            background: var(--gray-bg);
            border-radius: 16px;
        }

        .detail-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 400;
        }

        .detail-value {
            font-size: 15px;
            color: var(--gray-dark);
            margin-top: 6px;
            font-weight: 400;
        }

        .status-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-dark);
            font-size: 14px;
        }

        .form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--gray-light);
            border-radius: 40px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.12);
        }

        .btn-save {
            width: 100%;
            padding: 12px;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .btn-save:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 30px 0;
            padding-bottom: 32px;
        }

        .page-link {
            padding: 8px 16px;
            border: 1.5px solid var(--gray-light);
            border-radius: 40px;
            text-decoration: none;
            color: var(--gray-dark);
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .page-link:hover,
        .page-link.active {
            background: var(--green);
            color: var(--white);
            border-color: var(--green);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--gray-bg);
            border-radius: 20px;
        }

        .empty-state i {
            font-size: 48px;
            color: var(--green);
            margin-bottom: 15px;
        }

        .empty-state h3 {
            color: var(--gray-dark);
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 20px;
        }

        .empty-state p {
            color: var(--text-muted);
            font-weight: 400;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 24px;
            background: var(--green);
            color: var(--white);
            border-radius: 40px;
            box-shadow: var(--shadow);
            z-index: 1000;
            animation: slideInRight 0.3s ease-out, fadeOut 0.3s ease-out 2.5s forwards;
            font-weight: 500;
            font-size: 14px;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                visibility: hidden;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 20px;
            }

            .header, .nav-bar, .stats-grid, .requests-container {
                padding: 20px;
            }

            .nav-bar {
                flex-direction: column;
                gap: 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .request-item {
                padding: 18px;
            }

            .request-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chalkboard-user"></i> Панель администратора</h1>
            <p class="subtitle">Управление бронированием помещений для всероссийских конференций</p>
        </div>

        <div class="nav-bar">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-home"></i> Главная
            </a>
            <a href="?logout=1" class="btn btn-outline" onclick="return confirm('Выйти из аккаунта?')">
                <i class="fas fa-sign-out-alt"></i> Выход
            </a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" style="color: var(--green);"><?= $stats['total'] ?></div>
                <div class="stat-label">Всего бронирований</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #856404;"><?= $stats['new_requests'] ?></div>
                <div class="stat-label">🆕 Новые</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #0c5460;"><?= $stats['assigned'] ?></div>
                <div class="stat-label">📅 Мероприятие назначено</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #155724;"><?= $stats['completed'] ?></div>
                <div class="stat-label">✅ Мероприятие завершено</div>
            </div>
        </div>

        <div class="requests-container">
            <?php
            if ($query->num_rows === 0) {
            ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <h3>Бронирований пока нет</h3>
                    <p>Когда пользователи забронируют помещения, они появятся здесь</p>
                </div>
            <?php } else {
                while ($request = $query->fetch_assoc()) {
                    $status_class = match($request['status']) {
                        'Новая' => 'status-new',
                        'Мероприятие назначено' => 'status-assigned',
                        'Мероприятие завершено' => 'status-completed',
                        default => 'status-new'
                    };
                    
                    $venue = $request['curses'] ?? '—';
                    $venue_icon = '';
                    if(strpos($venue, 'Аудитория') !== false) $venue_icon = '🎓';
                    elseif(strpos($venue, 'Коворкинг') !== false) $venue_icon = '💼';
                    elseif(strpos($venue, 'Кинозал') !== false) $venue_icon = '🎬';
                    else $venue_icon = '🏛️';
            ?>
                <div class="request-item">
                    <div class="request-header">
                        <div class="user-info">
                            <h3><i class="fas fa-user"></i> <?= htmlspecialchars($request['login']) ?></h3>
                            <p><?= htmlspecialchars($request['fullname']) ?></p>
                        </div>
                        <div>
                            <span class="request-id">Бронь №<?= htmlspecialchars($request['id']) ?></span>
                            <span class="status-badge <?= $status_class ?>"><?= htmlspecialchars($request['status']) ?></span>
                        </div>
                    </div>

                    <div class="request-details">
                        <div class="detail-item">
                            <div class="detail-label"><i class="far fa-calendar-alt"></i> Дата и время</div>
                            <div class="detail-value"><?= htmlspecialchars($request['date']) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-building"></i> Тип помещения</div>
                            <div class="detail-value"><?= $venue_icon ?> <?= htmlspecialchars($venue) ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-credit-card"></i> Способ оплаты</div>
                            <div class="detail-value"><?= htmlspecialchars($request['payment'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-comment"></i> Доп. информация</div>
                            <div class="detail-value"><?= htmlspecialchars($request['additional_info'] ?? '—') ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-star"></i> Отзыв</div>
                            <div class="detail-value"><?= htmlspecialchars($request['review'] ?? '—') ?></div>
                        </div>
                    </div>

                    <div class="status-form">
                        <form method="POST" class="status-update-form">
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">

                            <div class="form-group">
                                <label class="form-label" for="status_<?= $request['id'] ?>">
                                    <i class="fas fa-tag"></i> Изменить статус бронирования:
                                </label>
                                <select name="status" id="status_<?= $request['id'] ?>" class="form-select">
                                    <option value="Новая" <?= $request['status'] == 'Новая' ? 'selected' : '' ?>>
                                        🆕 Новая
                                    </option>
                                    <option value="Мероприятие назначено" <?= $request['status'] == 'Мероприятие назначено' ? 'selected' : '' ?>>
                                        📅 Мероприятие назначено
                                    </option>
                                    <option value="Мероприятие завершено" <?= $request['status'] == 'Мероприятие завершено' ? 'selected' : '' ?>>
                                        ✅ Мероприятие завершено
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>

        <?php if ($stats['total'] > $limit): ?>
            <div class="pagination">
                <?php
                $total_pages = ceil($stats['total'] / $limit);
                for ($i = 1; $i <= $total_pages; $i++):
                ?>
                    <a href="?page=<?= $i ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($status_updated): ?>
        <div class="notification">
            <i class="fas fa-check-circle"></i> Статус бронирования успешно обновлён!
        </div>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.status-update-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.btn-save');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';

                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 2000);
            });
        });
        
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            setTimeout(() => {
                if (notification) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
    </script>
</body>
</html>
