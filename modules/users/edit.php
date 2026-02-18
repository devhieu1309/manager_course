<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

$data = [
    'title' => 'Chỉnh sửa người dùng'
];

layout('header', $data);
layout('sidebar');

$getData = filterData('get');
if (!empty($getData['id'])) {
    $user_id = $getData['id'];
    $detailUser = getOne("SELECT * FROM `users` WHERE id = $user_id");
    if (!empty($detailUser)) {
    } else {
        setsessionFlash('msg', 'Người dùng không tồn tại!!');
        setsessionFlash('msg_type', 'danger');
        redirect('?module=user&action=list');
    }
} else {
    setsessionFlash('msg', 'Có lỗi xảy ra, vui lòng thử lại!!');
    setsessionFlash('msg_type', 'danger');
    redirect('?module=user&action=list');
}


if (isPost()) {
    $filter = filterData();
    $errors = [];

    // Validate fullname
    if (empty(trim($filter['fullname']))) {
        $errors['fullname']['required'] = 'Họ tên bắt buộc phải nhập.';
    } else {
        if (strlen(trim($filter['fullname'])) < 5) {
            $errors['fullname']['length'] = 'Họ tên phải lớn hơn 5 kí tự.';
        }
    }

    if ($filter['email'] !== $detailUser['email']) {
        // Validate email
        if (empty(trim($filter['email']))) {
            $errors['email']['required'] = 'Email bắt buộc phải nhập.';
        } else {
            // Đúng định dạng email, email này đã tồn tại trong CSDL chưa
            if (!validateEmail(trim($filter['email']))) {
                $errors['email']['isEmail'] = 'Email không đúng định dạng.';
            } 
            // else {
            //     $email = $filter['email'];

            //     $checkEmail = getRows("SELECT * FROM users WHERE email = '$email'");
            //     if ($checkEmail > 0) {
            //         $errors['email']['check'] = 'Email đã tồn tại.';
            //     }
            // }
        }
    }

    // Validate phone
    if (empty(trim($filter['phone']))) {
        $errors['phone']['required'] = 'Số điện thoại bắt buộc phải nhập.';
    } else {
        if (!isPhone(trim($filter['phone']))) {
            $errors['phone']['isPhone'] = 'Số điện thoại không đúng định dạng.';
        }
    }

    // Validate password
    if (!empty(trim($filter['password']))) {
        if (strlen(trim($filter['password'])) < 6) {
            $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 kí tự.';
        }
    }

    if (empty($errors)) {
        $dataUpdate = [
            'fullname' => $filter['fullname'],
            'email' => $filter['email'],
            'phone' => $filter['phone'],
            'group_id' => $filter['group_id'],
            'status' => $filter['status'],
            'address' => $filter['address'] ?? '',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if(!empty($filter['password'])) {
           $dataUpdate['password'] = password_hash($filter['password'], PASSWORD_DEFAULT);
        }

        $updateStatus = update('users', $dataUpdate, "id = $user_id");
        if ($updateStatus) {
            setsessionFlash('msg', 'Cập nhật người dùng thành công.');
            setsessionFlash('msg_type', 'success');
            redirect('?module=users&action=list');
        } else {
            setsessionFlash('msg', 'Cập nhật người dùng thất bại!!');
            setsessionFlash('msg_type', 'danger');
        }
    } else {
        setsessionFlash('msg', 'Dữ liệu không hợp lệ, hãy kiểm tra lại!!');
        setsessionFlash('msg_type', 'danger');

        setSessionFlash('oldData', $filter);
        setSessionFlash('errors', $errors);
    }
}

$msg = getSessionFlash('msg');
$msg_type = getSessionFlash('msg_type');
$oldData = getSessionFlash('oldData');
if (!empty($detailUser)) {
    $oldData = $detailUser;
}
$errorArray = getSessionFlash('errors');
?>

<div class="container add-user">
    <h2>Chỉnh sửa người dùng</h2>
    <hr>
    <?php (!empty($msg) && !empty($msg_type)) ? getMsg($msg, $msg_type) : ''; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-6 pb-3">
                <label for="fullname">Họ và tên</label>
                <input name="fullname" value="<?php echo (!empty($oldData['fullname'])) ? $oldData['fullname'] : ''; ?>" id="fullname" type="text" class="form-control" placeholder="Họ tên">
                <?php echo (!empty($errorArray['fullname'])) ? formError($errorArray, 'fullname') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="email">Email</label>
                <input name="email" value="<?php echo (!empty($oldData['email'])) ? $oldData['email'] : ''; ?>" id="email" type="text" class="form-control" placeholder="Email">
                <?php echo (!empty($errorArray['email'])) ? formError($errorArray, 'email') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="phone">Số điện thoại</label>
                <input name="phone" value="<?php echo (!empty($oldData['phone'])) ? $oldData['phone'] : ''; ?>" id="phone" type="text" class="form-control" placeholder="Số điện thoại">
                <?php echo (!empty($errorArray['phone'])) ? formError($errorArray, 'phone') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="password">Mật khẩu</label>
                <input name="password" value="" id="password" type="password" class="form-control" placeholder="Mật khẩu">
                <?php echo (!empty($errorArray['password'])) ? formError($errorArray, 'password') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="address">Địa chỉ</label>
                <input name="address" value="<?php echo (!empty($oldData['address'])) ? $oldData['address'] : ''; ?>" id="address" type="text" class="form-control" placeholder="Địa chỉ">
                <?php echo (!empty($errorArray['address'])) ? formError($errorArray, 'address') : ''; ?>
            </div>
            <div class="col-3 pb-3">
                <label for="group">Phân cấp người dùng</label>
                <select id="group" name="group_id" class="form-select form-control">
                    <?php
                    $getGroup = getAll("SELECT * FROM groups");
                    ?>
                    <?php foreach ($getGroup as $item): ?>
                        <option <?php echo (!empty($oldData['group_id']) && $oldData['group_id'] == $item['id']) ? 'selected' : ''; ?> value="<?php echo $item['id'] ?>"><?php echo $item['name'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-3 pb-3">
                <label for="status">Trạng thái tài khoản</label>
                <select id="status" name="status" class="form-select form-control">
                    <option <?php echo (!empty($oldData['status']) && $oldData['status'] == 0) ? 'selected' : ''; ?> value="0">Chưa kích hoạt</option>
                    <option <?php echo (!empty($oldData['status']) && $oldData['status'] == 1) ? 'selected' : ''; ?> value="1">Đã kích hoạt</option>
                </select>
            </div>
        </div>
        <div class="col-3 pb-3">
            <button type="submit" class="btn btn-success">Xác nhận</button>
            <a href="?module=users&action=list" type="submit" class="btn btn-primary">Quay lại</a>
        </div>
    </form>
</div>

<?php layout('footer') ?>