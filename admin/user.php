<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: admin/user.php
// 文件大小: 9772 字节
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
check_admin();
$act = $_GET['act'];
switch($act) {
// 获取用户列表
case 'get_list':
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$keyword = isset($_GET['keyword']) ? safe_string($_GET['keyword']) : '';
$field = isset($_GET['field']) ? safe_string($_GET['field']) : '手机号';
// 构建WHERE条件
$where = '1';
if($keyword) {
$where .= sprintf(" AND %s LIKE '%%%s%%'", $field, $keyword);
}
// 获取总数
$total = $db->count('bm_user', $where);
// 获取分页数据
$pager = get_pagination($total, $page);
$sql = sprintf(
"SELECT * FROM bm_user WHERE %s ORDER BY id DESC LIMIT %d,%d",
$where,
$pager['offset'],
$pager['size']
);
$list = $db->get_all($sql);
json_result(1, 'ok', array(
'list' => $list,
'pager' => $pager
));
break;
// 添加/编辑用户
case 'save':
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$data = array(
'手机号' => safe_string($_POST['mobile']),
'密码' => safe_string($_POST['password']),
'类型' => safe_string($_POST['type']),
'备注' => safe_string($_POST['remark'])
);
// 验证数据
if(!is_mobile($data['手机号']) && $data['手机号'] != 'admin') {
json_result(0, '手机号格式错误');
}
// 检查手机号是否存在
$where = sprintf("手机号='%s'", $data['手机号']);
if($id) {
$where .= " AND id!=$id";
}
if($db->count('bm_user', $where) > 0) {
json_result(0, '手机号已存在');
}
// 如果有密码则加密
if($data['密码']) {
$data['密码'] = md5($data['密码']);
} else {
unset($data['密码']);
}
if($id) {
// 更新
if($db->update('bm_user', $data, "id=$id")) {
json_result(1, '保存成功');
}
} else {
// 新增
if($db->insert('bm_user', $data)) {
json_result(1, '添加成功');
}
}
json_result(0, '操作失败');
break;
// 删除用户
case 'delete':
$ids = isset($_POST['ids']) ? $_POST['ids'] : array();
if(!$ids) {
json_result(0, '请选择要删除的记录');
}
//$ids = array_map('intval', $ids);
preg_match_all('/\d+/', $ids, $ide);
$where = sprintf("id IN(%s)", implode(',', $ide[0]));
if($db->delete('bm_user', $where)) {
json_result(1, '删除成功');
}
json_result(0, '删除失败');
break;
// 导入用户
case 'import':
if(!isset($_POST['data'])) {
json_result(0, '请粘贴要导入的数据');
}
$rows = explode("\n", trim($_POST['data']));
$success = 0;
foreach($rows as $row) {
$cols = explode("\t", trim($row));
if(count($cols) >= 2) {
$data = array(
'手机号' => safe_string($cols[0]),
'密码' => md5(safe_string($cols[1])),
'类型' => isset($cols[2]) ? safe_string($cols[2]) : 'user',
'备注' => isset($cols[3]) ? safe_string($cols[3]) : '-'
);
if($db->insert('bm_user', $data)) {
$success++;
}
}
}
json_result(1, "成功导入 $success 条记录");
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
<title>用户管理</title>
<style>
body{font-family:Arial;margin:0;padding:15px;background:#f5f5f5;}
.top-bar{margin-bottom:15px;display:flex;justify-content:space-between;align-items:center;}
.search-box{display:flex;align-items:center;}
.search-box select{margin-right:10px;}
.btn{padding:8px 15px;border:none;border-radius:4px;cursor:pointer;margin-left:10px;}
.btn-primary{background:#007bff;color:#fff;}
.btn-danger{background:#dc3545;color:#fff;}
.table{width:100%;background:#fff;border-radius:4px;border-collapse:collapse;margin-bottom:15px;}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid #eee;}
.table th{background:#f8f9fa;}
.form-group{margin-bottom:15px;}
.form-group label{display:block;margin-bottom:5px;}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;}
.pager{text-align:center;margin-top:20px;}
.pager a{display:inline-block;padding:5px 10px;margin:0 5px;border:1px solid #ddd;border-radius:4px;color:#666;text-decoration:none;}
.pager a.active{background:#007bff;color:#fff;border-color:#007bff;}
</style>
</head>
<body>
<?php echo admin_header(); ?>
<div class="top-bar">
<div class="search-box">
<select id="field">
<option value="手机号">手机号</option>
<option value="备注">备注</option>
</select>
<input type="text" id="keyword" placeholder="请输入关键词">
<button class="btn btn-primary" onclick="loadData(1)">搜索</button>
</div>
<div>
<button class="btn btn-primary" onclick="showForm()">添加用户</button>
<button class="btn btn-primary" onclick="showImport()">批量导入</button>
<button class="btn btn-danger" onclick="deleteSelected()">批量删除</button>
</div>
</div>
<table class="table">
<thead>
<tr>
<th width="50"><input type="checkbox" onclick="toggleAll(this)"></th>
<th>ID</th>
<th>手机号</th>
<th>类型</th>
<th>备注</th>
<th>添加时间</th>
<th>最后登录</th>
<th>操作</th>
</tr>
</thead>
<tbody id="list"></tbody>
</table>
<div id="pager" class="pager"></div>
<?php echo admin_footer(); ?>
<script src="../inc/js.js<?php echo "?j=".$jstime;?>"></script>
<script>
var currentPage = 1;
// 加载数据
function loadData(page) {
currentPage = page || 1;
var params = {
page: currentPage,
keyword: document.getElementById('keyword').value,
field: document.getElementById('field').value
};
ajax({
url: '?act=get_list',
data: params,
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
list.forEach(function(item) {
html += `
<tr>
<td><input type="checkbox" name="ids" value="${item.id}"></td>
<td>${item.id}</td>
<td>${item.手机号}</td>
<td>${item.类型}</td>
<td>${item.备注}</td>
<td>${item.添加时间}</td>
<td>${item.最后登录 || '-'}</td>
<td>
<a href="javascript:;" onclick="showForm(${item.id},'${item.手机号}','${item.类型}','${item.备注}')">编辑</a>
<a href="javascript:;" onclick="deleteUser(${item.id})">删除</a>
</td>
</tr>
`;
});
document.getElementById('list').innerHTML = html || '<tr><td colspan="8" align="center">暂无数据</td></tr>';
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
document.getElementById('pager').innerHTML = html;
}
// 显示表单
function showForm(id, mobile, type, remark) {
showModal({
title: id ? '编辑用户' : '添加用户',
content: `
<div class="form-group">
<label>手机号</label>
<input type="text" id="mobile" value="${mobile || ''}" placeholder="请输入手机号">
</div>
<div class="form-group">
<label>密码${id ? '(不修改请留空)' : ''}</label>
<input type="password" id="password" placeholder="请输入密码">
</div>
<div class="form-group">
<label>类型</label>
<select id="type">
<option value="user" ${type=='user'?'selected':''}>普通用户</option>
<option value="admin" ${type=='admin'?'selected':''}>管理员</option>
</select>
</div>
<div class="form-group">
<label>备注</label>
<input type="text" id="remark" value="${remark || ''}" placeholder="请输入备注">
</div>
`,
buttons: [{
text: '保存',
click: function() {
var data = {
id: id || '',
mobile: document.getElementById('mobile').value,
password: document.getElementById('password').value,
type: document.getElementById('type').value,
remark: document.getElementById('remark').value
};
if(!data.mobile || (!id && !data.password)) {
alert('请填写必填项');
return;
}
ajax({
type: 'POST',
url: '?act=save',
data: data,
success: function(res) {
alert(res.msg);
if(res.code) {
loadData(currentPage);
}
}
});
}
}]
});
}
// 显示导入框
function showImport() {
showModal({
title: '批量导入',
content: `
<div class="form-group">
<label>请粘贴Excel数据(手机号、密码、类型、备注)</label>
<textarea id="import_data" rows="10" placeholder="一行一条记录，用Tab分隔"></textarea>
</div>
`,
buttons: [{
text: '导入',
click: function() {
var data = document.getElementById('import_data').value;
if(!data) {
alert('请粘贴要导入的数据');
return;
}
ajax({
type: 'POST',
url: '?act=import',
data: {data: data},
success: function(res) {
alert(res.msg);
if(res.code) {
loadData(1);
}
}
});
}
}]
});
}
// 删除用户
function deleteUser(id) {
if(confirm('确定要删除吗？')) {
ajax({
type: 'POST',
url: '?act=delete',
data: {ids: [id]},
success: function(res) {
alert(res.msg);
if(res.code) {
loadData(currentPage);
}
}
});
}
}
// 删除选项
function deleteSelected() {
var ids = [];
document.getElementsByName('ids').forEach(function(item) {
if(item.checked) {
ids.push(item.value);
}
});
if(!ids.length) {
alert('请选择要删除的记录');
return;
}
if(confirm('确定要删除选中的记录吗？')) {
ajax({
type: 'POST',
url: '?act=delete',
data: {ids: ids},
success: function(res) {
alert(res.msg);
if(res.code) {
loadData(currentPage);
}
}
});
}
}
// 全选/取消
function toggleAll(el) {
document.getElementsByName('ids').forEach(function(item) {
item.checked = el.checked;
});
}
// 加载初始数据
loadData(1);
</script>
</body>
</html>