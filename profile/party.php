<?php 
require_once('../config/conn.php');
require_once('../config/auth.php');

$partyQuery = "SELECT * FROM parties WHERE user_id = ?";
$stmt = $conn->prepare($partyQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$parties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../frontend/head.php'; ?>
<style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2c3e50;
        --accent-color: #00c04b;
        --text-color: #ecf0f1;
    }
    body.party-body {
        background-color: var(--secondary-color);
        color: var(--text-color);
    }
    .header {
        background-color: var(--primary-color);
        padding: 20px;
        border-radius: 5px;
        text-align: center;
        margin-bottom: 20px;
    }
    .card {
        background-color: var(--primary-color);
        color: var(--text-color);
        margin-bottom: 20px;
    }
    .progress-bar {
        background-color: var(--accent-color);
    }
</style>
<body class="party-body">
    <div class="container">
        <div class="header">
            <h1 class="display-4">Welcome to your party, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
        <div class="row">
            <?php if (count($parties) > 0) { ?>
                <?php foreach ($parties as $party) { 
                    for ($i = 1; $i <= 6; $i++) {
                        $pokemonId = $party['pokemon_' . $i];
                        if ($pokemonId) {
                            $pokemonQuery = "SELECT * FROM user_pokemons WHERE id = ?";
                            $stmt = $conn->prepare($pokemonQuery);
                            $stmt->bind_param("i", $pokemonId);
                            $stmt->execute();
                            $pokemon = $stmt->get_result()->fetch_assoc();
                            $stmt->close();

                            $hpPercentage = ($pokemon['current_hp'] / $pokemon['max_hp']) * 100;
                            $progressBarClass = 'bg-success';
                            if ($hpPercentage <= 25) {
                                $progressBarClass = 'bg-danger';
                            } elseif ($hpPercentage <= 50) {
                                $progressBarClass = 'bg-warning';
                            }
                            ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $pokemonId ?> - <?php echo htmlspecialchars($pokemon['nickname'] ? $pokemon['nickname'] : $pokemon['name']); ?></h5>
                                        <p class="card-text">Level: <?php echo htmlspecialchars($pokemon['level']); ?></p>
                                        <p class="card-text">HP: <?php echo htmlspecialchars($pokemon['current_hp']); ?></p>
                                        <div class="progress">
                                            <div class="progress-bar <?php echo $progressBarClass; ?>" role="progressbar" style="width: <?php echo $hpPercentage; ?>%;" aria-valuenow="<?php echo $pokemon['current_hp']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $pokemon['max_hp']; ?>"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } ?>
            <?php } else { ?>
                <p>You have no pokemons in your party.</p>
            <?php } ?>
        </div>
    </div>          
</body>
</html>
