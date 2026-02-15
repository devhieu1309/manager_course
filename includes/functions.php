<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

function layout($layoutName, $data = [])
{
    if (file_exists(_PATH_URL_TEMPLATES . "/layouts/$layoutName.php")) {
        require_once _PATH_URL_TEMPLATES . "/layouts/$layoutName.php";
    }
}

// Hàm gửi mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendMail($emailTo, $subject, $content)
{

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'hp110333@gmail.com';                     //SMTP username
        $mail->Password   = 'tooinbkwwxjxriii';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('hp110333@gmail.com', 'MinhHieuDEV');
        $mail->addAddress($emailTo);     //Add a recipient

        //Content
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $content;

        $mail->SMTPOptions = array(
            'ssl' => [
                'verify_peer' => true,
                'verify_depth' => 3,
                'allow_self_signed' => true,
            ],
        );

        return $mail->send();
    } catch (Exception $e) {
        echo "Gửi thất bại. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Kiểm tra phương thức post
function isPost()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Kiểm tra phương thức get
function isGet()
{
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

// Lọc dữ liệu
function filterData($method = '')
{
    $filterArr = [];
    if (empty($method)) {
        if (isGet()) {
            if (!empty($_GET)) {
                foreach ($_GET as $key => $value) {
                    $key = strip_tags($key);
                    if (is_array($value)) {
                        $filterArr[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                    } else {
                        $filterArr[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
            }
        }

        if (isPost()) {
            if (!empty($_POST)) {
                foreach ($_POST as $key => $value) {
                    $key = strip_tags($key);
                    if (is_array($value)) {
                        $filterArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                    } else {
                        $filterArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
            }
        }
    } else {
        if ($method == 'get') {
            if (!empty($_GET)) {
                foreach ($_GET as $key => $value) {
                    $key = strip_tags($key);
                    if (is_array($value)) {
                        $filterArr[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                    } else {
                        $filterArr[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
            }
        } else if ($method == 'post') {
            if (!empty($_POST)) {
                foreach ($_POST as $key => $value) {
                    $key = strip_tags($key);
                    if (is_array($value)) {
                        $filterArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY);
                    } else {
                        $filterArr[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
            }
        }
    }

    return $filterArr;
}

// Validate email
function validateEmail($email)
{
    if (!empty($email)) {
        $checkEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    return $checkEmail;
}

// Validate int
function validateInt($number)
{
    if (!empty($number)) {
        $checkNumber = filter_var($number, FILTER_VALIDATE_INT);
    }
    return $checkNumber;
}

// Validate phone 0352448746
function isPhone($phone)
{
    $phoneFirst = false;
    if ($phone[0] == '0') {
        $phoneFirst = true;
        $phone = substr($phone,1);
    }

    $checkPhone = false;
    if(validateInt($phone)) {
        $checkPhone = true;
    }

    if($phoneFirst & $checkPhone) {
        return true;
    }

    return false;
}

// Thông báo lỗi
function getMsg($msg, $type = 'success'){
    echo '<div class="annouce-message alert alert-' . $type . '">' . $msg . '</div>';
}

// Hiển thị lỗi
function formError($error, $fieldName){
    return (!empty($error[$fieldName])) ? '<div class="error">' . reset($error[$fieldName]) . '</div>' : false;
}

// Hamf hiển thị lại giá trị cũ
function oldDate($oldData, $fieldName) {
    return (!empty($oldData[$fieldName])) ? $oldData[$fieldName] : '';
}