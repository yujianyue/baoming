<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: inc/pubs.php
// 文件大小: 5448 字节
// 最后修改时间: 2024-12-16 18:42:25
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
if(!defined('IN_SYSTEM')) {
exit('Access Denied');
}
// 保存配置到文件
function save_config($config) {
$content = "<?php\nif(!defined('IN_SYSTEM')) {\n    exit('Access Denied');\n}\n\n";
$content .= '$config = ' . var_export($config, true) . ";\n";
return file_put_contents(__DIR__ . '/json.php', $content);
}
// 安全过滤函数
function safe_string($str) {
return htmlspecialchars(trim($str), ENT_QUOTES);
}
// 返回JSON提示
function json_result($code, $msg, $data = null) {
$result = ['code' => $code, 'msg' => $msg];
if ($data !== null) {
$result['data'] = $data;
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
}
// CSV文件读取函数
function read_csv($file) {
$data = [];
if (($handle = fopen($file, "r")) !== FALSE) {
while (($row = fgetcsv($handle, 0, ",")) !== FALSE) {
$data[] = $row;
}
fclose($handle);
}
return $data;
}
// 分页函数
function get_pagination($total, $page, $size = PAGE_SIZE) {
$pages = ceil($total / $size);
$page = max(1, min($page, $pages));
return [
'total' => $total,
'page' => $page,
'size' => $size,
'pages' => $pages,
'offset' => ($page - 1) * $size
];
}
// 检查手机号格式
function is_mobile($mobile) {
return preg_match('/^1[3-9]\d{9}$/', $mobile);
}
// 检查身份证号格式
function is_idcard($idcard) {
return preg_match('/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $idcard);
}
// 获取客户端IP
function get_client_ip() {
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
$pos = array_search('unknown', $arr);
if(false !== $pos) {
unset($arr[$pos]);
}
$ip = trim($arr[0]);
} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
$ip = $_SERVER['HTTP_CLIENT_IP'];
} else {
$ip = $_SERVER['REMOTE_ADDR'];
}
return $ip;
}
// 生成随机字符串
function random_str($length = 6) {
$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$str = '';
for($i = 0; $i < $length; $i++) {
$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
}
return $str;
}
// 检查登录状态
function check_login() {
if(!isset($_SESSION['user_id'])) {
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
json_result(403, '请先登录');
} else {
header('Location: login.php');
exit;
}
}
return $_SESSION['user_id'];
}
// 检查管理员权限
function check_admin($tp="1") {
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
if($tp=="1"){
 json_result(403, '无权限访问'); 
}else{
 header('Location: ../login.php');
 exit;
}
}
}
// 上传文件
function upload_file($file, $allowed_types = null) {
global $config;
if(!$allowed_types) {
$allowed_types = explode(',', $config['upload_types']);
}
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if(!in_array($ext, $allowed_types)) {
return array('code' => 0, 'msg' => '不允许的文件类型');
}
if($file['size'] > $config['upload_size'] * 1024) {
return array('code' => 0, 'msg' => '文件大小超过限制');
}
$filename = date('YmdHis') . random_str(6) . '.' . $ext;
$filepath = UPLOAD_PATH . $filename;
if(!move_uploaded_file($file['tmp_name'], $filepath)) {
return array('code' => 0, 'msg' => '上传失败');
}
return array(
'code' => 1,
'msg' => '上传成功',
'data' => array(
'filename' => $filename,
'filepath' => $filepath
)
);
}
// 输出后台页面头部
function admin_header() {
global $config,$title,$jstime;
$menu = array(
'用户管理' => array(
'user.php' => '用户列表'
),
'活动管理' => array(
'huodong.php' => '活动列表',
'baoming.php' => '报名管理',
'tongji.php' => '报名统计'
),
'系统设置' => array(
'shezhi.php' => '参数设置',
'../login.php' => '退出登陆'
)
);
//<a href="javascript:;" onclick="showChangePwd()">修改密码</a>
echo <<<EOT
<style>
*{margin:0;padding:0;box-sizing:border-box;text-decoration:none;}
body{font-family:Arial;background:#f5f5f5;}
.header{position:fixed;top:0;left:0;right:0;height:50px;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.1);display:flex;justify-content:space-between;align-items:center;padding:0 20px;z-index:1000;}
.header h1{font-size:20px;color:#333;}
.nav{display:flex;height:100%;}
.nav-item{position:relative;height:100%;padding:0 15px;color:#666;cursor:pointer;display:flex;align-items:center;}
.nav-item:hover{color:#007bff;background:#f8f9fa;}
.sub-menu{display:none;position:absolute;top:100%;right:0;min-width:100px;background:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.1);border-radius:4px;}
.nav-item:hover .sub-menu{display:block;}
.sub-menu a{display:block;padding:10px 15px;color:#666;}
.sub-menu a:hover{background:#f8f9fa;color:#007bff;}
.main{margin-top:65px;margin-bottom:50px;padding:15px;}
input,select,textarea{padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;}
</style>
<div class="header">
<h1>{$config['site_name']}</h1>
<div class="nav">
EOT;
foreach($menu as $title => $items) {
echo '<div class="nav-item">';
echo $title;
echo '<div class="sub-menu">';
foreach($items as $url => $name) {
echo "<a href=\"$url?t=$jstime\">$name</a>";
}
echo '</div></div>';
}
echo <<<EOT
</div>
</div>
<div class="main">
EOT;
}
// 输出后台页面底部
function admin_footer() {
global $config;
echo <<<EOT
</div>
<div style="position:fixed;bottom:0;left:0;right:0;padding:10px;text-align:center;background:#fff;border-top:1px solid #eee;color:#666;">
Copyright &copy; {$config['site_name']}
</div>
</body>
</html>
EOT;
}