<?php
session_start();


if (isset($_GET['logout'])) {
  session_destroy();
  header('Location: index.php');
  exit;
}


$is_admin = isset($_SESSION['admin']) && $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Конференции.РФ — бронирование помещений для конференций</title>

  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>

    :root {
      --gray-dark: #343A40;
     
      --gray-light: #CED4DA;
     
      --green: #28A745;
   
      --white: #FFFFFF;


    
      --gray-bg: #F4F6F8;
      --shadow-sm: 0 8px 20px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background-color: var(--gray-bg);
      color: var(--gray-dark);
      line-height: 1.5;
      min-height: 100vh;
    }

   
    h1 {
      font-size: 36px;
      font-weight: 700;
      color: var(--gray-dark);
      letter-spacing: -0.3px;
    }

    h2,
    h3 {
      font-weight: 600;
      color: var(--gray-dark);
    }

    h2 {
      font-size: 24px;
    }

    h3 {
      font-size: 18px;
    }

    p,
    .main-text,
    .feature-card p,
    .nav-buttons a {
      font-size: 16px;
      font-weight: 400;
    }

    .small-text,
    .footer,
    .slide-text,
    .dot-container,
    .text-muted {
      font-size: 12px;
      font-weight: 300;
    }

    .header {
      background: var(--white);
      border-bottom: 3px solid var(--green);
      box-shadow: var(--shadow-sm);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 16px 24px;
    }

    .logo {
      font-size: 26px;
      font-weight: 700;
      color: var(--gray-dark);
      text-decoration: none;
      letter-spacing: -0.2px;
      transition: color 0.2s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logo:hover {
      color: var(--green);
    }

    .nav-buttons {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .nav-buttons a {
      padding: 8px 20px;
      border-radius: 40px;
      font-size: 16px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s ease;
      border: 1.5px solid transparent;
    }

    .btn-login,
    .btn-register {
      background: transparent;
      border-color: var(--green);
      color: var(--green);
    }

    .btn-login:hover,
    .btn-register:hover {
      background: var(--green);
      color: var(--white);
    }

    .btn-admin,
    .btn-lk,
    .btn-create {
      background: transparent;
      border-color: var(--green);
      color: var(--green);
    }

    .btn-admin:hover,
    .btn-lk:hover,
    .btn-create:hover {
      background: var(--green);
      color: var(--white);
    }

    .btn-exit {
      background: transparent;
      border-color: var(--gray-light);
      color: var(--gray-dark);
    }

    .btn-exit:hover {
      background: var(--gray-dark);
      border-color: var(--gray-dark);
      color: var(--white);
    }

    .slideshow-container {
      max-width: 1100px;
      position: relative;
      margin: 40px auto 20px;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: var(--shadow-md);
      background: var(--white);
    }

    .mySlides {
      display: none;
    }

    .fade {
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0.4;
      }

      to {
        opacity: 1;
      }
    }

    .mySlides img {
      width: 100%;
      height: 440px;
      object-fit: cover;
      display: block;
    }

    .slide-text {
      position: absolute;
      bottom: 24px;
      left: 24px;
      background: rgba(52, 58, 64, 0.85);
      backdrop-filter: blur(4px);
      padding: 10px 20px;
      border-radius: 40px;
      font-size: 14px;
      font-weight: 500;
      color: var(--white);
      letter-spacing: 0.3px;
    }

    .prev,
    .next {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(52, 58, 64, 0.7);
      color: var(--white);
      border: none;
      cursor: pointer;
      padding: 12px 18px;
      font-size: 20px;
      border-radius: 50%;
      transition: 0.2s;
      font-weight: 500;
      width: 44px;
      text-align: center;
    }

    .prev {
      left: 16px;
    }

    .next {
      right: 16px;
    }

    .prev:hover,
    .next:hover {
      background: var(--green);
    }

    .dot-container {
      text-align: center;
      padding: 20px 0 12px;
    }

    .dot {
      cursor: pointer;
      height: 10px;
      width: 10px;
      margin: 0 6px;
      background-color: var(--gray-light);
      border-radius: 50%;
      display: inline-block;
      transition: 0.2s;
    }

    .dot.active,
    .dot:hover {
      background-color: var(--green);
      transform: scale(1.2);
    }

    .features-section {
      max-width: 1200px;
      margin: 48px auto;
      padding: 0 24px;
    }

    .features-title {
      text-align: center;
      margin-bottom: 48px;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 32px;
    }

    .feature-card {
      background: var(--white);
      padding: 32px 24px;
      border-radius: 28px;
      text-align: center;
      box-shadow: var(--shadow-sm);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      border: 1px solid var(--gray-light);
    }

    .feature-card:hover {
      transform: translateY(-6px);
      box-shadow: var(--shadow-md);
      border-color: var(--green);
    }

    .feature-icon {
      font-size: 48px;
      margin-bottom: 18px;
      display: inline-block;
    }

    .feature-card h3 {
      margin-bottom: 12px;
      color: var(--gray-dark);
    }

    .feature-card p {
      color: #495057;
      margin-bottom: 20px;
    }

    .btn-demo {
      display: inline-block;
      background: transparent;
      border: 1px solid var(--green);
      color: var(--green);
      padding: 8px 24px;
      border-radius: 60px;
      font-weight: 500;
      font-size: 14px;
      text-decoration: none;
      transition: 0.2s;
    }

    .btn-demo:hover {
      background: var(--green);
      color: var(--white);
    }

    .info-banner {
      max-width: 1100px;
      margin: 32px auto;
      padding: 0 24px;
    }

    .info-title {
      text-align: center;
      margin-bottom: 20px;
    }

    .info-title h2 {
      font-size: 24px;
      margin-bottom: 8px;
    }

    .info-title p {
      font-size: 16px;
      color: #5a6268;
    }

    .info-gallery {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
    }

    .info-card {
      background: var(--white);
      border: 1px solid var(--gray-light);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .info-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
      border-color: var(--green);
    }

    .info-card img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      display: block;
    }

    .info-card-text {
      padding: 12px 14px 14px;
    }

    .info-card-text h3 {
      font-size: 16px;
      margin-bottom: 6px;
      color: var(--gray-dark);
    }

    .info-card-text p {
      font-size: 14px;
      color: #5a6268;
    }

    .footer {
      text-align: center;
      padding: 28px 20px;
      background: var(--white);
      border-top: 1px solid var(--gray-light);
      font-size: 12px;
      font-weight: 300;
      color: #6c757d;
      margin-top: 48px;
    }

    @media (max-width: 768px) {
      .nav {
        flex-direction: column;
        gap: 12px;
      }

      .mySlides img {
        height: 260px;
      }

      .slide-text {
        font-size: 11px;
        bottom: 12px;
        left: 12px;
        padding: 6px 12px;
      }

      .prev,
      .next {
        padding: 6px 12px;
        width: 36px;
        font-size: 16px;
      }

      .features-title h1 {
        font-size: 28px;
      }

      .info-gallery {
        grid-template-columns: 1fr 1fr;
      }

      .feature-card {
        padding: 24px 18px;
      }
    }

    @media (max-width: 520px) {
      .info-gallery {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>

<body>

  <header class="header">
    <div class="nav">
      <a href="index.php" class="logo">🎤 Конференции.РФ</a>
      <div class="nav-buttons">
        <?php if (!isset($_SESSION['user_id'])): ?>
          <a href="login.php" class="btn-login">Войти</a>
          <a href="register.php" class="btn-register">Регистрация</a>
        <?php elseif ($is_admin): ?>
          <a href="admin.php" class="btn-admin">Панель администратора</a>
          <a href="?logout=1" class="btn-exit">Выход</a>
        <?php elseif (isset($_SESSION['user_id'])): ?>
          <a href="history.php" class="btn-lk">Мои бронирования</a>
          <a href="create.php" class="btn-create">Новая заявка</a>
          <a href="?logout=1" class="btn-exit">Выход</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="slideshow-container">
    <div class="mySlides fade">
      <img src="assets/assets (1).jpg" alt="Современная аудитория для конференций">
      <div class="slide-text">🎓 Аудитория на 150 мест — полное оснащение</div>
    </div>

    <div class="mySlides fade">
      <img src="assets/assets (17).jpg" alt="Коворкинг-центр">
      <div class="slide-text">💼 Коворкинг: гибкое пространство для воркшопов</div>
    </div>

    <div class="mySlides fade">
      <img src="assets/kinozal.jpg" alt="Кинозал с проектором">
      <div class="slide-text">🎬 Кинозал премиум-класса с акустикой</div>
    </div>

    <div class="mySlides fade">
      <img src="assets/plenar-zal (1).jpg" alt="Зал для пленарных заседаний">
      <div class="slide-text">🏛️ Пленарный зал — идеально для всероссийских конференций</div>
    </div>

    <a class="prev" onclick="plusSlides(-1)">❮</a>
    <a class="next" onclick="plusSlides(1)">❯</a>
  </div>

  <div class="dot-container">
    <span class="dot" onclick="currentSlide(1)"></span>
    <span class="dot" onclick="currentSlide(2)"></span>
    <span class="dot" onclick="currentSlide(3)"></span>
    <span class="dot" onclick="currentSlide(4)"></span>
  </div>

  <section class="features-section">
    <div class="features-title">
      <h1>Выберите площадку для вашей конференции</h1>
      <p style="font-size: 18px; margin-top: 12px; color: #5a6268;">Актовые залы, современные коворкинги и кинозалы —
        бронируйте онлайн</p>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🏛️</div>
        <h3>Аудитория / Конференц-зал</h3>
        <p>Вместимость до 200 человек. Проектор, акустика, трибуна, флипчарты. Идеально для пленарных заседаний и
          научных секций.</p>
        <a href="create.php" class="btn-demo">Забронировать</a>
      </div>

      <div class="feature-card">
        <div class="feature-icon">💻</div>
        <h3>Коворкинг</h3>
        <p>Гибкое пространство для воркшопов, круглых столов и нетворкинга. Wi-Fi, зоны отдыха, кухня. Модульная мебель.
        </p>
        <a href="create.php" class="btn-demo">Забронировать</a>
      </div>

      <div class="feature-card">
        <div class="feature-icon">🎥</div>
        <h3>Кинозал</h3>
        <p>Панорамный экран, многоканальный звук, удобные кресла. Для презентаций, кинопоказов и торжественных
          церемоний.</p>
        <a href="create.php" class="btn-demo">Забронировать</a>
      </div>
    </div>
  </section>

  <section class="info-banner">
    <div class="info-title">
      <h2>Система бронирования помещений</h2>
      <p>Удобно • Быстро • Прозрачно</p>
    </div>
    <div class="info-gallery">
      <article class="info-card">
        <img src="assets/assets (2).png" alt="Выбор зала">
        <div class="info-card-text">
          <h3>Выбор зала</h3>
          <p>Подберите формат площадки под масштаб и программу мероприятия.</p>
        </div>
      </article>
      <article class="info-card">
        <img src="assets/assets (16).jpg" alt="Онлайн-заявка">
        <div class="info-card-text">
          <h3>Онлайн-заявка</h3>
          <p>Оформляйте бронирование за пару минут без звонков и очередей.</p>
        </div>
      </article>
      <article class="info-card">
        <img src="assets/assets (8).jpg" alt="Подтверждение администратором">
        <div class="info-card-text">
          <h3>Подтверждение</h3>
          <p>Администратор проверяет заявку и фиксирует статус в личном кабинете.</p>
        </div>
      </article>
      <article class="info-card">
        <img src="assets/assets (14).jpg" alt="Проведение конференции">
        <div class="info-card-text">
          <h3>Проведение события</h3>
          <p>Команда площадки помогает провести конференцию стабильно и чётко.</p>
        </div>
      </article>
    </div>
  </section>

  <footer class="footer">
    © 2025 Конференции.РФ — информационная система бронирования помещений для проведения всероссийских конференций:
    аудитории, коворкинги, кинозалы.
  </footer>

  <script>
    let slideIndex = 1;
    showSlides(slideIndex);

    function plusSlides(n) {
      showSlides(slideIndex += n);
    }

    function currentSlide(n) {
      showSlides(slideIndex = n);
    }

    function showSlides(n) {
      let slides = document.getElementsByClassName("mySlides");
      let dots = document.getElementsByClassName("dot");

      if (n > slides.length) { slideIndex = 1; }
      if (n < 1) { slideIndex = slides.length; }

      for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
      }
      for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
      }

      if (slides[slideIndex - 1]) slides[slideIndex - 1].style.display = "block";
      if (dots[slideIndex - 1]) dots[slideIndex - 1].className += " active";
    }

    let slideInterval = setInterval(() => plusSlides(1), 4000);

    const container = document.querySelector('.slideshow-container');
    if (container) {
      container.addEventListener('mouseenter', () => clearInterval(slideInterval));
      container.addEventListener('mouseleave', () => {
        slideInterval = setInterval(() => plusSlides(1), 4000);
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      showSlides(slideIndex);
    });
  </script>
</body>

</html>