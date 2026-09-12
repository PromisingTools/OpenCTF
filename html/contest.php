<?php
/* Powered By c4e3bac3@foxmail.com Hello */
header('Cache-Control: no-cache, no-store, must-revalidate');header('Pragma: no-cache');header('Expires: 0');

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

function checkIdMatch($jsona, $jsonb) {
    $arrA = json_decode($jsona, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON 解析错误";
        exit();
    }

    $arrB = json_decode($jsonb, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON 解析错误";
        exit();
    }

    $idsA = array_column($arrA, 'id');
    $idsB = array_column($arrB, 'id');

    if (count($idsA) !== count(array_unique($idsA))) {
        return false;
    }

    if (count($arrA) !== count($arrB)) {
        return false;
    }

    if (!empty(array_diff($idsA, $idsB))) {
        return false;
    }

    return true;
}

function UpdateRank ($mysql, $studentid, $contestid) {
    $result_a = mysqli_query($mysql, "SELECT studentid, score FROM " . $contestid . "_pm WHERE studentid=\"" . $studentid . "\";");
    if (mysqli_num_rows($result_a) === 0) {
        $result_b = mysqli_query($mysql, "SELECT score FROM " . $contestid . " WHERE studentid = \"" . $studentid . "\";");
        if (mysqli_num_rows($result_b) !== 0) {
            $score = 0;
            while ($row = mysqli_fetch_assoc($result_b)) {
                $score = $score + $row["score"];
            }
            mysqli_query($mysql, "INSERT INTO " . $contestid . "_pm(studentid, score) VALUE (\"" . $studentid . "\", " . $score . ");");
        }
    }
    else {
        mysqli_query($mysql, "DELETE FROM " . $contestid . "_pm WHERE studentid = \"" . $studentid . "\";");
        $result_b = mysqli_query($mysql, "SELECT score FROM " . $contestid . " WHERE studentid = \"" . $studentid . "\";");
        if (mysqli_num_rows($result_b) !== 0) {
            $score = 0;
            while ($row = mysqli_fetch_assoc($result_b)) {
                $score = $score + $row["score"];
            }
            mysqli_query($mysql, "INSERT INTO " . $contestid . "_pm(studentid, score) VALUE (\"" . $studentid . "\", " . $score . ");");
        }
    }


}

if (!isset($_COOKIE[session_name()])) {
    http_response_code(404);
    echo "<br/><center><br/><h1>知 攻 善 防  |  遇 弱 则 强</h1><br/><h1>焉 知 攻  |  何 知 防</h1></center>";
    exit();
}

