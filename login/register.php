<?php
    include('../config/conn.php');
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../frontend/head.php'; ?>
<link rel="stylesheet" href="<?php echo $base_url; ?>frontend/css/login.css">
<body class="login-body">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title text-center">Duckiemon (register)</h2>
                        <form action="controller.php" method="POST" class="mt-3">
                            <input type="hidden" name="action" value="register"> 
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-buttons d-flex justify-content-between mt-4">
                                <a href="index.php" class="btn btn-primary">Login</a>
                                <button type="submit" name="register_button" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>