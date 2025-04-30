<?php
define('IN_SYSTEM', true);
session_start();

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 最后修改时间: 2024-12-16 14:07:22
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权

if(isset($_SESSION['user_id'])) {
    $url = $_SESSION['user_type'] == 'admin' ? 'user.php' : '../index.php';
    header("Location: $url");
    exit;
}else{
    header("Location: ../index.php");
    exit;
}
?>