<?php
/* Powered By c4e3bac3@foxmail.com */
session_start();
if (isset($_SESSION["studentID"])) {
    $studentid = $_SESSION["studentID"];
    if(ctype_digit($studentid)) {
        include "../config.php";
        $mysql_conn = mysqli_connect($DataBase["host"], $DataBase["username"], $DataBase["password"], $DataBase["db_name"], $DataBase["port"]);
        mysqli_query($mysql_conn, "use ". $DataBase["db_name"] . ";");
        $response = mysqli_query($mysql_conn, "select id, username, email from user where id = \"" . $studentid . "\";");
        if (mysqli_num_rows($response) === 0) {
            echo "None";
        } else {

            if (isset($_POST["status"])) {
                $status = $_POST["status"];
                if ($status === "Change") {
                    if (isset($_POST["email"])) {
                        $email = htmlspecialchars($_POST['email'], ENT_QUOTES);
                        $stmt = mysqli_prepare($mysql_conn, "UPDATE user set email = ? where id = ?");
                        mysqli_stmt_bind_param($stmt, 'ss', $email, $studentid);
                        mysqli_stmt_execute($stmt);
                    }

                    if (isset($password)) {
                        $password = htmlspecialchars($_POST["password"], ENT_QUOTES);
                        $stmt = mysqli_prepare($mysql_conn, "UPDATE user set password = ? where id = ?");
                        $password = hash("sha512", $password);
                        mysqli_stmt_bind_param($stmt, 'ss', $password, $studentid);
                        mysqli_stmt_execute($stmt);
                    }
                    if ($_POST['username']) {
                        $username = htmlspecialchars($_POST['username'], ENT_QUOTES);
                        $stmt = mysqli_prepare($mysql_conn, "UPDATE user set username = ? where id = ?");
                        mysqli_stmt_bind_param($stmt, 'ss', $username, $studentid);
                        mysqli_stmt_execute($stmt);
                    }
                }
                else if ($status === "ContestList") {
                    $result = mysqli_query($mysql_conn, "SELECT id, name, start_time, end_time FROM cmtn;");
                    if (mysqli_num_rows($result) === 0) {
                        echo "None";
                    }
                    else {
                        $string = "[";
                        while ($row = mysqli_fetch_assoc($result)) {
                            $string = $string . '{"id": "' . $row["id"] . '", "name": "' . $row["name"] . '", "start": "' . $row["start_time"] . '", "end": "' . $row["end_time"] . '"},';
                        }
                        $string = substr($string, 0, -1);
                        $string = $string . "]";
                        echo $string;

                    }
                }
                else if ($status === "RankList") {
                    if (isset($_POST["id"])) {
                        $id = $_POST["id"];
                        if (ctype_xdigit($id)) {
                            $result = mysqli_query($mysql_conn, "SELECT studentid, score FROM " . $id . "_pm;");
                            if (mysqli_num_rows($result) === 0) {
                                echo "None";
                                exit();
                            }
                            else {
                                $string = "{\"data\": [";
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $tmp = mysqli_query($mysql_conn, "SELECT username from user WHERE id = \"" . $row["studentid"] . "\";");
                                    $rrooww = mysqli_fetch_assoc($tmp);
                                    $string = $string . '{"username": "' . $rrooww["username"] . '", "score": "' . $row["score"] . '"},';
                                    
                                }
                                $string = substr($string, 0, -1);
                                $string = $string . "],";
                                
                                $result = mysqli_query($mysql_conn, "SELECT rn, studentid, score FROM (SELECT studentid,score, ROW_NUMBER() OVER (ORDER BY score DESC) AS rn FROM " . $id . "_pm) AS t WHERE studentid = '" . $_SESSION["studentID"] . "';");
                                if (mysqli_num_rows($result) === 0) {
                                    $string = $string . '"current": "None"}';
                                }
                                else {
                                    $tmp = mysqli_query($mysql_conn, "SELECT score from " . $id . "_pm where studentid = \"" . $_SESSION["studentID"] . "\";");
                                    $rrooww = mysqli_fetch_assoc($tmp);
                                    $row = mysqli_fetch_assoc($result);
                                    $string = $string . '"current" : ';
                                    $string = $string . '{"rank": "' . $row['rn'] . '",';
                                    $string = $string . '"score": "' . $rrooww['score'] . '"}';
                                    $string = $string . "}";
                                }
                                
                                echo $string;
                            }
                        }
                    }

                }
                exit();
            }
            
            $rows = mysqli_fetch_assoc($response);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>OpenCTF · 个人主页</title>
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
        .page-wrapper { width:100%; max-width:1100px; display:flex; flex-direction:column; gap:20px; }
        .page-header {
            text-align:center; font-size:2.2rem; font-weight:700;
            letter-spacing:2px; color:var(--accent);
        }
        .page-header span { color:var(--text); }
        .container { display:flex; gap:20px; }
        .main { flex:1; min-width:0; }
        .sidebar { width:300px; flex-shrink:0; }
        .card {
            background:var(--card); border:1px solid var(--border);
            border-radius:8px; padding:24px; margin-bottom:20px;
        }
        .section-title {
            font-size:1rem; font-weight:600; margin-bottom:16px;
            padding-bottom:8px; border-bottom:1px solid var(--border);
            display:flex; justify-content:space-between; align-items:center;
        }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:10px 8px; border-bottom:1px solid var(--border); text-align:left; font-size:0.85rem; }
        th { color:var(--muted); font-weight:500; }
        .btn {
            padding:6px 12px; background:var(--accent); color:#fff;
            border:none; border-radius:4px; font-size:0.75rem; font-weight:600;
            cursor:pointer; font-family:inherit; letter-spacing:0.3px;
            transition:0.15s; white-space:nowrap; margin-right:4px;
        }
        .btn:hover { background:var(--accent-hover); }
        .btn.danger { background:var(--danger); }
        .btn.danger:hover { background:var(--danger-hover); }
        .btn.small { padding:4px 8px; font-size:0.7rem; }
        input {
            width:100%; background:var(--input-bg); border:1px solid var(--border);
            border-radius:5px; color:var(--text); padding:8px 10px;
            font-size:0.85rem; font-family:inherit; outline:none; transition:0.15s;
        }
        input:focus { border-color:var(--accent); }
        .field { margin-bottom:14px; }
        .field label { display:block; font-size:0.78rem; color:var(--muted); margin-bottom:4px; }
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
        .empty-hint { color:var(--muted); text-align:center; padding:20px; font-size:0.85rem; }
        .contest-status { font-size:0.7rem; padding:2px 6px; border-radius:3px; }
        .contest-status.running { background:#14281a; color:#3fb950; }
        .contest-status.upcoming { background:#1a1a2e; color:#58a6ff; }
        .contest-status.ended { background:#2d1a1a; color:#f85149; }
        .profile-info p { margin-bottom:8px; font-size:0.85rem; }
        .profile-info span { color:var(--text); font-weight:500; }

        .modal {
            position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,0.7); display:none; align-items:center;
            justify-content:center; z-index:1000;
        }
        .modal.active { display:flex; }
        .modal-content {
            background:var(--card); border:1px solid var(--border);
            border-radius:8px; padding:24px; width:90%; max-width:700px;
            max-height:80vh; overflow-y:auto; position:relative;
        }
        .modal-close {
            position:absolute; top:12px; right:16px; background:none;
            border:none; color:var(--muted); font-size:1.4rem; cursor:pointer;
        }
        .modal-close:hover { color:var(--text); }
        .user-rank-highlight {
            background: #1a2e1a;
            font-weight: bold;
        }
        .page-subtitle {
            text-align: center;
            font-size: 0.75rem;
            color: var(--muted);
            margin-bottom: 20px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="page-header">Open<span>CTF</span></div>
        <div class="page-subtitle">Powered By c4e3bac3@foxmail.com</div>
        <div class="container">
            <div class="main">
                <div class="card">
                    <div class="section-title">🏆 竞赛列表</div>
                    <table>
                        <thead><tr><th>竞赛名称</th><th>开始时间</th><th>结束时间</th><th>状态</th><th>操作</th></tr></thead>
                        <tbody id="contest-tbody"></tbody>
                    </table>
                    <div id="contest-empty" class="empty-hint" style="display:none;">暂无可用竞赛</div>
                </div>
            </div>

            <div class="sidebar">
                <div class="card">
                    <div class="section-title">👤 个人资料</div>
                    <div id="profile-display">
                        <div class="profile-info">
                            <p>用户名: <span id="display-username"></span></p>
                            <p>学号: <span id="display-studentid"></span></p>
                            <p>邮箱: <span id="display-email"></span></p>
                        </div>
                        <button class="btn small" onclick="toggleEditProfile()">编辑资料</button>
                    </div>
                    <div id="profile-edit" style="display:none;">
                        <div class="field"><label>用户名</label><input type="text" id="edit-username" placeholder="用户名"></div>
                        <div class="field"><label>邮箱</label><input type="email" id="edit-email" placeholder="邮箱"></div>
                        <hr style="border-color:var(--border); margin:16px 0;">
                        <div class="field"><label>新密码（留空则不修改）</label><input type="password" id="new-password" placeholder="输入新密码"></div>
                        <div class="field"><label>确认新密码</label><input type="password" id="confirm-password" placeholder="再次输入新密码"></div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn small" onclick="saveProfile()">保存</button>
                            <button class="btn small danger" onclick="toggleEditProfile()">取消</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="ranking-modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('ranking-modal')">&times;</button>
            <div class="section-title" id="ranking-title">排行榜</div>
            <div id="user-summary" style="margin-bottom:16px; font-size:0.9rem; color:var(--accent);"></div>
            <table>
                <thead><tr><th>排名</th><th>用户名</th><th>分数</th></tr></thead>
                <tbody id="ranking-tbody"></tbody>
            </table>
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

        var currentUser = {
            username: undefined,
            studentid: undefined,
            email: undefined
        };

        var contests = [];

        var isEditing = false;

        function getContestStatus(startStr, endStr) {
            var now = new Date();
            var start = new Date(startStr);
            var end = new Date(endStr);
            if (now < start) return 'upcoming';
            if (now >= start && now <= end) return 'running';
            return 'ended';
        }

        function getStatusLabel(status) {
            if (status === 'running') return '进行中';
            if (status === 'upcoming') return '未开始';
            if (status === 'ended') return '已结束';
            return '';
        }

        function renderContests() {

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/homepage.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.send("status=ContestList");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    if (xhr.responseText === "None") {
                        showToast("没有竞赛", "success");
                    }
                    else {
                        contests = JSON.parse(xhr.responseText);
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
                            var status = getContestStatus(c.start, c.end);
                            var tr = document.createElement('tr');
                            tr.innerHTML =
                                '<td>' + c.name + '</td>' +
                                '<td>' + (c.start ? c.start.replace('T',' ') : '-') + '</td>' +
                                '<td>' + (c.end ? c.end.replace('T',' ') : '-') + '</td>' +
                                '<td><span class="contest-status ' + status + '">' + getStatusLabel(status) + '</span></td>' +
                                '<td>' +
                                    '<button class="btn small" onclick="enterContest(\'' + c.id + '\',\'' + status + '\')">进入比赛</button>' +
                                    '<button class="btn small" onclick="viewRanking(\'' + c.id + '\',\'' + c.name + '\')">查看详情</button>' +
                                '</td>';
                            tbody.appendChild(tr);
                        }
                    }
                }
                else {
                    showToast("加载竞赛列表失败", "error");
                }
            };
            
            
        }

        function enterContest(contestId, status) {
            if (status !== 'running') {
                showToast('该比赛当前不可进入（' + getStatusLabel(status) + '）', 'error');
                return;
            }
            window.open("/contest.php?id=" + contestId);
            showToast('即将进入竞赛', 'success');
        }

        function viewRanking(contestId, contestName) {

            var users = [];
            document.getElementById('ranking-title').textContent = '排行榜 - ' + contestName;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/homepage.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {

                    if (xhr.responseText === "None") {
                        showToast("没有人参与这场比赛", "success");
                    }
                    else {
                        users = JSON.parse(xhr.responseText);

                        users.data.sort(function(a, b) { return b.score - a.score; });
                        if (users.length < 20) {
                            var tmp = users.length;
                            for (var i = 0; i < 20 - tmp; i++) {
                                users.push({username:"NULL", score: 0});
                            }
                        }
                        
                        var ranking = [];
                        for (var i = 0; i < users.data.length; i++) {
                            ranking.push({
                                rank: i + 1,
                                username: users.data[i].username,
                                score: users.data[i].score
                            });
                            
                        }

                        var tbody = document.getElementById('ranking-tbody');
                        tbody.innerHTML = '';
                        for (var j = 0; j < ranking.length; j++) {
                            var row = ranking[j];
                            var tr = document.createElement('tr');
                            tr.innerHTML = '<td>' + ranking[j].rank + '</td><td>' + row.username + '</td><td>' + row.score + '</td>';
                            tbody.appendChild(tr);
                        }
                        console.log(users.current);
                        var summary = '';
                        if (users.current === "None") {
                            summary = '你尚未参加该比赛或暂无排名';
                            
                        } else {
                            summary = '我的排名：第 ' + users.current.rank + ' 名 | 分数：' + users.current.score;
                        }
                        document.getElementById('user-summary').textContent = summary;

                        document.getElementById('ranking-modal').classList.add('active');

                    }
                }
                    
            };
            xhr.send("status=RankList&id=" + contestId);
            

            
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        function updateProfileDisplay() {
            currentUser.username = "<?php echo $rows["username"];?>";
            currentUser.studentid = "<?php echo $rows["id"]; ?>";
            currentUser.email = "<?php echo $rows["email"]; ?>";
            document.getElementById('display-username').textContent = currentUser.username;
            document.getElementById('display-studentid').textContent = currentUser.studentid;
            document.getElementById('display-email').textContent = currentUser.email;
        }

        function toggleEditProfile() {
            isEditing = !isEditing;
            document.getElementById('profile-display').style.display = isEditing ? 'none' : 'block';
            document.getElementById('profile-edit').style.display = isEditing ? 'block' : 'none';
            if (isEditing) {
                document.getElementById('edit-username').value = currentUser.username;
                document.getElementById('edit-email').value = currentUser.email;
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            }
        }

        function saveProfile() {
            var newUsername = document.getElementById('edit-username').value.trim();
            var newEmail = document.getElementById('edit-email').value.trim();
            var newPassword = document.getElementById('new-password').value.trim();
            var confirmPassword = document.getElementById('confirm-password').value.trim();

            if (!newUsername) { showToast('用户名不能为空', 'error'); return; }
            if (!newEmail) { showToast('邮箱不能为空', 'error'); return; }
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) { showToast('两次密码不一致', 'error'); return; }
                if (newPassword.length < 6) { showToast('新密码至少需要6位', 'error'); return; }
            }

            currentUser.username = newUsername;
            currentUser.email = newEmail;


            var string = "email=" + newEmail + "&username=" + newUsername + "&password=" + newPassword + "&status=Change"
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/homepage.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                toggleEditProfile();
                if (xhr.status >= 200 && xhr.status < 300) {
                    showToast('个人资料更新成功', 'success');
                    updateProfileDisplay();
                } else {
                    showToast('个人资料更新失败', 'error');
                }
            };
            xhr.onerror = function() {
                showToast('网络错误，无法保存题目', 'error');
            };
            xhr.send(string);
            setTimeout(() => {location.reload();}, 350);
        }

        function showToast(msg, type) {
            type = type || 'success';
            var toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(function(){ toast.remove(); }, 2200);
        }

        updateProfileDisplay();
        renderContests();
    </script>
</body>
</html>

<?php
        exit();
        }
    }
}
?>
<br/>
<center>
    <br/>
    <h1> 道 阻 且 长 | 行 则 将 至 </h1>
</center>
