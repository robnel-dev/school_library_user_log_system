<?php
require_once '../inc/db.php';
require_once '../inc/config.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$student = null;
$active_visit = null;

// Helper function: sanitize string
function clean_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Helper function: validate inputs
function validate_student_no($student_no) {
    // Must be exactly 8 characters
    // Allowed: 8 digits (e.g., 21100058) OR 3 digits + 1 uppercase letter + 4 digits (e.g., 251S0000)
    return preg_match('/^([0-9]{8}|[0-9]{3}[A-Z][0-9]{4})$/', $student_no);
}

function validate_name($name) {
    return preg_match("/^[a-zA-Z\s\.\-']+$/", $name); // allow letters, space, . - '
}
function validate_year_course($yc) {
    return preg_match("/^[a-zA-Z0-9\s\.\-\/]+$/", $yc);
}
function validate_contact($contact) {
    // Allow empty OR PH mobile format (starts with 09 and 11 digits total)
    return empty($contact) || preg_match('/^(09)[0-9]{9}$/', $contact);
}


// =====================
// Check if student number is provided
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_no']) && !isset($_POST['register'])) {
    $student_no = clean_input($_POST['student_no']);

    if (!validate_student_no($student_no)) {
        $message = "Invalid student number format.";
    } else {
        // Check if student exists
        $query = "SELECT id, student_no, full_name, year_course FROM students WHERE student_no = :student_no";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_no', $student_no);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check for active visit (time_in without time_out)
            $query = "SELECT id, time_in, purpose FROM visits 
                      WHERE student_id = :student_id AND time_out IS NULL 
                      ORDER BY time_in DESC LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':student_id', $student['id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $active_visit = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
}

// =====================
// Handle time in action
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['time_in'])) {
    $student_id = intval($_POST['student_id']);
    $purpose = clean_input($_POST['purpose']);
    $notes = isset($_POST['notes']) ? clean_input($_POST['notes']) : '';

    if (empty($purpose)) {
        $message = "Purpose is required.";
    } else {
        $query = "INSERT INTO visits (student_id, date, time_in, purpose, notes) 
                  VALUES (:student_id, CURDATE(), NOW(), :purpose, :notes)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $stmt->bindParam(':purpose', $purpose);
        $stmt->bindParam(':notes', $notes);
        
        if ($stmt->execute()) {
            $message = "Time In recorded - have a good study session!";
            $student = [
                'id' => $student_id,
                'student_no' => clean_input($_POST['student_no']), 
                'full_name' => clean_input($_POST['full_name']), 
                'year_course' => clean_input($_POST['year_course'])
            ];
            $active_visit = ['id' => $db->lastInsertId(), 'time_in' => date('Y-m-d H:i:s'), 'purpose' => $purpose];
        } else {
            $message = "Error recording Time In. Please try again.";
        }
    }
}

// =====================
// Handle time out action
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['time_out'])) {
    $visit_id = intval($_POST['visit_id']);
    
    $query = "UPDATE visits SET time_out = NOW() WHERE id = :visit_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':visit_id', $visit_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $message = "Time Out recorded. Thank you for visiting!";
        $student = [
            'id' => intval($_POST['student_id'] ?? 0),
            'student_no' => clean_input($_POST['student_no']),
            'full_name' => clean_input($_POST['full_name']),
            'year_course' => clean_input($_POST['year_course'])
        ];
    } else {
        $message = "Error recording Time Out. Please try again.";
    }
}

