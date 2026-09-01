<?php
/* Powered By c4e3bac3@foxmail.com  Hello */
session_start();

if (isset($_SESSION["Administrator"]) && $_SESSION["Administrator"] === "Administrator") {
    if (isset($_POST["func"])) {
        $func = $_POST["func"];
        if($func === "userlist") {
            include "../config.php";
            $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
            mysqli_query($mysql_conn, "use ". $DataBase["db_name"]);
            $response = mysqli_query($mysql_conn, "select username,id,email from user;");
            if (mysqli_num_rows($response) === 0) {
                echo "None";
            } else {
                $jsonID = 0;
                $string = "[";
                while ($row = mysqli_fetch_assoc($response)) {
                    $string = $string . "{\"studentid\":\"" . $row['id'] . "\",\"username\":\"" . $row["username"] . "\",\"email\":\"" . $row["email"] . "\"},";
                    $jsonID = $jsonID + 1;
                }
                $string = substr($string, 0, -1);
                $string = $string . "]";
                echo $string;
                
            }
            exit();
        }
        else if ($func === "changepassword") {
            include "../config.php";
            $studentid = htmlspecialchars($_POST["studentid"], ENT_QUOTES);
            $password = htmlspecialchars($_POST["password"], ENT_QUOTES);
            if (ctype_digit($studentid) === true) {
                $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                mysqli_query($mysql_conn, "use ". $DataBase["db_name"]);
                $stmt = mysqli_prepare($mysql_conn, "UPDATE user set password = ? where id = ?");
                $password = hash("sha512", $password);
                mysqli_stmt_bind_param($stmt, 'ss', $password, $studentid);
                mysqli_stmt_execute($stmt);
            }
            exit();
        }
        else if ($func === "deleteuser") {
            include "../config.php";
            $studentid = htmlspecialchars($_POST["studentid"], ENT_QUOTES);
            if (ctype_digit($studentid) === true) {
                $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                mysqli_query($mysql_conn, "use ". $DataBase["db_name"]);
                $stmt = mysqli_prepare($mysql_conn, "DELETE FROM user WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 's', $studentid);
                mysqli_stmt_execute($stmt);

            }
            exit();
        }
        else if ($func === "CreateCompetition") {
            include "../config.php";
            $cmtnname = htmlspecialchars($_POST["cmtnname"], ENT_QUOTES);
            $start_time = htmlspecialchars($_POST["start_time"], ENT_QUOTES);
            $end_time = htmlspecialchars($_POST["end_time"], ENT_QUOTES);
            $id = hash("md5", $cmtnname);

            if(strtotime($start_time) <= strtotime($end_time)) {
                $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");
                
                $result = mysqli_query($mysql_conn, "SELECT id FROM cmtn WHERE id = \"" . $id . "\";");
                if (mysqli_num_rows($result) === 0) {
                    $stmt = mysqli_prepare($mysql_conn, "INSERT into cmtn (id, name, start_time, end_time) value (?, ?, ?, ?);");
                    mysqli_stmt_bind_param($stmt, 'ssss', $id, $cmtnname, $start_time, $end_time);
                    mysqli_stmt_execute($stmt);
                    if (ctype_xdigit($id)) {
                        mysqli_query($mysql_conn, "create table " . $id . "_ll (id char(255) PRIMARY KEY, optionA char(255), optionB char(255), optionC char(255), optionD char(255), correct char(255), stem char(255), score int(255));");
                        mysqli_query($mysql_conn, "create table " . $id . "_sc (id char(255) PRIMARY KEY, name char(255), timu char(255), flag char(255), add_score int(255), base_score int(255), type int(1));");
                        mysqli_query($mysql_conn, "create table " . $id . " (TitleID char(255), studentid char(255), score int(255));");
                        mysqli_query($mysql_conn, "create table " . $id . "_pm (studentid char(255) PRIMARY KEY, score int(255));");
                        mysqli_query($mysql_conn, "create table " . $id . "_container (time char(255) PRIMARY KEY, studentid char(255), answer char(255), ContestId char(255), ContainerId char(255), message char(255), TrueFalse int(1), type int(1));");
                    }
                }
            }
            
            exit();

        }
        else if ($func === "CompetitionList") {
            include "../config.php";
            $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
            mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");
            $result = mysqli_query($mysql_conn, "select id, name, start_time, end_time from cmtn;");
            if (mysqli_num_rows($result) === 0) {
                echo "None";
            } else {
                $string = "[";
                while ($row = mysqli_fetch_assoc($result)) {
                    $string = $string . "{\"id\":\"". $row["id"] . "\", \"name\":\"" . $row["name"] . "\", \"start\":\"" . $row["start_time"] . "\", \"end\":\"" . $row["end_time"] . "\"},";
                }
                $string = substr($string, 0, -1);
                $string = $string . "]";
                echo $string;
            }
            
            exit();

        }
        else if ($func === "CompetitionDelete") {
            if (isset($_POST["id"])) {
                include "../config.php";
                $id = htmlspecialchars($_POST["id"], ENT_QUOTES);
                
                if (ctype_xdigit($id)) {
                    $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                    mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");

                    $result = mysqli_query($mysql_conn, "SELECT id FROM cmtn WHERE id = \"" . $id . "\";");

                    if (mysqli_num_rows($result) === 0) {
                        echo "";
                    }
                    else {
                        $stmt = mysqli_prepare($mysql_conn, "DELETE FROM cmtn WHERE id = ?;");
                        mysqli_stmt_bind_param($stmt, 's', $id);
                        mysqli_stmt_execute($stmt);

                        mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");
                        $stmt = mysqli_prepare($mysql_conn, "DELETE FROM cmtn WHERE id = ?;");
                        mysqli_stmt_bind_param($stmt, 's', $id);
                        mysqli_stmt_execute($stmt);

                        mysqli_query($mysql_conn, "drop table " . $id . "_ll;");
                        mysqli_query($mysql_conn, "drop table " . $id . "_sc;");
                        mysqli_query($mysql_conn, "drop table " . $id . ";");
                        mysqli_query($mysql_conn, "drop table " . $id . "_pm;");
                        mysqli_query($mysql_conn, "drop table " . $id . "_container;");
                    }
                }
            }
            
            exit();

        }
        else if ($func === "CompetitionSave") {
            if (isset($_POST["id"])) {
                include "../config.php";
                $id = htmlspecialchars($_POST["id"], ENT_QUOTES);
                $ll_json = $_POST["ll"];
                $sc_json = $_POST["sc"];
                $ll = json_decode($ll_json);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo "JSON 解析错误";
                    exit();
                }
                $sc = json_decode($sc_json);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo "JSON 解析错误";
                    exit();
                }
                $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");

                if (ctype_xdigit($id)) {
                    $result = mysqli_query($mysql_conn, "SELECT id FROM cmtn WHERE id = \"" . $id . "\";");
                    if (mysqli_num_rows($result) === 0) {
                        echo "";
                    }
                    else {
                        foreach($ll as $i) {
                            $i->stem = htmlspecialchars($i->stem, ENT_QUOTES);
                            $i->id = hash("md5", $i->stem);
                            $i->optionA = htmlspecialchars($i->optionA, ENT_QUOTES);
                            $i->optionB = htmlspecialchars($i->optionB, ENT_QUOTES);
                            $i->optionC = htmlspecialchars($i->optionC, ENT_QUOTES);
                            $i->optionD = htmlspecialchars($i->optionD, ENT_QUOTES);
                            $i->score = htmlspecialchars($i->score, ENT_QUOTES);
                            $i->correct = htmlspecialchars($i->correct, ENT_QUOTES);
                            
                            if (!ctype_digit($i->score)) {
                                exit();
                            }
                        }
                        
                        foreach($sc as $i) {
                            $i->name = htmlspecialchars($i->name, ENT_QUOTES);
                            $i->id = hash("md5", $i->name);
                            $i->desc = htmlspecialchars($i->desc, ENT_QUOTES);
                            $i->answer = htmlspecialchars($i->answer, ENT_QUOTES);
                            $i->add_score = htmlspecialchars($i->add_score, ENT_QUOTES);
                            $i->base_score = htmlspecialchars($i->base_score, ENT_QUOTES);
                            if (!ctype_digit($i->add_score)) {
                                exit();
                            }
                            if (!ctype_digit($i->base_score)) {
                                exit();
                            }
                            if (!ctype_digit($i->answer_type)) {
                                exit();
                            }
                            
                        }
                        mysqli_query($mysql_conn, "delete from " . $id . "_sc;");
                        mysqli_query($mysql_conn, "delete from " . $id . "_ll;");
                        foreach($ll as $i) {
                            $stmt = mysqli_prepare($mysql_conn, "INSERT INTO " . $id . "_ll(id, optionA, optionB, optionC, optionD, correct, stem, score) value(?, ?, ?, ?, ?, ?, ?, ?)");
                            mysqli_stmt_bind_param($stmt, 'sssssssi', $i->id, $i->optionA, $i->optionB, $i->optionC, $i->optionD, $i->correct, $i->stem, $i->score);
                            mysqli_stmt_execute($stmt);
                        }
                        foreach($sc as $i) {
                            $stmt = mysqli_prepare($mysql_conn, "INSERT INTO " . $id . "_sc(id, name, timu, flag, add_score, base_score, type) value(?, ?, ?, ?, ?, ?, ?)");
                            mysqli_stmt_bind_param($stmt, 'ssssiii', $i->id, $i->name, $i->desc, $i->answer, $i->add_score, $i->base_score, $i->answer_type);
                            mysqli_stmt_execute($stmt);
                        }
                    }
                    
                }
            }

            exit();

        }
        else if ($func === "TitleList") {
            if (isset($_POST["id"])) {
                $id = htmlspecialchars($_POST["id"], ENT_QUOTES);
                if (ctype_xdigit($id)) {
                    include "../config.php";
                    $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                    mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");

                    $string = "{";
                    $result = mysqli_query($mysql_conn, "SELECT id, optionA, optionB, optionC, optionD, correct, stem, score FROM " . $id . "_ll;");
                    if (mysqli_num_rows($result) === 0) {
                        $string = $string . '"Theory": "None"';
                    } else {
                        $Theory = '"Theory": [';
                        while ($row = mysqli_fetch_assoc($result)) {
                            $Theory = $Theory . '{"id": "' . $row["id"] . '", "optionA": "' . $row["optionA"] . '", "optionB": "' . $row["optionB"] . '", "optionC": "' . $row["optionC"] . '", "optionD": "' . $row["optionD"] . '", "correct": "' . $row["correct"] . '", "stem": "' . str_replace("\n", "\\n", $row["stem"]) . '", "score": "' . $row["score"] . '"},';
                        }
                        $Theory = substr($Theory, 0, -1);
                        $Theory = $Theory . "]";
                        $string = $string . $Theory;
                    }
                    $string = $string . ",";
                    
                    $result = mysqli_query($mysql_conn, "SELECT id, name, timu, flag, add_score, base_score, type FROM " . $id . "_sc;");
                    if (mysqli_num_rows($result) === 0) {
                        $string = $string . '"Practical": "None"';
                    } else {
                        $Practical = '"Practical": [';
                        while ($row = mysqli_fetch_assoc($result)) {
                            $Practical = $Practical . '{"id": "' . $row["id"] . '", "name": "' . str_replace("\n", "\\n", $row["name"]) . '", "desc": "' . str_replace("\n", "\\n", $row["timu"]) . '", "answer": "' . $row["flag"] . '", "add_score": "' . $row["add_score"] . '", "base_score": "' . $row["base_score"] . '", "answer_type": "' . $row["type"] . '"},';
                        }
                        $Practical = substr($Practical, 0, -1);
                        $Practical = $Practical . "]";
                        $string = $string . $Practical;
                    }
                    $string = $string . "}";
                    echo $string;

                }
            }

            exit();
        }
        else if ($func === "EditMessage") {
            if (isset($_POST["id"])) {
                if (isset($_POST["content"])) {
                    $content = base64_decode($_POST["content"]);
                    $content = htmlspecialchars($content, ENT_QUOTES);
                    
                    if (strlen($content) > 254) {
                        exit();
                    }

                    $id = htmlspecialchars($_POST["id"], ENT_QUOTES);

                    if (ctype_xdigit($id)) {
                        include "../config.php";
                        $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                        mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");

                        $stmt = mysqli_prepare($mysql_conn, "update cmtn set message = ? where id = ? ;");
                        mysqli_stmt_bind_param($stmt, 'ss', $content, $id);
                        mysqli_stmt_execute($stmt);
                    }
                }
            }

            exit();
        }
        else if ($func === "GetMessage") {
            if (isset($_POST["id"])) {
                $id = htmlspecialchars($_POST["id"], ENT_QUOTES);
                if (ctype_xdigit($id)) {
                    include "../config.php";
                    $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
                    mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");
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
    }
}
else {echo "<br/><center><br/><h1> Crazy Thursday vivo 50 ! </h1></center>";exit();}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OpenCTF · 管理面板</title>
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
            background: var(--bg);
            color: var(--text);
            font-family: 'SF Mono', 'Consolas', 'Monaco', 'Courier New', monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 30px 20px;
            min-height: 100vh;
        }
        .container {margin-top: 25px;width:100%; max-width:1100px; display:flex; gap:20px; }
        .sidebar { width:160px; flex-shrink:0; display:flex; flex-direction:column; gap:12px; }
        .nav-btn {
            background:var(--card);
            border:1px solid var(--border);
            border-radius:6px;
            color:var(--muted);
            padding:14px 16px;
            cursor:pointer;
            font:inherit;
            font-size:0.9rem;
            text-align:left;
            transition:0.15s;
            letter-spacing:0.5px;
        }
        .nav-btn.active { border-color:var(--accent); color:var(--accent); box-shadow:0 0 0 2px rgba(63,185,80,0.1); }
        .nav-btn:hover { color:#e6edf3; background:#1c2128; }
        .main { flex:1; min-width:0; }
        .section { display:none; }
        .section.active { display:block; }
        .card {
            background:var(--card);
            border:1px solid var(--border);
            border-radius:8px;
            padding:24px;
            min-height:400px;
        }
        .section-title {
            font-size:1rem;
            font-weight:600;
            margin-bottom:16px;
            display:flex;
            justify-content:space-between;
            align-items:center;
        }
        table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        th, td { padding:10px 8px; border-bottom:1px solid var(--border); text-align:left; font-size:0.85rem; }
        th { color:var(--muted); font-weight:500; }
        .actions { display:flex; gap:6px; flex-wrap:wrap; }
        .btn {
            padding:6px 12px;
            background:var(--accent);
            color:#fff;
            border:none;
            border-radius:4px;
            font-size:0.75rem;
            font-weight:600;
            cursor:pointer;
            font:inherit;
            letter-spacing:0.3px;
            transition:0.15s;
            white-space:nowrap;
        }
        .btn:hover { background:var(--accent-hover); }
        .btn.danger { background:var(--danger); }
        .btn.danger:hover { background:var(--danger-hover); }
        .btn.small { padding:4px 8px; font-size:0.7rem; }
        input, textarea, select {
            width:100%;
            background:var(--input-bg);
            border:1px solid var(--border);
            border-radius:5px;
            color:var(--text);
            padding:8px 10px;
            font-size:0.85rem;
            font:inherit;
            outline:none;
            transition:0.15s;
        }
        input:focus, textarea:focus, select:focus { border-color:var(--accent); }
        textarea { resize:vertical; min-height:60px; }
        .field { margin-bottom:14px; }
        .field label { display:block; font-size:0.78rem; color:var(--muted); margin-bottom:4px; }
        .modal {
            position:fixed;
            top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.7);
            display:none;
            align-items:center;
            justify-content:center;
            z-index:1000;
        }
        .modal.active { display:flex; }
        .modal-content {
            background:var(--card);
            border:1px solid var(--border);
            border-radius:8px;
            padding:24px;
            width:90%;
            max-width:700px;
            max-height:85vh;
            overflow-y:auto;
            position:relative;
        }
        .modal-close {
            position:absolute;
            top:12px;
            right:16px;
            background:none;
            border:none;
            color:var(--muted);
            font-size:1.4rem;
            cursor:pointer;
            line-height:1;
        }
        .modal-close:hover { color:var(--text); }
        .modal-title { font-size:1.1rem; font-weight:600; margin-bottom:16px; }
        .problem-editor {
            border:1px solid var(--border);
            border-radius:6px;
            padding:12px;
            margin-bottom:12px;
            background:#0d1117;
            position:relative;
        }
        .problem-editor .remove-btn {
            position:absolute;
            top:8px;
            right:8px;
            background:var(--danger);
            color:#fff;
            border:none;
            border-radius:4px;
            padding:2px 8px;
            font-size:0.7rem;
            cursor:pointer;
        }
        .options-grid {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:8px;
            margin-top:8px;
        }
        .toast {
            position:fixed;
            top:20px;
            left:50%;
            transform:translateX(-50%);
            padding:10px 20px;
            border-radius:5px;
            font-size:0.85rem;
            z-index:9999;
            animation:fadeIn 0.25s ease;
            pointer-events:none;
        }
        .toast.success { background:#14281a; color:#3fb950; border:1px solid #1f3d28; }
        .toast.error { background:#3d1418; color:#f85149; border:1px solid #5c1f24; }
        @keyframes fadeIn {
            from { opacity:0; transform:translateX(-50%) translateY(-10px); }
            to { opacity:1; transform:translateX(-50%) translateY(0); }
        }
        .empty-hint {
            color:var(--muted);
            text-align:center;
            padding:20px;
            font-size:0.85rem;
        }
        .datetime-row { display:flex; gap:8px; }
        .datetime-row .field { flex:1; }
        .page-header {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--accent);
            width: 100%;
            margin: 0px 0 5px 0;
        }
        .page-header span { color:var(--text); }
        .page-subtitle {
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
            margin-bottom: 20px;
            width: 100%;
        }
        #announcement-content { min-height:180px; }
        .answer-type-group label { margin-right: 16px; font-size:0.8rem; }
        .answer-type-group input[type="radio"] { width:auto; margin-right:4px; }
    </style>
</head>
<body>
<div class="page-header">Open<span>CTF</span></div>
<div class="page-subtitle">Powered by c4e3bac3@foxmail.com</div>

<div class="container">
    <div class="sidebar">
        <button class="nav-btn active" id="nav-users" onclick="switchPanel('users')">用户列表</button>
        <button class="nav-btn" id="nav-contests" onclick="switchPanel('contests')">比赛管理</button>
    </div>

    <div class="main">
        <div class="section active card" id="section-users">
            <div class="section-title">用户列表</div>
            <table>
                <thead><tr><th>用户名</th><th>学号</th><th>邮箱</th><th>操作</th></tr></thead>
                <tbody id="user-tbody"></tbody>
            </table>
            <div id="user-empty" class="empty-hint" style="display:none;">暂无用户</div>
        </div>

        <div class="section card" id="section-contests">
            <div class="section-title">
                <span>比赛列表</span>
                <button class="btn small" onclick="openCreateContestModal()">+ 创建比赛</button>
            </div>
            <table>
                <thead><tr><th>名称</th><th>开始时间</th><th>结束时间</th><th>操作</th></tr></thead>
                <tbody id="contest-tbody"></tbody>
            </table>
            <div id="contest-empty" class="empty-hint" style="display:none;">暂无比赛</div>
        </div>
    </div>
</div>

<div class="modal" id="create-contest-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('create-contest-modal')">&times;</button>
        <div class="modal-title">创建新比赛</div>
        <div class="field">
            <label>比赛名称</label>
            <input type="text" id="new-contest-name" placeholder="比赛名称">
        </div>
        <div class="field">
            <label>开始时间</label>
            <div class="datetime-row">
                <div class="field"><input type="date" id="new-contest-start-date"></div>
                <div class="field"><input type="time" id="new-contest-start-time" value="09:00"></div>
            </div>
        </div>
        <div class="field">
            <label>结束时间</label>
            <div class="datetime-row">
                <div class="field"><input type="date" id="new-contest-end-date"></div>
                <div class="field"><input type="time" id="new-contest-end-time" value="18:00"></div>
            </div>
        </div>
        <div style="display:flex; gap:8px; margin-top:16px;">
            <button class="btn" onclick="createContest()">创建</button>
            <button class="btn danger" onclick="closeModal('create-contest-modal')">取消</button>
        </div>
    </div>
</div>

<div class="modal" id="edit-problems-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('edit-problems-modal')">&times;</button>
        <div class="modal-title" id="edit-modal-title">编辑题目</div>

        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-weight:600;">📝 理论题</span>
                <button class="btn small" onclick="addTheoryProblem()">+ 添加理论题</button>
            </div>
            <div id="theory-list"></div>
        </div>

        <hr style="border-color:var(--border); margin:16px 0;">

        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <span style="font-weight:600;">🔧 实操题</span>
                <button class="btn small" onclick="addPracticalProblem()">+ 添加实操题</button>
            </div>
            <div id="practical-list"></div>
        </div>

        <div style="display:flex; gap:8px; margin-top:20px;">
            <button class="btn" onclick="saveProblems()">保存题目</button>
            <button class="btn danger" onclick="closeModal('edit-problems-modal')">取消</button>
        </div>
    </div>
</div>

<div class="modal" id="edit-announcement-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal('edit-announcement-modal')">&times;</button>
        <div class="modal-title" id="announcement-modal-title">编辑公告</div>
        <div class="field">
            <label>公告内容</label>
            <textarea id="announcement-content" rows="8" placeholder="请输入公告内容…"></textarea>
        </div>
        <div style="display:flex; gap:8px; margin-top:16px;">
            <button class="btn" onclick="saveAnnouncement()">保存</button>
            <button class="btn danger" onclick="closeModal('edit-announcement-modal')">取消</button>
        </div>
    </div>
</div>

<script>
    var users = [];
    var contests = [];
    var editingContestId = null;
    var editingAnnouncementContestId = null;

    function switchPanel(panel) {
        document.getElementById('nav-users').classList.toggle('active', panel === 'users');
        document.getElementById('nav-contests').classList.toggle('active', panel === 'contests');
        document.getElementById('section-users').classList.toggle('active', panel === 'users');
        document.getElementById('section-contests').classList.toggle('active', panel === 'contests');

        if (panel === 'users') {
            loadUsers();
        } else {
            loadContests();
        }
    }

    function loadUsers() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    if (xhr.responseText == "None"){
                        showToast('没有用户数据', 'info');
                    }
                    else {
                        var raw = JSON.parse(xhr.responseText);
                        if (Array.isArray(raw)) {
                            users = raw;
                        } else if (typeof raw === 'object' && raw !== null) {
                            users = Object.values(raw);
                        } else {
                            users = [];
                        }
                        users = users.map(function(u, idx) {
                            if (!u.id) u.id = u.studentid || ('user_' + idx);
                            return u;
                        });
                        renderUsers();
                    }

                } catch (e) {
                    showToast('用户数据解析失败: ' + e.message, 'error');
                    users = [];
                    renderUsers();
                }
            } else {
                showToast('获取用户列表失败 (状态码: ' + xhr.status + ')', 'error');
                users = [];
                renderUsers();
            }
        };
        xhr.onerror = function() {
            showToast('网络错误，无法连接用户列表API', 'error');
            users = [];
            renderUsers();
        };
        xhr.send("func=userlist");
    }

    function renderUsers() {
        var tbody = document.getElementById('user-tbody');
        var empty = document.getElementById('user-empty');
        tbody.innerHTML = '';

        if (users.length === 0) {
            empty.style.display = 'block';
            return;
        }
        empty.style.display = 'none';

        for (var i = 0; i < users.length; i++) {
            var u = users[i];
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + u.username + '</td>' +
                '<td>' + (u.studentid || '-') + '</td>' +
                '<td>' + (u.email || '-') + '</td>' +
                '<td class="actions">' +
                    '<button class="btn small" onclick="changePassword(\'' + u.studentid + '\')">改密码</button>' +
                    '<button class="btn small danger" onclick="deleteUser(\'' + u.studentid + '\')">删除</button>' +
                '</td>';
            tbody.appendChild(tr);
        }
    }

    function changePassword(userId) {
        var newPwd = prompt('请输入新密码：');
        if (!newPwd || !newPwd.trim()) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                showToast('密码修改成功', 'success');
            } else {
                var res = JSON.parse(xhr.responseText);
                showToast(res.message || '修改失败', 'error');
            }
        };
        xhr.send("studentid=" + userId + "&password=" + newPwd + "&func=changepassword");

        showToast('用户 ' + userId +' 密码已更新', 'success');
    }

    function deleteUser(userId) {
        if (!confirm('确定删除该用户吗？')) return;
        users = users.filter(function(u) { return u.id !== userId; });
        renderUsers();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                showToast('用户已删除', 'success');
                loadUsers();
            } else {
                var res = JSON.parse(xhr.responseText);
                showToast(res.message || '删除失败', 'error');
            }
        };
        xhr.send("studentid=" + userId + "&func=deleteuser");

        loadUsers();
        showToast('用户已删除', 'success');
    }

    function loadContests() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    if (xhr.responseText == "None") {
                        showToast('没有比赛', 'success');
                    }
                    else {
                        contests = JSON.parse(xhr.responseText);
                        renderContests();
                    }

                } catch (e) {
                    showToast('比赛数据解析失败', 'error');
                }
            }
        };
        xhr.send("func=CompetitionList");
    }


    function renderContests() {
        var tbody = document.getElementById('contest-tbody');
        var empty = document.getElementById('contest-empty');
        tbody.innerHTML = '';

        if (contests.length === 0) {
            empty.style.display = 'block';
            return;
        }
        empty.style.display = 'none';

        for (var i = 0; i < contests.length; i++) {
            var c = contests[i];
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + c.name + '</td>' +
                '<td>' + (c.start ? c.start.replace('T', ' ') : '-') + '</td>' +
                '<td>' + (c.end ? c.end.replace('T', ' ') : '-') + '</td>' +
                '<td class="actions">' +
                    '<button class="btn small" onclick="editProblems(\'' + c.id + '\')">编辑题目</button>' +
                    '<button class="btn small danger" onclick="deleteContest(\'' + c.id + '\')">删除</button>' +
                    '<button class="btn small" onclick="openScreen(\'' + c.id + '\')">大屏</button>' +
                    
                    '<button class="btn small" onclick="editAnnouncement(\'' + c.id + '\')">编辑公告</button>' +
                '</td>';
            tbody.appendChild(tr);
        }
    }

    function openCreateContestModal() {
        document.getElementById('create-contest-modal').classList.add('active');
        document.getElementById('new-contest-name').value = '';
        document.getElementById('new-contest-start-date').value = '';
        document.getElementById('new-contest-end-date').value = '';
        document.getElementById('new-contest-start-time').value = '09:00';
        document.getElementById('new-contest-end-time').value = '18:00';
    }

    function createContest() {
        var name = document.getElementById('new-contest-name').value.trim();
        if (!name) return showToast('请输入比赛名称', 'error');
        var startDate = document.getElementById('new-contest-start-date').value;
        var startTime = document.getElementById('new-contest-start-time').value;
        var endDate = document.getElementById('new-contest-end-date').value;
        var endTime = document.getElementById('new-contest-end-time').value;
        if (!startDate || !startTime || !endDate || !endTime) {
            return showToast('请完整填写开始和结束时间', 'error');
        }
        var start = startDate + ' ' + startTime;
        var end = endDate + ' ' + endTime;
        var payload = "cmtnname=" + name + "&start_time=" + start + "&end_time=" + end + "&func=CreateCompetition";
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 201 || xhr.status === 200) {
                showToast('比赛创建成功', 'success');
                closeModal('create-contest-modal');
                loadContests();
            } else {
                try {
                    showToast(xhr.responseText || '创建失败', 'error');
                } catch (e) {
                    showToast('创建失败，状态码: ' + xhr.status, 'error');
                }
            }
        };
        xhr.onerror = function() {
            showToast('网络错误，无法创建比赛', 'error');
        };
        document.getElementById('create-contest-modal').classList.remove('active');
        xhr.send(payload);
    }

    function deleteContest(id) {
        if (!confirm('确定删除该比赛吗？')) return;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200 || xhr.status === 204) {
                showToast('比赛已删除', 'success');

            } else {
                showToast('删除失败', 'error');
            }
        };
        xhr.onerror = function() {
            showToast('网络错误', 'error');
        };
        xhr.send("id=" + id + "&func=CompetitionDelete");
        loadContests();
        setTimeout(() => {
            location.reload()
        }, 250);
    }

    function editProblems(contestId) {
        editingContestId = contestId;
        document.getElementById('edit-modal-title').innerHTML = "<input id=\"debug\" type=\"hidden\" value=\"" + contestId + "\" />编辑题目";
        document.getElementById('edit-problems-modal').classList.add('active');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200 || xhr.status === 204) {
                var object = JSON.parse(xhr.responseText);
                if (object["Practical"] != "None") {
                    renderPracticalList(object["Practical"]);
                }
                if (object["Theory"] != "None") {
                    renderTheoryList(object["Theory"]);
                }
            } else {
                showToast('错误', 'error');
            }
        };
        xhr.onerror = function() {
            showToast('网络错误', 'error');
        };
        xhr.send("id=" + contestId + "&func=TitleList");

    }

    function renderTheoryList(theoryList) {
        var container = document.getElementById('theory-list');
        container.innerHTML = '';
        for (var i = 0; i < theoryList.length; i++) {
            var t = theoryList[i];
            var html =
                '<div class="problem-editor" data-theory-id="' + t.id + '">' +
                    '<button class="remove-btn" onclick="removeTheory(\'' + t.id + '\')">×</button>' +
                    '<div class="field"><label>题干</label><textarea class="t-stem">' + (t.stem || '') + '</textarea></div>' +
                    '<div class="field"><label>分数</label><input type="text" id="score" value="' + (t.score || '') + '" placeholder="分数"></div>' +
                    '<div class="options-grid">' +
                        '<div class="field"><label>A</label><input type="text" class="t-opt" data-opt="A" value="' + (t.optionA || '') + '"></div>' +
                        '<div class="field"><label>B</label><input type="text" class="t-opt" data-opt="B" value="' + (t.optionB || '') + '"></div>' +
                        '<div class="field"><label>C</label><input type="text" class="t-opt" data-opt="C" value="' + (t.optionC || '') + '"></div>' +
                        '<div class="field"><label>D</label><input type="text" class="t-opt" data-opt="D" value="' + (t.optionD || '') + '"></div>' +
                    '</div>' +
                    '<div class="field"><label>正确答案</label>' +
                        '<select class="t-correct">' +
                            '<option value="A"' + (t.correct === 'A' ? ' selected' : '') + '>A</option>' +
                            '<option value="B"' + (t.correct === 'B' ? ' selected' : '') + '>B</option>' +
                            '<option value="C"' + (t.correct === 'C' ? ' selected' : '') + '>C</option>' +
                            '<option value="D"' + (t.correct === 'D' ? ' selected' : '') + '>D</option>' +
                        '</select>' +
                    '</div>' +
                '</div>';
            container.innerHTML += html;
        }
    }

    function addTheoryProblem() {
        var container = document.getElementById('theory-list');
        var html =
            '<div class="problem-editor" data-theory-id="null">' +
                '<button class="remove-btn" onclick="removeTheory(\'null\')">×</button>' +
                '<div class="field"><label>题干</label><textarea class="t-stem"></textarea></div>' +
                '<div class="field"><label>分数</label><input type="text" id="score" placeholder="分数"></div>' +
                '<div class="options-grid">' +
                    '<div class="field"><label>A</label><input type="text" class="t-opt" data-opt="A"></div>' +
                    '<div class="field"><label>B</label><input type="text" class="t-opt" data-opt="B"></div>' +
                    '<div class="field"><label>C</label><input type="text" class="t-opt" data-opt="C"></div>' +
                    '<div class="field"><label>D</label><input type="text" class="t-opt" data-opt="D"></div>' +
                '</div>' +
                '<div class="field"><label>正确答案</label>' +
                    '<select class="t-correct">' +
                        '<option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>' +
                    '</select>' +
                '</div>' +
            '</div>';
        container.innerHTML += html;
    }

    function removeTheory(id) {
        var el = document.querySelector('[data-theory-id="' + id + '"]');
        if (el) el.remove();
    }

    function renderPracticalList(practicalList) {
        var container = document.getElementById('practical-list');
        container.innerHTML = '';
        for (var i = 0; i < practicalList.length; i++) {
            var p = practicalList[i];
            var answerType = p.answer_type || '0';
            var answerValue = p.answer || '';
            var html =
                '<div class="problem-editor" data-practical-id="' + p.id + '">' +
                    '<button class="remove-btn" onclick="removePractical(\'' + p.id + '\')">×</button>' +
                    '<div class="field"><label>题目名称</label><input type="text" class="p-name" value="' + (p.name || '') + '" placeholder="实操题名称"></div>' +
                    '<div class="field"><label>题目描述</label><textarea class="p-desc">' + (p.desc || '') + '</textarea></div>' +
                    '<div class="field answer-type-group"><label>答案类型</label><br>' +
                        '<label><input type="radio" class="answer-type-radio" name="answer_type_' + i + '" value="1" ' + (answerType === '1' ? 'checked' : '') + ' onchange=""> 固定答案</label>' +
                        '<label><input type="radio" class="answer-type-radio" name="answer_type_' + i + '" value="2" ' + (answerType === '2' ? 'checked' : '') + ' onchange=""> 有限制动态答案</label>' +
                        '<label><input type="radio" class="answer-type-radio" name="answer_type_' + i + '" value="3" ' + (answerType === '3' ? 'checked' : '') + ' onchange=""> 无限制动态答案</label>' +
                    '</div>' +
                    '<div class="field"><label id="answer-label-' + i + '">答案</label><input type="text" class="p-answer" value="' + answerValue + '" placeholder="请输入固定答案或动态 flag 的 API"></div>' +
                    '<div class="field"><label>基础分数</label><input type="text" class="p-base-score" value="' + (p.base_score || '') + '" placeholder="基础分数"></div>' +
                    '<div class="field"><label>附加分数</label><input type="text" class="p-add-score" value="' + (p.add_score || '') + '" placeholder="附加分数"></div>' +
                '</div>';
            container.innerHTML += html;
        }
    }

    function addPracticalProblem() {
        var container = document.getElementById('practical-list');
        var idx = Date.now();
        var html =
            '<div class="problem-editor" data-practical-id="null">' +
                '<button class="remove-btn" onclick="removePractical(\'null\')">×</button>' +
                '<div class="field"><label>题目名称</label><input type="text" class="p-name" placeholder="实操题名称"></div>' +
                '<div class="field"><label>题目描述</label><textarea class="p-desc"></textarea></div>' +
                '<div class="field answer-type-group"><label>答案类型</label><br>' +
                    '<label><input type="radio" class="answer-type-radio" name="answer_type_' + idx + '" value="1" checked onchange=""> 固定答案</label>' +
                    '<label><input type="radio" class="answer-type-radio" name="answer_type_' + idx + '" value="2" onchange=""> 有限制动态答案</label>' +
                    '<label><input type="radio" class="answer-type-radio" name="answer_type_' + idx + '" value="3" onchange=""> 无限制动态答案</label>' +
                '</div>' +
                '<div class="field"><label id="answer-label-' + idx + '">答案</label><input type="text" class="p-answer" placeholder="请输入固定答案或动态 flag 的 API"></div>' +
                '<div class="field"><label>基础分数</label><input type="text" class="p-base-score" placeholder="基础分数"></div>' +
                '<div class="field"><label>附加分数</label><input type="text" class="p-add-score" placeholder="附加分数"></div>' +
            '</div>';
        container.innerHTML += html;
    }

    function removePractical(id) {
        var el = document.querySelector('[data-practical-id="' + id + '"]');
        if (el) el.remove();
    }

    function saveProblems() {
        var theoryEditors = document.querySelectorAll('#theory-list .problem-editor');
        var practicalEditors = document.querySelectorAll('#practical-list .problem-editor');
        var theoryPayload = [];
        for (var i = 0; i < theoryEditors.length; i++) {
            var el = theoryEditors[i];
            var stem = el.querySelector('.t-stem').value.trim();
            if (!stem) continue;

            var id = el.getAttribute('data-theory-id');
            var opts = el.querySelectorAll('.t-opt');
            theoryPayload.push({
                id: id !== 'null' ? id : null,
                stem: stem,
                optionA: opts[0].value.trim(),
                optionB: opts[1].value.trim(),
                optionC: opts[2].value.trim(),
                optionD: opts[3].value.trim(),
                correct: el.querySelector('.t-correct').value,
                score: el.querySelector('#score').value
            });
        }
        var practicalPayload = [];
        for (var j = 0; j < practicalEditors.length; j++) {
            var el = practicalEditors[j];
            var name = el.querySelector('.p-name').value.trim();
            var desc = el.querySelector('.p-desc').value.trim();
            if (!name && !desc) continue;

            var id = el.getAttribute('data-practical-id');
            var answerTypeRadio = el.querySelector('.answer-type-radio:checked');
            var answerType = answerTypeRadio ? answerTypeRadio.value : '1';
            var answerInput = el.querySelector('.p-answer');
            var answer = answerInput ? answerInput.value.trim() : '';
            var baseScore = el.querySelector('.p-base-score').value.trim();
            var addScore = el.querySelector('.p-add-score').value.trim();

            practicalPayload.push({
                id: id !== 'null' ? id : null,
                name: name,
                desc: desc,
                answer: answer,
                answer_type: answerType,
                base_score: baseScore,
                add_score: addScore
            });
        }
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                showToast('题目保存成功', 'success');
                closeModal('edit-problems-modal');
            } else {
                try {
                    var res = JSON.parse(xhr.responseText);
                    showToast(res.message || '保存失败', 'error');
                } catch (e) {
                    showToast('保存失败 (状态码: ' + xhr.status + ')', 'error');
                }
            }
        };
        xhr.onerror = function() {
            showToast('网络错误，无法保存题目', 'error');
        };
        xhr.send("func=CompetitionSave&ll=" + JSON.stringify(theoryPayload) + "&sc=" + JSON.stringify(practicalPayload) + "&id=" + document.getElementById("debug").value);
    }

    function editAnnouncement(contestId) {
        editingAnnouncementContestId = contestId;

        var contest = contests.find(function(c) { return c.id === contestId; });
        var titleEl = document.getElementById('announcement-modal-title');
        titleEl.textContent = '编辑公告' + (contest ? ' — ' + contest.name : '');

        var textarea = document.getElementById('announcement-content');
        textarea.value = '';

        document.getElementById('edit-announcement-modal').classList.add('active');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            textarea.value = decodeURIComponent(xhr.responseText);
        };
        xhr.onerror = function() {
            textarea.value = '';
        };
        xhr.send("func=GetMessage&id=" + contestId);
    }

    function saveAnnouncement() {
        var contestId = editingAnnouncementContestId;
        if (!contestId) {
            showToast('未指定比赛', 'error');
            return;
        }

        var content = document.getElementById('announcement-content').value.trim();

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/dashboard.php');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                showToast('公告保存成功', 'success');
                closeModal('edit-announcement-modal');
                loadContests();
            } else {
                showToast('保存失败 (状态码: ' + xhr.status + ')', 'error');
            }
        };
        xhr.onerror = function() {
            showToast('网络错误，无法保存公告', 'error');
        };
        xhr.send("func=EditMessage&id=" + contestId + "&content=" + window.btoa(encodeURIComponent(content)));
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    function showToast(msg, type) {
        type = type || 'success';
        var toast = document.createElement('div');
        toast.className = 'toast ' + type;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.remove();
        }, 2200);
    }
    function openScreen(contestId) {
        window.open('/screen.php?id=' + encodeURIComponent(contestId), '_blank');
    }

    loadUsers();
</script>
</body>
</html>
