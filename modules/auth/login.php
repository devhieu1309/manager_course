<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ");
}
$data = [
    "title" => "Đăng nhập hệ thống"
];
layout("header-auth", $data);

/**
- Validate dữ liệu nhập vào
- Kiểm tra email có tồn tại trong hệ thống hay không
- Kiểm tra mật khẩu có đúng hay không
- Dữ liệu khơp -> insert vào bảng token_login (để kiểm tra đăng nhập)

- Kiểm tra đăng nhập:
    + Gán token_login lên session
    + Trong phần header => Lấy token từ session về và so khớp với token trong bảng token_login
    + Nếu khớp thì đều hướng đến trang đích, không khớp đều hướng đến trang login

- Điều hướng đến trang dashboard
- Đăng nhập tài khoản ở 1 nơi tại 1 thời điểm
 */

if (isPost()) {
    $filter = filterData();
    $errors = [];

    // Validate email
    if (empty(trim($filter['email']))) {
        $errors['email']['required'] = 'Email bắt buộc phải nhập.';
    } else {
        // Đúng định dạng email, email này đã tồn tại trong CSDL chưa
        if (!validateEmail(trim($filter['email']))) {
            $errors['email']['isEmail'] = 'Email không đúng định dạng.';
        }
    }

    // Validate password
    if (empty(trim($filter['password']))) {
        $errors['password']['required'] = 'Mật khẩu bắt buộc phải nhập.';
    } else {
        if (strlen(trim($filter['password'])) < 6) {
            $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 kí tự.';
        }
    }

    if (empty($errors)) {
        // Kiểm tra dữ liệu
        $email = $filter['email'];
        $password = $filter['password'];

        // Kiểm tra email
        $checkEmail = getOne("SELECT * FROM users WHERE email = '$email'");
        if (!empty($checkEmail)) {
            if (!empty($password)) {
                $checkStatus = password_verify($password, $checkEmail['password']);
                if ($checkStatus) {
                    // Tài khoản chỉ login tại 1 nơi
                    $checkAlready = getRows("SELECT * FROM token_login WHERE user_id = {$checkEmail['id']}");
                    if ($checkAlready > 0) {
                        // echo 'Minh Hieu';
                        // die();
                        setsessionFlash('msg', 'Tài khoản đã được đăng nhập tại 1 nơi khác, vui lòng thử lại sau!!');
                        setsessionFlash('msg_type', 'danger');
                        redirect("?module=auth&action=login");
                    } else {
                        // Tạo token và insert vào bảng token_login
                        $token = sha1(uniqid() . time());

                        // Gán token lên session
                        setSession('token_login', $token);
                        $data = [
                            'token' => $token,
                            'user_id' => $checkEmail['id'],
                            'created_at' => date('Y:m:d M:i:s'),
                        ];
                        $inserToken = insert('token_login', $data);
                        if ($inserToken) {
                            redirect("/");
                        } else {
                            setsessionFlash('msg', 'Đăng nhập không thành công.');
                            setsessionFlash('msg_type', 'danger');
                        }
                    }
                } else {
                    setsessionFlash('msg', 'Vui lòng kiểm tra lại email hoặc mật khẩu!!');
                    setsessionFlash('msg_type', 'danger');
                }
            }
        } else {
            setsessionFlash('msg', 'Dữ liệu hợp lệ.');
            setsessionFlash('msg_type', 'success');
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
$errorArray = getSessionFlash('errors');
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
                <form action="" method="post">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <h2 class="fw-normal mb-3 me-3">Đăng nhập hệ thống</h2>
                    </div>

                    <!-- Email input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" name="email" id="form3Example3" class="form-control form-control-lg"
                            placeholder="Địa chỉ email" value="<?php echo (!empty($oldData['email'])) ? $oldData['email'] : ''; ?>" />
                        <?php echo (!empty($errorArray['email'])) ? formError($errorArray, 'email') : ''; ?>
                    </div>

                    <!-- Password input -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input type="password" name="password" id="form3Example4" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu" />
                        <?php echo (!empty($errorArray['password'])) ? formError($errorArray, 'password') : ''; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="<?php echo _HOST_URL; ?>?module=auth&action=forgot" class="text-body">Quên mật khẩu?</a>
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Đăng nhập</button>
                        <p class="small fw-bold mt-2 pt-1 mb-0">Bạn chưa có tài khoản? <a href="<?php echo _HOST_URL; ?>?module=auth&action=register"
                                class="link-danger">Đăng ký ngay</a></p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<?php layout('footer') ?>