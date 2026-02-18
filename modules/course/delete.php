<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

$filter = filterData('get');

if (!empty($filter)) {
    $course_id = $filter['id'];
    $checkCourse = getOne("SELECT * FROM `course` WHERE id = $course_id");

    if (!empty($checkCourse)) {
        $deleteStatus = delete('course', "id = $course_id");
        if ($deleteStatus) {
            setsessionFlash('msg', 'Xóa khóa học thành công.');
            setsessionFlash('msg_type', 'success');
            redirect('?module=course&action=list');
        }
    } else {
        setsessionFlash('msg', 'Khóa học không tồn tại!!');
        setsessionFlash('msg_type', 'danger');
    }
} else {
    setsessionFlash('msg', 'Đã có lỗi xảy ra, vui lòng thử lại sau!!');
    setsessionFlash('msg_type', 'danger');
}
