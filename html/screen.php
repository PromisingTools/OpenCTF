<?php
/* Powered By c4e3bac3@foxmail.com  Hello */
session_start(['cookie_httponly' => true]);
if (!isset($_COOKIE[session_name()])) {
    http_response_code(404);
    exit();
}
if (isset($_SESSION["Administrator"])) {
    if ($_SESSION["Administrator"] === "Administrator") {
        if (isset($_GET["id"])) {
            if (ctype_xdigit($_GET["id"])) {
                include_once "../config.php";
                $mysql_conn = db_connect();
                $cleanup = mysqli_query($mysql_conn, "SELECT ContainerId, time FROM " . $_GET["id"] . "_container WHERE TrueFalse = 1 AND type = 2;");
                if ($cleanup && mysqli_num_rows($cleanup) !== 0) {
                    while ($rowC = mysqli_fetch_assoc($cleanup)) {
                        if (time() - intval($rowC["time"]) >= CONTAINER_TTL) {
                            $stmt_stop = mysqli_prepare($mysql_conn, "UPDATE " . $_GET["id"] . "_container SET TrueFalse = 0 WHERE ContainerId = ? AND TrueFalse = 1;");
                            mysqli_stmt_bind_param($stmt_stop, 's', $rowC["ContainerId"]);
                            mysqli_stmt_execute($stmt_stop);
                        }
                    }
                }
                $response = mysqli_query($mysql_conn, "SELECT id, name FROM cmtn WHERE id = \"" . $_GET["id"] . "\";");
                if (mysqli_num_rows($response) !== 0) {
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OpenCTF · 计分板</title>
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
            display:flex; justify-content:center; padding:30px 20px; min-height:100vh;
        }
        .page-wrapper { width:100%; max-width:800px; display:flex; flex-direction:column; gap:24px; }
        .page-header { text-align:center; }
        .page-header h1 {
            font-size:2.2rem; font-weight:700; letter-spacing:2px;
            color:var(--accent); margin-bottom:4px;
        }
        .page-header h1 span { color:var(--text); }
        .page-header .subtitle { font-size:0.75rem; color:var(--muted); letter-spacing:0.5px; }
        .card {
            background:var(--card); border:1px solid var(--border);
            border-radius:8px; padding:24px;
        }
        .section-title {
            font-size:1.2rem; font-weight:600; margin-bottom:20px;
            padding-bottom:8px; border-bottom:1px solid var(--border);
        }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px 10px; border-bottom:1px solid var(--border); text-align:left; font-size:0.85rem; }
        th { color:var(--muted); font-weight:500; }
        tr:hover { background: #1c2128; }
        .rank { color:var(--accent); font-weight:600; width:60px; }
        .user { font-weight:500; }
        .score { text-align:right; font-weight:600; color:var(--accent); }
        .toast {
            position:fixed; top:20px; left:50%; transform:translateX(-50%);
            padding:10px 20px; border-radius:5px; font-size:0.85rem; z-index:999;
            animation:fadeIn 0.25s ease; pointer-events:none;
        }
        .toast.success { background:#14281a; color:#3fb950; border:1px solid #1f3d28; }
        .toast.error { background:#3d1418; color:#f85149; border:1px solid #5c1f24; }
        @keyframes fadeIn {
            from { opacity:0; transform:translateX(-50%) translateY(-10px); }
            to { opacity:1; transform:translateX(-50%) translateY(0); }
        }
        .section-title .contest-name { color:var(--muted); font-size:1rem; font-weight:400; margin-left: 25px;}
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="page-header">
            <h1>Open<span>CTF</span></h1>
            <div class="subtitle">Powered By c4e3bac3@foxmail.com</div>
        </div>
        <div class="card">
            <div class="section-title">🏆 计分板
                <span class="contest-name"><?php echo mysqli_fetch_assoc($response)["name"]; ?></span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>名次</th>
                        <th>学号</th>
                        <th>用户名</th>
                        <th>分数</th>
                    </tr>
                </thead>
                <tbody id="ranking-tbody"></tbody>
            </table>
        </div>
    </div>

    <script>
        function loadRanking() {
            var mockData = [
<?php
        $resultA = mysqli_query($mysql_conn, "SELECT p.score, p.studentid, u.username FROM " . $_GET["id"] . "_pm p JOIN user u ON u.id = p.studentid;");
        if (mysqli_num_rows($resultA) !== 0) {
            $string = "";
            while($rowA = mysqli_fetch_assoc($resultA)) {
                $string = $string . "{studentid: '" . $rowA["studentid"] . "', username: '" . $rowA["username"] . "', score: " . $rowA["score"] . "},";
            }
            $string = substr($string, 0, -1);
            echo $string;

        }

?>
            ];

            mockData.sort(function(a, b) { return b.score - a.score; });

            var tbody = document.getElementById('ranking-tbody');
            tbody.innerHTML = '';

            for (var i = 0; i < mockData.length; i++) {
                var user = mockData[i];
                var tr = document.createElement('tr');
                tr.innerHTML = '<td class="rank">' + (i + 1) + '</td>' +
                               '<td>' + user.studentid + '</td>' +
                               '<td class="user">' + user.username + '</td>' +
                               '<td class="score">' + user.score + '</td>';
                tbody.appendChild(tr);
            }
            setTimeout(() => {location.reload();}, 2500);
        }

        loadRanking();
        (() => {
            function ban() {
                const start = Date.now();
                const timer = setInterval(() => { debugger; if (Date.now() - start > 10000) { clearInterval(timer); } }, 200);
            }
            ban();
        })();
    </script>
</body>
</html>
<?php
                    exit();
                }
            }
        }
        
    }
}
?>


