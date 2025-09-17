<?php
include 'db.php';

// Fetch courses
$courses = $conn->query("SELECT * FROM courses");

// Handle mapping save
if (isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    foreach ($_POST['co_id'] as $co_id) {
        foreach ($_POST['po_id'][$co_id] as $po_id => $weightage) {
            if ($weightage > 0) {
                $conn->query("INSERT INTO co_po_mapping (co_id, po_id, weightage)
                              VALUES ('$co_id', '$po_id', '$weightage')");
            }
        }
    }
    echo "<div class='alert alert-success'>Mapping Saved Successfully!</div>";
}

// If a course is selected, get its COs + all POs
$cos = $pos = [];
if (isset($_POST['course_id']) && $_POST['course_id'] != '') {
    $course_id = $_POST['course_id'];
    $cos = $conn->query("SELECT * FROM course_outcomes WHERE course_id=$course_id");
    $pos = $conn->query("SELECT * FROM program_outcomes");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CO–PO Mapping</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">

<h2 class="mb-4">Map COs to POs</h2>

<form method="POST">
  <div class="mb-3">
    <label class="form-label">Select Course</label>
    <select class="form-control" name="course_id" onchange="this.form.submit()">
      <option value="">-- Select --</option>
      <?php while ($row = $courses->fetch_assoc()): ?>
        <option value="<?= $row['id'] ?>" <?= (isset($_POST['course_id']) && $_POST['course_id']==$row['id'])?'selected':'' ?>>
          <?= $row['course_name'] ?>
        </option>
      <?php endwhile; ?>
    </select>
  </div>
</form>

<?php if (!empty($cos) && !empty($pos)) { ?>
<form method="POST">
  <input type="hidden" name="course_id" value="<?= $course_id ?>">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>CO</th>
        <?php while ($p = $pos->fetch_assoc()): ?>
          <th><?= $p['po_code'] ?></th>
        <?php endwhile; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($c = $cos->fetch_assoc()): ?>
        <tr>
          <td><?= $c['co_code'] ?> - <?= $c['description'] ?></td>
          <?php 
          $pos2 = $conn->query("SELECT * FROM program_outcomes"); 
          while ($p = $pos2->fetch_assoc()): ?>
            <td>
              <input type="number" name="po_id[<?= $c['id'] ?>][<?= $p['id'] ?>]" class="form-control" min="0" max="3" value="0">
            </td>
          <?php endwhile; ?>
        </tr>
        <input type="hidden" name="co_id[]" value="<?= $c['id'] ?>">
      <?php endwhile; ?>
    </tbody>
  </table>
  <button type="submit" class="btn btn-primary">Save Mapping</button>
</form>
<?php } ?>

</body>
</html>
