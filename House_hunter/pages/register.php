<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../config/database.php';
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $user_type = $_POST['user_type'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    
    if($stmt->rowCount() > 0) {
        $error = "Email already exists!";
    } else {
        $query = "INSERT INTO users (name, email, password, phone, user_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$name, $email, $password, $phone, $user_type])) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<style>
    body {
        background: url(../h2.jpg);
        background-size:cover;
        background-position:center;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
        color:black;
    }
    section{
         background: rgba(0, 0, 0, 0.47);
    }
    
    .container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
    }
    
    .glass-container {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
        width: 1100px;
        max-width: 90%;
        position: relative;
    }
    
    .glass-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        z-index: -1;
        border-radius: 20px;
    }
    
    .glass-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(166, 8, 8, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .glass-header {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: white;
        border: none;
        padding: 2rem 1rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .glass-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.8rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .glass-body {
        padding: 2.5rem;
        background: rgba(255, 255, 255, 0.05);
    }
    
    .glass-form-control {
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        backdrop-filter: blur(10px);
    }
    
    .glass-form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .glass-form-control:focus {
        border-color: rgba(255, 255, 255, 0.6);
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        color: white;
    }
    
    .glass-form-select {
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        backdrop-filter: blur(10px);
    }
    
    .glass-form-select:focus {
        border-color: rgba(255, 255, 255, 0.6);
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.2);
        background: rgba(173, 0, 0, 0.62);
        transform: translateY(-2px);
        color: white;
    }
    
    .glass-btn {
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
        padding: 14px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-top: 1rem;
        color: white;
        backdrop-filter: blur(10px);
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .glass-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        background: linear-gradient(135deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.15) 100%);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: white;
    }
    
    .glass-alert {
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 15px;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }
    
    .glass-alert-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #f8d7da;
        border-color: rgba(220, 53, 69, 0.3);
    }
    
    .glass-text-center a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .glass-text-center a:hover {
        color: white;
        text-decoration: underline;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .glass-form-label {
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 8px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    .glass-container {
        animation: float 6s ease-in-out infinite;
    }
    
    .small-footer {
        background: #1a1a1a !important;
        padding: 15px 0 !important;
        margin-top: auto;
    }
    
    .small-footer .container {
        flex: none !important;
        display: block !important;
    }
    
    .small-footer .row {
        margin: 0;
    }
    
    .small-footer h5 {
        font-size: 1rem;
        margin-bottom: 8px;
    }
    
    .small-footer p, 
    .small-footer li {
        font-size: 0.85rem;
        margin-bottom: 5px;
    }
    
    .small-footer .py-4 {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
</style>
<section>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card glass-container">
                <div class="card-header glass-header">
                    <h4 class="text-center" style="color:black">Register <br> HOMEBASE KENYA</h4>
                </div>
                <div class="card-body glass-body">
                    <?php if(isset($error)): ?>
                        <div class="alert glass-alert glass-alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="glass-form-label">Full Name</label>
                            <input type="text" name="name" class="form-control glass-form-control" required placeholder="Enter your full name">
                        </div>
                        <div class="mb-3">
                            <label class="glass-form-label">Email</label>
                            <input type="email" name="email" class="form-control glass-form-control" required placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <label class="glass-form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control glass-form-control" required placeholder="Enter your phone number">
                        </div>
                        <div class="mb-3">
                            <label class="glass-form-label">Password</label>
                            <input type="password" name="password" class="form-control glass-form-control" required placeholder="Create a password">
                        </div>
                        <div class="mb-3">
                            <label class="glass-form-label">I am a:</label>
                            <select name="user_type" class="form-select glass-form-select" required>
                                <option value="tenant">House Hunter</option>
                                <option value="landlord">property owner</option>
                            </select>
                        </div>
                        <button type="submit" class="btn glass-btn w-100">Register</button>
                    </form>
                    <div class="text-center mt-3 glass-text-center">
                        <a href="login.php">Already have an account? Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    </section>

<footer class="small-footer bg-dark text-light">
    <div class="container py-2">
        <div class="row">
            <div class="col-md-4">
                <h5>HomeBase Kenya</h5>
                <p>Your trusted partner in finding the perfect rental home across Kenya.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="../index.php" class="text-light">Home</a></li>
                    <li><a href="../index.php?page=properties" class="text-light">Properties</a></li>
                    <li><a href="login.php" class="text-light">Login</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Info</h5>
                <p><i class="fas fa-phone"></i> 0715425497</p>
                <p><i class="fas fa-envelope"></i> lindahchebitok@gmail.com</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>