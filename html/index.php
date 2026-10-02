<?php
/* Powered By c4e3bac3@foxmail.com Hello */
include_once "../config.php";
session_start(['cookie_httponly' => true]);session_regenerate_id(true);header('Cache-Control: no-cache, no-store, must-revalidate');header('Pragma: no-cache');header('Expires: 0');

if(isset($_GET["img"])) {
    if ($_GET["img"] === $_SESSION["verify"]) {
        $_SESSION["code"] = RandomCode(8);
        GenerateImage($_SESSION["code"]);
        exit();
    }

}
else if (isset($_POST["status"])) {
    csrf_verify();
    $status = $_POST["status"];
    if ($status === "Admin") {
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["code"])) {
            if ($_POST["code"] === $_SESSION["code"]) {
                $_SESSION["code"] = RandomCode(8);
                $username = $_POST["username"];
                $password = $_POST["password"];
                if ($username === $Administrator["Username"]) {
                    if ($password === $Administrator["Password"]) {
                        $_SESSION["Administrator"] = "Administrator";
                        echo "window.location.href = \"/dashboard.php\";";
                    }
                }
            }
            else {
                $_SESSION["code"] = RandomCode(8);
                echo "location.reload();";
            }
        }

        
    }
    else if ($status === "Login") {
        if (isset($_POST["studentid"]) && isset($_POST["password"]) && isset($_POST["code"])) {
            $password = $_POST["password"];
            $studentid = htmlspecialchars($_POST["studentid"], ENT_QUOTES);
            if ($_SESSION["code"] === $_POST["code"]) {
                $_SESSION["code"] = RandomCode(8);
                if (ctype_digit($studentid) === true) {
                    $mysql_conn = db_connect();
                    if ($mysql_conn) {
                        $stmt = mysqli_prepare($mysql_conn, "SELECT password FROM user WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, 's', $studentid);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if (mysqli_num_rows($result) === 0) {
                            echo "alert(\"登录失败\");location.reload();";
                            
                        } else {
                            while ($row = mysqli_fetch_assoc($result)) {
                                if (password_verify($password, $row["password"])) {
                                    $_SESSION["studentID"] = $studentid;
                                    echo "alert(\"登录成功\");location.href = \"/homepage.php\";";
                                }
                                else {
                                    echo "alert(\"登录失败\");location.reload();";
                                }
                            }
                        }
                    }
                    else {
                        echo "alert(\"数据库错误\");location.reload();";
                    }
                }
                else {
                    echo "alert(\"注册参数错误\");location.reload();";
                }
            }
            else {
                $_SESSION["code"] = RandomCode(8);
                echo "location.reload();";
            }
        }
        else {
            echo "alert(\"参数错误\");";
        }

    }
    else if ($status === "Register") {
        if ($allow_register !== true) {
            echo "alert(\"当前禁止注册用户\");location.reload();";
            exit();
        }
        if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["studentid"]) && isset($_POST["email"]) && isset($_POST["code"])) {
            $username_raw = $_POST["username"];
            $email_raw = $_POST["email"];
            $username = htmlspecialchars($username_raw, ENT_QUOTES);
            $password = $_POST["password"];
            $studentid = htmlspecialchars($_POST["studentid"], ENT_QUOTES);
            $email = htmlspecialchars($email_raw, ENT_QUOTES);
            if (!is_safe_text($username_raw, 64) || !is_safe_text($email_raw, 254)) {
                $_SESSION["code"] = RandomCode(8);
                echo "alert(\"用户名或邮箱不能包含换行等控制字符，且长度不能超过限制\");location.reload();";
                exit();
            }
            if (strpos($username, "\\") !== false || strpos($username, "/") !== false || strpos($email, "\\") !== false || strpos($email, "/") !== false) {
                $_SESSION["code"] = RandomCode(8);
                echo "alert(\"用户名或邮箱不能包含斜杠或反斜杠\");location.reload();";
                exit();
            }
            if ($_SESSION["code"] === $_POST["code"]) {
                $_SESSION["code"] = RandomCode(8);
                if (ctype_digit($studentid) === true) {
                    $mysql_conn = db_connect();
                    if ($mysql_conn) {
                        $stmt = mysqli_prepare($mysql_conn, "SELECT id, username, password, email FROM user WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, 's', $studentid);
                        mysqli_stmt_execute($stmt);
                        $result = mysqli_stmt_get_result($stmt);
                        if (mysqli_num_rows($result) === 0) {
                            $stmt = mysqli_prepare($mysql_conn, "insert into user (id, username, password, email, enable) value (? ,?, ?, ?, 0)");
                            $password = password_hash($password, PASSWORD_DEFAULT);
                            mysqli_stmt_bind_param($stmt, 'ssss', $studentid, $username, $password, $email);
                            mysqli_stmt_execute($stmt);
                            echo "alert(\"注册成功，请登录\");location.reload();";
                            
                        } else {
                            echo "alert(\"该学号已被注册\");location.reload();";
                        }
                    }
                    else {
                        echo "alert(\"数据库错误\");location.reload();";
                    }
                    
                }
                else {
                    echo "alert(\"注册参数错误\");location.reload();";
                }
            }
            else {
                $_SESSION["code"] = RandomCode(8);
                echo "location.reload();";
            }
        }
        else {
            echo "alert(\"注册参数错误。\");location.reload();";
        }
    }
    exit();
}
$_SESSION["verify"] = hash("sha512", RandomCode(8));

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OpenCTF</title>
    <style>
        :root {
            --bg: #0d1117;
            --card: #161b22;
            --border: #30363d;
            --text: #c9d1d9;
            --muted: #8b949e;
            --accent: #3fb950;
            --accent-hover: #2ea043;
            --input-bg: #0d1117;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'SF Mono', 'Consolas', 'Monaco', 'Courier New', monospace;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 15px;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--accent);
        }
        .logo span {
            color: var(--text);
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 28px 24px;
        }
        .tabs {
            display: flex;
            margin-bottom: 22px;
            border-bottom: 1px solid var(--border);
        }
        .tab {
            flex: 1;
            text-align: center;
            padding: 10px 4px;
            cursor: pointer;
            color: var(--muted);
            font-size: 0.85rem;
            border-bottom: 2px solid transparent;
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
            font-family: inherit;
            letter-spacing: 0.5px;
            transition: 0.15s;
        }
        .tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        .tab:hover {
            color: #e6edf3;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .field {
            margin-bottom: 16px;
        }
        .field label {
            display: block;
            font-size: 0.78rem;
            color: var(--muted);
            margin-bottom: 5px;
            letter-spacing: 0.3px;
        }
        .field input {
            width: 100%;
            padding: 10px 12px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 5px;
            color: var(--text);
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: 0.15s;
        }
        .field input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(63, 185, 80, 0.1);
        }
        .btn {
            width: 100%;
            padding: 11px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            letter-spacing: 0.5px;
            transition: 0.15s;
            margin-top: 6px;
        }
        .btn:hover {
            background: var(--accent-hover);
        }
        .logo-sub {
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
            margin-bottom: 25px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
            z-index: 999;
        }
        .modal-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
            width: 90%;
            max-width: 320px;
            text-align: center;
            color: var(--text);
        }
        .modal-box h3 {
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            color: var(--accent);
        }
        .captcha-img-box {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .captcha-img-box img {
            border: 1px solid var(--border);
            border-radius: 4px;
            height: 80px;
            background: #fff;
            cursor: pointer;
        }
        .captcha-img-box button {
            background: none;
            border: 1px solid var(--border);
            color: var(--muted);
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.8rem;
            transition: 0.15s;
            white-space: nowrap;
        }
        .captcha-img-box button:hover {
            color: #e6edf3;
            border-color: var(--accent);
        }
        .modal-box input {
            width: 100%;
            padding: 10px 12px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 5px;
            color: var(--text);
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            margin-bottom: 16px;
            text-align: center;
            letter-spacing: 2px;
        }
        .modal-box input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(63, 185, 80, 0.1);
        }
        .modal-btns {
            display: flex;
            gap: 10px;
        }
        .modal-btns .btn {
            flex: 1;
            margin-top: 0;
        }
        .btn-cancel {
            background: #21262d;
            border: 1px solid var(--border);
        }
        .btn-cancel:hover {
            background: #30363d;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">Open<span>CTF</span></div>
    <div class="logo-sub">Powered By c4e3bac3@foxmail.com</div>
    <div class="card">
        <div class="tabs">
            <button class="tab active" id="tab-login" onclick="switchTab('login')">用户登录</button>
            <button class="tab" id="tab-register" onclick="switchTab('register')">用户注册</button>
            <button class="tab" id="tab-admin" onclick="switchTab('admin')">管理员登录</button>
        </div>

        <div class="section active" id="sec-login">
            <div class="field">
                <label>学号</label>
                <input type="text" id="login-username" placeholder="输入学号">
            </div>
            <div class="field">
                <label>密码</label>
                <input type="password" id="login-password" placeholder="输入密码">
            </div>
            <button class="btn" onclick="handleLogin()">登 录</button>
        </div>

        <div class="section" id="sec-register">
            <div class="field">
                <label>用户名</label>
                <input type="text" id="reg-username" placeholder="你的真实姓名">
            </div>
            <div class="field">
                <label>学号</label>
                <input type="text" id="reg-studentid" placeholder="输入学号">
            </div>
            <div class="field">
                <label>邮箱</label>
                <input type="email" id="reg-email" placeholder="your@email.com">
            </div>
            <div class="field">
                <label>密码</label>
                <input type="password" id="reg-password" placeholder="至少6位">
            </div>
            <div class="field">
                <label>确认密码</label>
                <input type="password" id="reg-confirm" placeholder="再次输入密码">
            </div>
            <button class="btn" onclick="handleRegister()">注 册</button>
        </div>

        <div class="section" id="sec-admin">
            <div class="field">
                <label>管理员密钥</label>
                <input type="password" id="admin-key" placeholder="输入管理员密钥">
            </div>
            <div class="field">
                <label>密码</label>
                <input type="password" id="admin-password" placeholder="输入管理员密码">
            </div>
            <button class="btn" onclick="handleAdminLogin()">管理员登录</button>
        </div>
    </div>
</div>

<div id="captcha-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>🔐 安全验证</h3>
        <div class="captcha-img-box">
            <img id="captcha-img" src="" alt="验证码" title="点击图片刷新验证码">
            <button id="refresh-captcha" type="button">换一张</button>
        </div>
        <input type="text" id="captcha-input" placeholder="输入图片中的字母和数字" maxlength="10" autocomplete="off">
        <div class="modal-btns">
            <button id="captcha-confirm" class="btn">确 认</button>
            <button id="captcha-cancel" class="btn btn-cancel">取 消</button>
        </div>
    </div>
</div>

<script>
    var CSRF_TOKEN = '<?php echo csrf_token(); ?>';
    (() => {
        function ban() {
            const start = Date.now();
            const timer = setInterval(() => { debugger; if (Date.now() - start > 10000) { clearInterval(timer); } }, 200);
        }
        ban();
    })();

    function switchTab(tab) {
        ['login','register','admin'].forEach(id => {
            document.getElementById('tab-'+id).classList.toggle('active', id===tab);
            document.getElementById('sec-'+id).classList.toggle('active', id===tab);
        });
    }

    function showCaptchaModal() {
        return new Promise((resolve, reject) => {
            const modal = document.getElementById('captcha-modal');
            const img = document.getElementById('captcha-img');
            const input = document.getElementById('captcha-input');
            const refreshBtn = document.getElementById('refresh-captcha');
            const confirmBtn = document.getElementById('captcha-confirm');
            const cancelBtn = document.getElementById('captcha-cancel');

            function loadCaptcha() {
                img.src = '/index.php?img=<?php echo $_SESSION["verify"]; ?>';
            }

            loadCaptcha();
            input.value = '';
            modal.style.display = 'flex';
            input.focus();

            refreshBtn.onclick = function() {location.reload()};
            img.onclick = function() {location.reload()};

            confirmBtn.onclick = function() {
                const captcha = input.value.trim();
                if (!captcha) {
                    alert('请输入验证码');
                    input.focus();
                    return;
                }
                modal.style.display = 'none';
                resolve(captcha);
            };

            cancelBtn.onclick = function() {
                modal.style.display = 'none';
                reject(new Error('用户取消'));
            };

            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    reject(new Error('用户取消'));
                }
            };

            input.onkeydown = function(e) {
                if (e.key === 'Enter') {
                    confirmBtn.click();
                }
            };
        });
    }

    function sendRequestWithCaptcha(params) {
        showCaptchaModal().then(captcha => {
            const fullParams = params + '&code=' + encodeURIComponent(captcha) + '&csrf_token=' + encodeURIComponent(CSRF_TOKEN);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/index.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                eval(xhr.responseText);
            };
            xhr.send(fullParams);
        }).catch(err => {
            location.reload()
        });
    }

    function handleLogin() {
        const params = "studentid=" + document.getElementById("login-username").value + "&password=" + document.getElementById("login-password").value + "&status=Login";
        sendRequestWithCaptcha(params);
    }

    function handleRegister() {
        if (document.getElementById("reg-password").value === document.getElementById("reg-confirm").value) {
            const params = "status=Register&studentid=" + document.getElementById("reg-studentid").value + "&password=" + document.getElementById("reg-password").value + "&username=" + document.getElementById("reg-username").value + "&email=" + document.getElementById("reg-email").value;
            sendRequestWithCaptcha(params);
        }
    }

    function handleAdminLogin() {
        const params = "username=" + document.getElementById("admin-key").value + "&password=" + document.getElementById("admin-password").value + "&status=Admin";
        sendRequestWithCaptcha(params);
    }
</script>
</body>
</html>
