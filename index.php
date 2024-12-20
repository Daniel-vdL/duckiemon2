<?php 
require_once('config/conn.php');
require_once('config/auth.php');

if (isset($_SESSION['role']) && $_SESSION['role'] == 1) {
    $admin_button = '<a href="admin" class="btn btn-primary me-2"><i class="fas fa-user-shield"></i> Admin</a>';
} else {
    $admin_button = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once 'frontend/head.php'; ?>
<link rel="stylesheet" href="<?php echo $base_url; ?>frontend/css/home.css">
<body class="home">
    <div class="auth-buttons">
            <?php echo $admin_button; ?>
            <a href="login/logout.php" class="btn btn-primary"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
    <div class="container">
        <div class="header">
            <h1 class="display-4">Welcome to DuckieMon!</h1>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                    <h5 class="card-title">Duckiedex</h5>
                    <p class="card-text">Explore the Duckiedex to learn more about all the pokemon.</p>
                    <a href="duckiedex" class="btn btn-primary">Go</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                    <h5 class="card-title">Catch a Pokemon</h5>
                    <p class="card-text">Try your luck and catch a pokemon in the wild.</p>
                    <a href="duckiecatch" class="btn btn-primary">Go</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>