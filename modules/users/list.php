<?php
if (!defined("_HIENU")) {
    die("Truy cập không hợp lệ!");
}
// ?module=users&action=list&group=1&keyword=hieu&page=2
// Phân trang: Trước ... 3, 4, '5', 6 , 7 ... Sau
// perPage, maxPage, offset


$data = [
    'title' => 'Danh sách người dùng'
];
layout('header', $data);
layout('sidebar');

$filter = filterData();
$clauseWhere = '';
$group = '0';
$keyword = '';

if (isGet()) {
    if (isset($filter['keyword'])) {
        $keyword = $filter['keyword'];
    }

    if (isset($filter['group'])) {
        $group = $filter['group'];
    }

    if (!empty($filter['keyword'])) {
        if (strpos($clauseWhere, 'WHERE') === false) {
            $clauseWhere .= ' WHERE ';
        } else {
            $clauseWhere .= ' AND ';
        }

        $clauseWhere .= "(a.fullname LIKE '%$keyword%' OR a.email LIKE '%$keyword%')";
    }

    if (!empty($filter['group'])) {
        if (strpos($clauseWhere, 'WHERE') === false) {
            $clauseWhere .= ' WHERE ';
        } else {
            $clauseWhere .= ' AND ';
        }

        $clauseWhere .= "a.group_id = $group";
    }
}

// Xử lý phân trang
$maxData = getRows("SELECT a.id FROM `users` a $clauseWhere");
$perPage = 10;
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


$getDetailAll = getAll("SELECT a.id, a.fullname, a.email, a.created_at, b.name FROM `users` a INNER JOIN `groups` b ON a.group_id = b.id $clauseWhere ORDER BY a.created_at DESC LIMIT $offset, $perPage");
// echo "SELECT a.id, a.fullname, a.email, a.created_at, b.name FROM `users` a INNER JOIN `groups` b ON a.group_id = b.id $clauseWhere ORDER BY a.created_at DESC";
$getGroups = getAll("SELECT * FROM `groups`");

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
        <a href="?module=users&action=add" class="btn btn-success mb-3"><i class="bi bi-plus"></i> Thêm mới user</a>
        <form action="" method="get">
            <input type="hidden" name="module" value="users">
            <input type="hidden" name="action" value="list">
            <div class="row mb-3">
                <div class="col-3">
                    <select class="form-select form-control" name="group">
                        <option value="">Nhóm người dùng</option>
                        <?php foreach ($getGroups as $key => $item) : ?>
                            <option <?php echo $item['id'] == $group ? 'selected' : ''; ?> value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
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
                    <th scope="col">Họ tên</th>
                    <th scope="col">Email</th>
                    <th scope="col">Ngày đăng ký</th>
                    <th scope="col">Nhóm</th>
                    <th scope="col">Phân quyền</th>
                    <th scope="col">Sửa</th>
                    <th scope="col">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($getDetailAll as $key => $item) : ?>
                    <tr>
                        <td><?php echo $key + 1; ?></td>
                        <td><?php echo $item['fullname']; ?></td>
                        <td><?php echo $item['email']; ?></td>
                        <td><?php echo $item['created_at']; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><a href="?module=users&action=permission&id=<?php echo $item['id']; ?>" class="btn btn-primary">Phân quyền</a></td>
                        <td><a href="?module=users&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-warning"><i class="bi bi-pencil"></i></a></td>
                        <td><a href="?module=users&action=delete&id=<?php echo $item['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa user này không?');" class="btn btn-danger"><i class="bi bi-trash"></i></a></td>
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