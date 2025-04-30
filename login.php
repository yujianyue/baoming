<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: login.php
// 文件大小: 3388 字节
// 最后修改时间: 2024-12-16 20:53:18
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
define('IN_SYSTEM', true);
session_start();
require_once './inc/conn.php';
require_once './inc/pubs.php';
require_once './inc/sqls.php';
// 处理AJAX请求
if(isset($_GET['act'])) {
$act = $_GET['act'];
switch($act) {
case 'login':
$mobile = safe_string($_POST['mobile']);
$password = safe_string($_POST['password']);
if(empty($mobile) || empty($password)) {
json_result(0, '请输入手机号和密码');
}
if(!is_mobile($mobile) && $mobile != 'admin') {
json_result(0, '手机号格式错误');
}
// 查询用户  AND 密码='%s'
$sql = sprintf(
"SELECT * FROM bm_user WHERE 手机号='%s' LIMIT 1",
$mobile
);
$user = $db->get_one($sql);
if(!$user) {
$motp = "user";
$data = array(
'手机号' => $mobile,
'密码' => md5($password),
'类型' => $motp,
'备注' => safe_string("注册")
);
if($db->insert('bm_user', $data)) {
$_SESSION['user_id'] = "0";
$_SESSION['user_mobile'] = $mobile;
$_SESSION['user_type'] = $motp;
json_result(1, '注册成功,新用户记住账号密码!', array('type' => $motp));
}
json_result(0, '用户名或密码错误');
}
if($user['密码']!=md5($password)){
json_result(0, '用户名或密码错误');
}
// 更新最后登录时间
$db->update('bm_user',
array('最后登录' => date('Y-m-d H:i:s')),
sprintf("id=%d", $user['id'])
);
// 设置session
$_SESSION['user_id'] = "0";
$_SESSION['user_mobile'] = $user['手机号'];
$_SESSION['user_type'] = $user['类型'];
json_result(1, '登录成功', array(
'type' => $user['类型']
));
break;
}
exit;
}
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>用户登录</title>
<style>
body{font-family:Arial;background:#f5f5f5;}
.login-box{width:360px;margin:100px auto;padding:20px;background:#fff;border-radius:4px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
.form-group{margin-bottom:15px;}
.form-group label{display:block;margin-bottom:5px;}
.form-group input{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;}
.btn{width:100%;padding:10px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;}
.btn:hover{background:#0056b3;}
#msg{margin:10px 0;padding:10px;border-radius:4px;}
.err{background:#f2dede;color:#a94442;}
.error{background:#f2dede;color:red;}
</style>
</head>
<body>
<div class="login-box">
<h2 style="text-align:center;margin-bottom:20px">用户登录</h2>
<div id="msg" class="err">新账号登陆即注册!</div>
<div class="form-group">
<label>手机号</label>
<input type="text" id="mobile" placeholder="请输入手机号">
</div>
<div class="form-group">
<label>密码</label>
<input type="password" id="password" placeholder="请输入密码">
</div>
<button class="btn" onclick="doLogin()">登 录</button>
</div>
<script src="../inc/js.js<?php echo "?j=".$jstime;?>"></script>
<script>
function doLogin() {
showMessage(0, '登陆中...');
var mobile = document.getElementById('mobile').value;
var password = document.getElementById('password').value;
if(!mobile || !password) {
showMessage(0, '请输入手机号和密码');
return;
}
ajax({
type: 'POST',
url: '?act=login',
data: {
mobile: mobile,
password: password
},
success: function(res) {
if(res.code) {
location.href = res.data.type == 'admin' ? './admin/user.php' : './index.php';
} else {
showMessage(0, res.msg);
}
}
});
}
function showMessage(code, msg) {
var div = document.getElementById('msg');
div.style.display = 'block';
div.className = code ? 'success' : 'error';
div.textContent = msg;
}
// 回车登录
document.getElementById('password').onkeyup = function(e) {
if(e.keyCode == 13) {
doLogin();
}
}
</script>
</body>
</html>