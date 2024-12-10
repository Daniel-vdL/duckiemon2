<?php 
require_once 'config/conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once 'frontend/head.php'; ?>
    <style>
        .home {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="home">
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