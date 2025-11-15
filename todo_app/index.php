<?php
require_once 'config/db.php'; 
require_once 'includes/auth_check.php'; 
$error = '';
$success = '';

if (isset($_POST['action']) && $_POST['action'] == 'create') {
    $title = trim($_POST['title']);
    
   
    if (!empty($title)) {
        
        $description = trim($_POST['description']);
        
        $due_date = empty($_POST['due_date']) ? NULL : $_POST['due_date'];
        
        try {
            $sql = "INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$current_user_id, $title, $description, $due_date]);
            $success = "Thêm công việc mới thành công!";
        } catch (PDOException $e) { 
            $error = "Lỗi khi thêm công việc: " . $e->getMessage(); 
        }
    } else { 
        $error = "Tiêu đề công việc không được để trống."; 
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $task_id_to_delete = $_GET['id'];
    
    try {
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$task_id_to_delete, $current_user_id]);
        
       
        if ($stmt->rowCount() > 0) {
            $success = "Xóa công việc thành công!";
        } else {
            $error = "Không tìm thấy công việc hoặc bạn không có quyền xóa.";
        }
        
    } catch (PDOException $e) { 
        $error = "Lỗi khi xóa công việc: " . $e->getMessage(); 
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'update_status' && isset($_POST['task_id'])) {
    $task_id_to_update = $_POST['task_id'];
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'in_progress', 'completed'];
    if (in_array($new_status, $allowed_statuses)) {
        try {
            $sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_status, $task_id_to_update, $current_user_id]);
            
            if ($stmt->rowCount() > 0) {
                $success = "Cập nhật trạng thái thành công!";
            } else {
                $error = "Không tìm thấy công việc hoặc bạn không có quyền.";
            }
        } catch (PDOException $e) { 
            $error = "Lỗi khi cập nhật trạng thái: " . $e->getMessage(); 
        }
    }
}

$status_filter = $_GET['status'] ?? ''; 
$sort_by = $_GET['sort'] ?? 'due_date';  
$sql_select = "SELECT * FROM tasks WHERE user_id = :user_id";
$params = ['user_id' => $current_user_id]; 


if (!empty($status_filter)) {
    
    if (in_array($status_filter, ['pending', 'in_progress', 'completed'])) {
        $sql_select .= " AND status = :status";
        $params['status'] = $status_filter;
    }
}

$allowed_sort_columns = ['due_date', 'created_at', 'status', 'title'];
if (in_array($sort_by, $allowed_sort_columns)) {
    $sql_select .= " ORDER BY $sort_by ASC"; 
} else {
    
    $sql_select .= " ORDER BY due_date ASC";
}

$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute($params);
$tasks = $stmt_select->fetchAll(); 

?>

<?php require 'includes/header.php';  ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<div class="card mb-4 shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Thêm công việc mới</h5>
    </div>
    <div class="card-body">
        <form action="index.php" method="POST"> <input type="hidden" name="action" value="create"> <div class="row g-3">
                <div class="col-md-5">
                    <label for="title" class="form-label">Tiêu đề *</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="col-md-5">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" rows="1"></textarea>
                </div>
                <div class="col-md-2">
                    <label for="due_date" class="form-label">Ngày hết hạn</label>
                    <input type="date" class="form-control" id="due_date" name="due_date" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Thêm công việc</button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="card mb-4">
    <div class="card-body">
        <form action="index.php" method="GET" class="row g-3 align-items-end"> <div class="col-md-5">
                <label for="status_filter" class="form-label">Lọc theo trạng thái</label>
                <select name="status" id="status_filter" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" <?= ($status_filter == 'pending') ? 'selected' : '' ?>>Đang chờ (Pending)</option>
                    <option value="in_progress" <?= ($status_filter == 'in_progress') ? 'selected' : '' ?>>Đang làm (In Progress)</option>
                    <option value="completed" <?= ($status_filter == 'completed') ? 'selected' : '' ?>>Hoàn thành (Completed)</option>
                </select>
            </div>
            <div class="col-md-5">
                <label for="sort_by" class="form-label">Sắp xếp theo</label>
                <select name="sort" id="sort_by" class="form-select">
                    <option value="due_date" <?= ($sort_by == 'due_date') ? 'selected' : '' ?>>Ngày hết hạn</option>
                    <option value="created_at" <?= ($sort_by == 'created_at') ? 'selected' : '' ?>>Ngày tạo</option>
                    <option value="title" <?= ($sort_by == 'title') ? 'selected' : '' ?>>Tiêu đề (A-Z)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Lọc / Sắp xếp</button>
            </div>
        </form>
    </div>
</div>


<h3 class>Danh sách công việc của bạn</h3>
<div class="list-group shadow-sm">
    
    <?php if (empty($tasks)): ?>
        
        <div class="list-group-item text-center text-muted p-4">
            Bạn chưa có công việc nào. Hãy thêm công việc mới ở trên!
        </div>
        
    <?php else: ?>
        
        <?php foreach ($tasks as $task): ?>
            
            <div class="list-group-item list-group-item-action mb-2 border">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?= htmlspecialchars($task['title']) ?></h5>
                    
                    <small class="text-muted">
                        Hết hạn: 
                        <?php if ($task['due_date']): ?>
                            <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </small>
                </div>
                
                <?php if (!empty($task['description'])): ?>
                    <p class="mb-1"><?= htmlspecialchars($task['description']) ?></p>
                <?php endif; ?>
                
                <hr class="my-2">
                
                <div class="d-flex justify-content-between align-items-center">
                    <form action="index.php" method="POST" class="d-inline-block">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                            <option value="pending" <?= ($task['status'] == 'pending') ? 'selected' : '' ?>>Đang chờ</option>
                            <option value="in_progress" <?= ($task['status'] == 'in_progress') ? 'selected' : '' ?>>Đang làm</option>
                            <option value="completed" <?= ($task['status'] == 'completed') ? 'selected' : '' ?>>Hoàn thành</option>
                        </select>
                    </form>
                    
                    <div>
                        <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa chi tiết</a>
                        
                        <a href="index.php?action=delete&id=<?= $task['id'] ?>" 
                           class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa công việc này?');">Xóa</a>
                    </div>
                </div>
            </div> <?php endforeach; ?>
        
    <?php endif; ?>
    
</div> <?php require 'includes/footer.php'; ?>