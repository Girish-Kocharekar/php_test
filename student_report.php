<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : null;

if (!$course_id) {
    echo "Please select a course from Dashboard first.";
    exit();
}

$query = "
    SELECT co.co_code, sa.score, sa.max_score
    FROM student_assessments sa
    JOIN course_outcomes co ON sa.co_id = co.id
    WHERE sa.student_id='$student_id' AND co.course_id='$course_id'
";
$result = mysqli_query($conn, $query);

$labels = [];
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['co_code'];
    $data[] = round(($row['score'] / $row['max_score']) * 100, 2);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CO Attainment Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Graphical CO Attainment Report</h2>
    <canvas id="coChart" width="600" height="400"></canvas>

    <script>
        const ctx = document.getElementById('coChart').getContext('2d');
        const coChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Attainment %',
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>

    <br>
    <a href="student_dashboard.php">⬅ Back to Dashboard</a>
</body>
</html>