session_start(['cookie_httponly' => true]);
if (isset($_SESSION["studentID"])) {
    $studentid = $_SESSION["studentID"];
    if(ctype_digit($studentid)) {
        include "../config.php";
        $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
        mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");
        $response = mysqli_query($mysql_conn, "select id, username, email from user where id = \"" . $studentid . "\";");
        if (mysqli_num_rows($response) === 0) {
            echo "None";
        }
        else {
            if (isset($_GET["id"])) {
                $ContestId = $_GET["id"];
                if (ctype_xdigit($ContestId)) {
                    $result = mysqli_query($mysql_conn, "SELECT id, name, start_time, end_time FROM cmtn WHERE id = \"" . $ContestId . "\";");
                    if (mysqli_num_rows($result) !== 0) {
                        $row = mysqli_fetch_assoc($result);
                        $Contest_Start_Time = $row["start_time"];
                        $Contest_End_Time = $row["end_time"];
                        $Contest_Name = $row["name"];
                        $Current_Time = time();
                        if (strtotime($Contest_Start_Time) <= $Current_Time) {
                            if (strtotime($Contest_End_Time) >= $Current_Time) {
                                if (isset($_POST["func"])) {
                                    $func = $_POST["func"];
                                    if ($func === "ll") {
                                        if (isset($_POST["json"])) {
                                            $ll_json = $_POST["json"];
                                            $result = mysqli_query($mysql_conn, "SELECT id, score, correct FROM " . $ContestId . "_ll;");
                                            if (mysqli_num_rows($result) !== 0) {
                                                $answer = "[";
                                                while($row = mysqli_fetch_assoc($result)) {
                                                    $answer = $answer . '{"id": "' . $row["id"] . '", "score": "' . $row["score"] . '", "correct": "' . $row["correct"] . '"},';
                                                }
                                                $answer = substr($answer, 0, -1);
                                                $answer = $answer . "]";

                                                if (checkIdMatch($answer, $ll_json)) {
                                                    $ll_json = json_decode($ll_json);
                                                    $answer = json_decode($answer);
                                                    $tmp = $answer[0];
                                                    $result_q = mysqli_query($mysql_conn, 'SELECT studentid FROM ' . $ContestId . ' WHERE studentid = "' . $studentid . '" and TitleID = "' . $tmp->id . '";');
                                                    if (mysqli_num_rows($result_q) === 0) {
                                                        foreach ($ll_json as $i) {
                                                            if (!ctype_xdigit($i->id)) {
                                                                exit();
                                                            }
                                                        }
                                                        foreach ($ll_json as $i) {
                                                            $result_a = mysqli_query($mysql_conn, "SELECT id, score, correct FROM " . $ContestId . "_ll WHERE id = \"" . $i->id . "\";");
                                                            $row_a = mysqli_fetch_assoc($result_a);
                                                            $correct = $row_a["correct"]; $score = $row_a["score"];
                                                            if ($correct == $i->answer) {
                                                                mysqli_query($mysql_conn, 'INSERT INTO ' . $ContestId . '(TitleID, studentid, score) value ("' . $i->id . '", "' . $studentid . '", ' . $score . ');');
                                                            }
                                                            else {
                                                                mysqli_query($mysql_conn, 'INSERT INTO ' . $ContestId . '(TitleID, studentid, score) value ("' . $i->id . '", "' . $studentid . '", 0);');
                                                            }
                                                        }
                                                        UpdateRank($mysql_conn, $studentid, $ContestId);
                                                    }
                                                    else {
                                                        echo "alert('你已完成理论题，请勿重复提交');";
                                                        exit();
                                                    }
                                                }
                                                else {
                                                    echo "alert('未完成理论题，请完成理论题');";
                                                    exit();
                                                }
                                            }
                                            else {
                                                exit();
                                            }
                                        }
                                        else {
                                            exit();
                                        }
                                    }
                                    else if ($func === "sc") {
                                        if (isset($_POST["id"])) {
                                            if (isset($_POST["answer"])) {
                                                if (ctype_xdigit($_POST["id"])) {
                                                    if (isset($_POST["verify_code"])) {
                                                        if ($_POST["verify_code"] === $_SESSION["code"]) {
                                                            $TitleID = $_POST["id"];
                                                            $Answer = htmlspecialchars($_POST["answer"], ENT_QUOTES);
                                                            $resultC = mysqli_query($mysql_conn, 'SELECT TitleID FROM ' . $ContestId . ' where TitleID="' . $TitleID . '" AND studentid="' . $studentid . '";');
                                                            if (mysqli_num_rows($resultC) === 0) {
                                                                $resultA = mysqli_query($mysql_conn, "SELECT id, flag, add_score, base_score, type FROM " . $ContestId . "_sc WHERE id = \"" . $TitleID . "\";");
                                                                if (mysqli_num_rows($resultA) !== 0) {
                                                                    $row_a = mysqli_fetch_assoc($resultA);
                                                                    if ($row_a["type"] == 1) {
                                                                        if ($Answer === $row_a["flag"]) {
                                                                            $score = $row_a["base_score"];
                                                                            $resultB = mysqli_query($mysql_conn, "SELECT count(TitleID) FROM " . $ContestId . " WHERE TitleID = \"" . $TitleID . "\";");
                                                                            $row_b = mysqli_fetch_assoc($resultB);
                                                                            if ($row_b["count(TitleID)"] <= $row_a["add_score"]) {
                                                                                $score = $score + ($row_a["add_score"] - $row_b["count(TitleID)"]);
                                                                            }
                                                                            mysqli_query($mysql_conn, "INSERT INTO " . $ContestId . "(TitleID, studentid, score) value (\"" . $TitleID . "\", \"" . $studentid . "\", " . $score . ")");
                                                                            UpdateRank($mysql_conn, $studentid, $ContestId);
                                                                            echo "true";
                                                                        }
                                                                    }
                                                                    else {
                                                                        $resultD = mysqli_query($mysql_conn, "SELECT answer FROM " . $ContestId . "_container WHERE TrueFalse = 1 AND studentid = \"" . $studentid . "\" AND ContestId = \"" . $TitleID . "\";");
                                                                        if (mysqli_num_rows($resultD) !== 0) {
                                                                            $row_c = mysqli_fetch_assoc($resultD);
                                                                            if ($Answer === $row_c["answer"]) {
                                                                                $score = $row_a["base_score"];
                                                                                $resultB = mysqli_query($mysql_conn, "SELECT count(TitleID) FROM " . $ContestId . " WHERE TitleID = \"" . $TitleID . "\";");
                                                                                $row_b = mysqli_fetch_assoc($resultB);
                                                                                if ($row_b["count(TitleID)"] <= $row_a["add_score"]) {
                                                                                    $score = $score + ($row_a["add_score"] - $row_b["count(TitleID)"]);
                                                                                }
                                                                                mysqli_query($mysql_conn, "INSERT INTO " . $ContestId . "(TitleID, studentid, score) value (\"" . $TitleID . "\", \"" . $studentid . "\", " . $score . ")");
                                                                                UpdateRank($mysql_conn, $studentid, $ContestId);
                                                                                echo "true";
                                                                            }
                                                                        }
                                                                        
                                                                    }
                                                                    
                                                                    
                                                                }
                                                            }
                                                        }
                                                        
                                                    }
                                                    $_SESSION["code"] = RandomCode(8);
                                                }
                                            }
                                        }
                                        exit();
                                    }
                                    else if ($func === "GetMessage") {
                                        if (isset($_POST["id"])) {
                                            $id = $_POST["id"];
                                            if (ctype_xdigit($id)) {
                                                $stmt = mysqli_prepare($mysql_conn, "select message from cmtn where id = ? ;");
                                                mysqli_stmt_bind_param($stmt, 's', $id);
                                                mysqli_stmt_execute($stmt);
                                                $result = mysqli_stmt_get_result($stmt);
                                                if (mysqli_num_rows($result) !== 0) {
                                                    while ($row = mysqli_fetch_assoc($result)) {
                                                        echo $row["message"];
                                                    }
                                                }
                                                
                                            }
                                        }
                                        exit();
                                    }
                                    else if ($func === "StartContaner") {
                                        if (isset($_POST["id"])) {
                                            $id = $_POST["id"];
                                            if (ctype_xdigit($id)) {
                                                if (time() - $_SESSION["visits"] > 15) {
                                                    $stmt = mysqli_prepare($mysql_conn, "select flag, type from " . $ContestId . "_sc where id = ? ;");
                                                    mysqli_stmt_bind_param($stmt, 's', $id);
                                                    mysqli_stmt_execute($stmt);
                                                    $result = mysqli_stmt_get_result($stmt);
                                                    if (mysqli_num_rows($result) !== 0) {
                                                        while ($row = mysqli_fetch_assoc($result)) {
                                                            if ($row["type"] === 2) {
                                                                if (true === true) {
                                                                    $resultA = mysqli_query($mysql_conn, "SELECT ContainerId FROM " . $ContestId . "_container WHERE studentid = \"" . $studentid . "\" AND type = 2 AND TrueFalse = 1;");
                                                                    if (mysqli_num_rows($resultA) != 0) {
                                                                        while ($row_a = mysqli_fetch_assoc($resultA)){
                                                                            httpPostForm($row["flag"] . "/stop", [$row_a["ContainerId"]]);
                                                                            mysqli_query($mysql_conn, "UPDATE " . $ContestId . "_container SET TrueFalse = 0 WHERE studentid = \"" . $studentid . "\" AND ContainerId = \"" . $row_a["ContainerId"] . "\" AND TrueFalse = 1;");
                                                                        }
                                                                    }
                                                                }
                                                                
                                                                if (true === true) {
                                                                    $response = httpGet($row["flag"] . "/start");
                                                                    $json = json_decode($response, true);
                                                                    $temp1 = strval(time());
                                                                    $temp2 = strval($studentid);
                                                                    $temp3 = strval($json["answer"]);
                                                                    $temp4 = strval($id);
                                                                    $temp5 = strval($json["ContainerID"]);
                                                                    $temp6 = strval($json["message"]);
                                                                    $temp7 = 1;
                                                                    $temp8 = 2;
                                                                    $stmta = mysqli_prepare($mysql_conn, "INSERT INTO " . $ContestId . "_container(time, studentid, answer, ContestId, ContainerId, message, TrueFalse, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                                                                    mysqli_stmt_bind_param($stmta, 'ssssssii', $temp1, $temp2, $temp3, $temp4, $temp5, $temp6, $temp7, $temp8);
                                                                    mysqli_stmt_execute($stmta);
                                                                }
                                                            }
                                                            else if ($row["type"] === 3) {
                                                                mysqli_query($mysql_conn, "UPDATE " . $ContestId . "_container SET TrueFalse = 0 WHERE studentid = \"" . $studentid . "\" AND ContestId = \"" . $id . "\";");
                                                                if (true === true) {
                                                                    $response = httpGet($row["flag"] . "/start");
                                                                    $json = json_decode($response, true);
                                                                    $temp1 = strval(time());
                                                                    $temp2 = strval($studentid);
                                                                    $temp3 = strval($json["answer"]);
                                                                    $temp4 = strval($id);
                                                                    $temp5 = strval($json["ContainerID"]);
                                                                    $temp6 = strval($json["message"]);
                                                                    $temp7 = 1;
                                                                    $temp8 = 3;
                                                                    $stmta = mysqli_prepare($mysql_conn, "INSERT INTO " . $ContestId . "_container(time, studentid, answer, ContestId, ContainerId, message, TrueFalse, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                                                                    mysqli_stmt_bind_param($stmta, 'ssssssii', $temp1, $temp2, $temp3, $temp4, $temp5, $temp6, $temp7, $temp8);
                                                                    mysqli_stmt_execute($stmta);
                                                                }
                                                                
                                                            }
                                                        }
                                                    }
                                                    
                                                }
                                                else {
                                                    header('Status: 403 Forbidden');
                                                    echo "十五秒内不能再次访问启动容器功能";
                                                }
                                                $_SESSION["visits"] = time();
                                                
                                            }
                                        }

                                        exit();
                                    }
                                    
                                    exit();
                                }
                                else if (isset($_GET["img"])) {
                                    if ($_GET["img"] === $_SESSION["verify"]) {
                                        $_SESSION["code"] = RandomCode(8);
                                        GenerateImage($_SESSION["code"]);
                                        $_SESSION["verify"] = RandomCode(8);
                                    }
                                    
                                    exit();
                                }
                                
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OpenCTF · 答题</title>
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
            --danger: #f85149;
            --danger-hover: #da3633;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background:var(--bg); color:var(--text);
            font-family:'SF Mono','Consolas','Monaco','Courier New',monospace;
            display:flex; justify-content:center; padding:20px 20px 30px; min-height:100vh;
        }
        .page-wrapper { width:100%; max-width:1300px; display:flex; flex-direction:column; gap:20px; }
        .page-header { text-align:center; }
        .page-header h1 {
            font-size:2.2rem; font-weight:700; letter-spacing:2px;
            color:var(--accent); margin-bottom:4px;
        }
        .page-header h1 span { color:var(--text); }
        .page-header .subtitle { font-size:0.75rem; color:var(--muted); letter-spacing:0.5px; }
        .container { display:flex; gap:20px; }
        
        .sidebar { width:340px; flex-shrink:0; display:flex; flex-direction:column; gap:20px; }
        .card {
            background:var(--card); border:1px solid var(--border);
            border-radius:8px; padding:20px;
        }
        .card h2 { font-size:1rem; margin-bottom:12px; color:var(--accent); }
        .user-badge {
            font-size:0.85rem; color:var(--text); margin-bottom:12px;
            padding-bottom:8px; border-bottom:1px solid var(--border);
        }
        .info-row { margin-bottom:8px; font-size:0.85rem; }
        .info-row span { color:var(--muted); }
        .score-box {
            text-align:center; background:#0d1117; border:1px solid var(--border);
            border-radius:6px; padding:12px; margin-top:12px;
        }
        .score-box .value { font-size:2rem; font-weight:700; color:var(--accent); }
        .grid-panel { flex:1; min-width:0; }
        .grid-container {
            display:grid; grid-template-columns:repeat(auto-fill, minmax(150px,1fr));
            gap:16px;
        }
        .challenge-square {
            background:var(--card); border:1px solid var(--border);
            border-radius:8px; display:flex; flex-direction:column;
            align-items:center; justify-content:center; aspect-ratio:1;
            cursor:pointer; transition:0.15s; text-align:center; padding:8px;
        }
        .challenge-square:hover { border-color:var(--accent); }
        .challenge-square.completed { border-color:var(--accent); opacity:0.7; cursor:not-allowed; }
        .challenge-square .name { font-size:0.9rem; font-weight:600; margin-bottom:4px; }
        .challenge-square .pts { font-size:0.75rem; color:var(--accent); }
        .challenge-square .tag {
            font-size:0.6rem; margin-top:4px; padding:2px 6px; border-radius:3px;
            background:#1f3d28; color:var(--accent);
        }
        .btn {
            padding:6px 12px; background:var(--accent); color:#fff;
            border:none; border-radius:4px; font-size:0.75rem; font-weight:600;
            cursor:pointer; font-family:inherit; letter-spacing:0.3px;
            transition:0.15s; white-space:nowrap;
        }
        .btn:hover { background:var(--accent-hover); }
        .btn.danger { background:var(--danger); }
        .btn.danger:hover { background:var(--danger-hover); }
        .btn.small { padding:4px 8px; font-size:0.7rem; }
        .modal {
            position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.7); display:none; align-items:center;
            justify-content:center; z-index:1000;
        }
        .modal.active { display:flex; }
        .modal-content {
            background:var(--card); border:1px solid var(--border);
            border-radius:8px; padding:24px; width:90%; max-width:600px;
            max-height:80vh; overflow-y:auto; position:relative;
        }
        .modal-close {
            position:absolute; top:12px; right:16px; background:none;
            border:none; color:var(--muted); font-size:1.4rem; cursor:pointer;
        }
        .modal-close:hover { color:var(--text); }
        .option-item {
            display:block; padding:8px; margin:6px 0; border:1px solid var(--border);
            border-radius:4px; cursor:pointer; background:var(--input-bg);
        }
        .option-item.selected { border-color:var(--accent); background:#14281a; }
        .option-item input[type="radio"] { display:none; }
        .field textarea {
            width:100%; background:var(--input-bg); border:1px solid var(--border);
            border-radius:5px; color:var(--text); padding:10px; font-size:0.85rem;
            font-family:inherit; outline:none; transition:0.15s; resize:vertical; min-height:100px;
        }
        .field textarea:focus { border-color:var(--accent); }
        .toast {
            position:fixed; top:20px; left:50%; transform:translateX(-50%);
            padding:10px 20px; border-radius:5px; font-size:0.85rem; z-index:9999;
            animation:fadeIn 0.25s ease; pointer-events:none;
        }
        .toast.success { background:#14281a; color:#3fb950; border:1px solid #1f3d28; }
        .toast.error { background:#3d1418; color:#f85149; border:1px solid #5c1f24; }
        @keyframes fadeIn {
            from { opacity:0; transform:translateX(-50%) translateY(-10px); }
            to { opacity:1; transform:translateX(-50%) translateY(0); }
        }
        #announcement-content {
            font-size:0.85rem;
            color:var(--text);
            word-break:break-word;
            line-height:1.6;
            white-space:pre-wrap;
        }
        #announcement-content.empty {
            color:var(--muted);
        }
        .modal-overlay {
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

        #captcha-modal {
            display: none;
        }

    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="page-header">
        <h1>Open<span>CTF</span></h1>
        <div class="subtitle">Powered By c4e3bac3@foxmail.com</div>
    </div>

    <div class="container">
        <div class="sidebar">
            <div class="card">
                <div class="user-badge">👤 玩家：<span id="display-username"></span></div>
                <h2>🏆 <span id="contest-name"></span></h2>
                <div class="info-row"><span>当前时间：</span><span id="current-time"></span></div>
                <div class="info-row"><span>开始时间：</span><span id="start-time"></span></div>
                <div class="info-row"><span>结束时间：</span><span id="end-time"></span></div>
                <div class="info-row"><span>剩余时间：</span><span id="remain-time"></span></div>
                <div class="score-box">
                    <div class="value" id="current-score">0</div>
                    <div>当前分数</div>
                </div>
                <div class="score-box" style="margin-top:8px;">
                    <div class="value" id="current-rank">-</div>
                    <div>当前排名</div>
                </div>
            </div>

            <div class="card" id="announcement-card">
                <h2>📢 公告</h2>
                <div id="announcement-content" class="empty">加载中…</div>
            </div>
        </div>

        <div class="grid-panel">
            <div class="grid-container" id="challenge-grid"></div>
        </div>
    </div>
</div>

<div class="modal" id="theory-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeTheoryModal()">&times;</button>
        <h2 style="margin-bottom:16px;">📝 理论题</h2>
        <div id="theory-question-area"></div>
        <div style="display:flex; justify-content:space-between; margin-top:16px;">
            <button class="btn small" id="prev-theory-btn" onclick="prevTheory()">上一题</button>
            <span id="theory-progress" style="color:var(--muted);"></span>
            <button class="btn small" id="next-theory-btn" onclick="nextTheory()">下一题</button>
        </div>
        <button class="btn" id="submit-theory-btn" onclick="submitTheory('<?php echo $ContestId; ?>');" style="width:100%; margin-top:16px; display:none;">提交全部理论题</button>
    </div>
</div>

<div class="modal" id="practical-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closePracticalModal()">&times;</button>
        <h2 id="practical-title" style="margin-bottom:8px;"></h2>
        <p id="practical-desc" style="margin-bottom:16px; color:var(--muted);"></p>
        <button class="btn small" style="margin-bottom: 15px;" id="ContainerButton">启动容器</button>
        
        <div class="field"><textarea id="practical-answer" placeholder="请输入答案"></textarea></div>
        <div id="practical-feedback" style="margin-top:8px; display:none;"></div>
        <button class="btn" onclick="Confirm();" style="width:100%; margin-top:12px;">提交答案</button>
    </div>
    <div id="captcha-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>🔐 安全验证</h3>
        <div class="captcha-img-box">
            <img id="captcha-img" style="width: 120%;" src="/contest.php?id=<?php echo $ContestId; ?>&img=<?php $_SESSION["verify"] = hash("sha512", RandomCode(8)); echo $_SESSION["verify"]; ?>" alt="验证码" title="点击图片刷新验证码">
            <button id="refresh-captcha" onclick="Refresh();" type="button">换一张</button>
        </div>
        <input type="text" id="captcha-input" placeholder="输入图片中的字母和数字" maxlength="10" autocomplete="off">
        <div class="modal-btns">
            <button id="captcha-confirm" class="btn" onclick="submitPractical();" >确 认</button>
            <button id="captcha-cancel" class="btn btn-cancel" onclick="BtnCancel();">取 消</button>
        </div>
    </div>
</div>
</div>

<script>
    (() => {
       function ban() {
           setInterval(() => { debugger; }, 50);
           try { ban(); } catch(err) {}
       }
       ban();
    })();

    function BtnCancel() {document.getElementById("captcha-modal").style.display = "none";}

    function Refresh() {location.reload();}

    function Confirm() {document.getElementById("captcha-modal").style.display = "flex";}

    var currentUser = { username: '<?php if(true) {$rows = mysqli_fetch_assoc($response); echo $rows["username"];} ?>' };
    var contestInfo = {
        id: '<?php echo $ContestId; ?>',
        name: '<?php echo $Contest_Name; ?>',
        start: '<?php echo $Contest_Start_Time; ?>',
        end: '<?php echo $Contest_End_Time; ?>'
    };
    var userScore = <?php if (true) {$result = mysqli_query($mysql_conn, "SELECT score FROM " . $ContestId . "_pm WHERE studentid = \"" . $studentid . "\";");if (mysqli_num_rows($result) === 0) {echo "0";}else {echo mysqli_fetch_assoc($result)["score"];}}?>;

    var userRank = <?php if (true) {$result = mysqli_query($mysql_conn, "SELECT rn, studentid, score FROM (SELECT studentid,score, ROW_NUMBER() OVER (ORDER BY score DESC) AS rn FROM " . $ContestId . "_pm) AS t WHERE studentid = '" . $studentid . "';");if (mysqli_num_rows($result) === 0) {echo "0";}else {echo mysqli_fetch_assoc($result)["rn"];}} ?>;

    var theoryQuestions = [];
    var practicalChallenges = [];
    var theoryCompleted = <?php
        if (true) {
            $resultA = mysqli_query($mysql_conn, 'SELECT id, stem, score, optionA, optionB, optionC, optionD FROM ' . $ContestId . '_ll;');
            if (mysqli_num_rows($resultA) === 0) {
                echo "false";
            }
            else {
                $resultB = mysqli_query($mysql_conn, 'SELECT TitleID, studentid FROM ' . $ContestId . ' WHERE TitleID = "' . mysqli_fetch_assoc($resultA)["id"] .'" AND studentid = "' . $studentid . '";');
                if (mysqli_num_rows($resultB) === 0) {
                    echo "false";
                }
                else {
                    echo "true";
                }
            }
        }
    ?>;

    var theoryAnswers = [];
    var currentTheoryIndex = 0;
    var currentPracticalId = null;
    var timerInterval = null;

    function init() {
        document.getElementById('display-username').textContent = currentUser.username;
        document.getElementById('contest-name').textContent = contestInfo.name;
        document.getElementById('start-time').textContent = contestInfo.start;
        document.getElementById('end-time').textContent = contestInfo.end;

        updateScoreAndRank();
        updateTime();
        loadChallenges();
        renderChallengeGrid();
        updateScoreAndRank();
        timerInterval = setInterval(updateTime, 1000);

        loadAnnouncement();
    }

    function loadAnnouncement() {
        var contentEl = document.getElementById('announcement-content');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/contest.php?id=' + contestInfo.id);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                contentEl.textContent = decodeURIComponent(xhr.responseText);
                
            } else {
                contentEl.textContent = '暂无公告';
                contentEl.className = 'empty';
            }
        };
        xhr.onerror = function() {
            contentEl.textContent = '暂无公告';
            contentEl.className = 'empty';
        };
        xhr.send("func=GetMessage&id=" + contestInfo.id);
    }

    function loadChallenges() {
        theoryQuestions = <?php
            if (true) {
                $result = mysqli_query($mysql_conn, 'SELECT id, stem, score, optionA, optionB, optionC, optionD FROM ' . $ContestId . '_ll;');
                if (mysqli_num_rows($result) === 0) {
                    echo "[]";
                }
                else {
                    $string = "[";
                    while ($row = mysqli_fetch_assoc($result)) {
                        $string = $string . '{id: "' . $row["id"] . '", stem: "' . str_replace("\n", "<br/>", $row["stem"]) . '", optionA: "' . $row["optionA"] . '", optionB: "' . $row["optionB"] . '", optionC: "' . $row["optionC"] . '", optionD: "' . $row["optionD"] . '", correct: undefined, score: ' . $row["score"] . '},';
                    }
                    $string = substr($string, 0, -1);
                    $string = $string . "]";
                    echo $string;
                }
            }
        ?>;
        practicalChallenges = <?php
            if (true) {
                $resultA = mysqli_query($mysql_conn, 'SELECT id, name, timu, base_score, add_score, type FROM ' . $ContestId . '_sc;');
                if (mysqli_num_rows($resultA) === 0) {
                    echo "[]";
                }
                else {
                    $string = "[";
                    while ($row = mysqli_fetch_assoc($resultA)) {
                        $score = $row["base_score"];
                        $tf = "false";
                        $resultC = mysqli_query($mysql_conn, 'SELECT TitleID, studentid, score FROM ' . $ContestId . ' WHERE TitleID = "' . $row["id"] . '" and studentid = "' . $studentid . '";');
                        if (mysqli_num_rows($resultC) === 0) {
                            $resultB = mysqli_query($mysql_conn, 'SELECT count(TitleID) FROM ' . $ContestId . ' WHERE TitleID = "' . $row["id"] . '";');
                            if (mysqli_num_rows($resultB) === 0) {
                                $score = $score + $row["add_score"];
                            }
                            else {
                                $rowa = mysqli_fetch_assoc($resultB);
                                if ($rowa["count(TitleID)"] <= $row["add_score"]) {
                                    $score = $score + ($row["add_score"] - $rowa["count(TitleID)"]);
                                }
                            }
                        }
                        else {
                            $score = mysqli_fetch_assoc($resultC)["score"];
                            $tf = "true";
                        }
                        $message = "";
                        if (true == true) {
                            $resultD = mysqli_query($mysql_conn, 'SELECT message FROM ' . $ContestId . '_container where TrueFalse = 1 AND studentid = "' . $studentid . '" AND ContestId = "' . $row["id"] . '";');
                            if (mysqli_num_rows($resultD) != 0) {
                                $message = mysqli_fetch_assoc($resultD)["message"];
                            }
                        }
                        $string = $string . '{id: "' . $row["id"] . '", name: "' . str_replace("\n", "<br/>", $row["name"]) . '", desc: "' . str_replace("\n", "<br/>", $row["timu"]) .  '", answer: undefined, completed: ' . $tf . ', score: ' . $score . ', answer_type: ' . $row["type"] . ', message: "' . $message . '"},';
                    }
                    $string = substr($string, 0, -1);
                    $string = $string . "]";
                    echo $string;
                }
            }
        ?>;
        processChallenges();
    }

    function processChallenges() {
        theoryAnswers = [];
        for (var i=0; i < theoryQuestions.length; i++) {
            theoryAnswers.push(null);
        }
        renderChallengeGrid();
    }

    function updateTime() {
        var now = new Date();
        document.getElementById('current-time').textContent = now.toLocaleString('zh-CN', { timeZone: 'Asia/Shanghai' })
        var end = new Date(contestInfo.end);
        var diff = end - now;
        if (diff <= 0) {
            document.getElementById('remain-time').textContent = '比赛已结束';
            clearInterval(timerInterval);
            return;
        }
        var hours = Math.floor(diff / 3600000);
        var minutes = Math.floor((diff % 3600000) / 60000);
        var seconds = Math.floor((diff % 60000) / 1000);
        document.getElementById('remain-time').textContent = hours+'时'+minutes+'分'+seconds+'秒';
    }

    function updateScoreAndRank() {
        document.getElementById('current-score').textContent = userScore;
        document.getElementById('current-rank').textContent = userRank;
    }

    function renderChallengeGrid() {
        var grid = document.getElementById('challenge-grid');
        grid.innerHTML = '';

        var totalTheoryScore = 0;
        for (var i=0; i<theoryQuestions.length; i++) totalTheoryScore += theoryQuestions[i].score;
        var theoryDiv = document.createElement('div');
        theoryDiv.className = 'challenge-square' + (theoryCompleted ? ' completed' : '');
        theoryDiv.innerHTML = '<div class="name">理论题</div>' +
                              '<div class="pts">共'+theoryQuestions.length+'题 / '+totalTheoryScore+'分</div>' +
                              (theoryCompleted ? '<div class="tag">已完成</div>' : '');
        if (!theoryCompleted) theoryDiv.onclick = openTheoryModal;
        grid.appendChild(theoryDiv);

        for (var j=0; j<practicalChallenges.length; j++) {
            var p = practicalChallenges[j];
            var div = document.createElement('div');
            div.className = 'challenge-square' + (p.completed ? ' completed' : '');
            div.innerHTML = '<div class="name">'+p.name+'</div>' +
                            '<div class="pts">'+p.score+'分</div>' +
                            (p.completed ? '<div class="tag">已完成</div>' : '');
                            

            if (!p.completed) div.onclick = (function(id){ return function(){ openPracticalModal(id); }; })(p.id);
            grid.appendChild(div);
        }
    }

    function startContainer(id) {
        var ContestID = "<?php echo $ContestId; ?>";
        for (var i = 0; i < practicalChallenges.length; i++) {
            if (id == practicalChallenges[i].id) {
                if (practicalChallenges[i].answer_type == 2) {
                    if (confirm("启动该容器后，其他已经启动的容器会关闭。单机确定启动当前题目的容器，单机取消不执行启动容器的操作。")) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '/contest.php?id=' + ContestID);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                showToast("容器启动成功，该页面刷新后重新点击该题目可以看到容器信息", "success");
                                setTimeout(() => {location.reload()}, 2650);
                                
                            } else if (xhr.status == 403) {
                                alert(xhr.responseText, "error");
                            }
                            else {
                                showToast('提交失败请重试', 'error');
                            }
                        };

                        xhr.send("func=StartContaner&id=" + practicalChallenges[i].id);
                    }
                }
                else {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', '/contest.php?id=' + ContestID);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            showToast("容器启动成功，该页面刷新后重新点击该题目可以看到容器信息", "success");
                            setTimeout(() => {location.reload()}, 1650);
                            
                        } else if (xhr.status == 403) {
                            alert(xhr.responseText, "error");
                        }
                        else {
                            showToast('提交失败请重试', 'error');
                        }
                    };
                    xhr.send("func=StartContaner&id=" + practicalChallenges[i].id);
                }
            }
        }
        
    }

    function openTheoryModal() {
        if (theoryAnswers.length !== 0) {
            if (theoryCompleted) return;
            currentTheoryIndex = 0;
            for (var i=0; i < theoryAnswers.length; i++) theoryAnswers[i] = null;
            showTheoryQuestion();
            document.getElementById('theory-modal').classList.add('active');
        }
    }
    function closeTheoryModal() { document.getElementById('theory-modal').classList.remove('active'); }

    function showTheoryQuestion() {
        var q = theoryQuestions[currentTheoryIndex];
        var container = document.getElementById('theory-question-area');
        var html = '<div style="margin-bottom:12px; font-weight:600;">第 '+(currentTheoryIndex+1)+' 题 ('+q.score+'分)</div><p>'+q.stem+'</p>';
        for (var i=0; i<['A','B','C','D'].length; i++) {
            var opt = ['A','B','C','D'][i];
            var selected = theoryAnswers[currentTheoryIndex] === opt;
            html += '<label class="option-item'+(selected?' selected':'')+'" onclick="selectTheoryOption(\''+opt+'\')">'+
                        '<input type="radio" name="theory-opt" value="'+opt+'" '+(selected?'checked':'')+'>'+opt+'. '+q['option'+opt]+
                    '</label>';
        }
        container.innerHTML = html;
        document.getElementById('prev-theory-btn').style.visibility = currentTheoryIndex===0 ? 'hidden' : 'visible';
        var nextBtn = document.getElementById('next-theory-btn');
        var submitBtn = document.getElementById('submit-theory-btn');
        if (currentTheoryIndex === theoryQuestions.length-1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
        document.getElementById('theory-progress').textContent = (currentTheoryIndex+1)+'/'+theoryQuestions.length;
    }

    function selectTheoryOption(option) {
        theoryAnswers[currentTheoryIndex] = option;
        var items = document.querySelectorAll('#theory-question-area .option-item');
        items.forEach(function(item){ item.classList.remove('selected'); });
        var el = document.querySelector('.option-item input[value="'+option+'"]');
        if (el) el.parentElement.classList.add('selected');
    }
    function prevTheory() { if (currentTheoryIndex>0) { currentTheoryIndex--; showTheoryQuestion(); } }
    function nextTheory() { if (currentTheoryIndex<theoryQuestions.length-1) { currentTheoryIndex++; showTheoryQuestion(); } }

    function submitTheory(id) {
        for (var i=0; i<theoryAnswers.length; i++) {
            if (!theoryAnswers[i]) { showToast('请完成所有理论题再提交', 'error'); return; }
        }
        var payload = [];
        for (var i=0; i<theoryQuestions.length; i++) {
            payload.push({id: theoryQuestions[i].id, answer: theoryAnswers[i] });
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/contest.php?id=' + id);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                eval(xhr.responseText);
            } else {
                showToast('提交失败请重试', 'error');
            }
            closeTheoryModal();
            theoryCompleted = true;
            updateScoreAndRank();
            renderChallengeGrid();
            setTimeout(() => {location.reload(); }, 650);
        };
        xhr.onerror = function() { showToast('网络错误，提交失败', 'error'); };
        xhr.send("func=ll&json=" + JSON.stringify(payload));
    }

    function openPracticalModal(id) {
        var challenge = null;
        for (var i=0; i<practicalChallenges.length; i++) {
            if (practicalChallenges[i].id === id) { challenge = practicalChallenges[i]; break; }
        }
        if (!challenge || challenge.completed) return;
        currentPracticalId = id;
        document.getElementById('practical-title').innerHTML = challenge.name;
        document.getElementById('practical-desc').innerHTML = challenge.desc + "<br/>" + challenge.message;
        document.getElementById('practical-answer').innerHTML = '';
        document.getElementById('practical-feedback').style.display = 'none';
        document.getElementById('practical-modal').classList.add('active');
        if (challenge.answer_type === 1) {
            document.getElementById("ContainerButton").style.display = "none";
            document.getElementById('ContainerButton').setAttribute('onclick', 'startContainer("' + challenge.id + '");');
        }
        else {
            document.getElementById("ContainerButton").style.display = "block";
            document.getElementById('ContainerButton').setAttribute('onclick', 'startContainer("' + challenge.id + '");');
        }
    }
    function closePracticalModal() { document.getElementById('practical-modal').classList.remove('active'); }

    function submitPractical() {
        var answer = document.getElementById('practical-answer').value.trim();
        if (!answer) { showToast('请输入答案', 'error'); return; }
        var challenge = null;
        for (var i=0; i<practicalChallenges.length; i++) {
            if (practicalChallenges[i].id === currentPracticalId) { challenge = practicalChallenges[i]; break; }
        }
        if (!challenge) return;


        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/contest.php?id=<?php echo $ContestId ?>');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var isCorrect = xhr.responseText;
                var fb = document.getElementById('practical-feedback');
                fb.style.display = 'block';
                if (isCorrect == "true") {
                    fb.innerHTML = '<span style="color:var(--accent);">✅ 回答正确！获得 '+challenge.score+' 分</span>';
                    updateScoreAndRank();
                    renderChallengeGrid();
                    setTimeout(() => {location.reload()}, 650);
                } else {
                    fb.innerHTML = '<span style="color:var(--danger);">❌ 回答错误，请重试</span>';
                }
                document.getElementById('practical-answer').value = '';
            } else {
                showToast('提交失败请重试', 'error');
            }
            setTimeout(() => {location.reload()}, 1500);
        };

        xhr.send("func=sc&id=" + challenge.id + "&answer=" + answer + "&verify_code=" + document.getElementById("captcha-input").value);
    }

    function showToast(msg, type) {
        type = type || 'success';
        var t = document.createElement('div');
        t.className = 'toast '+type;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function(){ t.remove(); }, 2200);
    }

    init();
</script>
</body>
</html>
<?php
                                exit();
                            }
                            else {
                                echo "<br/><center><h1>比 赛 已 结 束</h1></center>";
                                exit();
                            }
                        }
                        else {
                            echo "<br/><center><h1>比 赛 未 开 始</h1></center>";
                            exit();
                        }
                        
                    }
                }
            }
        }

    }

}


?>
<br/>
<center>
    <br/>
    <h1>知 攻 善 防  |  遇 弱 则 强</h1>
    <br/>
    <h1>焉 知 攻  |  何 知 防</h1>
</center>

