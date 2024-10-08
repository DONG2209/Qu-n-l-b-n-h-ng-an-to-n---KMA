<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: ../../index.php');
    exit; 
}

include '../../pages/connect.php';

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_sql = "SELECT COUNT(*) AS total FROM orders";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_orders = $total_row['total'];

$total_pages = ceil($total_orders / $limit);

$sql = "SELECT o.id AS order_id, o.user_id, o.username, o.phone, o.email, o.address, o.created_at, o.received,
               oi.product_id, oi.product_name, oi.quantity, oi.price, o.total_price, o.status
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        ORDER BY o.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hóa đơn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .home {
        left: 60px;
        font-size: 26px;
        position: absolute; 
    }

    .home a {
        text-decoration: none;
        color: #007bff;
    }

    .home a:hover {
        text-decoration: underline;
    }

    .action-buttons a {
        margin-right: 5px;
    }

    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }

    h1 {
        text-align: center;
        margin-bottom: 30px;
    }
    .pagination {
        text-align: center; 
        margin: 20px 0; 
    }

    .pagination ul {
        list-style-type: none; 
        padding: 0; 
    }
    .pagination li {
        display: inline; 
        margin: 0 5px; 
    }
    .pagination a {
        text-decoration: none; 
        color: #007bff; 
        padding: 8px 12px; 
        border: 1px solid #007bff; 
        border-radius: 4px; 
        transition: background-color 0.3s; 
    }
    .pagination a:hover {
        background-color: #007bff; 
        color: white; 
    }
    .pagination a[style*="font-weight: bold;"] {
        background-color: #007bff; 
        color: white; 
        cursor: default; 
    }
    </style>
    <script>
        function deleteOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn xóa hóa đơn này?')) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "../libs/delete_order.php?id=" + orderId, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText;
                        if (response.includes("Xóa thành công")) {
                            var row = document.getElementById('order-' + orderId);
                            if (row) {
                                row.remove();
                            }
                        }else {
                            alert("Có lỗi xảy ra: " + response);
                        }
                    }
                };
                xhr.send();
            }
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="home">
            <a href="../index.php">Home</a>
        </div>
        <h1>Quản lý hóa đơn</h1>
        <a href="../libs/add_order.php" class="btn btn-success mb-3">Thêm hóa đơn mới</a>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Mã Đơn Hàng</th>
                    <th>Mã khách hàng</th>
                    <th>Tên Khách Hàng</th>
                    <th>Điện Thoại</th>
                    <th>Email</th>
                    <th>Địa Chỉ</th>
                    <th>Mã sản phẩm</th>
                    <th>Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Giá</th>
                    <th>Tổng Giá Trị</th>
                    <th>Ngày Đặt</th>
                    <th>Trạng thái</th>
                    <th>Ngày nhận</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr id="order-<?php echo $row['order_id']; ?>">
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['address']; ?></td>
                            <td><?php echo $row['product_id']; ?></td>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo number_format($row['total_price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo $row['received']; ?></td>
                            <td class="action-buttons">
                                <a href="../libs/edit_order.php?id=<?php echo $row['order_id']; ?>" class="btn btn-primary btn-sm">Sửa</a>
                                <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="deleteOrder(<?php echo $row['order_id']; ?>)">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14" class="text-center">Không có hóa đơn nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li><a href="?page=<?php echo $page - 1; ?>">&laquo; Trước</a></li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li>
                        <a href="?page=<?php echo $i; ?>" style="<?php echo $i == $page ? 'font-weight: bold;' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li><a href="?page=<?php echo $page + 1; ?>">Tiếp &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
