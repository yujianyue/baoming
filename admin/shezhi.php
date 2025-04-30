<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: admin/shezhi.php
// 文件大小: 4064 字节
// 最后修改时间: 2024-12-16 14:07:22
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
define('IN_SYSTEM', true);
session_start();
require_once '../inc/conn.php';
require_once '../inc/pubs.php';
require_once '../inc/json.php';
require_once '../inc/sqls.php';

// 处理AJAX请求
if(isset($_GET['act'])) {
$act = $_GET['act'];
check_admin(); // 检查管理员权限
switch($act) {
// 保存设置
case 'save':
$data = array(
'site_name' => safe_string($_POST['site_name']),
'site_keywords' => safe_string($_POST['site_keywords']),
'site_description' => safe_string($_POST['site_description']),
'admin_email' => safe_string($_POST['admin_email']),
'icp_beian' => safe_string($_POST['icp_beian']),
'page_size' => intval($_POST['page_size'])
);
// 验证数据
if(empty($data['site_name'])) {
json_result(0, '请填写站点名称');
}
if(!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
json_result(0, '管理员邮箱格式错误');
}
if($data['page_size'] < 1) {
json_result(0, '每页记录数不能小于1');
}
// 保存到配置文件
if(save_config($data)) {
json_result(1, '保存成功');
}
json_result(0, '保存失败');
break;
}
exit;
}
check_admin("2"); // 检查管理员权限
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>系统设置</title>
<style>
body { font-family: Arial; margin: 0; padding: 15px; background: #f5f5f5; }
.form-box {
max-width: 800px;
margin: 0 auto;
background: #fff;
border-radius: 4px;
padding: 20px;
box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.form-group {
margin-bottom: 15px;
}
.form-group label {
display: block;
margin-bottom: 5px;
color: #333;
}
.form-group input, .form-group select, .form-group textarea {
width: 100%;
padding: 8px;
border: 1px solid #ddd;
border-radius: 4px;
box-sizing: border-box;
}
.form-group .help {
font-size: 12px;
color: #666;
margin-top: 5px;
}
.btn {
padding: 8px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
background: #007bff;
color: #fff;
}
.btn:hover {
background: #0056b3;
}
</style>
</head>
<body>
<?php echo admin_header(); ?>
<div class="form-box">
<h2>系统设置</h2>
<div class="form-group">
<label>站点名称</label>
<input type="text" id="site_name" value="<?php echo $config['site_name']; ?>">
<div class="help">显示在浏览器标题栏和页面顶部</div>
</div>
<div class="form-group">
<label>站点关键词</label>
<input type="text" id="site_keywords" value="<?php echo $config['site_keywords']; ?>">
<div class="help">用于SEO优化，多个关键词用英文逗号分隔</div>
</div>
<div class="form-group">
<label>站点描述</label>
<textarea id="site_description" rows="3"><?php echo $config['site_description']; ?></textarea>
<div class="help">用于SEO优化，简要描述网站的主要内容</div>
</div>
<div class="form-group">
<label>管理员邮箱</label>
<input type="email" id="admin_email" value="<?php echo $config['admin_email']; ?>">
<div class="help">用于接收系统通知</div>
</div>
<div class="form-group">
<label>ICP备案号</label>
<input type="text" id="icp_beian" value="<?php echo $config['icp_beian']; ?>">
<div class="help">显示在页面底部，没有可留空</div>
</div>
<div class="form-group">
<label>每页显示记录数</label>
<input type="number" id="page_size" value="<?php echo $config['page_size']; ?>">
<div class="help">用于列表分页显示，建议20-50之间</div>
</div>
<button class="btn" onclick="saveConfig()">保存设置</button>
</div>
<?php echo admin_footer(); ?>
<script src="../inc/js.js<?php echo "?j=".$jstime;?>"></script>
<script>
// 保存设置
function saveConfig() {
var data = {
site_name: document.getElementById('site_name').value,
site_keywords: document.getElementById('site_keywords').value,
site_description: document.getElementById('site_description').value,
admin_email: document.getElementById('admin_email').value,
icp_beian: document.getElementById('icp_beian').value,
page_size: document.getElementById('page_size').value,
};
if(!data.site_name) {
alert('请填写站点名称');
return;
}
ajax({
type: 'POST',
url: '?act=save',
data: data,
success: function(res) {
alert(res.msg);
if(res.code) {
location.reload();
}
}
});
}
</script>
</body>
</html>