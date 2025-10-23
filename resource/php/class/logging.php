<?php
function logAdminAction($pdo, $user_id, $log_action) {
    $sql = "INSERT INTO tbl_admin_log (user_id, log_action) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id, $log_action]);
}
?>