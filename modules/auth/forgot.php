<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ");
}

$data = [
    "title" => "Quên mật khẩu"
];
layout("header-auth", $data);

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

    if (empty($errors)) {
        // Gửi mail
        if (!empty($filter['email'])) {
            $email = $filter['email'];

            $checkEmail = getOne("SELECT * FROM users WHERE email = '$email'");
            if (!empty($checkEmail)) {
                // Upate forgot_token vào bảng users.
                $forget_token = sha1(uniqid() . time());
                $data = [
                    'forget_token' => $forget_token,
                ];
                $condition = "id = {$checkEmail['id']}";
                $updateStatus = update('users', $data, $condition);
                if ($updateStatus) {

                    $emailTo = $email;
                    $subject = 'Đặt lại mật khẩu hệ thống MinhHieuDEV';
                    $content = "Bạn đang yêu cầu đặt lại mật khẩu tài khoản tại MinhHieuDEV. <br>";
                    $content .= "Để thay đổi mật khẩu tài khoản, bạn hãy click vào đường link bên dưới: <br>";
                    $content .= _HOST_URL . '?module=auth&action=reset&token=' . $forget_token . '<br>';
                    $content .= "Cảm ơn bạn đã ủng hộ MinhHieuDEV!!";

                    // Gửi email
                    sendMail($emailTo, $subject, $content);

                    setsessionFlash('msg', 'Gửi yêu cầu thành công, vui lòng kiểm tra email.');
                    setsessionFlash('msg_type', 'success');
                } else {
                    setsessionFlash('msg', 'Đã có lỗi xảy ra, vui lòng thử lại sau!!');
                    setsessionFlash('msg_type', 'danger');
                }
            }
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
                        <h2 class="fw-normal mb-3 me-3">Quên mật khẩu</h2>
                    </div>

                    <!-- Email -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="email" name="email" value="<?php echo (!empty($oldData['email'])) ? $oldData['email'] : ''; ?>" id="form3Example3" class="form-control form-control-lg"
                            placeholder="Địa chỉ email" />
                        <?php echo (!empty($errorArray['email'])) ? formError($errorArray, 'email') : ''; ?>
                    </div>

                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Gửi</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</section>

<?php layout('footer') ?>