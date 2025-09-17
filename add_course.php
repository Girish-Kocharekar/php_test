<?php
include 'db.php';

// Handle course submission
if (isset($_POST['course_name'])) {
    $course_name = $_POST['course_name'];
    $conn->query("INSERT INTO courses (course_name) VALUES ('$course_name')");
    $course_id = $conn->insert_id;

    // Insert COs
    foreach ($_POST['co_code'] as $i => $co_code) {
        $desc = $_POST['description'][$i];
        if (!empty($co_code) && !empty($desc)) {
            $conn->query("INSERT INTO course_outcomes (course_id, co_code, description) 
                          VALUES ('$course_id', '$co_code', '$desc')");
        }
    }

    echo "<div class='alert alert-success'>Course & COs Added Successfully!</div>";
}

// Fetch existing courses
$courses = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Course & COs</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script>
    function addRow() {
      const table = document.getElementById("coTable");
      const row = table.insertRow();
      row.innerHTML = `
        <td><input type="text" name="co_code[]" class="form-control" placeholder="CO1"></td>
        <td><input type="text" name="description[]" class="form-control" placeholder="Outcome Description"></td>
      `;
    }
  </script>
</head>
<body class="p-4">

<h2 class="mb-4">Add New Course & COs</h2>

<form method="POST">
  <div class="mb-3">
    <label class="form-label">Course Name</label>
    <input type="text" name="course_name" class="form-control" required>
  </div>

  <h5>Course Outcomes</h5>
  <table class="table table-bordered" id="coTable">
    <tr>
      <th>CO Code</th>
      <th>Description</th>
    </tr>
    <tr>
      <td><input type="text" name="co_code[]" class="form-control" placeholder="CO1"></td>
      <td><input type="text" name="description[]" class="form-control" placeholder="Outcome Description"></td>
    </tr>
  </table>
  <button type="button" class="btn btn-secondary" onclick="addRow()">+ Add CO</button>
  <br><br>
  <button type="submit" class="btn btn-primary">Save Course</button>
</form>

<hr>
<h4>Existing Courses</h4>
<ul>
  <?php while ($c = $courses->fetch_assoc()): ?>
    <li><?= $c['course_name'] ?></li>
  <?php endwhile; ?>
</ul>

</body>
</html>
