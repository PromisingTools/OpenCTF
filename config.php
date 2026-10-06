<?php
/* Powered By c4e3bac3@foxmail.com Hello */
$Administrator = [
    "Username" => "Admin",
    "Password" => "1234567890"
];

$DataBase = [
    "host" => "127.0.0.1",
    "port" => "3306",
    "username" => "OpenCTF",
    "password" => "1234567890",
    "db_name" => "openctf"
];

/*
    是否允许用户自行注册。
    true  允许注册（默认）
    false 禁止注册（注册接口会在后端直接拒绝）
*/
$allow_register = true;

/*
有限制动态容器存活时长（秒），25 分钟
单位是秒
*/
define("CONTAINER_TTL", 1500);

/*
    动态容器 API 鉴权令牌（HTTP 请求头 X-Auth-Token）。
    Web 端调用容器 API 的 /start、/stop 时，会通过请求头 X-Auth-Token 携带本值；
    app.py 在处理 /start、/stop 之前会校验该请求头，只有与自身硬编码的令牌一致才处理请求，否则返回 401。
    本值必须与 app.py 中的 CONTAINER_API_TOKEN 完全一致；正式部署请修改为随机强密钥。
    该令牌只能阻止未授权的直接调用，仍建议通过防火墙限制容器 API 仅允许 Web 服务器等可信来源访问。
*/
define("CONTAINER_API_TOKEN", "020c84a3d3841be34f4806dad78cff1dc7f34fe99ca11afd");

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

/*
    校验用户名 / 邮箱等文本字段是否安全。

    拒绝控制字符（换行 \n、回车 \r、制表符 \t、NUL 等）：
    这类字符会污染后台手工拼接的 JSON（例如 dashboard.php 的用户列表），
    导致管理员面板解析失败；同时限制最大长度，避免超长字段。

    返回 true 表示通过校验。
*/
function is_safe_text($value, $maxLen = 64) {
    if (!is_string($value)) {
        return false;
    }
    if (strlen($value) > $maxLen) {
        return false;
    }
    /* 匹配 ASCII 控制字符（0x00-0x1F、0x7F），不使用 /u 以便逐字节判断 */
    if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
        return false;
    }
    return true;
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
    sudo apt install php-gd
    sudo yum install php-gd

    Powered By c4e3bac3@foxmail.com
    数据库只支持 MySQL 和 MariaDB
    competition -> cmtn
    user 表记录 学号 姓名 密码 邮箱
    cmtn 表记录 比赛ID 比赛名称 开始时间 结束时间（比赛ID 为 md5，不含 OpenCTF_ 前缀）
    OpenCTF_比赛ID_ll 记录理论题
    OpenCTF_比赛ID_sc 记录实操题
    OpenCTF_比赛ID_pm 记录某个用户总共拿了多少分,也就是排名
    OpenCTF_比赛ID 表记录单用户对应做对的题目的分数（该分数是最终判定的分数）


    初始化数据库时记得在数据库里创建这两个表

    create table user (id char(255) PRIMARY KEY, username char(255), password char(255), email char(255), enable int(1) NOT NULL DEFAULT 0);
    create table cmtn (id char(255) PRIMARY KEY, name char(255), start_time char(255), end_time char(255), message varchar(10000));

    如果是从旧版本升级，user 表需要手动增加 enable 字段（该字段控制是否允许参赛，1 允许 / 0 禁止，默认阻止参赛）：
    ALTER TABLE user ADD COLUMN enable int(1) NOT NULL DEFAULT 0;

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

/*
    部署、使用与安全说明

    项目信息：
        博客：https://blog.csdn.net/khchgkhbdfxk/article/details/166897392
        下载：https://download.csdn.net/download/khchgkhbdfxk/92965797
        该平台适用于小型 CTF 比赛。

    快速部署：
        将项目解压到 /var/www/ 目录，开启 Apache2 并配置好数据库即可。

    数据库要求补充：
        暂时只支持 MySQL 和 MariaDB；数据库存储引擎需设为 InnoDB，字符集设为 UTF8MB4。
        MySQL 配置示例：
            [mysqld]
            default-storage-engine = InnoDB
            character-set-server = utf8mb4

    PHP 扩展要求：
        在 PHP 插件中开启 CURL 功能（php-gd 的安装见上方注释）。

    目录结构：
        /var/www/config.php
        /var/www/html/index.php
        /var/www/html/contest.php
        /var/www/html/dashboard.php
        /var/www/html/homepage.php
        /var/www/html/screen.php

    参赛权限与注册开关：
        user.enable 字段控制用户是否允许参赛：1 允许、0 禁止。被禁止的用户在 homepage.php 无法加载竞赛列表、访问 contest.php 会被直接拒绝。
        管理员可在 dashboard.php 的用户列表中查看每个用户的参赛状态，并进行单个「批准参赛 / 阻止参赛」或「全部允许参赛 / 全部拒绝参赛」操作。
        管理员可在 dashboard.php 通过「批量导入用户」按行导入用户，每行格式为 学号,姓名,邮箱,初始密码（导入的用户默认 enable=0）。
        通过注册页 index.php 新注册的用户默认 enable=0（阻止参赛），需管理员批准后才能参赛。
        $allow_register 控制是否允许用户自行注册：true 允许、false 禁止。

    安全建议（注意事项）：
        1. 部署后立即修改默认凭据：请修改 $Administrator 的默认管理员密码（Admin / 1234567890）和 $DataBase 的数据库密码（OpenCTF / 1234567890），避免使用源码中硬编码的默认值。
        2. config.php 不要暴露在 Web 根目录：config.php 应位于站点根目录（html/）之外，并确保 Apache 的 DocumentRoot 指向 html/，防止配置文件被直接访问而泄露凭据。
        3. 数据库权限说明：平台需要 DROP 和 CREATE 权限（用于动态创建/删除比赛数据表），建议仅在 db_name 数据库上授予所需权限，并限制数据库账号的来源主机，避免授予全局（*.*）权限。
        4. 动态容器 API（app.py）的安全配置：/start、/stop 现通过请求头 X-Auth-Token 鉴权（见上方 CONTAINER_API_TOKEN 注释）；请将示例默认令牌替换为随机强密钥；示例答案仍为硬编码，正式使用时应随机化每次答案；同时建议通过防火墙仅允许 Web 服务器等可信来源访问 API。
        5. 建议启用 HTTPS：会话 Cookie 未设置 Secure 标志，在明文 HTTP 下易被窃取，建议为站点启用 HTTPS。
*/
?>
