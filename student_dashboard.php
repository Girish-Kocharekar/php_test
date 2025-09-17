<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student details
$studentQuery = "SELECT * FROM students WHERE id='$student_id'";
$studentResult = mysqli_query($conn, $studentQuery);
$student = mysqli_fetch_assoc($studentResult);

// Get all courses where student has assessments
$courseQuery = "
    SELECT DISTINCT c.id, c.course_name
    FROM student_assessments sa
    JOIN course_outcomes co ON sa.co_id = co.id
    JOIN courses c ON co.course_id = c.id
    WHERE sa.student_id='$student_id'
";
$courses = mysqli_query($conn, $courseQuery);

// Check if a course is selected
$selected_course = isset($_GET['course_id']) ? $_GET['course_id'] : null;

$assessments = [];
if ($selected_course) {
    $assessmentQuery = "
        SELECT co.co_code, co.description, sa.score, sa.max_score
        FROM student_assessments sa
        JOIN course_outcomes co ON sa.co_id = co.id
        WHERE sa.student_id='$student_id' AND co.course_id='$selected_course'
    ";
    $assessments = mysqli_query($conn, $assessmentQuery);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $student['name']; ?> (<?php echo $student['roll_no']; ?>)</h2>

    <h3>Select Course</h3>
    <form method="GET">
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

    <?php if ($selected_course && mysqli_num_rows($assessments) > 0) { ?>
        <h3>CO Attainment for Course</h3>
        <table border="1" cellpadding="8">
            <tr>
                <th>CO Code</th>
                <th>Description</th>
                <th>Score</th>
                <th>Max Score</th>
                <th>Attainment %</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($assessments)) { 
                $attainment = ($row['score'] / $row['max_score']) * 100;
            ?>
            <tr>
                <td><?php echo $row['co_code']; ?></td>
                <td><?php echo $row['description']; ?></td>
                <td><?php echo $row['score']; ?></td>
                <td><?php echo $row['max_score']; ?></td>
                <td><?php echo round($attainment,2); ?>%</td>
            </tr>
            <?php } ?>
        </table>
        <br>
        <a href="student_report.php?course_id=<?php echo $selected_course; ?>">View Graphical Report</a>
    <?php } ?>
</body>
</html>
