<?php
require_once 'config/db.php';
require_once 'includes/auth_check.php';

$error = '';
$success = '';
$task = null; 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php"); 
    exit;
}

$task_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = empty($_POST['due_date']) ? NULL : $_POST['due_date'];
    $status = $_POST['status']; 

    if (empty($title)) {
        $error = "Tiêu đề không được để trống.";
    } else {
        try {
            $sql_update = "UPDATE tasks 
                           SET title = ?, description = ?, due_date = ?, status = ?
                           WHERE id = ? AND user_id = ?";
            
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                $title, 
                $description, 
                $due_date, 
                $status, 
                $task_id,
                $current_user_id
            ]);
            
            $success = "Cập nhật công việc thành công!";
            
        } catch (PDOException $e) {
            $error = "Lỗi khi cập nhật: " . $e->getMessage();
        }
    }
}

try {
    $sql_select = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
    $stmt_select = $pdo->prepare($sql_select);
    $stmt_select->execute([$task_id, $current_user_id]);
    
    $task = $stmt_select->fetch(); 

    if (!$task) {
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    
    die("Lỗi khi tải thông tin công việc: " . $e->getMessage()); 
}

?>
<?php require 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        
        <a href="index.php" class="btn btn-outline-secondary mb-3">&laquo; Quay lại danh sách</a>

        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Chỉnh sửa công việc</h4>
            </div>
            <div class="card-body">
                
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

                <?php if ($task): ?>
                
                <form action="edit_task.php?id=<?= $task_id ?>" method="POST">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($task['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Ngày hết hạn</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   value="<?= htmlspecialchars($task['due_date']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?= ($task['status'] == 'pending') ? 'selected' : '' ?>>Đang chờ</option>
                                <option value="in_progress" <?= ($task['status'] == 'in_progress') ? 'selected' : '' ?>>Đang làm</option>
                                <option value="completed" <?= ($task['status'] == 'completed') ? 'selected' : '' ?>>Hoàn thành</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    </div>
                    
                </form>
                
                <?php endif; ?>
                
            </div>
        </div>
        
    </div>
</div>

<?php require 'includes/footer.php'; ?>