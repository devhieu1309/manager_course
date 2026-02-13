<?php  
if(!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

// Truy vấn nhiều dòng dữ liệu
function getAll($sql) {
    global $conn;
    $stm = $conn->prepare($sql);
    $stm -> execute();
    $result = $stm -> fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

// Hàm đếm số lượng dòng
function getRows($sql){
    global $conn;
    $stm = $conn->prepare($sql);
    $stm->execute();
    $result = $stm->rowCount();
    return $result;
}

// Truy vấn 1 dòng dữ liệu
function getOne($sql) {
    global $conn;
    $stm = $conn->prepare($sql);
    $stm -> execute();
    $result = $stm -> fetch(PDO::FETCH_ASSOC);
    return $result;
}

// Insert dữ liệu
function insert($table, $data) {
    global $conn;

    $keys = array_keys($data);
    $cot = implode(',', $keys);
    $place = ':' . implode(',:', $keys);

    $sql = "INSERT INTO $table ($cot) VALUES($place)";

    $stm = $conn->prepare($sql);

    // Thực thi câu lệnh
    $stm->execute($data);
}

// Update dữ liệu
function update($table, $data, $condition = '') {
    global $conn;

    $update = '';
    foreach($data as $key => $value) {
        $update .= $key . '=:' .$key . ',';
    }

    $update = rtrim($update, ',');

    if(!empty($condition)){
        $sql = "UPDATE $table SET $update WHERE $condition";
    }else {
        $sql = "UPDATE $table SET $update";
    }

    // Chuẩn bị câu lệnh sql
    $stm = $conn->prepare($sql);

    $stm->execute($data);
    
}

// Hàm xóa dữ liệu
function delete($table, $condition = ''){
    global $conn;

    if(!empty($condition)){
        $sql = "DELETE FROM $table WHERE $condition";
    }else {
        $sql = "DELETE FROM $table";
    }

    $stm = $conn->prepare($sql);

    $stm->execute();
}

// Hàm lấy ID dữ liệu mới insert
function lastID() {
    global $conn;
    return $conn->lastInsertId();
}

// Xóa session
function removeSession($key = ''){
    if(empty($key)){
        session_unset();
        session_destroy();
        return true;
    }else{
        if(isset($_SESSION[$key])){
            unset($_SESSION[$key]);
            return true;
        }
    }
    return false;
}

// Tạo session flash
function setSessionFlash($key, $value) {
    $key = $key . '_flash';
    $rel = setSession($key, $value);
    return $rel;
}

// Lấy session flash
function getSessionFlash($key) {
    $key = $key . '_flash';
    $rel = getSession($key);
    removeSession($key);
    return $rel;
}