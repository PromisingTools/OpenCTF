# Powered By c4e3bac3@foxmail.com Hello 

https://blog.csdn.net/khchgkhbdfxk/article/details/161870863

该平台适用于小型 CTF 比赛。


建议阅读 config.php 里的注释

把该项目 解压到 /var/www/ 目录，并开启 apache2 和配置数据库就行

数据库暂时只支持 MySQL 和 MariaDB


数据库记得改为 InnoDB 和 UTF8MB4

需要给 $DataBase 里设置的 db_name 数据库 给予 SELECT,DELETE,UPDATE,INSERT,DROP,CREATE 权限

---

[mysqld]

default-storage-engine = InnoDB

character-set-server = utf8mb4

---

记得在 PHP 插件中开启 CURL 功能

---
需要安装 php-gd 模块

sudo apt install php-gd

sudo yum install php-gd

---



需要在数据库中创建两个表，如下所示

create table user (id char(255) PRIMARY KEY, username char(255), password char(255), email char(255));

create table cmtn (id char(255) PRIMARY KEY, name char(255), start_time char(255), end_time char(255), message varchar(10000));


---

接下来讲解目录结构

/var/www/config.php

/var/www/html/index.php

/var/www/html/contest.php

/var/www/html/dashboard.php

/var/www/html/homepage.php

/var/www/html/screen .php