// =====================
// Handle registration
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $student_no = clean_input($_POST['student_no']);
    $full_name = clean_input($_POST['full_name']);
    $year_course = clean_input($_POST['year_course']);
    $contact_no = clean_input($_POST['contact_no']);
    
    if (!validate_student_no($student_no)) {
        $message = "Invalid student number.";
    } elseif (!validate_name($full_name)) {
        $message = "Invalid full name. Only letters, spaces, . - ' allowed.";
    } elseif (!validate_year_course($year_course)) {
        $message = "Invalid year & course format.";
    } elseif (!validate_contact($contact_no)) {
        $message = "Invalid contact number. Must be 10–15 digits.";
    } else {
        $query = "INSERT INTO students (student_no, full_name, year_course, contact_no) 
                  VALUES (:student_no, :full_name, :year_course, :contact_no)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_no', $student_no);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':year_course', $year_course);
        $stmt->bindParam(':contact_no', $contact_no);
        
        if ($stmt->execute()) {
            $message = "Registration successful! You can now check in.";
            $student = [
                'id' => $db->lastInsertId(), 
                'student_no' => $student_no, 
                'full_name' => $full_name, 
                'year_course' => $year_course
            ];
        } else {
            $message = "Error registering. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Check-In - SPCBA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>SAN PEDRO COLLEGE OF BUSINESS ADMINISTRATION</h1>
            <h2>Library Users Statistics</h2>

            <nav>
                <a href="../staff/login.php" class="button">Staff Login</a>
            </nav>
        </header>
        
        <main>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (!$student): ?>
                <!-- Student Number Input Form -->
                <form method="POST" class="card">
                    <h3>Enter Your Student Number</h3>
                    <div class="form-group">
                        <label for="student_no">Student Number:</label>
                       <input type="text" id="student_no" name="student_no" 
                        required autofocus maxlength="8" minlength="8"
                        pattern="([0-9]{8}|[0-9]{3}[A-Z][0-9]{4})"
                        title="Input Valid Student Number!">

                    </div>
                    <button type="submit">Check In/Out</button>
                </form>
                
            <?php elseif ($active_visit): ?>
                <!-- Time Out Form -->
                <form method="POST" class="card">
                    <h3>Time Out</h3>
                    <p>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</p>
                    <p>Course: <?php echo htmlspecialchars($student['year_course']); ?></p>
                    <p>You timed in at <?php echo date('h:i A', strtotime($active_visit['time_in'])); ?> for <?php echo htmlspecialchars($active_visit['purpose']); ?>.</p>
                    
                    <input type="hidden" name="visit_id" value="<?php echo intval($active_visit['id']); ?>">
                    <input type="hidden" name="student_id" value="<?php echo intval($student['id']); ?>">
                    <input type="hidden" name="student_no" value="<?php echo htmlspecialchars($student['student_no']); ?>">
                    <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>">
                    <input type="hidden" name="year_course" value="<?php echo htmlspecialchars($student['year_course']); ?>">
                    
                    <button type="submit" name="time_out">Time Out</button>
                    <a href="index.php" class="button">Back</a>
                </form>
                
            <?php else: ?>
                <!-- Time In Form -->
                <form method="POST" class="card">
                    <h3>Time In</h3>
                    <p>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</p>
                    <p>Course: <?php echo htmlspecialchars($student['year_course']); ?></p>
                    
                    <div class="form-group">
                        <label for="purpose">Purpose of Visit:</label>
                        <select id="purpose" name="purpose" required>
                            <option value="">Select Purpose</option>
                            <option value="Study">Study</option>
                            <option value="Research">Research</option>
                            <option value="Borrowing">Borrowing</option>
                            <option value="Returning">Returning</option>
                            <option value="Group Work">Group Work</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes (optional):</label>
                        <textarea id="notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <input type="hidden" name="student_id" value="<?php echo intval($student['id']); ?>">
                    <input type="hidden" name="student_no" value="<?php echo htmlspecialchars($student['student_no']); ?>">
                    <input type="hidden" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>">
                    <input type="hidden" name="year_course" value="<?php echo htmlspecialchars($student['year_course']); ?>">
                    
                    <button type="submit" name="time_in">Time In</button>
                    <a href="index.php" class="button">Back</a>
                </form>
            <?php endif; ?>
            
            <?php if (isset($_POST['student_no']) && !$student && !isset($_POST['register'])): ?>
                <!-- Registration Form for New Students -->
                <form method="POST" class="card">
                    <h3>First-Time Registration</h3>
                    <p>Student number not found. Please register your details.</p>
                    
                    <div class="form-group">
                        <label for="reg_student_no">Student Number:</label>
                        <input type="text" id="reg_student_no" name="student_no" value="<?php echo htmlspecialchars($_POST['student_no']); ?>" required readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name:</label>
                        <input type="text" id="full_name" name="full_name" required pattern="[a-zA-Z\s\.\-']+">
                    </div>
                    
                    <div class="form-group">
                        <label for="year_course">Year & Course / Strand:</label>
                        <input type="text" id="year_course" name="year_course" placeholder="e.g., Gr. 11 - STEM / 1st Yr. - BSA" required pattern="[a-zA-Z0-9\s\.\-\/]+">
                    </div>
                    
                   <div class="form-group">
                        <label for="contact_no">Contact Number:</label>
                        <input type="text" id="contact_no" name="contact_no" 
                            pattern="^09[0-9]{9}$" 
                            title="Enter a valid PH mobile number (e.g., 09123456789)"
                            maxlength="11">
                    </div>

                    
                    <button type="submit" name="register">Register</button>
                    <a href="index.php" class="button">Cancel</a>
                </form>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> San Pedro College of Business Administration</p>
        </footer>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
