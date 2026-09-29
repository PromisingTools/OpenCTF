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

date_default_timezone_set('Asia/Shanghai');

/* 有限制动态容器存活时长（秒），25 分钟 */
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
