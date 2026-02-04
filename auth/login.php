<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];
    
    if (!empty($username_email) && !empty($password)) {
        $conn = getDBConnection();
        
        // Check if input is email or username
        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'Active'");
        $stmt->bind_param("ss", $username_email, $username_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department_id'] = $user['department_id'];
                $_SESSION['full_name'] = trim($user['f_name'] . ' ' . $user['l_name']);
                
                // Redirect to dashboard
                header('Location: ' . BASE_URL . 'pages/dashboard.php');
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        } else {
            $error = 'Invalid username/email or password.';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NEECO Ticketing System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Animated Login Styles */
        .animated-login-container {
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Characters Container */
        .characters-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 40px;
        }
        
        .characters-stage {
            position: relative;
            width: 100%;
            max-width: 500px;
            height: 450px;
        }
        
        /* Individual Character */
        .character {
            position: absolute;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            cursor: pointer;
        }
        
        .character.bouncing {
            animation: bounce 2s ease-in-out infinite;
        }
        
        /* Character 1 - Transformer (Top Left) - LARGEST */
        .character-1 {
            left: 5%;
            top: 8%;
            width: 140px;
            height: 140px;
            z-index: 4;
        }
        
        .character-1 .body {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #1976D2 0%, #0D47A1 100%);
            border-radius: 15px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .character-1 .coils {
            position: absolute;
            width: 100%;
            height: 40%;
            top: 15%;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0 20px;
        }
        
        .coil {
            width: 25px;
            height: 35px;
            border: 4px solid #FFC107;
            border-radius: 8px;
            background: transparent;
        }
        
        .terminals {
            position: absolute;
            top: 5px;
            width: 100%;
            display: flex;
            justify-content: space-around;
            padding: 0 15px;
        }
        
        .terminal {
            width: 8px;
            height: 15px;
            background: #424242;
            border-radius: 2px;
        }
        
        /* Voltage indicators */
        .voltage-indicator {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 14px;
            font-weight: bold;
            color: #FFC107;
            text-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
            transition: all 0.3s ease;
        }
        
        .voltage-indicator.high {
            font-size: 18px;
            color: #FF5252;
            text-shadow: 0 0 10px rgba(255, 82, 82, 0.8);
            animation: voltage-high 0.5s ease-in-out infinite;
        }
        
        .voltage-indicator.low {
            font-size: 12px;
            color: #4CAF50;
            text-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }
        
        @keyframes voltage-high {
            0%, 100% { transform: translateX(-50%) scale(1); }
            50% { transform: translateX(-50%) scale(1.1); }
        }
        
        /* Character 2 - Light Bulb (Top Right) - MEDIUM */
        .character-2 {
            right: 8%;
            top: 15%;
            width: 110px;
            height: 110px;
            z-index: 3;
        }
        
        .character-2 .body {
            width: 110px;
            height: 110px;
            background: transparent;
            position: relative;
        }
        
        .bulb-glass {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #FFF9C4 0%, #FFF176 100%);
            border-radius: 50% 50% 45% 45%;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 5px 20px rgba(255, 193, 7, 0.5),
                        inset 0 -10px 20px rgba(255, 152, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .bulb-glass.on {
            background: linear-gradient(135deg, #FFFDE7 0%, #FFF59D 100%);
            box-shadow: 0 0 30px rgba(255, 235, 59, 0.8),
                        0 0 50px rgba(255, 193, 7, 0.5),
                        inset 0 -10px 20px rgba(255, 152, 0, 0.2);
        }
        
        .bulb-glass.off {
            background: linear-gradient(135deg, #E0E0E0 0%, #BDBDBD 100%);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2),
                        inset 0 -10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .bulb-filament {
            width: 20px;
            height: 30px;
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .bulb-filament::before,
        .bulb-filament::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 15px;
            background: #FF6F00;
            border-radius: 2px;
        }
        
        .bulb-filament::before {
            left: 5px;
            transform: rotate(-15deg);
        }
        
        .bulb-filament::after {
            right: 5px;
            transform: rotate(15deg);
        }
        
        .bulb-base {
            width: 40px;
            height: 30px;
            background: linear-gradient(135deg, #9E9E9E 0%, #757575 100%);
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 0 0 5px 5px;
        }
        
        .bulb-screw {
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                0deg,
                #757575 0px,
                #757575 4px,
                #616161 4px,
                #616161 8px
            );
            border-radius: 0 0 5px 5px;
        }
        
        /* Character 3 - Switch (Bottom Left) - SMALL */
        .character-3 {
            left: 12%;
            bottom: 18%;
            width: 90px;
            height: 90px;
            z-index: 2;
        }
        
        .character-3 .body {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #66BB6A 0%, #388E3C 100%);
            border-radius: 20px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .switch-base {
            position: absolute;
            width: 50px;
            height: 30px;
            background: white;
            border-radius: 15px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .switch-toggle {
            position: absolute;
            width: 24px;
            height: 24px;
            background: #4CAF50;
            border-radius: 50%;
            top: 3px;
            right: 3px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .switch-toggle.off {
            background: #9E9E9E;
            right: auto;
            left: 3px;
        }
        
        /* Character 4 - Battery (Bottom Right) - MEDIUM-LARGE */
        .character-4 {
            right: 10%;
            bottom: 12%;
            width: 120px;
            height: 120px;
            z-index: 1;
        }
        
        .character-4 .body {
            width: 80px;
            height: 100px;
            background: linear-gradient(135deg, #E53935 0%, #C62828 100%);
            border-radius: 12px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin: 20px auto 0;
        }
        
        .battery-top {
            position: absolute;
            width: 40px;
            height: 15px;
            background: #D32F2F;
            border-radius: 5px 5px 0 0;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .battery-level {
            position: absolute;
            bottom: 5px;
            left: 5px;
            right: 5px;
            height: 70%;
            background: linear-gradient(to top, #4CAF50 0%, #8BC34A 100%);
            border-radius: 5px;
            transition: height 0.3s ease;
        }
        
        .battery-plus {
            position: absolute;
            width: 30px;
            height: 4px;
            background: white;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .battery-plus::after {
            content: '';
            position: absolute;
            width: 4px;
            height: 30px;
            background: white;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        /* Battery warning exclamation */
        .battery-warning {
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .battery-warning.show {
            opacity: 1;
            animation: warning-pulse 1s ease-in-out infinite;
        }
        
        .exclamation {
            width: 8px;
            height: 20px;
            background: #FF5252;
            border-radius: 2px;
            position: relative;
            margin: 0 auto;
        }
        
        .exclamation::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            background: #FF5252;
            border-radius: 50%;
            bottom: -12px;
            left: 0;
        }
        
        @keyframes warning-pulse {
            0%, 100% { transform: translateX(-50%) scale(1); }
            50% { transform: translateX(-50%) scale(1.2); }
        }
        
        /* Bulb flicker animation */
        @keyframes bulb-flicker {
            0%, 100% { opacity: 0.3; }
            10% { opacity: 1; }
            20% { opacity: 0.3; }
            30% { opacity: 1; }
            40% { opacity: 0.4; }
            50% { opacity: 1; }
            60% { opacity: 0.3; }
            70% { opacity: 0.8; }
            80% { opacity: 0.3; }
            90% { opacity: 1; }
        }
        
        .bulb-glass.flickering {
            animation: bulb-flicker 0.8s ease-in-out;
        }
        
        /* Eyes - Positioned differently for each character */
        .eyes {
            position: absolute;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 20px;
            transition: all 0.3s ease;
        }
        
        /* Transformer eyes */
        .character-1 .eyes {
            top: 60%;
            gap: 25px;
        }
        
        /* Light bulb eyes */
        .character-2 .eyes {
            top: 50px;
            gap: 18px;
        }
        
        /* Switch eyes */
        .character-3 .eyes {
            top: 22%;
            gap: 15px;
        }
        
        /* Battery eyes */
        .character-4 .eyes {
            top: 45%;
            gap: 18px;
        }
        
        .eye {
            width: 14px;
            height: 14px;
            background: white;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
        }
        
        .character-1 .eye {
            width: 16px;
            height: 16px;
        }
        
        .character-3 .eye {
            width: 12px;
            height: 12px;
        }
        
        .pupil {
            width: 7px;
            height: 7px;
            background: #333;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.1s ease;
        }
        
        .character-1 .pupil {
            width: 8px;
            height: 8px;
        }
        
        .character-3 .pupil {
            width: 6px;
            height: 6px;
        }
        
        /* Pupils looking left */
        .eyes.looking-left .pupil {
            transform: translate(-80%, -50%) !important;
        }
        
        /* Mouth */
        .mouth {
            position: absolute;
            width: 35px;
            height: 18px;
            border: 3px solid white;
            border-top: none;
            border-radius: 0 0 35px 35px;
            left: 50%;
            transform: translateX(-50%);
            transition: all 0.3s ease;
        }
        
        /* Transformer mouth */
        .character-1 .mouth {
            top: 78%;
            width: 40px;
        }
        
        /* Light bulb mouth */
        .character-2 .mouth {
            top: 70px;
            width: 30px;
        }
        
        /* Switch mouth */
        .character-3 .mouth {
            top: 68%;
            width: 28px;
        }
        
        /* Battery mouth */
        .character-4 .mouth {
            top: 63%;
            width: 32px;
        }
        
        /* Mouth variations */
        .mouth.smile {
            border-radius: 0 0 35px 35px;
        }
        
        .mouth.surprised {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 3px solid white;
        }
        
        .mouth.awkward {
            border-radius: 35px 35px 0 0;
            border-top: 3px solid white;
            border-bottom: none;
            transform: translateX(-50%) rotate(15deg);
        }
        
        .mouth.whistle {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 3px solid white;
            background: transparent;
        }
        
        /* Special effects */
        .sweat-drop {
            position: absolute;
            width: 10px;
            height: 14px;
            background: #4FC3F7;
            border-radius: 50% 50% 50% 0;
            top: 15%;
            right: 10%;
            transform: rotate(-45deg);
            opacity: 0;
            animation: sweat 1s ease-in-out infinite;
        }
        
        .music-note {
            position: absolute;
            font-size: 24px;
            color: white;
            opacity: 0;
            animation: float-note 2s ease-in-out infinite;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        
        .music-note:nth-child(1) {
            top: -25px;
            right: 15px;
            animation-delay: 0s;
        }
        
        .music-note:nth-child(2) {
            top: -35px;
            right: 35px;
            animation-delay: 0.5s;
        }
        
        /* Login Form Container */
        .login-form-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        
        .login-box-animated {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 450px;
            position: relative;
        }
        
        .login-logo-animated {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-logo-animated h1 {
            color: var(--primary-green);
            margin-bottom: 5px;
            font-size: 32px;
        }
        
        .login-logo-animated p {
            color: var(--medium-gray);
            font-size: 16px;
        }
        
        /* Character states */
        .character.looking-right .eyes {
            transform: translateX(10px);
        }
        
        .character.looking-up .eyes {
            transform: translateY(-5px);
        }
        
        .character.stretching .body {
            transform: scaleX(1.05);
        }
        
        .character.turned-around {
            transform: rotateY(180deg);
        }
        
        /* Animations */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes sweat {
            0% { opacity: 0; top: 15%; }
            50% { opacity: 1; }
            100% { opacity: 0; top: 35%; }
        }
        
        @keyframes float-note {
            0% { opacity: 0; transform: translateY(0) scale(0.8); }
            50% { opacity: 1; transform: translateY(-15px) scale(1); }
            100% { opacity: 0; transform: translateY(-30px) scale(0.8); }
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .animated-login-container {
                flex-direction: column;
            }
            
            .characters-container {
                height: 350px;
            }
            
            .characters-stage {
                height: 300px;
                max-width: 400px;
            }
            
            .character-1 {
                width: 110px;
                height: 110px;
            }
            
            .character-1 .body {
                width: 110px;
                height: 110px;
            }
            
            .character-2 {
                width: 90px;
                height: 90px;
            }
            
            .character-2 .body {
                width: 90px;
                height: 90px;
            }
            
            .character-3 {
                width: 75px;
                height: 75px;
            }
            
            .character-3 .body {
                width: 75px;
                height: 75px;
            }
            
            .character-4 {
                width: 100px;
                height: 100px;
            }
            
            .character-4 .body {
                width: 65px;
                height: 85px;
            }
        }
        
        /* Alert styling for animated page */
        .alert-animated {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-animated.alert-danger {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
    </style>
</head>
<body>
    <div class="animated-login-container">
        <!-- Characters Side -->
        <div class="characters-container">
            <div class="characters-stage">
                <!-- Character 1 - Transformer (Largest) -->
                <div class="character character-1 bouncing" id="char1">
                    <div class="body">
                        <div class="terminals">
                            <div class="terminal"></div>
                            <div class="terminal"></div>
                            <div class="terminal"></div>
                        </div>
                        <div class="coils">
                            <div class="coil"></div>
                            <div class="coil"></div>
                            <div class="coil"></div>
                        </div>
                        <div class="voltage-indicator" id="voltageIndicator">220V</div>
                        <div class="eyes">
                            <div class="eye"><div class="pupil" id="pupil1-1"></div></div>
                            <div class="eye"><div class="pupil" id="pupil1-2"></div></div>
                        </div>
                        <div class="mouth smile"></div>
                        <div class="sweat-drop" style="display: none;"></div>
                    </div>
                </div>
                
                <!-- Character 2 - Light Bulb (Medium) -->
                <div class="character character-2 bouncing" id="char2">
                    <div class="body">
                        <div class="bulb-glass on" id="bulbGlass">
                            <div class="bulb-filament"></div>
                        </div>
                        <div class="bulb-base">
                            <div class="bulb-screw"></div>
                        </div>
                        <div class="eyes">
                            <div class="eye"><div class="pupil" id="pupil2-1"></div></div>
                            <div class="eye"><div class="pupil" id="pupil2-2"></div></div>
                        </div>
                        <div class="mouth smile"></div>
                        <div class="music-note" style="display: none;">♪</div>
                        <div class="music-note" style="display: none;">♫</div>
                    </div>
                </div>
                
                <!-- Character 3 - Switch (Small) -->
                <div class="character character-3 bouncing" id="char3">
                    <div class="body">
                        <div class="eyes">
                            <div class="eye"><div class="pupil" id="pupil3-1"></div></div>
                            <div class="eye"><div class="pupil" id="pupil3-2"></div></div>
                        </div>
                        <div class="switch-base">
                            <div class="switch-toggle" id="switchToggle"></div>
                        </div>
                        <div class="mouth smile"></div>
                    </div>
                </div>
                
                <!-- Character 4 - Battery (Medium-Large) -->
                <div class="character character-4 bouncing" id="char4">
                    <div class="body">
                        <div class="battery-top"></div>
                        <div class="battery-level" id="batteryLevel"></div>
                        <div class="battery-plus"></div>
                        <div class="battery-warning" id="batteryWarning">
                            <div class="exclamation"></div>
                        </div>
                        <div class="eyes">
                            <div class="eye"><div class="pupil" id="pupil4-1"></div></div>
                            <div class="eye"><div class="pupil" id="pupil4-2"></div></div>
                        </div>
                        <div class="mouth smile"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Login Form Side -->
        <div class="login-form-container">
            <div class="login-box-animated">
                <div class="login-logo-animated">
                    <h1>NEECO II - Area 1</h1>
                    <p>Ticketing System</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert-animated alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username_email" class="required">Username or Email</label>
                        <input type="text" id="username_email" name="username_email" class="form-control" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <div class="password-toggle">
                            <input type="password" id="password" name="password" class="form-control" required>
                            <button type="button" class="password-toggle-btn" id="showPasswordBtn" onclick="togglePasswordAnimated()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </button>
                </form>
                
                <p style="text-align: center; margin-top: 30px; color: var(--medium-gray); font-size: 13px;">
                    © 2026 NEECO II - Area 1. All rights reserved.
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Character animation controller
        const characters = document.querySelectorAll('.character');
        const usernameField = document.getElementById('username_email');
        const passwordField = document.getElementById('password');
        const showPasswordBtn = document.getElementById('showPasswordBtn');
        const switchToggle = document.getElementById('switchToggle');
        const bulbGlass = document.getElementById('bulbGlass');
        const voltageIndicator = document.getElementById('voltageIndicator');
        const batteryLevel = document.getElementById('batteryLevel');
        const batteryWarning = document.getElementById('batteryWarning');
        let passwordVisible = false;
        let mouseTrackingEnabled = true;
        let bulbFlickerInterval = null;
        let batteryGlanceInterval = null;
        
        // Random bulb flicker when password is shown
        function startBulbFlicker() {
            bulbFlickerInterval = setInterval(() => {
                if (passwordVisible) {
                    bulbGlass.classList.add('flickering');
                    setTimeout(() => {
                        bulbGlass.classList.remove('flickering');
                    }, 800);
                }
            }, Math.random() * 3000 + 2000); // Random interval between 2-5 seconds
        }
        
        function stopBulbFlicker() {
            if (bulbFlickerInterval) {
                clearInterval(bulbFlickerInterval);
                bulbFlickerInterval = null;
            }
        }
        
        // Battery periodic glance at password field
        function startBatteryGlance() {
            const char4 = document.getElementById('char4');
            const eyes4 = char4.querySelector('.eyes');
            const mouth4 = char4.querySelector('.mouth');
            
            batteryGlanceInterval = setInterval(() => {
                if (passwordVisible) {
                    // Quick peek to the right (password field)
                    eyes4.classList.remove('looking-left');
                    eyes4.style.transform = 'translateX(10px)';
                    mouth4.classList.remove('smile', 'awkward', 'whistle', 'surprised');
                    mouth4.classList.add('surprised');
                    
                    // Quickly look back left after 300ms
                    setTimeout(() => {
                        eyes4.style.transform = '';
                        eyes4.classList.add('looking-left');
                        mouth4.classList.remove('surprised');
                        mouth4.classList.add('awkward');
                    }, 300);
                }
            }, Math.random() * 4000 + 3000); // Random interval between 3-7 seconds
        }
        
        function stopBatteryGlance() {
            if (batteryGlanceInterval) {
                clearInterval(batteryGlanceInterval);
                batteryGlanceInterval = null;
            }
        }
        
        // Mouse tracking for eye movement (only when enabled)
        document.addEventListener('mousemove', (e) => {
            if (!mouseTrackingEnabled) return;
            
            const stage = document.querySelector('.characters-stage');
            const rect = stage.getBoundingClientRect();
            
            characters.forEach((char, index) => {
                const charRect = char.getBoundingClientRect();
                const charCenterX = charRect.left + charRect.width / 2;
                const charCenterY = charRect.top + charRect.height / 2;
                
                const pupils = char.querySelectorAll('.pupil');
                
                pupils.forEach(pupil => {
                    const angle = Math.atan2(e.clientY - charCenterY, e.clientX - charCenterX);
                    const distance = Math.min(3, Math.hypot(e.clientX - charCenterX, e.clientY - charCenterY) / 100);
                    
                    const x = Math.cos(angle) * distance;
                    const y = Math.sin(angle) * distance;
                    
                    pupil.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;
                });
            });
        });
        
        // Username field focus
        usernameField.addEventListener('focus', () => {
            if (!mouseTrackingEnabled) return;
            
            characters.forEach(char => {
                char.classList.add('looking-right', 'stretching');
                const mouth = char.querySelector('.mouth');
                mouth.classList.remove('smile', 'surprised', 'awkward', 'whistle');
                mouth.classList.add('surprised');
            });
        });
        
        usernameField.addEventListener('blur', () => {
            if (!mouseTrackingEnabled) return;
            
            characters.forEach(char => {
                char.classList.remove('looking-right', 'stretching');
                const mouth = char.querySelector('.mouth');
                mouth.classList.remove('surprised');
                mouth.classList.add('smile');
            });
        });
        
        // Password field focus
        passwordField.addEventListener('focus', () => {
            if (!mouseTrackingEnabled) return;
            
            // First, they look at each other (center)
            characters.forEach(char => {
                char.classList.add('looking-up');
                const mouth = char.querySelector('.mouth');
                mouth.classList.remove('smile', 'surprised', 'awkward', 'whistle');
            });
            
            // Then they nod (quick up and down)
            setTimeout(() => {
                characters.forEach(char => {
                    char.style.transform = 'translateY(-5px)';
                });
                
                setTimeout(() => {
                    characters.forEach(char => {
                        char.style.transform = 'translateY(0)';
                    });
                    
                    // Then look at password field
                    setTimeout(() => {
                        characters.forEach(char => {
                            char.classList.remove('looking-up');
                            char.classList.add('looking-right', 'stretching');
                            const mouth = char.querySelector('.mouth');
                            mouth.classList.add('smile');
                        });
                    }, 200);
                }, 200);
            }, 300);
        });
        
        passwordField.addEventListener('blur', () => {
            if (!passwordVisible && mouseTrackingEnabled) {
                characters.forEach(char => {
                    char.classList.remove('looking-right', 'stretching', 'looking-up');
                    const mouth = char.querySelector('.mouth');
                    mouth.classList.remove('surprised', 'awkward', 'whistle');
                    mouth.classList.add('smile');
                });
            }
        });
        
        // Show password button click
        function togglePasswordAnimated() {
            const passwordInput = document.getElementById('password');
            const icon = showPasswordBtn.querySelector('i');
            
            passwordVisible = !passwordVisible;
            
            if (passwordVisible) {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                
                // Disable mouse tracking
                mouseTrackingEnabled = false;
                
                // All characters look left
                characters.forEach(char => {
                    char.classList.remove('looking-right', 'stretching', 'looking-up');
                    const eyes = char.querySelector('.eyes');
                    eyes.classList.add('looking-left');
                });
                
                // Character 1 - Transformer: Raises voltage (HIGH)
                voltageIndicator.textContent = '440V';
                voltageIndicator.classList.remove('low');
                voltageIndicator.classList.add('high');
                const char1 = document.getElementById('char1');
                const mouth1 = char1.querySelector('.mouth');
                mouth1.classList.remove('smile', 'surprised', 'whistle');
                mouth1.classList.add('awkward');
                const sweat1 = char1.querySelector('.sweat-drop');
                sweat1.style.display = 'block';
                
                // Character 2 - Light Bulb: Turns off and flickers, whistles
                bulbGlass.classList.remove('on');
                bulbGlass.classList.add('off');
                const char2 = document.getElementById('char2');
                const mouth2 = char2.querySelector('.mouth');
                mouth2.classList.remove('smile', 'surprised', 'awkward');
                mouth2.classList.add('whistle');
                const notes = char2.querySelectorAll('.music-note');
                notes.forEach(note => note.style.display = 'block');
                startBulbFlicker();
                
                // Character 3 - Switch: Turns OFF
                const char3 = document.getElementById('char3');
                switchToggle.classList.add('off');
                const mouth3 = char3.querySelector('.mouth');
                mouth3.style.opacity = '0.5';
                
                // Character 4 - Battery: Low battery with warning, starts periodic glances
                batteryLevel.style.height = '20%';
                batteryLevel.style.background = '#FF5252';
                batteryWarning.classList.add('show');
                const char4 = document.getElementById('char4');
                const eyes4 = char4.querySelector('.eyes');
                const mouth4 = char4.querySelector('.mouth');
                mouth4.classList.remove('smile', 'surprised', 'awkward', 'whistle');
                
                // Start periodic glancing
                startBatteryGlance();
                
                // Initial peek
                setTimeout(() => {
                    eyes4.classList.remove('looking-left');
                    eyes4.style.transform = 'translateX(10px)';
                    mouth4.classList.add('surprised');
                    
                    setTimeout(() => {
                        eyes4.style.transform = '';
                        eyes4.classList.add('looking-left');
                        mouth4.classList.remove('surprised');
                        mouth4.classList.add('awkward');
                    }, 300);
                }, 200);
                
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                
                // Re-enable mouse tracking
                mouseTrackingEnabled = true;
                
                // Stop intervals
                stopBulbFlicker();
                stopBatteryGlance();
                
                // Remove looking-left class and return to normal
                characters.forEach(char => {
                    const eyes = char.querySelector('.eyes');
                    eyes.classList.remove('looking-left');
                    eyes.style.transform = '';
                    const mouth = char.querySelector('.mouth');
                    mouth.classList.remove('awkward', 'whistle', 'surprised');
                    mouth.classList.add('smile');
                    mouth.style.opacity = '1';
                });
                
                // Transformer: Normal voltage
                voltageIndicator.textContent = '220V';
                voltageIndicator.classList.remove('high');
                
                // Light Bulb: Turn back on
                bulbGlass.classList.remove('off', 'flickering');
                bulbGlass.classList.add('on');
                
                // Switch turns back ON
                switchToggle.classList.remove('off');
                
                // Battery: Full charge
                batteryLevel.style.height = '70%';
                batteryLevel.style.background = 'linear-gradient(to top, #4CAF50 0%, #8BC34A 100%)';
                batteryWarning.classList.remove('show');
                
                // Hide special effects
                document.querySelectorAll('.sweat-drop, .music-note').forEach(el => {
                    el.style.display = 'none';
                });
            }
        }
        
        // Hover effects on characters
        characters.forEach(char => {
            char.addEventListener('mouseenter', () => {
                char.style.transform = 'scale(1.1)';
                char.classList.remove('bouncing');
            });
            
            char.addEventListener('mouseleave', () => {
                char.style.transform = 'scale(1)';
                char.classList.add('bouncing');
            });
        });
    </script>
</body>
</html>