<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .split-screen {
            display: flex;
            height: 100vh;
        }

        /* Left Section - Form Area */
        .left-section {
            flex: 1;
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Login Card */
        .login-card {
            background: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 3rem;
            width: 100%;
            max-width: 420px;
        }

        /* Right Section - Hero Image */
        .right-section {
            flex: 1;
            overflow: hidden;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Typography */
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .login-subtitle {
            font-size: 1rem;
            font-weight: 400;
            color: #4B5563;
            margin-bottom: 2.5rem;
            text-align: center;
        }

        /* Form Styles */
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            background-color: #F3F4F6;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background-color: #F3F4F6;
            border-color: #1D4ED8;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        /* Button */
        .btn-signin {
            background-color: #1D4ED8;
            border: none;
            border-radius: 8px;
            color: #FFFFFF;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.875rem 1.5rem;
            width: 100%;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-signin:hover {
            background-color: #1E40AF;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-signin:active {
            transform: translateY(0);
        }

        /* Form spacing */
        .form-group {
            margin-bottom: 1.5rem;
        }

        /* Alert styles */
        .alert {
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
        }

        .alert-success {
            background-color: #F0FDF4;
            color: #166534;
        }

        /* Link styles */
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-link a {
            color: #1D4ED8;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .split-screen {
                flex-direction: column;
            }

            .right-section {
                display: none;
            }

            .left-section {
                padding: 1rem;
            }

            .login-card {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Left Section - Form Area -->
        <div class="left-section">
            <div class="login-card">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <h1 class="login-title">Masuk Akun</h1>
                <p class="login-subtitle">Web Peminjaman Inventaris Alat di Laboratorium</p>

                <form method="POST" action="/login">
                    <div class="form-group">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               placeholder="Masukkan alamat email Anda"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Masukkan kata sandi Anda"
                               required>
                    </div>

                    <button type="submit" class="btn btn-signin">
                        Masuk
                    </button>
                </form>

                <div class="register-link">
                    Belum punya akun? <a href="/register">Daftar di sini</a>
                </div>
            </div>
        </div>

        <!-- Right Section - Hero Image -->
        <div class="right-section">
            <img src="/images/auth-filler.webp" alt="Gedung Laboratorium" class="hero-image">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
