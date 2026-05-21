
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Zona Restringida! 🚧</title>
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
            max-width: 550px;
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .icon-container {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        h1 {
            font-size: 2rem;
            color: #ff4757;
            margin-bottom: 15px;
        }

        p {
            font-size: 1.1rem;
            color: #57606f;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background-color: #1e90ff;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s ease, transform 0.2s ease;
            box-shadow: 0 5px 15px rgba(30, 144, 255, 0.3);
        }

        .btn:hover {
            background-color: #117ee6;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        span{
            font-size: 4rem;
            color: #ff4757;
            margin-bottom: 15px;
        }

        /* Add this to your stylesheet */
        .gift-box {
            width: 100px;
            height: 100px;
            background: #ff4757;
            position: relative;
            cursor: pointer;
        }
        .gift-box::before {
            content: "";
            position: absolute;
            top: 0; left: 45px;
            width: 10px; height: 100%;
            background: #2ed573;
        }
        .gift-box:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Puedes cambiar este emoji por una ilustración o GIF divertido -->
    <div class="icon-container">🐕 <span>Alto ahi</span></div>

    <p>
        Nuestro perrito guardián dice que este lugar está reservado para gente especial.
    </p>

    <!-- Redirige al usuario al inicio o a la página anterior -->

    <div id="gift-container" ></div>

</div>

</body>
</html>

<script>
    // 1. Get the target div by its ID
    const giftDiv = document.getElementById("gift-container");

    // 2. Set the innerHTML to the gift's web URL
    giftDiv.innerHTML = '<img src="https://media2.giphy.com/media/v1.Y2lkPTc5MGI3NjExdng0YTllajFoZWM2NjM2Y3Jrc29oZXJraXp6NXA5d2U2YmNlbzZ0byZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/eROyQirLbyuNxioHKh/giphy.gif" ' +
        'alt="Animated Gift" style="width: 150px; height: auto;">';
</script>