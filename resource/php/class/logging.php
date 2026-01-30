<?php
function logAdminAction($pdo, $user_id, $log_action) {
    $expiration_date = date('Y-m-d H:i:s', strtotime('+1 year'));
    
    $sql = "INSERT INTO tbl_admin_log (user_id, log_action, log_expiration) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id, $log_action, $expiration_date]);
}

function cleanupExpiredLogs($pdo) {
    $sql = "DELETE FROM tbl_admin_log WHERE log_expiration < NOW()";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute();
}
?>