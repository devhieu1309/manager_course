<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

// Set session
function setSession($key, $value)
{
    if (!empty(session_id())) {
        $_SESSION[$key] = $value;
        return true;
    }
    return false;
}

// Lấy dữ liệu từ session
function getSession($key = '')
{
    if (empty($key)) {
        return $_SESSION;
    }else{
        if(isset($_SESSION[$key])){
            return $_SESSION[$key];
        }
    }

    return false;
}
