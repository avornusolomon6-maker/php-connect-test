<?php
header('Content-Type: application/json');
require 'connect.php'; // your db connection

// Get POST data
$std_id        = $_POST['std_id'] ?? '';
$std_school    = $_POST['std_school'] ?? '';
$std_program   = $_POST['std_program'] ?? '';
$std_level     = $_POST['std_level'] ?? '';
$std_semester  = $_POST['std_semester'] ?? '';
$std_year      = $_POST['std_year'] ?? '';
$std_facility  = $_POST['std_facility'] ?? '';
$std_ward      = $_POST['std_ward'] ?? '';
$std_group     = $_POST['std_group'] ?? '';
$std_score     = $_POST['std_score'] ?? '';
$std_examiner  = $_POST['std_examiner'] ?? '';

if (empty($std_id)) {
    echo json_encode(["status" => "error", "message" => "Missing student ID"]);
    exit;
}

try {
    // Step 1: Check if student already exists
    $stmt = $conn->prepare("SELECT std_level, std_score, std_score2, date FROM results WHERE std_id = ?");
    $stmt->execute([$std_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Step 2: Level check
        if ($std_level !== $result['std_level']) {
            echo json_encode(["status" => "error", "message" => "Student data does not match existing record"]);
            exit;
        }

        // Step 3: Level-based logic
        switch ($std_level) {
            case "100": case "200": case "500": case "600":
                if (empty($result['std_score'])) {
                    $update = $conn->prepare("UPDATE results 
                        SET std_facility=?, std_ward=?, std_score=?, std_examiner=?, date=CURRENT_DATE, time=CURRENT_TIME 
                        WHERE std_id=?");
                    $ok = $update->execute([$std_facility, $std_ward, $std_score, $std_examiner, $std_id]);
                    echo json_encode($ok ? 
                        ["status" => "success", "message" => "Result saved successfully!"] :
                        ["status" => "error", "message" => "Failed to save result"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "$std_id has already been scored"]);
                }
                break;

            case "300": case "400":
                if (empty($result['std_score'])) {
                    $update = $conn->prepare("UPDATE results 
                        SET std_facility=?, std_ward=?, std_score=?, std_examiner=?, date=CURRENT_DATE, time=CURRENT_TIME 
                        WHERE std_id=?");
                    $ok = $update->execute([$std_facility, $std_ward, $std_score, $std_examiner, $std_id]);
                    echo json_encode($ok ? 
                        ["status" => "success", "message" => "Result saved successfully!"] :
                        ["status" => "error", "message" => "Failed to save result"]);
                } elseif (empty($result['std_score2'])) {
                    $lastDate = new DateTime($result['date']);
                    $today = new DateTime();
                    $interval = $today->diff($lastDate)->days;

                    if ($interval < 1) {
                        echo json_encode(["status" => "error", "message" => "$std_id has already been scored today"]);
                        exit;
                    }

                    $update = $conn->prepare("UPDATE results 
                        SET std_facility2=?, std_ward2=?, std_score2=?, std_examiner2=?, date2=CURRENT_DATE, time2=CURRENT_TIME 
                        WHERE std_id=?");
                    $ok = $update->execute([$std_facility, $std_ward, $std_score, $std_examiner, $std_id]);
                    echo json_encode($ok ? 
                        ["status" => "success", "message" => "Result saved successfully!"] :
                        ["status" => "error", "message" => "Failed to save result"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "$std_id has already been scored"]);
                }
                break;
        }
    } else {
        // Step 4: Insert new result
        $insert = $conn->prepare("INSERT INTO results 
            (std_id, std_school, std_program, std_level, std_semester, std_year, std_facility, std_ward, std_group, std_score, std_examiner, date, time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE, CURRENT_TIME)");
        $ok = $insert->execute([$std_id, $std_school, $std_program, $std_level, $std_semester, $std_year,
                                $std_facility, $std_ward, $std_group, $std_score, $std_examiner]);

        echo json_encode($ok ?
            ["status" => "success", "message" => "Result saved successfully!"] :
            ["status" => "error", "message" => "Failed to save result"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
