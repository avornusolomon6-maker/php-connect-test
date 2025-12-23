<?php
// insert_careplan.php
header('Content-Type: application/json; charset=UTF-8');
require 'connect.php'; // must set $conn as a PDO instance and produce no extra output

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// read POST inputs
$std_id = trim($_POST['std_id'] ?? '');
$std_level = trim($_POST['std_level'] ?? '');
$care_plan_value = trim($_POST['care_plan_value'] ?? '');
$examiner = trim($_POST['examiner'] ?? '');
$session_no = trim($_POST['session_no'] ?? '');

if ($std_id === '' || $std_level === '' || $care_plan_value === '' || $examiner === '' || $session_no === '') {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

try {
    // fetch existing result row
    $stmt = $conn->prepare("SELECT * FROM results WHERE std_id = ? LIMIT 1");
    $stmt->execute([$std_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(["status" => "error", "message" => "This student has not been examined yet"]);
        exit;
    }

    // case-insensitive comparisons
    $examinerLower = mb_strtolower($examiner, 'UTF-8');
    $rowExam1 = isset($row['std_examiner']) ? mb_strtolower($row['std_examiner'], 'UTF-8') : '';
    $rowExam2 = isset($row['std_examiner2']) ? mb_strtolower($row['std_examiner2'], 'UTF-8') : '';
    $rowLevel = isset($row['std_level']) ? $row['std_level'] : '';

    // check level matches
    if ($rowLevel !== $std_level) {
        echo json_encode(["status" => "error", "message" => "Student data does not match the existing data"]);
        exit;
    }

    // helper to convert varchar scores to int safely
    $toInt = function($val) {
        if ($val === null || $val === '') return 0;
        return intval($val);
    };

    // levels that use primary score only
    //$primaryLevels = ["100","200","500","600"];

    if ($session_no === "1") {

        // must be examined by std_examiner (exact match)
        if ($rowExam1 !== $examinerLower) {
            echo json_encode(["status" => "error", "message" => "$std_id was not examined by $examiner"]);
            exit;
        }

        // check care_plan flag: treat missing as "0"
        $currentCarePlanFlag = isset($row['care_plan']) ? $row['care_plan'] : '0';
        if ($currentCarePlanFlag !== '0') {
            echo json_encode(["status" => "error", "message" => "$std_id has been scored already"]);
            exit;
        }

        $currentScore = $toInt($row['std_score']);
        $add = $toInt($care_plan_value);
        $newScore = strval($currentScore + $add);

        $update = $conn->prepare("UPDATE results SET std_score = ?, care_plan = ? WHERE std_id = ?");
        $ok = $update->execute([$newScore, "1", $std_id]);

        if ($ok && $update->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "Care plan saved successfully"]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save care plan. Please try again"]);
            exit;
        }

    } elseif ($session_no === "2") {
        // sessionNo 2: handle first or second examiner
        // if examiner matches std_examiner => update std_score & care_plan
        // else if matches std_examiner2 => update std_score2 & care_plan2
        if ($examinerLower === $rowExam1) {
            // update first examiner's score
            $currentCarePlanFlag = isset($row['care_plan']) ? $row['care_plan'] : '0';
            if ($currentCarePlanFlag !== '0') {
                echo json_encode(["status" => "error", "message" => "$std_id has been scored already"]);
                exit;
            }
            $currentScore = $toInt($row['std_score']);
            $add = $toInt($care_plan_value);
            $newScore = strval($currentScore + $add);

            $update = $conn->prepare("UPDATE results SET std_score = ?, care_plan = ? WHERE std_id = ?");
            $ok = $update->execute([$newScore, "1", $std_id]);

            if ($ok && $update->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "Care plan saved successfully"]);
                exit;
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to save care plan. Please try again"]);
                exit;
            }

        } elseif ($examinerLower === $rowExam2) {
            // update second examiner's score2
            $currentCarePlanFlag2 = isset($row['care_plan2']) ? $row['care_plan2'] : '0';
            if ($currentCarePlanFlag2 !== '0') {
                echo json_encode(["status" => "error", "message" => "$std_id has been scored already"]);
                exit;
            }
            $currentScore2 = $toInt($row['std_score2']);
            $add = $toInt($care_plan_value);
            $newScore2 = strval($currentScore2 + $add);

            $update = $conn->prepare("UPDATE results SET std_score2 = ?, care_plan2 = ? WHERE std_id = ?");
            $ok = $update->execute([$newScore2, "1", $std_id]);

            if ($ok && $update->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "Care plan saved successfully"]);
                exit;
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to save care plan. Please try again"]);
                exit;
            }

        } else {
            echo json_encode(["status" => "error", "message" => "$std_id was not examined by $examiner"]);
            exit;
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Unsupported session number"]);
        exit;
    }

} catch (Exception $e) {
    // do not leak internal details in production; return message for debugging
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    exit;
}
?>
