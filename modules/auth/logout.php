<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ");
}

if (checkLogin()) {
    $token = getSession('token_login');
    $removeToken = delete('token_login', "token = '$token'");

    if ($removeToken) {
        removeSession('token_login');
        redirect('?module=auth&action=login');
    } else {
        setsessionFlash('msg', 'Lỗi hệ thống, xin vui lòng thử lại sau!!');
        setsessionFlash('msg_type', 'danger');
    }
} else {
    setsessionFlash('msg', 'Lỗi hệ thống, xin vui lòng thử lại sau!!');
    setsessionFlash('msg_type', 'danger');
}
