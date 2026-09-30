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
    email    char(255),
    enable   int(1) NOT NULL DEFAULT 0
);

create table cmtn (
    id         char(255) PRIMARY KEY,
    name       char(255),
    start_time char(255),
    end_time   char(255),
    message    varchar(10000)
);
```

> 从旧版本升级时，需给 `user` 表手动增加 `enable` 字段（控制是否允许参赛，`1` 允许 / `0` 禁止，默认阻止参赛）：

```sql
ALTER TABLE user ADD COLUMN enable int(1) NOT NULL DEFAULT 0;
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

## 参赛权限与注册开关

- `user.enable` 字段控制用户是否允许参赛：`1` 允许、`0` 禁止。被禁止的用户在 `homepage.php` 无法加载竞赛列表、访问 `contest.php` 会被直接拒绝。
- 管理员可在 `dashboard.php` 的用户列表中查看每个用户的参赛状态，并进行单个「批准参赛 / 阻止参赛」或「全部允许参赛 / 全部拒绝参赛」操作。
- 管理员可在 `dashboard.php` 通过「批量导入用户」按行导入用户，每行格式为 `学号,姓名,邮箱,初始密码`（导入的用户默认 `enable=0`）。
- 通过注册页 `index.php` 新注册的用户默认 `enable=0`（阻止参赛），需管理员批准后才能参赛。
- `config.php` 中的 `$allow_register` 控制是否允许用户自行注册：`true` 允许、`false` 禁止。

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
