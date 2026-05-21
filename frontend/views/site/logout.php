<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Cerrada 👋</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            text-align: center;
            max-width: 500px;
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .icon-container {
            font-size: 70px;
            margin-bottom: 20px;
            display: inline-block;
            animation: wave 2.5s ease-in-out infinite;
            transform-origin: 70% 70%;
        }

        /* Animación divertida de la mano saludando */
        @keyframes wave {
            0% { transform: rotate( 0.0deg) }
            10% { transform: rotate(14.0deg) }
            20% { transform: rotate(-8.0deg) }
            30% { transform: rotate(14.0deg) }
            40% { transform: rotate(-4.0deg) }
            50% { transform: rotate(10.0deg) }
            60% { transform: rotate( 0.0deg) }
            100% { transform: rotate( 0.0deg) }
        }

        h1 {
            font-size: 2rem;
            color: #2f3542;
            margin-bottom: 15px;
        }

        p {
            font-size: 1.1rem;
            color: #57606f;
            line-height: 1.6;
            margin-bottom: 35px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: #2ed573;
            color: white;
            box-shadow: 0 5px 15px rgba(46, 213, 115, 0.3);
        }

        .btn-primary:hover {
            background-color: #26af5f;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #f1f2f6;
            color: #57606f;
        }

        .btn-secondary:hover {
            background-color: #e4e7ed;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .security-note {
            margin-top: 30px;
            font-size: 0.85rem;
            color: #a4b0be;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- El emoji tiene una animación de saludo integrada en el CSS -->
    <div class="icon-container">👋</div>

    <h1>¡Sesión cerrada con éxito!</h1>

    <p>
        Tu sesión se ha guardado y cerrado de forma segura. Gracias por pasar un rato con nosotros hoy. ¡Esperamos verte de nuevo muy pronto!
    </p>

    <p class="security-note">
        💡 Consejo de seguridad: Si estás en un equipo compartido, recuerda cerrar las pestañas del navegador.
    </p>
</div>

</body>
</html>
