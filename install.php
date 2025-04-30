<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: install.php
// 文件大小: 5053 字节
// 最后修改时间: 2024-12-16 14:07:22
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
define('IN_SYSTEM', true);
// 引入配置文件
require_once 'inc/conn.php';
require_once 'inc/pubs.php';
require_once 'inc/sqls.php';
// 处理AJAX请求
if(isset($_GET['act'])) {
$act = $_GET['act'];
switch($act) {
case 'create_tables':
// 创建用户表
$sql_user = "CREATE TABLE IF NOT EXISTS `bm_user` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`手机号` varchar(24) NOT NULL DEFAULT '-' COMMENT '手机号',
`密码` varchar(36) NOT NULL DEFAULT '-' COMMENT '密码',
`类型` varchar(8) NOT NULL DEFAULT 'user' COMMENT 'admin/user',
`备注` varchar(64) NOT NULL DEFAULT '-' COMMENT '备注',
`添加时间` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
`改密时间` datetime DEFAULT NULL COMMENT '改密时间',
`最后登录` datetime DEFAULT NULL COMMENT '最后登录',
PRIMARY KEY (`id`),
UNIQUE KEY `手机号` (`手机号`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
// 创建活动信息表
$sql_info = "CREATE TABLE IF NOT EXISTS `bm_info` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`活动名称` varchar(36) NOT NULL DEFAULT '某团队旅游报名' COMMENT '活动名称',
`活动选项` varchar(512) NOT NULL DEFAULT '长江一日游|黄河一日游' COMMENT '活动选项',
`报名须知` varchar(512) NOT NULL DEFAULT '简单说明150字以内' COMMENT '报名须知',
`限定报名` text COMMENT '不限制或一行一个账号(手机号)',
`活动开关` int(2) NOT NULL DEFAULT '0' COMMENT '手动结束开关',
`添加时间` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
`关闭时间` datetime DEFAULT NULL COMMENT '关闭时间',
PRIMARY KEY (`id`),
KEY `活动名称` (`活动名称`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
// 创建报名记录表
$sql_jilu = "CREATE TABLE IF NOT EXISTS `bm_jilu` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`活动ID` varchar(36) NOT NULL DEFAULT '-' COMMENT '活动ID',
`用户名` varchar(36) NOT NULL DEFAULT '-' COMMENT '用户名',
`手机号` varchar(36) NOT NULL DEFAULT '-' COMMENT '手机号',
`真实姓名` varchar(36) NOT NULL DEFAULT '-' COMMENT '真实姓名',
`身份证号` varchar(36) NOT NULL DEFAULT '-' COMMENT '身份证号',
`选择内容` varchar(64) NOT NULL DEFAULT '-' COMMENT '选项选择',
`当前状态` int(2) NOT NULL DEFAULT '0' COMMENT '报名状态:默认1审核2未通过0',
`提交时间` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '提交时间',
PRIMARY KEY (`id`),
KEY `用户名` (`用户名`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
try {
$db->query($sql_user);
$db->query($sql_info);
$db->query($sql_jilu);
// 创建默认管理员账号
$admin = array(
'手机号' => 'admin',
'密码' => md5('123456'),
'类型' => 'admin',
'备注' => '系统管理员'
);
$db->insert('bm_user', $admin);
json_result(1, '数据表创建成功');
} catch(Exception $e) {
json_result(0, '数据表创建失败: ' . $e->getMessage());
}
break;
case 'import_data':
try {
// 导入测试用户数据
$users = array(
array('13800138001', '123456', 'user', '测试用户1'),
array('13800138002', '123456', 'user', '测试用户2'),
array('13800138003', '123456', 'user', '测试用户3')
);
foreach($users as $user) {
$db->insert('bm_user', array(
'手机号' => $user[0],
'密码' => md5($user[1]),
'类型' => $user[2],
'备注' => $user[3]
));
}
// 导入测试活动数据
$activities = array(
array('长江三峡游', '三峡大坝|三峡人家|宜昌市区', '为期3天的三峡旅游'),
array('张家界游', '天门山|大峡谷|森林公园', '为期4天的张家界之旅'),
array('九寨沟游', '黄龙|九寨沟|都江堰', '为期5天的九寨沟之旅')
);
foreach($activities as $activity) {
$db->insert('bm_info', array(
'活动名称' => $activity[0],
'活动选项' => $activity[1],
'报名须知' => $activity[2]
));
}
json_result(1, '测试数据导入成功');
} catch(Exception $e) {
json_result(0, '测试数据导入失败: ' . $e->getMessage());
}
break;
}
exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>系统安装</title>
<style>
body { font-family: Arial; max-width: 800px; margin: 20px auto; padding: 0 20px; }
.btn { padding: 10px 20px; margin: 10px; cursor: pointer; }
#msg { margin: 20px 0; padding: 10px; border-radius: 4px; }
.success { background: #dff0d8; color: #3c763d; }
.error { background: #f2dede; color: #a94442; }
</style>
</head>
<body>
<h1>活动报名系统安装</h1>
<div>
<button class="btn" onclick="createTables()">创建数据表</button>
<button class="btn" onclick="importData()">导入测试数据</button>
</div>
<div id="msg"></div>
<script src="inc/js.js<?php echo "?j=".$jstime;?>"></script>
<script>
function createTables() {
ajax({
url: '?act=create_tables',
success: function(res) {
showMessage(res.code, res.msg);
}
});
}
function importData() {
ajax({
url: '?act=import_data',
success: function(res) {
showMessage(res.code, res.msg);
}
});
}
function showMessage(code, msg) {
var div = document.getElementById('msg');
div.className = code ? 'success' : 'error';
div.textContent = msg;
}
</script>
</body>
</html>