<?php
session_start();
include 'db.php';

// Step 1: Get all courses
$courseQuery = "SELECT * FROM courses";
$courses = mysqli_query($conn, $courseQuery);

$selected_course = isset($_GET['course_id']) ? $_GET['course_id'] : null;
$selected_student = isset($_GET['student_id']) ? $_GET['student_id'] : null;

$students = [];
$cos = [];

if ($selected_course) {
    // Step 2: Get students (all students for now)
    $studentQuery = "SELECT * FROM students";
    $students = mysqli_query($conn, $studentQuery);

    // Step 3: Get COs for selected course
    $coQuery = "SELECT * FROM course_outcomes WHERE course_id='$selected_course'";
    $cos = mysqli_query($conn, $coQuery);
}

// Step 4: Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $selected_course && $selected_student) {
    foreach ($_POST['co'] as $co_id => $values) {
        $score = $values['score'];
        $max_score = $values['max'];

        // Check if record already exists
        $checkQuery = "SELECT * FROM student_assessments WHERE student_id='$selected_student' AND co_id='$co_id'";
        $checkResult = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            // Update existing record
            $update = "UPDATE student_assessments 
                       SET score='$score', max_score='$max_score' 
                       WHERE student_id='$selected_student' AND co_id='$co_id'";
            mysqli_query($conn, $update);
        } else {
            // Insert new record
            $insert = "INSERT INTO student_assessments (student_id, co_id, score, max_score) 
                       VALUES ('$selected_student', '$co_id', '$score', '$max_score')";
            mysqli_query($conn, $insert);
        }
    }
    $message = "Assessment data saved successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher - Manage Assessments</title>
</head>
<body>
    <h2>Teacher: Add Student CO Assessments</h2>

    <!-- Step 1: Select Course -->
    <form method="GET">
        <label>Select Course:</label>
        <select name="course_id" onchange="this.form.submit()">
            <option value="">-- Select Course --</option>
            <?php while($c = mysqli_fetch_assoc($courses)) { ?>
                <option value="<?php echo $c['id']; ?>" 
                    <?php if($selected_course == $c['id']) echo "selected"; ?>>
                    <?php echo $c['course_name']; ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if ($selected_course) { ?>
        <!-- Step 2: Select Student -->
        <form method="GET">
            <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
            <label>Select Student:</label>
            <select name="student_id" onchange="this.form.submit()">
                <option value="">-- Select Student --</option>
                <?php mysqli_data_seek($students, 0); while($s = mysqli_fetch_assoc($students)) { ?>
                    <option value="<?php echo $s['id']; ?>" 
                        <?php if($selected_student == $s['id']) echo "selected"; ?>>
                        <?php echo $s['name'] . " (" . $s['roll_no'] . ")"; ?>
                    </option>
                <?php } ?>
            </select>
        </form>
    <?php } ?>

    <?php if ($selected_course && $selected_student) { ?>
        <!-- Step 3: Show COs and allow input -->
        <form method="POST">
            <table border="1" cellpadding="8">
                <tr>
                    <th>CO Code</th>
                    <th>Description</th>
                    <th>Score</th>
                    <th>Max Score</th>
                </tr>
                <?php while($co = mysqli_fetch_assoc($cos)) { 
                    // fetch existing data if available
                    $check = mysqli_query($conn, "SELECT * FROM student_assessments WHERE student_id='$selected_student' AND co_id='".$co['id']."'");
                    $existing = mysqli_fetch_assoc($check);
                ?>
                <tr>
                    <td><?php echo $co['co_code']; ?></td>
                    <td><?php echo $co['description']; ?></td>
                    <td><input type="number" name="co[<?php echo $co['id']; ?>][score]" value="<?php echo $existing['score'] ?? ''; ?>"></td>
                    <td><input type="number" name="co[<?php echo $co['id']; ?>][max]" value="<?php echo $existing['max_score'] ?? ''; ?>"></td>
                </tr>
                <?php } ?>
            </table>
            <br>
            <button type="submit">Save Data</button>
        </form>
    <?php } ?>

    <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
</body>
</html>
