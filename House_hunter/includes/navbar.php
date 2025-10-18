<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: hsla(299, 81%, 49%, 0.811)">
    <div class="container">
        <a class="navbar-brand" href="index.php" style="font-weight:bold">
            <i class="fas fa-home"></i> HOMEBASE KENYA
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php" style="color:black">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=properties" style="color:black">Properties</a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php" style="color:black">Dashboard</a>
                    </li>
                    <?php if($_SESSION['user_type'] == 'landlord'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="add_property.php" style="color:black">Add Property</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/login.php" style="color: black; transition: all 0.3s ease;" 
                           onmouseover="this.style.color='#f7f7f7ff'" 
                           onmouseout="this.style.color='black'">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/register.php" style="color: black; transition: all 0.3s ease;" 
                           onmouseover="this.style.color='#f7f7f7ff'" 
                           onmouseout="this.style.color='black'">Register</a>
                    </li>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'landlord'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="landlord_messages.php" style="color: white; transition: all 0.3s ease;" 
                           onmouseover="this.style.color='#ffd700'" 
                           onmouseout="this.style.color='white'">
                            <i class="fas fa-envelope"></i> Messages
                            <?php
                           
                            require_once 'config/database.php';
                            $database = new Database();
                            $db = $database->getConnection();
                            
                            $unreadQuery = "SELECT COUNT(*) as unread_count 
                                           FROM contacts c 
                                           JOIN properties p ON c.property_id = p.id 
                                           WHERE p.landlord_id = ? AND c.status = 'unread'";
                            $unreadStmt = $db->prepare($unreadQuery);
                            $unreadStmt->execute([$_SESSION['user_id']]);
                            $unread_count = $unreadStmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
                            
                            if($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>