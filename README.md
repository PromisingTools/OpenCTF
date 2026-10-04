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

## 数据表说明

比赛相关表以比赛 ID 作为前缀命名：

- `user` 表记录学号、姓名、密码、邮箱（`id` 为学号）。
- `cmtn` 表记录比赛 ID、比赛名称、开始时间、结束时间（代码中 `competition` 对应 `cmtn`）。
- `<比赛ID>_ll` 记录该比赛的理论题。
- `<比赛ID>_sc` 记录该比赛的实操题。
- `<比赛ID>_pm` 记录每个用户的总分，也就是排名。
- `<比赛ID>` 记录单个用户做对题目对应的分数（该分数是最终判定的分数）。

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

## 实操题评分与动态容器

### 实操题扣分规则

- 第一个做对题目的分数为 附加分 + 基础分。
- 第二个做对的分数为 附加分 - 1 + 基础分。
- 第三个为 附加分 - 2 + 基础分，第四个为 附加分 - 3 + 基础分，以此类推……
- 直到附加分扣完为止，基础分不会扣。

### 动态容器

- 实操题支持随机 flag 和动态容器功能，通过请求第三方 API 实现。
- 需要在 `dashboard.php` 里选择动态容器功能并配置第三方 API 地址。
- 「无限制动态答案」：比赛用户在启动容器时不会销毁之前的容器。
- 「有限制动态答案」：比赛用户在启动容器时会请求 API 的 `/stop` 来销毁容器。
- 有限制动态容器的存活时长由 `config.php` 的 `CONTAINER_TTL` 定义（`1500` 秒，即 25 分钟），超时后由 `screen.php` 清理。
- 请求 API 的格式和返回值请看 `app.py` 文件。

---

## 动态容器 API 鉴权（X-Auth-Token）

- `config.php` 中的 `CONTAINER_API_TOKEN` 与 `app.py` 中的 `CONTAINER_API_TOKEN` 是动态容器 API 的共享鉴权令牌。
- Web 端调用容器 API 的 `/start`、`/stop` 时，会在 HTTP 请求头中携带 `X-Auth-Token`（取值为 `CONTAINER_API_TOKEN`）。
- `app.py` 在处理 `/start`、`/stop` 之前会校验请求头 `X-Auth-Token` 是否与自身硬编码的令牌一致；不一致时返回 `401 {"error": "unauthorized"}`。
- 两处令牌必须完全一致，否则容器启动/停止会全部失败；正式部署时请修改为随机强密钥，不要使用示例默认值。
- 该令牌只能阻止未授权的直接调用，仍建议通过防火墙限制容器 API 仅允许 Web 服务器等可信来源访问。

## 安全建议（注意事项）

1. **部署后立即修改默认凭据**
   请修改 `config.php` 中的默认管理员密码（`Admin` / `1234567890`）和数据库密码（`OpenCTF` / `1234567890`），避免使用源码中硬编码的默认值，否则他人拿到源码即可登录管理面板、连接数据库。

2. **`config.php` 不要暴露在 Web 根目录**
   `config.php` 应位于站点根目录（`html/`）之外，并确保 Apache 的 `DocumentRoot` 指向 `html/`，防止配置文件被直接访问而泄露凭据。

3. **数据库权限说明**
   平台需要 `DROP` 和 `CREATE` 权限（用于动态创建/删除比赛数据表），建议仅在 `db_name` 数据库上授予所需权限，并限制数据库账号的来源主机，避免授予全局（`*.*`）权限。

4. **动态容器 API（`app.py`）的安全配置**
   `/start`、`/stop` 现通过请求头 `X-Auth-Token` 鉴权（见 `config.php` 与 `app.py` 中的 `CONTAINER_API_TOKEN`）。请将示例默认令牌替换为随机强密钥；示例答案仍为硬编码，正式使用时应随机化每次答案；同时建议通过防火墙仅允许 Web 服务器等可信来源访问 API。

5. **建议启用 HTTPS**
   会话 Cookie 未设置 `Secure` 标志，在明文 HTTP 下易被窃取，建议为站点启用 HTTPS。
