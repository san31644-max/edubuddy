<?php
http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: white;
            padding: 20px;
        }

        .maintenance-box {
            width: 100%;
            max-width: 650px;
            text-align: center;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 55px 35px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
        }

        .icon {
            font-size: 75px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 42px;
            margin-bottom: 15px;
        }

        p {
            font-size: 17px;
            color: #cbd5e1;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .status {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(59,130,246,0.15);
            border: 1px solid #3b82f6;
            border-radius: 30px;
            color: #93c5fd;
            font-size: 14px;
            font-weight: bold;
        }

        .loader {
            width: 45px;
            height: 45px;
            border: 4px solid rgba(255,255,255,0.15);
            border-top: 4px solid white;
            border-radius: 50%;
            margin: 30px auto 0;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        footer {
            margin-top: 35px;
            font-size: 13px;
            color: #64748b;
        }

        @media (max-width: 600px) {
            .maintenance-box {
                padding: 40px 20px;
            }

            h1 {
                font-size: 30px;
            }

            .icon {
                font-size: 60px;
            }
        }
    </style>
</head>

<body>

<div class="maintenance-box">

    <div class="icon">🛠️</div>

    <h1>We'll Be Back Soon</h1>

    <p>
        Our website is currently undergoing scheduled maintenance
        to improve your experience.
        <br><br>
        We apologize for the inconvenience and appreciate your patience.
    </p>

    <div class="status">
        SYSTEM MAINTENANCE IN PROGRESS
    </div>

    <div class="loader"></div>

    <footer>
        &copy; <?php echo date('Y'); ?> Your Website Name. All rights reserved.
    </footer>

</div>

</body>
</html>