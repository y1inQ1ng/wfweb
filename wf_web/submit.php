<?php
/**
 * 文峰所外来人员登记 - 表单处理脚本
 * 功能：接收表单数据，验证后跳转到感谢页面
 */

// ==================== 配置区域 ====================
// 管理员接收邮箱（修改为您的真实邮箱）
$admin_email = "1579527706@qq.com";  // ← 请修改这里

// 感谢页面路径
$thank_you_page = "wf_web/thank_you.html";

// 是否开启调试模式（true显示错误详情，上线后改为false）
$debug_mode = true;

// ==================== 函数定义 ====================
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ==================== 主程序开始 ====================
if ($debug_mode) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
}

// 确保没有前置输出
if (ob_get_level()) ob_end_clean();

// 验证请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("错误：请通过表单提交访问本页面");
}

// 收集并清理表单数据
$form_data = [
    'houseNumber' => clean_input($_POST['houseNumber'] ?? ''),
    'name' => clean_input($_POST['name'] ?? ''),
    'idNumber' => clean_input($_POST['idNumber'] ?? ''),
    'phone' => clean_input($_POST['phone'] ?? ''),
    'livingCondition' => clean_input($_POST['livingCondition'] ?? ''),
    'workAddress' => clean_input($_POST['workAddress'] ?? ''),
    'landlordInfo' => clean_input($_POST['landlordInfo'] ?? ''),
    'securityPropaganda' => clean_input($_POST['securityPropaganda'] ?? ''),
    'selfReport' => clean_input($_POST['selfReport'] ?? ''),
    'houseType' => clean_input($_POST['houseType'] ?? '')
];

// 简单验证必填字段
$required_fields = ['houseNumber', 'name', 'idNumber', 'phone', 'livingCondition', 'workAddress'];
foreach ($required_fields as $field) {
    if (empty($form_data[$field])) {
        die("错误：请填写所有必填字段");
    }
}

// 验证身份证格式（18位数字或17位数字+X/x）
if (!preg_match('/^\d{17}[\dXx]$|^\d{18}$/', $form_data['idNumber'])) {
    die("错误：身份证号码格式不正确");
}

// 验证手机号格式
if (!preg_match('/^1[3-9]\d{9}$/', $form_data['phone'])) {
    die("错误：手机号码格式不正确");
}

// ==================== 邮件发送部分 ====================
$email_subject = "文峰所外来人员登记 - {$form_data['name']}";
$email_content = "以下是提交的登记信息：\n\n";

foreach ($form_data as $key => $value) {
    $email_content .= ucfirst($key) . ": " . $value . "\n";
}

$email_content .= "\n提交时间: " . date('Y-m-d H:i:s');
$email_content .= "\nIP地址: " . $_SERVER['REMOTE_ADDR'];

$headers = "From: 文峰所登记系统 <noreply@{$_SERVER['HTTP_HOST']}>\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// 实际发送邮件（测试时可先注释掉）
// $mail_sent = mail($admin_email, $email_subject, $email_content, $headers);
$mail_sent = true; // 测试时模拟发送成功

// ==================== 处理结果 ====================
if ($mail_sent) {
    // 记录到日志文件（可选）
    $log_entry = date('Y-m-d H:i:s') . " | {$form_data['name']} | {$form_data['phone']}\n";
    file_put_contents('submissions.log', $log_entry, FILE_APPEND);
    
    // 跳转到感谢页面
    header("Location: $thank_you_page");
    exit;
} else {
    die("提交失败：邮件发送出错，请稍后再试");
}
?>
