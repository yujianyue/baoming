<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: admin/baoming.php
// 文件大小: 8727 字节
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
// 获取报名列表
case 'get_list':
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$keyword = isset($_GET['keyword']) ? safe_string($_GET['keyword']) : '';
$field = isset($_GET['field']) ? safe_string($_GET['field']) : '手机号';
$activity = isset($_GET['activity']) ? intval($_GET['activity']) : 0;
// 构建WHERE条件
$where = '1';
if($keyword) {
$where .= sprintf(" AND j.%s LIKE '%%%s%%'", $field, $keyword);
}
if($activity) {
$where .= sprintf(" AND j.活动ID=%d", $activity);
}
// 获取总数
$sql = "SELECT COUNT(*) as total FROM bm_jilu j WHERE $where";
$count = $db->get_one($sql);
$total = $count['total'];
// 获取分页数据
$pager = get_pagination($total, $page);
$sql = sprintf(
"SELECT j.*, i.活动名称
FROM bm_jilu j
LEFT JOIN bm_info i ON i.id=j.活动ID
WHERE %s ORDER BY j.id DESC LIMIT %d,%d",
$where,
$pager['offset'],
$pager['size']
);
$list = $db->get_all($sql);
// 获取活动列表(用于筛选)
$activities = $db->get_all("SELECT id,活动名称 FROM bm_info ORDER BY id DESC");
json_result(1, 'ok', array(
'list' => $list,
'pager' => $pager,
'activities' => $activities
));
break;
// 修改状态
case 'status':
$id = intval($_POST['id']);
$status = intval($_POST['status']);
if($db->update('bm_jilu', array('当前状态' => $status), "id=$id")) {
json_result(1, '状态修改成功');
}
json_result(0, '状态修改失败');
break;
// 批量修改状态
case 'batch_status':
$ids = isset($_POST['ids']) ? $_POST['ids'] : array();
$status = intval($_POST['status']);
if(!$ids) {
json_result(0, '请选择要操作的记录');
}
$ids = array_map('intval', $ids);
$where = sprintf("id IN(%s)", implode(',', $ids));
if($db->update('bm_jilu', array('当前状态' => $status), $where)) {
json_result(1, '状态修改成功');
}
json_result(0, '状态修改失败');
break;
// 删除报名
case 'delete':
$ids = isset($_POST['ids']) ? $_POST['ids'] : array();
if(!$ids) {
json_result(0, '请选择要删除的记录');
}
//$ids = array_map('intval', $ids);
preg_match_all('/\d+/', $ids, $ide);
$where = sprintf("id IN(%s)", implode(',', $ide[0]));
if($db->delete('bm_jilu', $where)) {
json_result(1, '删除成功');
}
json_result(0, '删除失败');
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
<title>报名管理</title>
<style>
body{font-family:Arial;margin:0;padding:15px;background:#f5f5f5;}
.top-bar{margin-bottom:15px;display:flex;justify-content:space-between;align-items:center;}
.search-box{display:flex;align-items:center;}
.search-box select{margin-right:10px;}
.btn{padding:8px 15px;border:none;border-radius:4px;cursor:pointer;margin-left:10px;}
.btn-primary{background:#007bff;color:#fff;}
.btn-danger{background:#dc3545;color:#fff;}
.btn-success{background:#28a745;color:#fff;}
.btn-warning{background:#ffc107;color:#000;}
.table{width:100%;background:#fff;border-radius:4px;border-collapse:collapse;margin-bottom:15px;}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid #eee;}
.table th{background:#f8f9fa;}
.pager{text-align:center;margin-top:20px;}
.pager a{display:inline-block;padding:5px 10px;margin:0 5px;border:1px solid #ddd;border-radius:4px;color:#666;text-decoration:none;}
.pager a.active{background:#007bff;color:#fff;border-color:#007bff;}
</style>
</head>
<body>
<?php echo admin_header(); ?>
<div class="top-bar">
<div class="search-box">
<select id="activity" onchange="loadData(1)">
<option value="0">所有活动</option>
</select>
<select id="field">
<option value="手机号">手机号</option>
<option value="真实姓名">真实姓名</option>
<option value="身份证号">身份证号</option>
</select>
<input type="text" id="keyword" placeholder="请输入关键词">
<button class="btn btn-primary" onclick="loadData(1)">搜索</button>
</div>
<div>
<button class="btn btn-success" onclick="batchStatus(1)">批量通过</button>
<button class="btn btn-warning" onclick="batchStatus(2)">批量拒绝</button>
<button class="btn btn-danger" onclick="deleteSelected()">批量删除</button>
</div>
</div>
<table class="table">
<thead>
<tr>
<th width="50"><input type="checkbox" onclick="toggleAll(this)"></th>
<th>ID</th>
<th>活动名称</th>
<th>手机号</th>
<th>真实姓名</th>
<th>身份证号</th>
<th>选择内容</th>
<th>状态</th>
<th>提交时间</th>
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
field: document.getElementById('field').value,
activity: document.getElementById('activity').value
};
ajax({
url: '?act=get_list',
data: params,
success: function(res) {
if(res.code) {
renderList(res.data.list);
renderPager(res.data.pager);
renderActivities(res.data.activities);
}
}
});
}
// 渲染活动下拉框
function renderActivities(list) {
var select = document.getElementById('activity');
if(select.options.length <= 1) {
list.forEach(function(item) {
var option = new Option(item.活动名称, item.id);
select.add(option);
});
}
}
// 渲染列表
function renderList(list) {
var html = '';
list.forEach(function(item) {
var status = ['待审核', '已通过', '未通过'][item.当前状态];
var statusBtn = '';
if(item.当前状态 == 0) {
statusBtn = `
<button class="btn btn-success" onclick="changeStatus(${item.id},1)">通过</button>
<button class="btn btn-warning" onclick="changeStatus(${item.id},2)">拒绝</button>
`;
}
html += `
<tr>
<td><input type="checkbox" name="ids" value="${item.id}"></td>
<td>${item.id}</td>
<td>${item.活动名称}</td>
<td>${item.手机号}</td>
<td>${item.真实姓名}</td>
<td>${item.身份证号}</td>
<td>${item.选择内容}</td>
<td>${status}</td>
<td>${item.提交时间}</td>
<td>
${statusBtn}
<a href="javascript:;" onclick="deleteRecord(${item.id})">删除</a>
</td>
</tr>
`;
});
document.getElementById('list').innerHTML = html || '<tr><td colspan="10" align="center">暂无数据</td></tr>';
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
// 修改状态
function changeStatus(id, status) {
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
// 批量修改状态
function batchStatus(status) {
var ids = [];
document.getElementsByName('ids').forEach(function(item) {
if(item.checked) {
ids.push(item.value);
}
});
if(!ids.length) {
alert('请选择要操作的记录');
return;
}
ajax({
type: 'POST',
url: '?act=batch_status',
data: {ids: ids, status: status},
success: function(res) {
alert(res.msg);
if(res.code) {
loadData(currentPage);
}
}
});
}
// 删除记录
function deleteRecord(id) {
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