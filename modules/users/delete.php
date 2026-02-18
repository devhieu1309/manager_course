<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

$getData = filterData('get');

if (!empty($getData['id'])) {
    $user_id = $getData['id'];

    $checkUser = getRows("SELECT * FROM `users` WHERE id = $user_id");
    if ($checkUser > 0) {
        // Xóa tài khoản
        $checkToken = getRows("SELECT * FROM `token_login` WHERE user_id = $user_id");
        if ($checkToken > 0) {
            // Xóa token
            delete('token_login', "user_id = $user_id");
        }

        $checkDelete = delete('users', "id = $user_id");
        if ($checkDelete) {
            setsessionFlash('msg', 'Xóa người dùng thành công.');
            setsessionFlash('msg_type', 'success');
            redirect('?module=users&action=list');
        } else {
            setsessionFlash('msg', 'Xóa người dùng thất bại!!');
            setsessionFlash('msg_type', 'danger');
        }
    } else {
        setsessionFlash('msg', 'Người dùng không tồn tại!');
        setsessionFlash('msg_type', 'danger');
        redirect('?module=users&action=list');
    }
}
