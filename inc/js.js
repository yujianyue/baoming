// AJAX通用函数
function ajax(options) {
options = options || {};
options.type = (options.type || "GET").toUpperCase();
options.dataType = options.dataType || "json";
const params = formatParams(options.data);
let xhr;
if(window.XMLHttpRequest) {
xhr = new XMLHttpRequest();
} else {
xhr = new ActiveXObject('Microsoft.XMLHTTP');
}
xhr.onreadystatechange = function() {
if(xhr.readyState == 4) {
const status = xhr.status;
if(status >= 200 && status < 300) {
let response;
if(options.dataType == "json") {
response = JSON.parse(xhr.responseText);
} else {
response = xhr.responseText;
}
options.success && options.success(response);
} else {
options.error && options.error(status);
}
}
}
if(options.type == "GET") {
xhr.open("GET", options.url + "&" + params, true);
xhr.send(null);
} else if(options.type == "POST") {
xhr.open("POST", options.url, true);
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
xhr.send(params);
}
}
// 参数格式化
function formatParams(data) {
const arr = [];
for(let name in data) {
arr.push(encodeURIComponent(name) + "=" + encodeURIComponent(data[name]));
}
return arr.join("&");
}
// 显示遮罩层
function showModal(options) {
const modal = document.createElement("div");
modal.className = "modal";
const content = document.createElement("div");
content.className = "modal-content";
// 标题栏
const header = document.createElement("div");
header.className = "modal-header";
header.innerHTML = `
<h3>${options.title || ''}</h3>
<span class="close">&times;</span>
`;
// 内容区
const body = document.createElement("div");
body.className = "modal-body";
body.innerHTML = options.content;
// 按钮栏
const footer = document.createElement("div");
footer.className = "modal-footer";
if(options.buttons) {
options.buttons.forEach(btn => {
const button = document.createElement("button");
button.innerText = btn.text;
button.onclick = btn.click;
footer.appendChild(button);
});
}
content.appendChild(header);
content.appendChild(body);
content.appendChild(footer);
modal.appendChild(content);
document.body.appendChild(modal);
// 关闭按钮事件
modal.querySelector(".close").onclick = function() {
document.body.removeChild(modal);
};
return modal;
}
// 添加样式
const style = document.createElement("style");
style.textContent = `
.modal {
position: fixed;
top: 0;
left: 0;
width: 100%;
height: 100%;
background: rgba(0,0,0,0.5);
display: flex;
align-items: center;
justify-content: center;
z-index: 1000;
}
.modal-content {
background: #fff;
border-radius: 4px;
min-width: 300px;
max-width: 90%;
max-height: 90vh;
display: flex;
flex-direction: column;
}
.modal-header {
padding: 15px;
border-bottom: 1px solid #eee;
display: flex;
justify-content: space-between;
align-items: center;
}
.modal-body {
padding: 15px;
overflow-y: auto;
flex: 1;
}
.modal-footer {
padding: 15px;
border-top: 1px solid #eee;
text-align: right;
}
.modal-footer button {
margin-left: 10px;
padding: 5px 15px;
border: none;
border-radius: 4px;
cursor: pointer;
}
.close {
cursor: pointer;
font-size: 20px;
}
.close:hover {
color: #999;
}
`;
document.head.appendChild(style);