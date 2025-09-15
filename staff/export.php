
<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/csrf.php';

Auth::requireLogin();
Auth::checkTimeout();

$database = new Database();
$db = $database->getConnection();

// Default date range (current month)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

// Get filtered visits
$query = "SELECT v.id, s.student_no, s.full_name, s.year_course, v.date, v.time_in, v.time_out, v.purpose, v.notes 
          FROM visits v 
          JOIN students s ON v.student_id = s.id 
          WHERE v.date BETWEEN :start_date AND :end_date 
          ORDER BY v.date DESC, v.time_in DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$visits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle export
if (isset($_GET['export']) && $_GET['export'] == '1') {
    if ($format == 'csv') {
        // CSV Export
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=library_visits_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Date', 'Student No', 'Name', 'Year/Course', 'Time In', 'Time Out', 'Duration (min)', 'Purpose', 'Notes'));
        
        foreach ($visits as $visit) {
            $duration = '';
            if ($visit['time_out']) {
                $time_in = new DateTime($visit['time_in']);
                $time_out = new DateTime($visit['time_out']);
                $interval = $time_in->diff($time_out);
                $duration = $interval->h * 60 + $interval->i;
            }
            
            fputcsv($output, array(
                $visit['date'],
                $visit['student_no'],
                $visit['full_name'],
                $visit['year_course'],
                date('h:i A', strtotime($visit['time_in'])),
                $visit['time_out'] ? date('h:i A', strtotime($visit['time_out'])) : 'Active',
                $duration,
                $visit['purpose'],
                $visit['notes']
            ));
        }
        
        fclose($output);
        exit();
    } elseif ($format == 'xlsx') {
        // Excel Export - using simple HTML table as fallback
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=library_visits_' . date('Y-m-d') . '.xls');
        
        echo '<table border="1">';
        echo '<tr><th>Date</th><th>Student No</th><th>Name</th><th>Year/Course</th><th>Time In</th><th>Time Out</th><th>Duration (min)</th><th>Purpose</th><th>Notes</th></tr>';
        
        foreach ($visits as $visit) {
            $duration = '';
            if ($visit['time_out']) {
                $time_in = new DateTime($visit['time_in']);
                $time_out = new DateTime($visit['time_out']);
                $interval = $time_in->diff($time_out);
                $duration = $interval->h * 60 + $interval->i;
            }
            
            echo '<tr>';
            echo '<td>' . $visit['date'] . '</td>';
            echo '<td>' . $visit['student_no'] . '</td>';
            echo '<td>' . $visit['full_name'] . '</td>';
            echo '<td>' . $visit['year_course'] . '</td>';
            echo '<td>' . date('h:i A', strtotime($visit['time_in'])) . '</td>';
            echo '<td>' . ($visit['time_out'] ? date('h:i A', strtotime($visit['time_out'])) : 'Active') . '</td>';
            echo '<td>' . $duration . '</td>';
            echo '<td>' . $visit['purpose'] . '</td>';
            echo '<td>' . $visit['notes'] . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit();
    }
}

// Get date range statistics
$query = "SELECT 
            COUNT(*) as total_visits,
            COUNT(DISTINCT student_id) as unique_students,
            AVG(TIMESTAMPDIFF(MINUTE, time_in, time_out)) as avg_duration
          FROM visits 
          WHERE date BETWEEN :start_date AND :end_date AND time_out IS NOT NULL";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - Library System</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>SAN PEDRO COLLEGE OF BUSINESS ADMINISTRATION</h1>
            <h2>Export Visit Data</h2>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="visits.php">Visits</a>
                <a href="export.php" class="active">Export</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['staff_fullname']); ?>)</a>
            </nav>
        </header>
        
        <main>
            <div class="card">
                <h3>Export Options</h3>
                <form method="GET" action="export.php">
                    <div class="filters">
                        <div class="filter-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>" required>
                        </div>
                        
                        <div class="filter-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>" required>
                        </div>
                        
                        <div class="filter-group">
                            <label for="format">Format:</label>
                            <select id="format" name="format">
                                <option value="csv" <?php echo $format == 'csv' ? 'selected' : ''; ?>>CSV</option>
                                <option value="xlsx" <?php echo $format == 'xlsx' ? 'selected' : ''; ?>>Excel (XLSX)</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="filter" value="1">Apply Filters</button>
                    <button type="submit" name="export" value="1" class="secondary">Export Data</button>
                </form>
            </div>
            
            <?php if (count($visits) > 0): ?>
            <div class="card">
                <h3>Data Preview (<?php echo count($visits); ?> records)</h3>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Visits</h3>
                        <div class="stat-number"><?php echo $stats['total_visits']; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Unique Students</h3>
                        <div class="stat-number"><?php echo $stats['unique_students']; ?></div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Avg. Duration (min)</h3>
                        <div class="stat-number"><?php echo round($stats['avg_duration'], 1); ?></div>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student No</th>
                            <th>Name</th>
                            <th>Year/Course</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Duration</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($visits, 0, 10) as $visit): ?>
                        <tr>
                            <td><?php echo $visit['date']; ?></td>
                            <td><?php echo htmlspecialchars($visit['student_no']); ?></td>
                            <td><?php echo htmlspecialchars($visit['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($visit['year_course']); ?></td>
                            <td><?php echo date('h:i A', strtotime($visit['time_in'])); ?></td>
                            <td><?php echo $visit['time_out'] ? date('h:i A', strtotime($visit['time_out'])) : 'Active'; ?></td>
                            <td>
                                <?php if ($visit['time_out']): 
                                    $time_in = new DateTime($visit['time_in']);
                                    $time_out = new DateTime($visit['time_out']);
                                    $interval = $time_in->diff($time_out);
                                    echo $interval->h * 60 + $interval->i . ' min';
                                endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($visit['purpose']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (count($visits) > 10): ?>
                <p>Showing first 10 of <?php echo count($visits); ?> records. Export to see all.</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <p>No visits found for the selected date range.</p>
            </div>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> San Pedro College of Business Administration</p>
        </footer>
    </div>
    
    <script>
        // Set default date inputs to current month if empty
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            if (!startDate.value) {
                const firstDay = new Date();
                firstDay.setDate(1);
                startDate.value = firstDay.toISOString().split('T')[0];
            }
            
            if (!endDate.value) {
                const lastDay = new Date();
                lastDay.setMonth(lastDay.getMonth() + 1);
                lastDay.setDate(0);
                endDate.value = lastDay.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>