<?php
include 'db.php'; // database connection

// Fetch courses
$courses = $conn->query("SELECT * FROM courses");

// When form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $co_ids = $_POST['co_id'];
    $scores = $_POST['score'];
    $max_scores = $_POST['max_score'];

    for ($i = 0; $i < count($co_ids); $i++) {
        $co_id = $co_ids[$i];
        $score = $scores[$i];
        $max_score = $max_scores[$i];

        $conn->query("INSERT INTO student_assessments (student_id, co_id, score, max_score) 
                      VALUES ('$student_id', '$co_id', '$score', '$max_score')");
    }
    echo "<div class='alert alert-success'>Marks added successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Add Marks</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<h2 class="mb-4">Admin: Enter Student Marks</h2>

<form method="POST">
  <!-- Select Course -->
  <div class="mb-3">
    <label class="form-label">Select Course</label>
    <select class="form-control" name="course_id" id="courseSelect" onchange="this.form.submit()">
      <option value="">-- Select --</option>
      <?php while ($row = $courses->fetch_assoc()): ?>
        <option value="<?= $row['id'] ?>" <?= (isset($_POST['course_id']) && $_POST['course_id'] == $row['id']) ? 'selected' : '' ?>>
          <?= $row['course_name'] ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>
</form>

<?php
if (isset($_POST['course_id']) && $_POST['course_id'] != '') {
    $course_id = $_POST['course_id'];

    // Fetch students
    $students = $conn->query("SELECT * FROM students");

    // Fetch COs for this course
    $cos = $conn->query("SELECT * FROM course_outcomes WHERE course_id=$course_id");
?>
<form method="POST">
  <input type="hidden" name="course_id" value="<?= $course_id ?>">

  <!-- Select Student -->
  <div class="mb-3">
    <label class="form-label">Select Student</label>
    <select class="form-control" name="student_id" required>
      <?php while ($s = $students->fetch_assoc()): ?>
        <option value="<?= $s['id'] ?>"><?= $s['name'] ?> (<?= $s['roll_no'] ?>)</option>
      <?php endwhile; ?>
    </select>
  </div>

  <!-- Enter CO Marks -->
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>CO Code</th>
        <th>Description</th>
        <th>Score</th>
        <th>Max Score</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($co = $cos->fetch_assoc()): ?>
      <tr>
        <td><?= $co['co_code'] ?></td>
        <td><?= $co['description'] ?></td>
        <td>
          <input type="hidden" name="co_id[]" value="<?= $co['id'] ?>">
          <input type="number" name="score[]" class="form-control" required>
        </td>
        <td>
          <input type="number" name="max_score[]" class="form-control" value="20" required>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <button type="submit" class="btn btn-success">Save Marks</button>
</form>
<?php } ?>

</body>
</html>
