<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'connect.php';
include 'save_audit.php';

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

// Gmail sender credentials
$SENDER_EMAIL = "avornusolomon6@gmail.com";       // Replace with your Gmail
$SENDER_PASS  = "yhbw hnao kbju ubli";            // Use Gmail App Password (not Gmail password)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username'] ?? ''));
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';

    if (empty($username) || empty($old_pass) || empty($new_pass)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    try {
        // Fetch user info
        $stmt = $conn->prepare("SELECT email, password, school FROM staffs WHERE LOWER(staff_name) = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "User not found"]);
            exit;
        }

        // Verify old password
        if (!password_verify($old_pass, $user['password'])) {
            echo json_encode(["status" => "error", "message" => "Incorrect current password"]);
            exit;
        }

        // Hash new password and update in DB
        $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE staffs SET password = ? WHERE LOWER(staff_name) = ?");
        $update->execute([$new_hashed, $username]);

        // Send Gmail notification
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $SENDER_EMAIL;
            $mail->Password = $SENDER_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($SENDER_EMAIL, 'Intrasem Support');
            $mail->addAddress($user['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Account Activity – Password Changed';
            $mail->Body = "
                <p>Dear {$username},</p>
                <p>Your password for the <b>Intrasem</b> account was recently changed.</p>
                <p>If you did not perform this action, please contact the Teaching and Examination office immediately.</p>
                <br>
                <p>Regards,<br>Intrasem Support Team</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            save_audit_log($conn, $username, $user['school']." Examiner", "Email Error", "Failed to send password change email: ".$mail->ErrorInfo);
        }

        // Save audit log
        save_audit_log($conn, $username, $user['school']." Examiner", "Password Reset", "Password changed successfully");

        echo json_encode(["status" => "success", "message" => "Password changed successfully"]);
    } catch (Exception $e) {
        // Catch all PHP or PDO errors safely
        error_log("Password reset error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "Server error. Please try again later."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
