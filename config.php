<?php
/* Powered By c4e3bac3@foxmail.com Hello */
$Administrator = [
    "Username" => "Admin",
    "Password" => "1234567890"
];

$DataBase = [
    "host" => "127.0.0.1",
    "port" => "33060",
    "username" => "OpenCTF",
    "password" => "1234567890",
    "db_name" => "openctf"
];

/* ===================== 公共函数（各页面共享） ===================== */

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify() {
    $expected = $_SESSION['csrf_token'] ?? '';
    $actual = $_POST['csrf_token'] ?? '';
    if ($expected === '' || $actual === '' || !hash_equals($expected, $actual)) {
        http_response_code(403);
        exit('CSRF 校验失败');
    }
}

function GenerateImage($code) {
    $image = imagecreatetruecolor(120, 40);

    $bgColor = imagecolorallocate($image, 243, 243, 243);
    imagefill($image, 0, 0, $bgColor);

    for ($i = 0; $i < 15; $i++) {
        $lineColor = imagecolorallocate($image, mt_rand(100,200), mt_rand(100,200), mt_rand(100,200));
        imageline($image, mt_rand(0, 120), mt_rand(0, 40), mt_rand(0, 120), mt_rand(0, 40), $lineColor);
    }

    for ($i = 0; $i < 200; $i++) {
        $pixelColor = imagecolorallocate($image, mt_rand(50,150), mt_rand(50,150), mt_rand(50,150));
        imagesetpixel($image, mt_rand(0, 120), mt_rand(0, 40), $pixelColor);
    }

    $fontSize = 135;
    $fontWidth = imagefontwidth($fontSize);
    $fontHeight = imagefontheight($fontSize);

    $textWidth = $fontWidth * strlen($code);
    $x = (120 - $textWidth) / 2;
    $y = (40 - $fontHeight) / 2;

    for ($i = 0; $i < strlen($code); $i++) {
        $charColor = imagecolorallocate($image, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
        $charX = $x + ($i * $fontWidth) + mt_rand(-1, 1);
        $charY = $y + mt_rand(-2, 2);
        imagestring($image, $fontSize, $charX, $charY, $code[$i], $charColor);
    }

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    return $code;
}

function RandomCode($len) {
    $code = '';
    $charset = '1234567890qazwsxedcrfvtgbyhnujmikolpQAZWSXEDCRFVTGBYHNUJMIKOLP';
    $charsetLen = strlen($charset) - 1;
    for ($i = 0; $i < $len; $i++) {
        $code .= $charset[random_int(0, $charsetLen)];
    }

    return $code;
}

function httpGet($url, $headers = [], $timeout = 10) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function httpPostForm($url, $data, $headers = [], $timeout = 10) {
    $postData = http_build_query($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $defaultHeaders = ['Content-Type: application/x-www-form-urlencoded'];
    $allHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

    $response = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return $response;
}

function db_connect() {
    global $DataBase;
    $conn = mysqli_connect("p:" . $DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
    if ($conn) {
        mysqli_set_charset($conn, 'utf8mb4');
    }
    return $conn;
}

date_default_timezone_set('Asia/Shanghai');

/*
有限制动态容器存活时长（秒），25 分钟
单位是秒
*/
define("CONTAINER_TTL", 1500);


/*
    sudo apt install php-gd
    sudo yum install php-gd

    Powered By c4e3bac3@foxmail.com
    数据库只支持 MySQL 和 MariaDB
    competition -> cmtn
    user 表记录 学号 姓名 密码 邮箱
    cmtn 表记录 比赛ID 比赛名称 开始时间 结束时间
    比赛ID_ll 记录理论题
    比赛ID_sc 记录实操题
    比赛ID_pm 记录某个用户总共拿了多少分,也就是排名
    比赛ID 表记录单用户对应做对的题目的分数（该分数是最终判定的分数）


    初始化数据库时记得在数据库里创建这两个表

    create table user (id char(255) PRIMARY KEY, username char(255), password char(255), email char(255));
    create table cmtn (id char(255) PRIMARY KEY, name char(255), start_time char(255), end_time char(255), message varchar(10000));

    需要给 $DataBase 里设置的 db_name 数据库 给予 SELECT,DELETE,UPDATE,INSERT,DROP,CREATE 权限
    
    实操题扣分规则：
        第一个做对题目的分数为 附加分 + 基础分。
        第二个作对题目的分数为 附加分 - 1 + 基础分
        第三个作对题目的分数为 附加分 - 2 + 基础分
        第四个作对题目的分数为 附加分 - 3 + 基础分
        第五个作对题目的分数为 附加分 - 4 + 基础分
        以此类推…………
        直到附加分扣完为止，基础分不会扣

    
    实操题支持随机 flag 和动态容器功能，使用请求第三方 API 来实现。
    需要在 dashboard.php 里选择动态容器功能并配置第三方 API 地址。
    "无限制动态答案" 比赛用户在启动容器时不会销毁之前的容器
    "有限制动态答案" 比赛用户在启动容器时会请求 API 的 /stop 来销毁容器
    请求 API 格式请看 /app.py 文件



*/
?>
