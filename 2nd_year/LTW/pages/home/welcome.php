<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <style>
        :root {
            --purple-dark: #0D47A1;
            --purple-medium: #42A5F5;
            --purple-light: #90CAF9;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            padding: 0;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('../../assets/img/wallpaper.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        
        .container {
            max-width: 600px;
            width: 90%;
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            z-index: 1;
        }
        
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        
        .logo {
            width: 180px;
            height: auto;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        p {
            color: #555;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.3);
            position: relative;
            overflow: hidden;
            min-width: 150px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(106, 13, 173, 0.4);
        }
        
        .btn:active {
            transform: translateY(1px);
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .btn:hover::after {
            opacity: 1;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #1976D2, #64B5F6);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #1565C0, #1976D2);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                background: rgba(255, 255, 255, 0.95);
            }
            
            .logo {
                width: 140px;
            }
            
            .buttons {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="../../assets/img/image3.png" alt="Logo do Site" class="logo">
        <h1>Bem-vindo ao Nosso Site!</h1>
        <p>Por favor, faça login ou registre-se para aceder à sua conta.</p>
        
        <div class="buttons">
            <a href="../../pages/auth/login.php" class="btn btn-login">Login</a>
            <a href="../../pages/auth/register.php" class="btn btn-register">Registrar</a>
        </div>
    </div>
</body>
</html>