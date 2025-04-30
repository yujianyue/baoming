<?php

// 查立得PHP+mysql多主题简易报名系统 V2024.12.12
// 文件路径: inc/sqls.php
// 文件大小: 1655 字节
// 最后修改时间: 2024-12-16 14:07:22
// 作者: yujianyue
// 邮件: 15058593138@qq.com
// 版权所有,保留发行权和署名权
// 
if(!defined('IN_SYSTEM')) {
exit('Access Denied');
}
class Database {
private $conn;
public function __construct($conn) {
$this->conn = $conn;
}
// 执行SQL查询
public function query($sql) {
$result = mysqli_query($this->conn, $sql);
if(!$result) {
throw new Exception("SQL Error: " . mysqli_error($this->conn));
}
return $result;
}
// 获取单条记录
public function get_one($sql) {
$result = $this->query($sql);
return mysqli_fetch_assoc($result);
}
// 获取多条记录
public function get_all($sql) {
$result = $this->query($sql);
$data = array();
while($row = mysqli_fetch_assoc($result)) {
$data[] = $row;
}
return $data;
}
// 插入记录
public function insert($table, $data) {
$fields = array_keys($data);
$values = array_values($data);
foreach($values as &$value) {
$value = "'" . safe_string($value) . "'";
}
$sql = sprintf(
"INSERT INTO %s (%s) VALUES (%s)",
$table,
implode(',', $fields),
implode(',', $values)
);
return $this->query($sql);
}
// 更新记录
public function update($table, $data, $where) {
$sets = array();
foreach($data as $key => $value) {
$sets[] = $key . "='" . safe_string($value) . "'";
}
$sql = sprintf(
"UPDATE %s SET %s WHERE %s",
$table,
implode(',', $sets),
$where
);
return $this->query($sql);
}
// 删除记录
public function delete($table, $where) {
$sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);
return $this->query($sql);
}
// 获取记录总数
public function count($table, $where = '1') {
$sql = sprintf("SELECT COUNT(*) as total FROM %s WHERE %s", $table, $where);
$row = $this->get_one($sql);
return intval($row['total']);
}
}
// 实例化数据库类
$db = new Database($conn);