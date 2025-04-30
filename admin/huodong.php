<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: admin/huodong.php
// 文件大小: 10206 字节
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
// 获取活动列表
case 'get_list':
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$keyword = isset($_GET['keyword']) ? safe_string($_GET['keyword']) : '';
$field = isset($_GET['field']) ? safe_string($_GET['field']) : '活动名称';
// 构建WHERE条件
$where = '1';
if($keyword) {
$where .= sprintf(" AND %s LIKE '%%%s%%'", $field, $keyword);
}
// 获取总数
$total = $db->count('bm_info', $where);
// 获取分页数据
$pager = get_pagination($total, $page);
$sql = sprintf(
"SELECT * FROM bm_info WHERE %s ORDER BY id DESC LIMIT %d,%d",
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
// 添加/编辑活动
case 'save':
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$data = array(
'活动名称' => safe_string($_POST['name']),
'活动选项' => safe_string($_POST['options']),
'报名须知' => safe_string($_POST['notice']),
'限定报名' => safe_string($_POST['limit']),
'活动开关' => intval($_POST['status'])
);
// 验证数据
if(empty($data['活动名称']) || empty($data['活动选项'])) {
json_result(0, '请填写活动名称和选项');
}
if($id) {
// 更新
if($data['活动开关'] == 1) {
$data['关闭时间'] = date('Y-m-d H:i:s');
}
if($db->update('bm_info', $data, "id=$id")) {
json_result(1, '保存成功');
}
} else {
// 新增
if($db->insert('bm_info', $data)) {
json_result(1, '添加成功');
}
}
json_result(0, '操作失败');
break;
// 删除活动
case 'delete':
$ids = isset($_POST['ids']) ? $_POST['ids'] : "";
if(!$ids || $ids=="") {
json_result(0, '请选择要删除的记录');
}
//$ids = array_map('intval', $ids);
preg_match_all('/\d+/', $ids, $ide);
$where = sprintf("id IN(%s)", implode(',', $ide[0]));
// 检查是否有报名记录
$sql = sprintf("SELECT COUNT(*) as total FROM bm_jilu WHERE 活动ID IN(%s)", implode(',', $ide[0]));
$count = $db->get_one($sql);
if($count['total'] > 0) {
json_result(0, '选中的活动已有报名记录，不能删除');
}
if($db->delete('bm_info', $where)) {
json_result(1, '删除成功');
}
json_result(0, '删除失败');
break;
// 修改状态
case 'status':
$id = intval($_POST['id']);
$status = intval($_POST['status']);
$data = array('活动开关' => $status);
if($status == 1) {
$data['关闭时间'] = date('Y-m-d H:i:s');
}
if($db->update('bm_info', $data, "id=$id")) {
json_result(1, '状态修改成功');
}
json_result(0, '状态修改失败');
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
<title>活动管理</title>
<style>
body{font-family:Arial;margin:0;padding:15px;background:#f5f5f5;}
.top-bar{margin-bottom:15px;display:flex;justify-content:space-between;align-items:center;}
.search-box{display:flex;align-items:center;}
.search-box select{margin-right:10px;}
.btn{padding:8px 15px;border:none;border-radius:4px;cursor:pointer;margin-left:10px;}
.btn-primary{background:#007bff;color:#fff;}
.btn-danger{background:#dc3545;color:#fff;}
.btn-success{background:#28a745;color:#fff;}
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
<option value="活动名称">活动名称</option>
<option value="报名须知">报名须知</option>
</select>
<input type="text" id="keyword" placeholder="请输入关键词">
<button class="btn btn-primary" onclick="loadData(1)">搜索</button>
</div>
<div>
<button class="btn btn-primary" onclick="showForm()">发布活动</button>
<button class="btn btn-danger" onclick="deleteSelected()">批量删除</button>
</div>
</div>
<table class="table">
<thead>
<tr>
<th width="50"><input type="checkbox" onclick="toggleAll(this)"></th>
<th>ID</th>
<th>活动名称</th>
<th>活动选项</th>
<th>报名须知</th>
<th>状态</th>
<th>添加时间</th>
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
var status = item.活动开关 == 0 ? '进行中' : '已结束';
var statusBtn = item.活动开关 == 0 ?
`<button class="btn btn-danger" onclick="changeStatus(${item.id},1)">结束</button>` :
`<button class="btn btn-success" onclick="changeStatus(${item.id},0)">重启</button>`;
html += `
<tr>
<td><input type="checkbox" name="ids" value="${item.id}"></td>
<td>${item.id}</td>
<td>${item.活动名称}</td>
<td>${item.活动选项}</td>
<td>${item.报名须知}</td>
<td>${status}</td>
<td>${item.添加时间}</td>
<td>
${statusBtn}
<a href="javascript:;" onclick="showForm(${item.id},'${item.活动名称}','${item.活动选项}','','',${item.活动开关})">编辑</a>
<a href="javascript:;" onclick="deleteActivity(${item.id})">删除</a>
</td>
</tr>
<textarea id="a${item.id}" style="display:none;">${item.限定报名||''}</textarea>
<textarea id="b${item.id}" style="display:none;">${item.报名须知}</textarea>
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
function showForm(id, name, options, notice, limit, status) {
if(id > 1) {
var limit = document.getElementById('a'+id).value || '';
var notice = document.getElementById('b'+id).value || '';
}
showModal({
title: id ? '编辑活动' : '发布活动',
content: `
<div class="form-group">
<label>活动名称</label>
<input type="text" id="name" value="${name || ''}" placeholder="请输入活动名称">
</div>
<div class="form-group">
<label>活动选项(用|分隔)</label>
<textarea id="options" rows="3" placeholder="例如: 选项1|选项2|选项3">${options || ''}</textarea>
</div>
<div class="form-group">
<label>报名须知</label>
<textarea id="notice" rows="3" placeholder="请输入报名须知">${notice || ''}</textarea>
</div>
<div class="form-group">
<label>限定报名(一行一个身份证，不限制请留空)</label>
<textarea id="limit" rows="3" placeholder="一行一个身份证">${limit || ''}</textarea>
</div>
<div class="form-group">
<label>活动状态</label>
<select id="status">
<option value="0" ${status==0?'selected':''}>进行中</option>
<option value="1" ${status==1?'selected':''}>已结束</option>
</select>
</div>
`,
buttons: [{
text: '保存',
click: function() {
var data = {
id: id || '',
name: document.getElementById('name').value,
options: document.getElementById('options').value,
notice: document.getElementById('notice').value,
limit: document.getElementById('limit').value,
status: document.getElementById('status').value
};
if(!data.name || !data.options) {
alert('请填写活动名称和选项');
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
// 修改状态
function changeStatus(id, status) {
if(confirm('确定要' + (status == 1 ? '结束' : '重新开启') + '此活动吗？')) {
ajax({
type: 'POST',
url: '?act=status',
data: {id: id, status: status},
success: function(res) {
alert(res.msg);
if(res.code) {
loadData(currentPage);
}
}
});
}
}
// 删除活动
function deleteActivity(id) {
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
// 删除选中
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