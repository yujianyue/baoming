<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: inc/conn.php
// 文件大小: 799 字节
// 最后修改时间: 2024-12-16 14:07:22
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
// 禁止直接访问
if(!defined('IN_SYSTEM')) {
exit('Access Denied');
}
$title  = "简易多主题报名系统";
$jstime = "2024121212";
// 数据库连接配置
$db_config = array(
'host' => 'localhost',
'user' => 'baoming_chalide',
'pass' => 'A5PrzD6KYdHpjnhx',
'name' => 'baoming_chalide',
'port' => 3306,
'charset' => 'utf8mb4'
);
// 创建数据库连接
$conn = @mysqli_connect(
$db_config['host'],
$db_config['user'],
$db_config['pass'],
$db_config['name'],
$db_config['port']
);
if(!$conn) {
die("数据库连接失败：" . mysqli_connect_error());
}
mysqli_set_charset($conn, $db_config['charset']);
// 时区设置
date_default_timezone_set('Asia/Shanghai');
// 系统常量定义
define('PAGE_SIZE', 10);  // 每页记录数
define('UPLOAD_PATH', '../uploads/'); // 上传目录