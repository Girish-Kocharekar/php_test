<?php
include 'db.php';

// Fetch all POs
$poQuery = $conn->query("SELECT * FROM program_outcomes");
$pos = [];
while ($row = $poQuery->fetch_assoc()) {
    $pos[$row['id']] = [
        'po_code' => $row['po_code'],
        'description' => $row['description'],
        'attainment' => 0,
        'count' => 0
    ];
}

// Fetch CO–PO mappings
$mappingQuery = $conn->query("SELECT cpm.co_id, cpm.po_id, cpm.weightage, co.co_code 
    FROM co_po_mapping cpm
    JOIN course_outcomes co ON co.id = cpm.co_id");

$mappings = [];
while ($map = $mappingQuery->fetch_assoc()) {
    $mappings[] = $map;
}

// Fetch student assessment scores
$scoreQuery = $conn->query("SELECT co_id, AVG(score/max_score*100) as avg_score 
    FROM student_assessments 
    GROUP BY co_id");

$co_attainments = [];
while ($row = $scoreQuery->fetch_assoc()) {
    $co_attainments[$row['co_id']] = $row['avg_score']; // percentage attainment
}

// Calculate PO attainment (average of mapped COs)
foreach ($mappings as $map) {
    $co_id = $map['co_id'];
    $po_id = $map['po_id'];
    $weight = $map['weightage'];

    if (isset($co_attainments[$co_id])) {
        $pos[$po_id]['attainment'] += $co_attainments[$co_id] * $weight;
        $pos[$po_id]['count'] += $weight;
    }
}

// Final PO attainment values
$po_codes = [];
$po_values = [];
foreach ($pos as $po) {
    $po_codes[] = $po['po_code'];
    $attain = ($po['count'] > 0) ? round($po['attainment'] / $po['count'], 2) : 0;
    $po_values[] = $attain;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PO Attainment Report</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">

<h2 class="mb-4">Program Outcome (PO) Attainment Report</h2>

<div class="card p-3 mb-4">
  <canvas id="poChart"></canvas>
</div>

<script>
  const ctx = document.getElementById('poChart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($po_codes); ?>,
      datasets: [{
        label: 'PO Attainment (%)',
        data: <?php echo json_encode($po_values); ?>,
        borderWidth: 1,
        backgroundColor: 'rgba(54, 162, 235, 0.6)'
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          max: 100
        }
      }
    }
  });
</script>

<!-- Show Table -->
<table class="table table-bordered mt-4">
  <thead class="table-dark">
    <tr>
      <th>PO Code</th>
      <th>Description</th>
      <th>Attainment (%)</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($pos as $po): 
      $attain = ($po['count'] > 0) ? round($po['attainment'] / $po['count'], 2) : 0;
    ?>
      <tr>
        <td><?= $po['po_code'] ?></td>
        <td><?= $po['description'] ?></td>
        <td><?= $attain ?>%</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
