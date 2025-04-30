<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: index.php
// 文件大小: 11520 字节
// 最后修改时间: 2024-12-16 14:07:22
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
define('IN_SYSTEM', true);
session_start();
require_once 'inc/conn.php';
require_once 'inc/pubs.php';
require_once 'inc/json.php';
require_once 'inc/sqls.php';
// 检查登录状态
check_login();
// 处理AJAX请求
if(isset($_GET['act'])) {
$act = $_GET['act'];
switch($act) {
// 获取活动列表
case 'get_list':
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
// 获取总数
$total = $db->count('bm_info', '活动开关=0');
// 获取分页数据
$pager = get_pagination($total, $page);
$sql = sprintf(
"SELECT * FROM bm_info WHERE 活动开关=0 ORDER BY id DESC LIMIT %d,%d",
$pager['offset'],
$pager['size']
);
$list = $db->get_all($sql);
json_result(1, 'ok', array(
'list' => $list,
'pager' => $pager
));
break;
// 获取我的报名记录
case 'my_list':
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
// 获取总数
$where = sprintf("用户名='%s'", $_SESSION['user_mobile']);
$total = $db->count('bm_jilu', $where);
// 获取分页数据
$pager = get_pagination($total, $page);
$sql = sprintf(
"SELECT j.*,i.活动名称,i.活动开关
FROM bm_jilu j
LEFT JOIN bm_info i ON i.id=j.活动ID
WHERE j.用户名='%s'
ORDER BY j.id DESC LIMIT %d,%d",
$_SESSION['user_mobile'],
$pager['offset'],
$pager['size']
);
$list = $db->get_all($sql);
json_result(1, 'ok', array(
'sqls' => $sql,
'list' => $list,
'pager' => $pager
));
break;
// 获取报名信息
case 'get_info':
$id = intval($_GET['id']);
// 获取活动信息
$activity = $db->get_one("SELECT * FROM bm_info WHERE id=$id");
if(!$activity) {
json_result(0, '活动不存在');
}
if($activity['活动开关'] == 1) {
json_result(0, '活动已结束');
}
// 获取报名记录
$sql = sprintf(
"SELECT * FROM bm_jilu WHERE 活动ID=%d AND 用户名='%s' LIMIT 1",
$id,
$_SESSION['user_mobile']
);
$record = $db->get_one($sql);
// 如果没有报名记录,获取最近一次报名信息
if(!$record) {
$sql = sprintf(
"SELECT * FROM bm_jilu WHERE 手机号='%s' ORDER BY id DESC LIMIT 1",
$_SESSION['user_mobile']
);
$record = $db->get_one($sql);
}
json_result(1, 'ok', array(
'activity' => $activity,
'record' => $record
));
break;
// 提交报名
case 'submit':
$id = intval($_POST['id']);
// 检查活动状态
$activity = $db->get_one("SELECT * FROM bm_info WHERE id=$id");
if(!$activity || $activity['活动开关'] != 0) {
json_result(0, '活动已结束');
}
$data = array(
'活动ID' => $id,
'用户名' => $_SESSION['user_mobile'],
'手机号' => safe_string($_POST['mobile']),
'真实姓名' => safe_string($_POST['name']),
'身份证号' => safe_string($_POST['idcard']),
'选择内容' => safe_string($_POST['option'])
);
// 验证数据
if(!is_mobile($data['手机号'])) {
json_result(0, '手机号格式错误');
}
if(!is_idcard($data['身份证号'])) {
json_result(0, '身份证号格式错误');
}
preg_match_all('/\w+/', $activity['限定报名'], $idm);
if(count($idm[0])>2 && !in_array($data['身份证号'], $idm[0])) {
json_result(0, '限定的身份证号码才能报名!'); //设定的三个以上号码有效
}
// 检查是否已报名
$where = sprintf(
"活动ID='%d' AND 身份证号='%s'",
$data['活动ID'],
$data['身份证号']
);
$exists = $db->get_one("SELECT id FROM bm_jilu WHERE $where");
if($exists) {
// 更新报名
if($db->update('bm_jilu', $data, "id=".$exists['id'])) {
json_result(1, '修改成功');
}
json_result(0, '修改失败');
} else {
// 新增报名
if($db->insert('bm_jilu', $data)) {
json_result(1, '报名成功');
}
json_result(0, '报名失败');
}
break;
// 修改密码
case 'change_pwd':
$old_pwd = safe_string($_POST['old_pwd']);
$new_pwd = safe_string($_POST['new_pwd']);
$confirm_pwd = safe_string($_POST['confirm_pwd']);
if(empty($old_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
json_result(0, '请填写完整');
}
if($new_pwd !== $confirm_pwd) {
json_result(0, '两次输入的新密码不一致');
}
// 验证旧密码
$sql = sprintf(
"SELECT id FROM bm_user WHERE id=%d AND 密码='%s' LIMIT 1",
$_SESSION['user_id'],
md5($old_pwd)
);
if(!$db->get_one($sql)) {
json_result(0, '原密码错误');
}
// 更新密码
$db->update('bm_user',
array(
'密码' => md5($new_pwd),
'改密时间' => date('Y-m-d H:i:s')
),
sprintf("id=%d", $_SESSION['user_id'])
);
json_result(1, '密码修改成功');
break;
}
exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $config['site_name']; ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Arial;background:#f5f5f5;}
.container{max-width:1200px;margin:0 auto;padding:15px;}
.nav{position:fixed;bottom:0;left:0;width:100%;background:#fff;display:flex;border-top:1px solid #eee;}
.nav a{flex:1;text-align:center;padding:10px;color:#666;text-decoration:none;}
.nav a.active{color:#007bff;}
.list{margin-bottom:60px;}
.card{background:#fff;border-radius:4px;padding:15px;margin-bottom:15px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
.card h3{margin-bottom:10px;}
.card p{color:#666;margin:5px 0;}
.btn{display:inline-block;padding:8px 15px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;margin-right:10px;}
.btn:hover{background:#0056b3;}
.form-group{margin-bottom:15px;}
.form-group label{display:block;margin-bottom:5px;}
.form-group input,.form-group select{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;}
.pager{text-align:center;margin-top:20px;}
.pager a{display:inline-block;padding:5px 10px;margin:0 5px;border:1px solid #ddd;border-radius:4px;color:#666;text-decoration:none;}
.pager a.active{background:#007bff;color:#fff;border-color:#007bff;}
</style>
</head>
<body>
<div class="container">
<div id="list" class="list"></div>
</div>
<div class="nav">
<a href="javascript:;" class="active" onclick="showTab(this,'activity')">活动列表</a>
<a href="javascript:;" onclick="showTab(this,'my')">我的报名</a>
<a href="javascript:;" onclick="showChangePwd()">修改密码</a>
<a href="./login.php">退出登录</a>
</div>
<script src="inc/js.js<?php echo "?j=".$jstime;?>"></script>
<script>
var currentTab = 'activity';
var currentPage = 1;
// 加载数据
function loadData(page) {
currentPage = page || 1;
var data = {
p: page
};
var url = '?act=' + (currentTab == 'activity' ? 'get_list' : 'my_list');
url += '&page=' + currentPage;
ajax({
url: url,
data: data,
success: function(res) {
if(res.code) {
renderList(res.data.list);
renderPager(res.data.pager);
}
}
});
}
// 渲染列表
function renderList(list) {
var html = '';
if(currentTab == 'activity') {
list.forEach(function(item) {
html += `
<div class="card">
<h3>${item.活动名称}</h3>
<p>活动选项：${item.活动选项}</p>
<p id="${item.id}" style="dislay:none;">报名须知：${item.报名须知}</p>
<p>
<button class="btn" onclick="showNotice('${item.报名须知}')">报名须知</button>
<button class="btn" onclick="showSignup(${item.id})">立即报名</button>
</p>
</div>
`;
});
} else {
list.forEach(function(item) {
var status = ['待审核', '已通过', '未通过'][item.当前状态];
var btnHtml = item.活动开关 == 0 ?
`<button class="btn" onclick="showSignup(${item.活动ID})">修改报名</button>` : '';
html += `
<div class="card">
<h3>${item.活动名称}</h3>
<p>报名选项：${item.选择内容}</p>
<p>报名时间：${item.提交时间}</p>
<p>审核状态：${status}</p>
<p>${btnHtml}</p>
</div>
`;
});
}
document.getElementById('list').innerHTML = html || '<div class="card">暂无数据</div>';
}
// 渲染分页
function renderPager(pager) {
if(pager.pages <= 1) return;
var html = '';
if(pager.page > 1) {
html += `<a href="javascript:;" onclick="loadData(1)">首页</a>`;
html += `<a href="javascript:;" onclick="loadData(${pager.page-1})">上一页</a>`;
}
var start = Math.max(1, pager.page - 2);
var end = Math.min(pager.pages, start + 4);
for(var i = start; i <= end; i++) {
if(i == pager.page) {
html += `<a href="javascript:;" class="active">${i}</a>`;
} else {
html += `<a href="javascript:;" onclick="loadData(${i})">${i}</a>`;
}
}
if(pager.page < pager.pages) {
html += `<a href="javascript:;" onclick="loadData(${pager.page+1})">下一页</a>`;
html += `<a href="javascript:;" onclick="loadData(${pager.pages})">末页</a>`;
}
var div = document.createElement('div');
div.className = 'pager';
div.innerHTML = html;
document.getElementById('list').appendChild(div);
}
// 切换标签
function showTab(el, tab) {
document.querySelector('.nav a.active').classList.remove('active');
el.classList.add('active');
currentTab = tab;
loadData(1);
}
// 显示报名须知
function showNotice(notice) {
showModal({
title: '报名须知',
content: `<div style="white-space:pre-wrap">${notice}</div>`
});
}
// 显示报名表单
function showSignup(id) {
ajax({
url: '?act=get_info',
data: {id: id},
success: function(res) {
if(!res.code) {
alert(res.msg);
return;
}
var data = res.data;
var opts = data.activity.活动选项.split('|');
var optHtml = opts.map(function(opt) {
var selected = data.record && data.record.选择内容 == opt ? ' selected' : '';
return `<option value="${opt}"${selected}>${opt}</option>`;
}).join('');
showModal({
title: (data.record ? '修改' : '报名') + ' - ' + data.activity.活动名称,
content: `
<div class="form-group">
<label>手机号</label>
<input type="text" id="mobile" value="${data.record ? data.record.手机号 : ''}" placeholder="请输入手机号">
</div>
<div class="form-group">
<label>真实姓名</label>
<input type="text" id="name" value="${data.record ? data.record.真实姓名 : ''}" placeholder="请输入真实姓名">
</div>
<div class="form-group">
<label>身份证号</label>
<input type="text" id="idcard" value="${data.record ? data.record.身份证号 : ''}" placeholder="请输入身份证号">
</div>
<div class="form-group">
<label>选择项目</label>
<select id="option">${optHtml}</select>
</div>
`,
buttons: [{
text: '提交',
click: function() {
var formData = {
id: id,
mobile: document.getElementById('mobile').value,
name: document.getElementById('name').value,
idcard: document.getElementById('idcard').value,
option: document.getElementById('option').value
};
if(!formData.mobile || !formData.name || !formData.idcard) {
alert('请填写完整信息');
return;
}
ajax({
type: 'POST',
url: '?act=submit',
data: formData,
success: function(res) {
alert(res.msg);
if(res.code) {
location.reload();
}
}
});
}
}]
});
}
});
}
// 显示修改密码
function showChangePwd() {
showModal({
title: '修改密码',
content: `
<div class="form-group">
<label>原密码</label>
<input type="password" id="old_pwd" placeholder="请输入原密码">
</div>
<div class="form-group">
<label>新密码</label>
<input type="password" id="new_pwd" placeholder="请输入新密码">
</div>
<div class="form-group">
<label>确认密码</label>
<input type="password" id="confirm_pwd" placeholder="请再次输入新密码">
</div>
`,
buttons: [{
text: '保存',
click: function() {
var data = {
old_pwd: document.getElementById('old_pwd').value,
new_pwd: document.getElementById('new_pwd').value,
confirm_pwd: document.getElementById('confirm_pwd').value
};
if(!data.old_pwd || !data.new_pwd || !data.confirm_pwd) {
alert('请填写完整');
return;
}
if(data.new_pwd != data.confirm_pwd) {
alert('两次输入的新密码不一致');
return;
}
ajax({
type: 'POST',
url: '?act=change_pwd',
data: data,
success: function(res) {
alert(res.msg);
if(res.code) {
location.reload();
}
}
});
}
}]
});
}
// 加载初始数据
loadData(1);
</script>
</body>
</html>