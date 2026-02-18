<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}
// ?module=users&action=list&course_category=1&keyword=hieu&page=2
// Phân trang: Trước ... 3, 4, '5', 6 , 7 ... Sau
// perPage, maxPage, offset


$data = [
    'title' => 'Danh sách khóa học'
];
layout('header', $data);
layout('sidebar');

$filter = filterData();
$clauseWhere = '';
$courseCategoty = '0';
$keyword = '';

if (isGet()) {
    if (isset($filter['keyword'])) {
        $keyword = $filter['keyword'];
    }

    if (isset($filter['course_category'])) {
        $courseCategoty = $filter['course_category'];
    }

    if (!empty($filter['keyword'])) {
        if (strpos($clauseWhere, 'WHERE') === false) {
            $clauseWhere .= ' WHERE ';
        } else {
            $clauseWhere .= ' AND ';
        }

        $clauseWhere .= "(a.name LIKE '%$keyword%' OR a.description LIKE '%$keyword%')";
    }

    if (!empty($filter['course_category'])) {
        if (strpos($clauseWhere, 'WHERE') === false) {
            $clauseWhere .= ' WHERE ';
        } else {
            $clauseWhere .= ' AND ';
        }

        $clauseWhere .= "a.category_id = $courseCategoty";
    }
}

// Xử lý phân trang
$maxData = getRows("SELECT a.id FROM `course` a $clauseWhere");
$perPage = 5;
$maxPage = ceil($maxData / $perPage);

if (isset($filter['page'])) {
    $page = $filter['page'];
} else {
    $page = 1;
}

if ($page > $maxPage || $page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $perPage;


$getDetailAll = getAll("SELECT a.id, a.name, a.thumbnail, a.price, a.created_at, b.name as category_name FROM `course` a INNER JOIN `course_category` b ON a.category_id = b.id $clauseWhere ORDER BY a.created_at DESC LIMIT $offset, $perPage");

// Xử lý query
if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = str_replace("page=$page", "", $queryString);
    $queryString = rtrim($queryString, '&');
}

$msg = getSessionFlash('msg');
$msg_type = getSessionFlash('msg_type');
?>

<div class="container grid-user">
    <div class="container-fulid">
        <?php (!empty($msg) && !empty($msg_type)) ? getMsg($msg, $msg_type) : ''; ?>
        <a href="?module=course&action=add" class="btn btn-success mb-3"><i class="bi bi-plus"></i> Thêm mới khóa học</a>
        <form action="" method="get">
            <input type="hidden" name="module" value="course">
            <input type="hidden" name="action" value="list">
            <div class="row mb-3">
                <div class="col-3">
                    <select class="form-select form-control" name="course_category">
                        <option value="">Lĩnh vực</option>
                        <?php
                        $getCourseCategory = getAll("SELECT * FROM course_category");
                        ?>
                        <?php foreach ($getCourseCategory as $key => $item) : ?>
                            <option <?php echo $item['id'] == $courseCategoty ? 'selected' : ''; ?> value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-7">
                    <input value="<?php echo $keyword; ?>" class="form-control" name="keyword" type="text" placeholder="Nhập thông tin tìm kiếm...">
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
            </div>
        </form>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th scope="col">STT</th>
                    <th scope="col">Tên khóa học</th>
                    <th scope="col">Thumbnail</th>
                    <th scope="col">Giá</th>
                    <th scope="col">Lĩnh vực</th>
                    <th scope="col">Sửa</th>
                    <th scope="col">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($getDetailAll as $key => $item) : ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><img src="<?php echo $item['thumbnail']; ?>" alt="" width="100px"></td>
                        <td><?php echo $item['price']; ?></td>
                        <td><?php echo $item['category_name']; ?></td>
                        <td><a href="?module=course&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil"></i></a></td>
                        <td><a href="?module=course&action=delete&id=<?php echo $item['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa khóa học này không?');" class="btn btn-danger"><i class="bi bi-trash"></i></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $page - 1; ?>">Trước</a></li>
                <?php endif; ?>

                <!-- Tính vị trí bắt đầu 3 - 2 = 1-->
                <?php
                $start = $page - 1;
                if ($start < 1) {
                    $start = 1;
                }
                ?>
                <?php if ($start > 1): ?>
                    <li class="page-item"><a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $page - 1; ?>">...</a></li>
                <?php endif; ?>

                <?php
                $end = $page + 1;
                if ($end > $maxPage) {
                    $end = $maxPage;
                }
                ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>"><a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>

                <?php if ($end < $maxPage): ?>
                    <li class="page-item"><a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $page + 1; ?>">...</a></li>
                <?php endif; ?>

                <?php if ($page < $maxPage): ?>
                    <li class="page-item"><a class="page-link" href="?<?php echo $queryString; ?>&page=<?php echo $page + 1; ?>">Sau</a></li>
                <?php endif; ?>

            </ul>
        </nav>
    </div>
</div>

<?php layout('footer'); ?>