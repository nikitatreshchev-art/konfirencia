<?php
session_start();
include('db.php');

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['admin']) && $_SESSION['admin']) {
        header('Location: admin.php');
    } else {
        header('Location: create.php');
    }
    exit;
}

$error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    
    if (empty($login) || empty($password)) {
        $error = true;
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = true;
            $error_message = 'Неверный логин или пароль';
        } else {
            $user = $result->fetch_assoc();

            $password_valid = false;

            if (password_verify($password, $user['password'])) {
                $password_valid = true;
            }
            elseif ($password === $user['password']) {
                $password_valid = true;
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            if (!$password_valid) {
                $error = true;
                $error_message = 'Неверный логин или пароль';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_fullname'] = $user['fullname'];

                if ($user['login'] == 'Admin26') {
                    $_SESSION['admin'] = true;
                    header('Location: admin.php');
                } else {
                    header('Location: create.php');
                }
                exit;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Конференции.РФ</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        .wave-bg {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 120px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(40,167,69,0.05)" fill-opacity="1" d="M0,192L48,197.3C96,203,192,213,288,208C384,203,480,181,576,181.3C672,181,768,203,864,208C960,213,1056,203,1152,186.7C1248,171,1344,149,1392,138.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') repeat-x;
            background-size: cover;
            animation: waveMove 12s linear infinite;
            z-index: 0;
        }

        @keyframes waveMove {
            0% { background-position-x: 0; }
            100% { background-position-x: 1440px; }
        }

        .container {
            max-width: 460px;
            width: 100%;
            background: var(--white);
            padding: 40px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            animation: slideInUp 0.5s ease-out;
            position: relative;
            z-index: 1;
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

        .logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.3px;
            color: var(--gray-dark);
            margin-bottom: 8px;
        }

        .logo p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }

        .form-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .form-header h2 {
            color: var(--gray-dark);
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 400;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 14px 18px;
            border-radius: 20px;
            margin-bottom: 24px;
            text-align: center;
            border-left: 4px solid #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 400;
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--gray-dark);
            font-size: 14px;
        }

        .form-group label span {
            margin-right: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--gray-light);
            border-radius: 16px;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            font-weight: 400;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.12);
        }

        .form-group input:hover {
            border-color: var(--green);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--green);
            color: var(--white);
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Roboto', sans-serif;
            font-weight: 600;
            transition: all 0.25s ease;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(40, 167, 69, 0.3);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .form-footer {
            margin-top: 28px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--gray-light);
        }

        .form-footer p {
            color: var(--text-muted);
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 400;
        }

        .register-link {
            color: var(--green);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .register-link:hover {
            color: #1e7e34;
            text-decoration: underline;
        }

        .back-home {
            display: inline-block;
            margin-top: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 300;
            transition: color 0.2s ease;
        }

        .back-home:hover {
            color: var(--green);
        }

        .form-group {
            animation: fadeInUp 0.4s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.05s; }
        .form-group:nth-child(2) { animation-delay: 0.1s; }
        .btn-login { animation: fadeInUp 0.4s ease-out 0.15s both; }
        .form-footer { animation: fadeInUp 0.4s ease-out 0.2s both; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 28px 20px;
            }
            .logo h1 {
                font-size: 24px;
            }
            .form-header h2 {
                font-size: 20px;
            }
            .btn-login {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="wave-bg"></div>
    
    <div class="container">
        <div class="logo">
            <h1>🎤 Конференции.РФ</h1>
            <p>Бронирование помещений для всероссийских конференций</p>
        </div>

        <div class="form-header">
            <h2>Добро пожаловать!</h2>
            <p>Войдите, чтобы забронировать аудиторию, коворкинг или кинозал</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <span>⚠️</span>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="login">
                    <span>👤</span> Логин
                </label>
                <input type="text" id="login" name="login" 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['login']) : ''; ?>"
                       placeholder="conf_organizer" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">
                    <span>🔒</span> Пароль
                </label>
                <input type="password" id="password" name="password" 
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                Войти в личный кабинет
            </button>
        </form>

        <div class="form-footer">
            <p>Нет аккаунта? <a href="register.php" class="register-link">Зарегистрироваться →</a></p>
            <a href="index.php" class="back-home">← Вернуться на главную</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const login = document.getElementById('login').value.trim();
                const password = document.getElementById('password').value;
                
                if (!login || !password) {
                    e.preventDefault();
                    showError('Пожалуйста, заполните все поля');
                    return;
                }
                
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '⏳ Вход...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                }, 5000);
            });
        }
        
        function showError(message) {
            const existingError = document.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<span>⚠️</span> ${message}`;
            
            const formHeader = document.querySelector('.form-header');
            formHeader.insertAdjacentElement('afterend', errorDiv);
            
            const container = document.querySelector('.container');
            container.style.animation = 'shakeError 0.4s ease-in-out';
            setTimeout(() => {
                container.style.animation = '';
            }, 400);
        }
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateX(3px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateX(0)';
            });
        });
        
        const savedLogin = localStorage.getItem('savedLoginConference');
        if (savedLogin && !document.getElementById('login').value) {
            document.getElementById('login').value = savedLogin;
        }
        
        form.addEventListener('submit', function() {
            const login = document.getElementById('login').value;
            localStorage.setItem('savedLoginConference', login);
        });
    </script>
</body>
</html>