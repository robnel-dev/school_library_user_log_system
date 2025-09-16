<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/csrf.php';

Auth::requireLogin();
Auth::checkTimeout();

$database = new Database();
$db = $database->getConnection();

// Get today's statistics
$today = date('Y-m-d');

// Total visits today
$query = "SELECT COUNT(*) as total FROM visits WHERE date = :today";
$stmt = $db->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->execute();
$total_visits = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Unique students today
$query = "SELECT COUNT(DISTINCT student_id) as unique_students FROM visits WHERE date = :today";
$stmt = $db->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->execute();
$unique_students = $stmt->fetch(PDO::FETCH_ASSOC)['unique_students'];

// Average visit duration today (in minutes)
$query = "SELECT AVG(TIMESTAMPDIFF(MINUTE, time_in, time_out)) as avg_duration 
          FROM visits WHERE date = :today AND time_out IS NOT NULL";
$stmt = $db->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->execute();
$avg_result = $stmt->fetch(PDO::FETCH_ASSOC);
$avg_duration = isset($avg_result['avg_duration']) && $avg_result['avg_duration'] !== null
    ? round($avg_result['avg_duration'], 1)
    : 0;

// Current active visits
$query = "SELECT COUNT(*) as active FROM visits WHERE time_out IS NULL";
$stmt = $db->prepare($query);
$stmt->execute();
$active_visits = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

// Recent visits
$query = "SELECT v.id, s.student_no, s.full_name, s.year_course, v.date, v.time_in, v.time_out, v.purpose 
          FROM visits v 
          JOIN students s ON v.student_id = s.id 
          ORDER BY v.time_in DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library System</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <div class="container">
<header class="site-header">
    <img src="../public/assets/images/spcbaheader.png" alt="SPCBA Logo" class="logo">
    <div class="header-center">
        <h1 class="site-title">SAN PEDRO COLLEGE OF BUSINESS ADMINISTRATION</h1>
        <p class="site-subtitle">Library Staff Dashboard</p>
    </div>
    <nav class="header-nav">
        <a href="dashboard.php" class="btn btn--primary">Dashboard</a>
        <a href="export.php" class="btn btn--ghost">Export</a>
        <a href="logout.php" class="btn btn--danger">Logout</a>
    </nav>
</header>

        
       <main id="main" class="main" role="main">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Today's Visits</h3>
                    <div class="stat-number"><?php echo $total_visits; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Unique Students</h3>
                    <div class="stat-number"><?php echo $unique_students; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Avg. Duration (min)</h3>
                    <div class="stat-number"><?php echo $avg_duration; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>🟢 Active Now</h3>
                    <div class="stat-number"><?php echo $active_visits; ?></div>
                </div>
            </div>
            
            <!-- Recent Visits -->
            <div class="card">
                <h3>Recent Visits</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Student No.</th>
                                <th>Year/Course</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_visits as $visit): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($visit['date'])); ?></td>
                                <td><?php echo htmlspecialchars($visit['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($visit['student_no']); ?></td>
                                <td><?php echo htmlspecialchars($visit['year_course']); ?></td>
                                <td><?php echo date('h:i A', strtotime($visit['time_in'])); ?></td>
                                <td><?php echo $visit['time_out'] ? date('h:i A', strtotime($visit['time_out'])) : '🟢 Active'; ?></td>
                                <td><?php echo htmlspecialchars($visit['purpose']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        
        <?php include '../public/footer.php'; ?>
    </div>
</body>
</html>
