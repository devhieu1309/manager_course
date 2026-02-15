<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ");
}

$data = [
    "title" => "Đăng ký hệ thống"
];
layout("header-auth", $data);

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

    // Validate email
    if (empty(trim($filter['email']))) {
        $errors['email']['required'] = 'Email bắt buộc phải nhập.';
    } else {
        // Đúng định dạng email, email này đã tồn tại trong CSDL chưa
        if (!validateEmail(trim($filter['email']))) {
            $errors['email']['isEmail'] = 'Email không đúng định dạng.';
        } else {
            $email = $filter['email'];

            $checkEmail = getRows("SELECT * FROM users WHERE email = '$email'");
            if ($checkEmail > 0) {
                $errors['email']['check'] = 'Email đã tồn tại.';
            }
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
    if (empty(trim($filter['password']))) {
        $errors['password']['required'] = 'Mật khẩu bắt buộc phải nhập.';
    } else {
        if (strlen(trim($filter['password'])) < 6) {
            $errosr['password']['length'] = 'Mật khẩu phải lớn hơn 6 kí tự.';
        }
    }

    // Validate confirm password
    if (empty(trim($filter['confirm_password']))) {
        $errosr['confirm_password']['required'] = 'Vui lòng nhập lại mật khẩu.';
    } else {
        if (trim($filter['password']) !== trim($filter['confirm_password'])) {
            $esrror['confirm_password']['like'] = 'Mật khẩu nhập lại không khớp.';
        }
    }

    if (empty($errors)) {
        // Table: users, data;
        $activeToken = sha1(uniqid() . time());
        $data = [
            'fullname' => $filter['fullname'],
            'address' => $filter['address'] ?? '',
            'phone' => $filter['phone'],
            'email' => $filter['email'],
            'password' => password_hash($filter['password'], PASSWORD_DEFAULT),
            'active_token' =>  $activeToken,
            'group_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $insertStatus = insert('users', $data);
        if ($insertStatus) {
            $emailTo = $filter['email'];
            $subject = 'Kích hoạt tài khoản hệ thống MinhHieuDEV';
            $content = "Chúc mừng bạn đã đăng ký thành công tài khoản tại MinhHieuDEV <br>";
            $content .= "Để kích hoạt tài khoản, bạn hãy click vào đường link bên dưới: <br>";
            $content .= _HOST_URL . '?module=auth&action=active&token=' . $activeToken . '<br>';
            $content .= "Cảm ơn bạn đã ủng hộ MinhHieuDEV!!";

            // Gửi email
            sendMail($emailTo, $subject, $content);

            setsessionFlash('msg', 'Đăng ký thành công, vui lòng kiểm tra email để kích hoạt tài khoản.');
            setsessionFlash('msg_type', 'success');
        } else {
            setsessionFlash('msg', 'Đăng ký thất bại.');
            setsessionFlash('msg_type', 'danger');
        }
    } else {
        setsessionFlash('msg', 'Dữ liệu không hợp lệ, hãy kiểm tra lại!!');
        setsessionFlash('msg_type', 'danger');

        setSessionFlash('oldData', $filter);
        setSessionFlash('error', $error);
    }

    $msg = getSessionFlash('msg');
    $msg_type = getSessionFlash('msg_type');
    $oldData = getSessionFlash('oldData');
    $errorArray = getSessionFlash('error');
}
?>
<section class="vh-100">
    <div class="container-fluid h-custom">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="<?php echo _HOST_URL_TEMPLATES; ?>/assets/image/draw2.webp"
                    class="img-fluid" alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                <?php (!empty($msg) && !empty($msg_type)) ? getMsg($msg, $msg_type) : ''; ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <h2 class="fw-normal mb-3 me-3">Đăng ký tài khoản</h2>
                    </div>

                    <!-- Họ tên -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="fullname" type="text" class="form-control form-control-lg"
                            placeholder="Họ tên" value="<?php echo (!empty($oldData['fullname'])) ? $oldData['fullname'] : ''; ?>" />
                        <?php echo (!empty($errorArray['fullname'])) ? formError($errorArray, 'fullname') : ''; ?>
                    </div>

                    <!-- Nhập email -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="email" type="text" class="form-control form-control-lg"
                            placeholder="Địa chỉ email" value="<?php echo (!empty($oldData['email'])) ? $oldData['email'] : ''; ?>" />
                        <?php echo (!empty($errorArray['email'])) ? formError($errorArray, 'email') : ''; ?>
                    </div>

                    <!-- Số điện thoại -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="phone" type="text" class="form-control form-control-lg"
                            placeholder="Nhập số điện thoại" value="<?php echo (!empty($oldData['phone'])) ? $oldData['phone'] : ''; ?>" />
                        <?php echo (!empty($errorArray['phone'])) ? formError($errorArray, 'phone') : ''; ?>
                    </div>

                    <!-- Mật khẩu -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input name="password" type="password" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu" />
                        <?php echo (!empty($errorArray['password'])) ? formError($errorArray, 'password') : ''; ?>
                    </div>

                    <!-- Nhập lại mật khẩu -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="confirm_password" type="password" class="form-control form-control-lg"
                            placeholder="Nhập lại mật khẩu" />
                        <?php echo (!empty($errorArray['confirm_password'])) ? formError($errorArray, 'confirm_password') : ''; ?>
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Đăng ký</button>
                        <p class="small fw-bold mt-2 pt-1 mb-0">Bạn đã có tài khoản? <a href="<?php echo _HOST_URL; ?>?module=auth&action=login"
                                class="link-danger">Đăng nhập ngay</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php layout('footer') ?>