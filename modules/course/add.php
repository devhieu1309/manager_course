<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}

$data = [
    'title' => 'Thêm mới khóa học'
];

layout('header', $data);
layout('sidebar');

if (isPost()) {
    $filter = filterData();
    $errors = [];

    // Validate name
    if (empty(trim($filter['name']))) {
        $errors['name']['required'] = 'Tên khóa học bắt buộc phải nhập.';
    } else {
        if (strlen(trim($filter['name'])) < 5) {
            $errors['name']['length'] = 'Tên khóa học phải lớn hơn 5 kí tự.';
        }
    }

    // Validate slug
    if (empty(trim($filter['slug']))) {
        $errors['slug']['required'] = 'Đường dẫn bắt buộc phải nhập.';
    } else {
        if (strlen(trim($filter['slug'])) < 5) {
            $errors['slug']['length'] = 'Đường dẫn phải lớn hơn 5 kí tự.';
        }
    }

    // Validate price
    if (empty(trim($filter['price']))) {
        $errors['price']['required'] = 'Giá khóa học bắt buộc phải nhập.';
    }

    // Validate description
    if (empty(trim($filter['description']))) {
        $errors['description']['required'] = 'Mô tả khóa học bắt buộc phải nhập.';
    }

    if (empty($errors)) {
        // Xử lý upload ảnh
        $uploadDir = './templates/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Tạo mới thư mực upload
        }

        $fileName = basename($_FILES['thumbnail']['name']);

        $targetFile = $uploadDir . time() . "-" . $fileName;
        $thumbnail = "";
        $checkMove = move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile);
        if ($checkMove) {
            $thumbnail = $targetFile;
        }

        $dataInsert = [
            'name' => $filter['name'],
            'slug' => $filter['slug'],
            'price' => $filter['price'],
            'description' => $filter['description'],
            'thumbnail' => $thumbnail,
            'category_id' => $filter['category_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $insertStatus = insert('course', $dataInsert);
        if ($insertStatus) {
            setsessionFlash('msg', 'Thêm mới khóa học thành công.');
            setsessionFlash('msg_type', 'success');
            redirect('?module=course&action=list');
        } else {
            setsessionFlash('msg', 'Thêm khóa học thất bại!!');
            setsessionFlash('msg_type', 'danger');
        }
    } else {
        setsessionFlash('msg', 'Dữ liệu không hợp lệ, hãy kiểm tra lại!!');
        setsessionFlash('msg_type', 'danger');

        setSessionFlash('oldData', $filter);
        setSessionFlash('errors', $errors);
    }

    $msg = getSessionFlash('msg');
    $msg_type = getSessionFlash('msg_type');
    $oldData = getSessionFlash('oldData');
    $errorArray = getSessionFlash('errors');
}
?>

<div class="container add-user">
    <h2>Thêm mới khóa học</h2>
    <hr>
    <?php (!empty($msg) && !empty($msg_type)) ? getMsg($msg, $msg_type) : ''; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-6 pb-3">
                <label for="name">Tên khóa học</label>
                <input name="name" value="<?php echo (!empty($oldData['name'])) ? $oldData['name'] : ''; ?>" id="name" type="text" class="form-control" placeholder="Tên khóa học">
                <?php echo (!empty($errorArray['name'])) ? formError($errorArray, 'name') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="slug">Đường dẫn</label>
                <input name="slug" value="<?php echo (!empty($oldData['slug'])) ? $oldData['slug'] : ''; ?>" id="slug" type="text" class="form-control" placeholder="Đường dẫn">
                <?php echo (!empty($errorArray['slug'])) ? formError($errorArray, 'slug') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="description">Mô tả khóa học</label>
                <textarea name="description" id="description" class="form-control" placeholder="Mô tả khóa học"><?php echo (!empty($oldData['description'])) ? $oldData['description'] : ''; ?></textarea>
                <?php echo (!empty($errorArray['description'])) ? formError($errorArray, 'description') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="price">Giá khóa học</label>
                <input name="price" value="<?php echo (!empty($oldData['price'])) ? $oldData['price'] : ''; ?>" id="price" type="number" class="form-control" placeholder="Giá khóa học">
                <?php echo (!empty($errorArray['price'])) ? formError($errorArray, 'price') : ''; ?>
            </div>
            <div class="col-6 pb-3">
                <label for="thumbnail">Thumbnail</label>
                <input name="thumbnail" value="<?php echo (!empty($oldData['thumbnail'])) ? $oldData['thumbnail'] : ''; ?>" id="thumbnail" type="file" class="form-control" placeholder="Thumbnail">
                <img width="200" id="previewImge" class="preview-image pt-3" src="" style="display: none" alt="">
                <?php echo (!empty($errorArray['thumbnail'])) ? formError($errorArray, 'thumbnail') : ''; ?>
            </div>
            <div class="col-3 pb-3">
                <label for="group">Lĩnh vực</label>
                <select id="group" name="category_id" class="form-select form-control">
                    <?php
                    $getCourseCategory = getAll("SELECT * FROM course_category");
                    ?>
                    <?php foreach ($getCourseCategory as $item): ?>
                        <option value="<?php echo $item['id'] ?>"><?php echo $item['name'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <div class="col-3 pb-3">
            <button type="submit" class="btn btn-success">Xác nhận</button>
        </div>
    </form>
</div>

<script>
    const previewImage = document.getElementById('previewImge');
    const thumbnailInput = document.getElementById('thumbnail');
    thumbnailInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    })
</script>

<script>
    // Hàm chuyển text thành slug
    function createSlug(strig) {
        return strig.toLowerCase()
            .normalize('NFD') // chuyển ký tự có dấu thành tổ hợp
            .replace(/[\u0300-\u036f]/g, '') // xóa dấu
            .replace(/đ/g, 'd') // thay đ → d
            .replace(/[^a-z0-9\s-]/g, '') // xóa ký tự đặc biệt
            .trim() // bỏ khoảng trắng đầu/cuối
            .replace(/\s+/g, '-') // thay khoảng trắng → -
            .replace(/-+/g, '-'); // bỏ trùng dấu -
    }

    document.getElementById('name').addEventListener('input', function() {
        document.getElementById('slug').value = createSlug(this.value);
    })
</script>


<?php layout('footer') ?>