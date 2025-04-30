<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: admin/tongji.php
// 文件大小: 6749 字节
// 最后修改时间: 2024-12-16 19:28:08
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
case 'get_activities':
$list = $db->get_all("SELECT id,活动名称 FROM bm_info ORDER BY id DESC");
json_result(1, 'ok', array('list' => $list));
break;
// 获取统计数据
case 'get_stats':
$id = intval($_GET['id']);
if(!$id) {
json_result(0, '请选择活动');
}
// 获取活动信息
$activity = $db->get_one("SELECT * FROM bm_info WHERE id=$id");
if(!$activity) {
json_result(0, '活动不存在');
}
// 获取报名总数
$total = $db->count('bm_jilu', "活动ID=$id");
// 获取各状态数量
$status = array();
for($i = 0; $i <= 2; $i++) {
$count = $db->count('bm_jilu', "活动ID=$id AND 当前状态=$i");
$status[$i] = $count;
}
// 获取各选项数量
$options = array();
$opts = explode('|', $activity['活动选项']);
foreach($opts as $opt) {
$count = $db->count('bm_jilu', "活动ID=$id AND 选择内容='$opt'");
$options[$opt] = $count;
}
json_result(1, 'ok', array(
'activity' => $activity,
'total' => $total,
'status' => $status,
'options' => $options
));
break;
// 导出数据
case 'export':
$id = intval($_GET['id']);
$tp = intval($_GET['tp']);
if(!$id) {
json_result(0, '请选择活动');
}
// 获取报名数据
$sql = "SELECT * FROM bm_jilu WHERE 活动ID=$id ORDER BY id DESC";
$list = $db->get_all($sql);
if($tp == "1"){ $tg = "\t"; $hz=".txt"; }else{ $tg = ","; $hz=".csv";}
// 生成CSV文件
//$filename = date('YmdHis') ."_{$id}_". uniqid() . $hz;
$filename = date('YmdHis') . uniqid() . $hz;
$filepath = '../uploads/' . $filename;
$fp = fopen($filepath, 'w');
//header('Content-Type: text/csv; charset=utf-8');
//header('Content-Disposition: attachment; filename='.$filename);
fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加BOM头，防止中文乱码
// 写入表头
fputcsv($fp, array('ID','手机号','真实姓名','身份证号','选择内容','状态','提交时间'), $tg);
// 写入数据
foreach($list as $row) {
$status = array('待审核','已通过','未通过')[$row['当前状态']];
fputcsv($fp, array(
$row['id'],
$row['手机号'],
$row['真实姓名'],
$row['身份证号'],
$row['选择内容'],
$status,
$row['提交时间']
), $tg);
}
fclose($fp);
json_result(1, 'ok', array(
'url' => '../uploads/' . $filename
));
break;
// 清空报名
case 'clear':
$id = intval($_POST['id']);
if(!$id) {
json_result(0, '请选择活动');
}
if($db->delete('bm_jilu', "活动ID=$id")) {
json_result(1, '清空成功');
}
json_result(0, '清空失败');
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
<title>报名统计</title>
<style>
body{font-family:Arial;margin:0;padding:15px;background:#f5f5f5;}
.top-bar{margin-bottom:15px;display:flex;align-items:center;}
.top-bar select{margin-right:10px;padding:8px;flex:3;}
.btn{padding:8px 15px;border:none;border-radius:4px;cursor:pointer;margin-left:10px;}
.btn-primary{background:#007bff;color:#fff;}
.btn-danger{background:#dc3545;color:#fff;}
.stats-box{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:15px;margin-bottom:15px;}
.stats-card{background:#fff;border-radius:4px;padding:15px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
.stats-card h3{margin:0 0 10px 0;color:#333;}
.stats-card p{margin:5px 0;color:#666;}
.stats-card .num{font-size:24px;color:#007bff;margin-right:5px;}
.chart-box{background:#fff;border-radius:4px;padding:15px;margin-bottom:15px;}
</style>
</head>
<body>
<?php echo admin_header(); ?>
<div class="top-bar">
<select id="activity" onchange="loadStats()">
<option value="0">请选择活动</option>
</select>
<button class="btn btn-primary" onclick="exportData(2)">导出CSV数据</button>
<button class="btn btn-primary" onclick="exportData(1)">导出TXT数据</button>
<button class="btn btn-danger" onclick="clearData()">清空报名</button>
</div>
<div class="stats-box">
<div class="stats-card">
<h3>报名总数</h3>
<p><span class="num" id="total">0</span>人</p>
</div>
<div class="stats-card">
<h3>审核状态</h3>
<p>待审核：<span class="num" id="status0">0</span>人</p>
<p>已通过：<span class="num" id="status1">0</span>人</p>
<p>未通过：<span class="num" id="status2">0</span>人</p>
</div>
<div class="stats-card">
<h3>选项分布</h3>
<div id="options"></div>
</div>
</div>
<div style="display:none;"><a href="" id="down" download>下载</a></div>
<?php echo admin_footer(); ?>
<script src="../inc/js.js<?php echo "?j=".$jstime;?>"></script>
<script>
// 加载活动列表
function loadActivities() {
ajax({
url: '?act=get_activities',
success: function(res) {
if(res.code) {
var select = document.getElementById('activity');
res.data.list.forEach(function(item) {
var option = new Option(item.活动名称, item.id);
select.add(option);
});
}
}
});
}
// 加载统计数据
function loadStats() {
var id = document.getElementById('activity').value;
if(id == 0) {
document.getElementById('total').textContent = '0';
document.getElementById('status0').textContent = '0';
document.getElementById('status1').textContent = '0';
document.getElementById('status2').textContent = '0';
document.getElementById('options').innerHTML = '';
return;
}
ajax({
url: '?act=get_stats',
data: {id: id},
success: function(res) {
if(res.code) {
var data = res.data;
document.getElementById('total').textContent = data.total;
document.getElementById('status0').textContent = data.status[0];
document.getElementById('status1').textContent = data.status[1];
document.getElementById('status2').textContent = data.status[2];
var html = '';
for(var opt in data.options) {
html += `<p>${opt}：<span class="num">${data.options[opt]}</span>人</p>`;
}
document.getElementById('options').innerHTML = html;
}
}
});
}
// 导出数据
function exportData(tp) {
var id = document.getElementById('activity').value;
if(id == 0) {
alert('请选择活动');
return;
}
ajax({
url: '?act=export&tp='+tp,
data: {id: id},
success: function(res) {
if(res.code) {
//window.open(res.data.url);
  document.getElementById('down').href = res.data.url;
  document.getElementById('down').click();
} else {
alert(res.msg);
}
}
});
}
// 清空数据
function clearData() {
var id = document.getElementById('activity').value;
if(id == 0) {
alert('请选择活动');
return;
}
if(confirm('确定要清空此活动的所有报名记录吗？此操作不可恢复！')) {
ajax({
type: 'POST',
url: '?act=clear',
data: {id: id},
success: function(res) {
alert(res.msg);
if(res.code) {
loadStats();
}
}
});
}
}
// 加载初始数据
loadActivities();
</script>
</body>
</html>