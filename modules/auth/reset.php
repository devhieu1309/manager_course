<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ");
}

$data = [
    "title" => "Đặt lại mật khẩu"
];
layout("header-auth", $data);

// Lấy token từ url
$filterGet = filterData('get');
$tokenReset  = $filterGet['token'] ?? false;

if (!empty($tokenReset)) {
    // Check token có chính sách hay không
    $checkToken = getOne("SELECT * FROM users WHERE forget_token = '$tokenReset'");
    if (!empty($checkToken)) {
        if (isPost()) {
            $filter = filterData();
            $errors = [];

            // Validate password
            if (empty(trim($filter['password']))) {
                $errors['password']['required'] = 'Mật khẩu bắt buộc phải nhập.';
            } else {
                if (strlen(trim($filter['password'])) < 6) {
                    $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 kí tự.';
                }
            }

            // Validate confirm password
            if (empty(trim($filter['confirm_password']))) {
                $errors['confirm_password']['required'] = 'Vui lòng nhập lại mật khẩu.';
            } else {
                if (trim($filter['password']) !== trim($filter['confirm_password'])) {
                    $errors['confirm_password']['like'] = 'Mật khẩu nhập lại không khớp.';
                }
            }

            if (empty($errors)) {
                $password = password_hash($filter['password'], PASSWORD_DEFAULT);
                $data = [
                    'password' => $password,
                    'forget_token' => null,
                    'updated_at' => date('Y:m:d H:i:s')
                ];
                $condition = "id = {$checkToken['id']}";
                $updateStatus = update('users', $data, $condition);

                if ($updateStatus) {
                    $emailTo = $checkToken['email'];
                    $subject = 'Đổi mật khẩu thành công';
                    $content = "Chúc mừng bạn đã đổi mật khẩu thành công tại MinhHieuDEV <br>";
                    $content .= "Nếu không phải bạn thao tác đổi mật khẩu thì hãy liên hệ ngay với admin.<br>";
                    $content .= "Cảm ơn bạn đã ủng hộ MinhHieuDEV!!";

                    // Gửi email
                    sendMail($emailTo, $subject, $content);

                    setsessionFlash('msg', 'Đổi mật khẩu thành công.');
                    setsessionFlash('msg_type', 'success');
                } else {
                    setsessionFlash('msg', 'Đã có lỗi xảy ra, vui lòng thử lại sau!!');
                    setsessionFlash('msg_type', 'danger');
                }
            } else {
                setsessionFlash('msg', 'Dữ liệu không hợp lệ, hãy kiểm tra lại!!');
                setsessionFlash('msg_type', 'danger');

                setSessionFlash('oldData', $filter);
                setSessionFlash('errors', $errors);
            }
        }
    } else {
        getMsg('Liên kết đã hết hạn hoặc không tồn tại!!', 'danger');
    }
} else {
    getMsg('Liên kết đã hết hạn hoặc không tồn tại!!', 'danger');
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
                        <h2 class="fw-normal mb-3 me-3">Đặt lại mật khẩu</h2>
                    </div>

                    <!-- Password mới -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="password" type="password" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu mới" />
                        <?php echo (!empty($errorArray['password'])) ? formError($errorArray, 'password') : ''; ?>
                    </div>

                    <!-- Nhập lại Password mới -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="confirm_password" type="password" class="form-control form-control-lg"
                            placeholder="Nhập lại mật khẩu mới" />
                        <?php echo (!empty($errorArray['confirm_password'])) ? formError($errorArray, 'confirm_password') : ''; ?>
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Gửi</button>
                        <p class="small fw-bold mt-2 pt-1 mb-0">Đã đổi mật khẩu? <a href="<?php echo _HOST_URL; ?>?module=auth&action=login"
                                class="link-danger">Quay lại trang đăng nhập</a></p>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<?php layout('footer') ?>
