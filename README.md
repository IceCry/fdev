### BY: 森森 1050575278

项目地址：[https://github.com/IceCry/fdev](https://github.com/IceCry/fdev "https://github.com/IceCry/fdev")

### fdev

yet another fast development framework based on thinkphp6

### 说明

- uniapp目录为uni-app快速开发框架
- res/init.sql 初始化数据库文件
- 项目默认模块为admin


### TODO

- 邮箱功能
- 工作流完善
- 在线插件安装
- 在线升级
- 微信支付
- 微信公众号、小程序登录
- 定时任务
- 数据库管理
- sg11加密核心库，限制指定ip/域名/时间访问(仅限商业项目)

### 已安装依赖

- topthink/think-multi-app
- topthink/think-view
- topthink/think-captcha
- firebase/php-jwt
- phpoffice/phpspreadsheet
- spatie/macroable
- xaboy/form-builder


### 未安装，根据项目可选部分：

- workerman/channel
- overtrue/socialite
- phpoffice/phpword
- topthink/think-image
- overtrue/wechat
- overtrue/easy-sms
- godruoyi/php-snowflake
- aliyuncs/oss-sdk-php
- qcloud/cos-sdk-v5
- endroid/qr-code
- qiniu/php-sdk
- topthink/think-worker
- workerman/gateway-worker
- workerman/gatewayclient
- workerman/channel
- topthink/think-queue


### 环境

- PHP 7.4+
- MySQL 5.7
- NPM 6.10.2

### 开发框架

- ThinkPHP 6.0.7
- layuiAdmin 1.4.0
- uView 1.8.3


### 使用方法

1. `git clone` 项目
2. 执行`composer install`安装依赖
3. 导入数据库、修改数据库配置
4. 默认帐号：18888888888，默认密码：111111


### 运行命令

- 如需使用gateway，请安装对应扩展并执行：`php think worker:gateway`
- 如需消息队列请安装依赖并执行：`php think queue:listen --queue SENSEN`