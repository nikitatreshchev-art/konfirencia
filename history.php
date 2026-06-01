<?php
session_start();
if (!isset($_SESSION['user_id']))
    die('Чтобы посмотреть историю бронирований, необходимо войти в аккаунт.');
include('db.php');
$has_additional_info = $con->query("SHOW COLUMNS FROM request LIKE 'additional_info'");
if ($has_additional_info && $has_additional_info->num_rows === 0) {
    $con->query("ALTER TABLE request ADD COLUMN additional_info TEXT NULL");
}
$has_is_completed = $con->query("SHOW COLUMNS FROM request LIKE 'is_completed'");
if ($has_is_completed && $has_is_completed->num_rows === 0) {
    $con->query("ALTER TABLE request ADD COLUMN is_completed TINYINT(1) NOT NULL DEFAULT 0");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $review = $con->real_escape_string($_POST['review']);
    $user_id = (int) $_SESSION['user_id'];
    $request_id = (int) $_POST['request_id'];
    $con->query("UPDATE request SET review='$review' WHERE id='$request_id' AND user_id='$user_id' AND is_completed=1");
    echo '<div class="success-message">✓ Отзыв о мероприятии успешно сохранён!</div>';
}

$user_id = (int) $_SESSION['user_id'];
$query = $con->query("SELECT * FROM request WHERE user_id='$user_id' ORDER BY date DESC");
if (!$query)
    die('query error: ' . $con->error);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои бронирования — Конференции.РФ</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --gray-dark: #343A40;

            --gray-light: #CED4DA;
            --green: #28A745;
            --white: #FFFFFF;
            --gray-bg: #F4F6F8;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, var(--gray-bg) 0%, #eef2f5 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 950px;
            margin: 0 auto;
            background: var(--white);
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            animation: slideInUp 0.5s ease-out;
            border: 1px solid var(--gray-light);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-home {
            display: inline-block;
            background: var(--green);
            color: var(--white);
            padding: 10px 24px;
            text-decoration: none;
            border-radius: 40px;
            margin-bottom: 28px;
            transition: all 0.25s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .btn-home:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 32px;
            color: var(--gray-dark);
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.2px;
        }

        .success-message {
            background: #e3f5e8;
            color: #155724;
            padding: 14px 18px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid var(--green);
            font-size: 14px;
            font-weight: 400;
            animation: slideInRight 0.4s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .request {
            border: 1px solid var(--gray-light);
            margin: 20px 0;
            padding: 24px;
            border-radius: 24px;
            background: var(--white);
            transition: all 0.25s ease;
        }

        .request:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
            border-color: var(--green);
        }

        .request h2 {
            margin-top: 0;
            color: var(--gray-dark);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-light);
        }

        .request b {
            color: var(--gray-dark);
            font-weight: 600;
        }

        .request p {
            margin: 10px 0;
            font-weight: 400;
            font-size: 15px;
        }

        .status-new {
            color: #856404;
            font-weight: 600;
            background: #fff3cd;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .status-processing {
            color: #0c5460;
            font-weight: 600;
            background: #d1ecf1;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .status-completed {
            color: #155724;
            font-weight: 600;
            background: #e3f5e8;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .status-cancelled {
            color: #721c24;
            font-weight: 600;
            background: #f8d7da;
            display: inline-block;
            padding: 4px 14px;
            border-radius: 40px;
            font-size: 12px;
        }

        .review-form {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed var(--gray-light);
        }

        .review-form form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .review-form input[type="text"] {
            flex: 1;
            padding: 12px 16px;
            border: 1.5px solid var(--gray-light);
            border-radius: 40px;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
        }

        .review-form input[type="text"]:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.12);
        }

        .review-form button {
            padding: 10px 24px;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-family: 'Roboto', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .review-form button:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .review-text {
            margin-top: 14px;
            padding: 12px 16px;
            background: var(--gray-bg);
            border-radius: 20px;
            color: var(--gray-dark);
            font-weight: 400;
            font-size: 14px;
            border-left: 3px solid var(--green);
        }

        .review-text b {
            color: var(--gray-dark);
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            font-size: 16px;
            font-weight: 400;
        }

        .empty-state a {
            color: var(--green);
            text-decoration: none;
            font-weight: 600;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        .create-button {
            text-align: center;
            margin-top: 36px;
        }

        .create-button a {
            background: var(--green);
            color: var(--white);
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 40px;
            font-weight: 600;
            display: inline-block;
            transition: all 0.25s ease;
        }

        .create-button a:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(40, 167, 69, 0.3);
        }

        .venue-icon {
            display: inline-block;
            margin-right: 6px;
        }

        @media (max-width: 650px) {
            .container {
                padding: 24px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .request h2 {
                font-size: 18px;
            }

            .review-form form {
                flex-direction: column;
            }

            .review-form input[type="text"] {
                width: 100%;
            }

            .review-form button {
                width: 100%;
            }
        }

        .lk-slider {
            margin: 0 0 28px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            border: 1px solid var(--gray-light);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
            background: var(--white);
        }

        .lk-slide {
            display: none;
        }

        .lk-slide.active {
            display: block;
        }

        .lk-slide img {
            width: 100%;
            height: 260px;
            object-fit: cover;
            display: block;
        }

        .lk-slide-text {
            position: absolute;
            left: 14px;
            bottom: 14px;
            background: rgba(52, 58, 64, 0.85);
            color: var(--white);
            padding: 8px 14px;
            border-radius: 40px;
            font-size: 13px;
            font-weight: 500;
        }

        .lk-prev,
        .lk-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(52, 58, 64, 0.7);
            color: var(--white);
            border: none;
            cursor: pointer;
            padding: 10px 14px;
            font-size: 18px;
            border-radius: 50%;
            transition: 0.2s;
            font-weight: 500;
            width: 40px;
            text-align: center;
            line-height: 1;
            z-index: 5;
        }

        .lk-prev {
            left: 12px;
        }

        .lk-next {
            right: 12px;
        }

        .lk-prev:hover,
        .lk-next:hover {
            background: var(--green);
        }

        @media (max-width: 650px) {
            .lk-slide img {
                height: 190px;
            }

            .lk-prev,
            .lk-next {
                width: 34px;
                padding: 7px 10px;
                font-size: 15px;
            }

            .lk-slide-text {
                font-size: 12px;
                left: 10px;
                right: 10px;
                bottom: 10px;
            }
        }

        @media (max-width: 390px) {
            body {
                padding: 14px 8px;
            }

            .container {
                padding: 16px 12px;
                border-radius: 16px;
            }

            h1 {
                font-size: 20px;
                margin-bottom: 18px;
            }

            .btn-home,
            .create-button a {
                width: 100%;
                text-align: center;
                padding: 10px 12px;
            }

            .request {
                padding: 14px 12px;
                margin: 12px 0;
                border-radius: 16px;
            }

            .request h2 {
                font-size: 16px;
                margin-bottom: 10px;
                padding-bottom: 8px;
            }

            .request p {
                font-size: 14px;
                margin: 8px 0;
                overflow-wrap: anywhere;
            }

            .review-text {
                padding: 10px 12px;
                font-size: 13px;
            }

            .lk-slider {
                margin-bottom: 16px;
                border-radius: 14px;
            }

            .lk-slide img {
                height: 160px;
            }

            .lk-prev,
            .lk-next {
                width: 30px;
                font-size: 13px;
                padding: 6px 8px;
            }

            .status-new,
            .status-processing,
            .status-completed,
            .status-cancelled {
                font-size: 11px;
                padding: 3px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn-home">🏠 На главную</a>

        <h1>📋 Мои бронирования помещений</h1>

        <div class="lk-slider" id="lkSlider">
            <div class="lk-slide active">
                <img src="assets/plenar-zal (1).jpg" alt="Конференц-зал">
                <div class="lk-slide-text">🏛️ Конференц-зал для пленарных сессий</div>
            </div>
            <div class="lk-slide">
                <img src="assets/assets (17).jpg" alt="Коворкинг">
                <div class="lk-slide-text">💼 Коворкинг для воркшопов и командной работы</div>
            </div>
            <div class="lk-slide">
                <img src="assets/kinozal.jpg" alt="Кинозал">
                <div class="lk-slide-text">🎬 Кинозал для презентаций и показов</div>
            </div>
            <button type="button" class="lk-prev" id="lkPrev" aria-label="Предыдущий слайд">&#10094;</button>
            <button type="button" class="lk-next" id="lkNext" aria-label="Следующий слайд">&#10095;</button>
        </div>

        <?php
        $i = 0;
        if ($query->num_rows == 0) {
            echo '<div class="empty-state">🎤 У вас пока нет бронирований.<br><br>✍️ <a href="create.php">Забронировать аудиторию, коворкинг или кинозал</a></div>';
        }
        while ($request = $query->fetch_assoc()) {
            $i++;

            $status_class = 'status-new';
            $status_text = htmlspecialchars($request['status']);
            if ($status_text == 'Новая')
                $status_class = 'status-new';
            elseif ($status_text == 'В обработке')
                $status_class = 'status-processing';
            elseif ($status_text == 'Завершено')
                $status_class = 'status-completed';
            elseif ($status_text == 'Отменено')
                $status_class = 'status-cancelled';
            if ((int) $request['is_completed'] === 1)
                $status_class = 'status-completed';

            $venue = htmlspecialchars($request['curses']);
            $venue_icon = '';
            if (strpos($venue, 'Аудитория') !== false)
                $venue_icon = '🎓';
            elseif (strpos($venue, 'Коворкинг') !== false)
                $venue_icon = '💼';
            elseif (strpos($venue, 'Кинозал') !== false)
                $venue_icon = '🎬';
            else
                $venue_icon = '🏛️';

            echo '
            <div class="request">
                <h2>📄 Бронирование #' . $request['id'] . '</h2>
                <p><b>📅 Дата и время:</b> ' . htmlspecialchars($request['date']) . '</p>
                <p><b>' . $venue_icon . ' Тип помещения:</b> ' . $venue . '</p>
                <p><b>💳 Способ оплаты:</b> ' . htmlspecialchars($request['payment']) . '</p>
                <p><b>📊 Статус:</b> <span class="' . $status_class . '">' . $status_text . '</span></p>';

            if (!empty($request['additional_info'])) {
                echo '<div class="review-text"><b>📝 Дополнительная информация:</b> ' . htmlspecialchars($request['additional_info']) . '</div>';
            }

            if (!empty($request['review'])) {
                echo '<div class="review-text"><b>⭐ Отзыв о проведении:</b> ' . htmlspecialchars($request['review']) . '</div>';
            }

            if ((int) $request['is_completed'] === 1) {
                echo '
                <div class="review-form">
                    <form action="" method="POST">
                        <input type="hidden" name="request_id" value="' . $request['id'] . '">
                        <input type="text" name="review" placeholder="✍️ Оставьте отзыв о качестве организации конференции..." value="' . htmlspecialchars($request['review'] ?? '') . '">
                        <button type="submit">⭐ Оставить отзыв</button>
                    </form>
                </div>';
            }
            echo '</div>';
        }
        ?>

        <div class="create-button">
            <a href="create.php">🎤 Забронировать помещение</a>
        </div>
    </div>
    <script>
        (function () {
            const slider = document.getElementById('lkSlider');
            if (!slider) return;
            const slides = slider.querySelectorAll('.lk-slide');
            if (!slides.length) return;
            let index = 0;
            const showSlide = (newIndex) => {
                slides[index].classList.remove('active');
                index = (newIndex + slides.length) % slides.length;
                slides[index].classList.add('active');
            };

            const next = () => showSlide(index + 1);
            const prev = () => showSlide(index - 1);

            const prevBtn = document.getElementById('lkPrev');
            const nextBtn = document.getElementById('lkNext');
            if (prevBtn) prevBtn.addEventListener('click', prev);
            if (nextBtn) nextBtn.addEventListener('click', next);

            let timer = setInterval(next, 4000);
            slider.addEventListener('mouseenter', () => clearInterval(timer));
            slider.addEventListener('mouseleave', () => {
                timer = setInterval(next, 4000);
            });
        })();
    </script>
</body>

</html>