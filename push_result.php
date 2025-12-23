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
$taskNo = $_POST['taskNo'] ?? '';
$sessionNo = $_POST['sessionNo'] ?? '';

if (empty($std_id)) {
    echo json_encode(["status" => "error", "message" => "Missing student ID"]);
    exit;
}

try {
    // Step 1: Check if student already exists
    $stmt = $conn->prepare("SELECT std_level, std_score, std_score2, std_examiner, std_examiner2, date, task1, task2 FROM results WHERE std_id = ?");
    $stmt->execute([$std_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Step 2: Level check
        if ($std_level !== $result['std_level']) {
            echo json_encode(["status" => "error", "message" => "Student data does not match existing record"]);
            exit;
        }

        if (empty($result['std_score'])) {
            $result['std_score'] = "0";
        }
        if (empty($result['std_score2'])) {
            $result['std_score2'] = "0";
        }

        // Step 3: Level-based logic
        switch ($sessionNo) {
            case "1":              
                if ($taskNo === "1") {
                    if ($result['task1'] >= 1) {
                        echo json_encode(["status" => "error", "message" => "$std_id has already been scored"]);
                        exit;
                    }
                    $newScore = (float)$std_score;
                    $updatedScore = ((float)$result['std_score']) + $newScore;
                    $update = $conn->prepare("
                        UPDATE results 
                        SET std_score=?, task1=task1+1,
                        std_facility=?, std_ward=?,
                        std_examiner=?, date=CURRENT_DATE, time=CURRENT_TIME
                        WHERE std_id=?");                  
                    $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                    
                    
                    echo json_encode($ok ? 
                        ["status" => "success", "message" => "Result saved successfully!"] :
                        ["status" => "error", "message" => "Failed to save result"]);
                } elseif($taskNo === "2") {
                    
                    if ($result['task1'] < 1) {                        
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score=?, task1=task1+1, std_facility=?, std_ward=?, std_examiner=?, date=CURRENT_DATE, 
                        time=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);             
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);                        
                    }elseif($result['task1'] < 2){
                        if (strcasecmp($result['std_examiner'], $std_examiner) !== 0) {
                            echo json_encode([
                            "status" => "error",
                            "message" => "Task 2 must be scored by the same examiner who scored Task 1"]);
                            exit;
                        }                        
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score=?, task1=task1+1, std_facility=?, std_ward=?, std_examiner=?, date=CURRENT_DATE, 
                        time=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);             
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);                   
                    }else{
                        echo json_encode(["status" => "error", "message" => "Task 1 and 2 already submitted"]);
                        exit;
                    }
                } else{
                    echo json_encode(["status" => "error", "message" => "Invalid task number"]);
                    exit;
                }
                break;

            case "2":
                if ($taskNo === "1") {
                    if ($result['task1'] < 1) {
                    
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score=?, task1=task1+1, std_facility=?, std_ward=?, std_examiner=?, date=CURRENT_DATE, 
                        time=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                    
                    
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);
                    } elseif ($result['task2'] < 1){
                        $lastDate = new DateTime($result['date']);
                        $today = new DateTime();
                        $interval = $today->diff($lastDate)->days;
                        if ($interval < 1) {
                            echo json_encode(["status" => "error", "message" => "$std_id has already been scored today"]);
                            exit;
                        }
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score2']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score2=?, task2=task2+1, std_facility2=?, std_ward2=?, std_examiner2=?, date2=CURRENT_DATE, 
                        time2=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                   
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);
                    }else {
                        echo json_encode(["status" => "error", "message" => "$std_id has already been examine twice"]);
                            exit;
                    }
                } elseif ($taskNo === "2") {

                    if ($result['task1'] < 1) {                    
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score=?, task1=task1+1, std_facility=?, std_ward=?, std_examiner=?, date=CURRENT_DATE, 
                        time=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                    
                    
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);
                    }else if($result['task1'] < 2){
                        if (strcasecmp($result['std_examiner'], $std_examiner) !== 0) {
                            echo json_encode([
                            "status" => "error",
                            "message" => "Task 2 must be scored by the same examiner who scored Task 1"]);
                            exit;
                        }
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score=?, task1=task1+1, std_facility=?, std_ward=?, std_examiner=?, date=CURRENT_DATE, 
                        time=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                    
                    
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);                    
                }elseif ($result['task2'] < 1) {    
                        $lastDate = new DateTime($result['date']);
                        $today = new DateTime();
                        $interval = $today->diff($lastDate)->days;
                        if ($interval < 1) {
                            echo json_encode(["status" => "error", "message" => "$std_id has already been scored today"]);
                            exit;
                        }
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score2']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score2=?, task2=task2+1, std_facility2=?, std_ward2=?, std_examiner2=?, date2=CURRENT_DATE, 
                        time2=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                    
                    
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);
                    }else if($result['task2'] < 2){
                        if (strcasecmp($result['std_examiner2'], $std_examiner) !== 0) {
                            echo json_encode([
                            "status" => "error",
                            "message" => "Task 2 must be scored by the same examiner who scored Task 1"]);
                            exit;
                        }
                        $newScore = (float)$std_score;
                        $updatedScore = ((float)$result['std_score2']) + $newScore;
                        $update = $conn->prepare("UPDATE results SET std_score2=?, task2=task2+1, std_facility2=?, std_ward2=?, std_examiner2=?, date2=CURRENT_DATE, 
                        time2=CURRENT_TIME WHERE std_id=?");                  
                        $ok = $update->execute([$updatedScore, $std_facility, $std_ward, $std_examiner, $std_id]);                    
                    
                        echo json_encode($ok ? 
                            ["status" => "success", "message" => "Result saved successfully!"] :
                            ["status" => "error", "message" => "Failed to save result"]);
                    }else {
                    echo json_encode(["status" => "error", "message" => "$std_id has already been scored"]);
                    }
                }else{
                    //invalid task no
                    echo json_encode(["status" => "error", "message" => "Invalid task number"]);
                }
                break;
        }
    } else {
        // Step 4: Insert new result
        $taskNumber = 1;
        $insert = $conn->prepare("INSERT INTO results 
            (std_id, std_school, std_program, std_level, std_semester, std_year, std_facility, std_ward, std_group, std_score, std_examiner, date, time, task1)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE, CURRENT_TIME, ?)");
        $ok = $insert->execute([$std_id, $std_school, $std_program, $std_level, $std_semester, $std_year,
                                $std_facility, $std_ward, $std_group, $std_score, $std_examiner, $taskNumber]);

        echo json_encode($ok ?
            ["status" => "success", "message" => "Result saved successfully!"] :
            ["status" => "error", "message" => "Failed to save result"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
