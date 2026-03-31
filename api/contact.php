<?php
/**
 * API Contact Form Handler for Vercel
 * Handles contact form submissions in serverless environment
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Fallback to form data
    $input = $_POST;
}

// Validate required fields
$required_fields = ['name', 'email', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $errors[$field] = ucfirst($field) . ' is required';
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['errors' => ['email' => 'Invalid email address']]);
    exit;
}

try {
    // Sanitize input
    $name = htmlspecialchars(strip_tags(trim($input['name'])));
    $email = htmlspecialchars(strip_tags(trim($input['email'])));
    $subject = htmlspecialchars(strip_tags(trim($input['subject'] ?? 'Contact Form Submission')));
    $message = htmlspecialchars(strip_tags(trim($input['message'])));
    
    // For Vercel, you can use email services like:
    // - Resend
    // - SendGrid
    // - AWS SES
    // - Or store in database
    
    // Option 1: Store in database
    if (isset($_ENV['DATABASE_URL'])) {
        require_once 'database.php';
        $db = getDatabaseConnection();
        
        $stmt = $db->prepare("
            INSERT INTO contact_messages (name, email, subject, message, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$name, $email, $subject, $message]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully! We will get back to you soon.'
        ]);
    } else {
        // Option 2: Send email (using Resend example)
        // You'll need to install: composer require resend/resend
        if (class_exists('Resend')) {
            $resend = new Resend($_ENV['RESEND_API_KEY']);
            
            $result = $resend->emails->send([
                'from' => 'onboarding@resend.dev',
                'to' => $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com',
                'subject' => $subject,
                'html' => "
                    <h2>New Contact Message</h2>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Subject:</strong> {$subject}</p>
                    <hr>
                    <p><strong>Message:</strong></p>
                    <p>{$message}</p>
                "
            ]);
            
            if ($result->id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Message sent successfully! We will get back to you soon.'
                ]);
            } else {
                throw new Exception('Failed to send email');
            }
        } else {
            // Fallback: Just log the message
            error_log("Contact Form: {$name} ({$email}) - {$subject}");
            
            echo json_encode([
                'success' => true,
                'message' => 'Message received! We will get back to you soon.'
            ]);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to process your message. Please try again.',
        'details' => $e->getMessage()
    ]);
}
?>
