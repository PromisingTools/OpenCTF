# OpenCTF

> Powered By c4e3bac3@foxmail.com · Hello

博客：https://blog.csdn.net/khchgkhbdfxk/article/details/166897392

下载：https://download.csdn.net/download/khchgkhbdfxk/92965797

该平台适用于**小型 CTF 比赛**。

> 建议阅读 `config.php` 里的注释。

> 动态容器 API 的请求格式和返回值请看 `app.py` 文件。

---

## 快速部署

将项目解压到 `/var/www/` 目录，开启 Apache2 并配置好数据库即可。

---

## 数据库要求

- 暂时只支持 **MySQL** 和 **MariaDB**。
- 数据库存储引擎需设为 **InnoDB**，字符集设为 **UTF8MB4**。
- 需要给 `$DataBase` 里设置的 `db_name` 数据库授予 `SELECT, DELETE, UPDATE, INSERT, DROP, CREATE` 权限。

### MySQL 配置示例

```ini
[mysqld]
default-storage-engine = InnoDB
character-set-server = utf8mb4
```

---

## PHP 扩展要求

1. 在 PHP 插件中开启 **CURL** 功能。
2. 安装 **php-gd** 模块：

   ```bash
   sudo apt install php-gd   # Debian / Ubuntu
   sudo yum install php-gd   # CentOS / RHEL
   ```

---

## 初始化数据表

需要在数据库中创建以下两个表：

```sql
create table user (
    id       char(255) PRIMARY KEY,
    username char(255),
    password char(255),
    email    char(255)
);

create table cmtn (
    id         char(255) PRIMARY KEY,
    name       char(255),
    start_time char(255),
    end_time   char(255),
    message    varchar(10000)
);
```

---

## 目录结构

```
/var/www/config.php
/var/www/html/index.php
/var/www/html/contest.php
/var/www/html/dashboard.php
/var/www/html/homepage.php
/var/www/html/screen.php
```

---

## 安全建议（注意事项）

1. **部署后立即修改默认凭据**
   请修改 `config.php` 中的默认管理员密码（`Admin` / `1234567890`）和数据库密码（`OpenCTF` / `1234567890`），避免使用源码中硬编码的默认值，否则他人拿到源码即可登录管理面板、连接数据库。

2. **`config.php` 不要暴露在 Web 根目录**
   `config.php` 应位于站点根目录（`html/`）之外，并确保 Apache 的 `DocumentRoot` 指向 `html/`，防止配置文件被直接访问而泄露凭据。

3. **数据库权限说明**
   平台需要 `DROP` 和 `CREATE` 权限（用于动态创建/删除比赛数据表），建议仅在 `db_name` 数据库上授予所需权限，并限制数据库账号的来源主机，避免授予全局（`*.*`）权限。

4. **动态容器 API（`app.py`）需加鉴权与访问控制**
   `/start`、`/stop` 当前无鉴权且示例答案硬编码。正式使用时应为 API 增加鉴权、随机化每次答案，并通过防火墙校验请求来源是否合法（仅允许 Web 服务器等可信来源访问）。

5. **建议启用 HTTPS**
   会话 Cookie 未设置 `Secure` 标志，在明文 HTTP 下易被窃取，建议为站点启用 HTTPS。
