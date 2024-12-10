<?php
require_once('../config/conn.php');
require_once('../config/auth.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'createTables':
        createAllTables($conn);
        break;

    case 'clearTables':
        clearAllTables($conn);
        break;
    
    case 'deleteTables':
        deleteAllTables($conn);
        break;
    
    case 'fillPokemons':
        fillPokemons($conn);
        break;

    case 'fillItems':
        fillItems($conn);
        break;

    case 'fillNatures':
        fillNatures($conn);
        break;

    default:
        echo "Invalid action\n";
}

$conn->close();

function fillPokemons($conn) {
    $apiUrl = "https://pokeapi.co/api/v2/pokemon?limit=721";
    $response = file_get_contents($apiUrl);
    $pokemonData = json_decode($response, true);
    $inserted = [];
    $errors = [];

    if (!tableExists($conn, 'pokemons')) {
        createPokemonsTable($conn);
    }

    foreach ($pokemonData['results'] as $pokemon) {
        $name = $pokemon['name'];
        $url = $pokemon['url'];

        if (!pokemonExists($conn, $name)) {
            $name = adjustPokemonName($name);
            $rarity = calculateRarity($url);
            $catchRate = calculateCatchRate($url);

            $pokemonDetailResponse = file_get_contents($url);
            $pokemonDetailData = json_decode($pokemonDetailResponse, true);
            $speciesUrl = $pokemonDetailData['species']['url'];
            $speciesResponse = file_get_contents($speciesUrl);
            $speciesData = json_decode($speciesResponse, true);

            $isLegendary = $speciesData['is_legendary'];
            $isMythical = $speciesData['is_mythical'];

            if (insertPokemon($conn, $name, $url, $rarity, $catchRate, $isLegendary, $isMythical)) {
                $inserted[] = $name;
            } else {
                $errors[] = "Error inserting $name: " . $conn->error;
            }
        } 
    }

    echo json_encode([
        'inserted' => $inserted,
        'errors' => $errors,
    ]);
}

function calculateCatchRate($url) {
    $pokemonDetailResponse = file_get_contents($url);
    $pokemonDetailData = json_decode($pokemonDetailResponse, true);

    $speciesUrl = $pokemonDetailData['species']['url'];
    $speciesResponse = file_get_contents($speciesUrl);
    $speciesData = json_decode($speciesResponse, true);

    return $speciesData['capture_rate'];
}

function fillItems($conn) {
    $apiUrl = "https://pokeapi.co/api/v2/item?limit=1000";
    $response = file_get_contents($apiUrl);
    $itemData = json_decode($response, true);
    $inserted = [];
    $errors = [];

    if (!tableExists($conn, 'items')) {
        createItemsTable($conn);
    }

    foreach ($itemData['results'] as $item) {
        $name = $item['name'];
        $url = $item['url'];

        if (!itemExists($conn, $name)) {
            if (insertItem($conn, $name, $url)) {
                $inserted[] = $name;
            } else {
                $errors[] = "Error inserting $name: " . $conn->error;
            }
        }
    }

    echo json_encode([
        'inserted' => $inserted,
        'errors' => $errors,
    ]);
}

function fillNatures($conn) {
    $natures = [
        ['name' => 'adamant', 'increased_stat' => 'attack', 'decreased_stat' => 'special-attack'],
        ['name' => 'bashful', 'increased_stat' => 'none', 'decreased_stat' => 'none'],
        ['name' => 'bold', 'increased_stat' => 'defense', 'decreased_stat' => 'attack'],
        ['name' => 'brave', 'increased_stat' => 'attack', 'decreased_stat' => 'speed'],
        ['name' => 'calm', 'increased_stat' => 'special-defense', 'decreased_stat' => 'attack'],
        ['name' => 'careful', 'increased_stat' => 'special-defense', 'decreased_stat' => 'special-attack'],
        ['name' => 'docile', 'increased_stat' => 'none', 'decreased_stat' => 'none'],
        ['name' => 'gentle', 'increased_stat' => 'special-defense', 'decreased_stat' => 'defense'],
        ['name' => 'hardy', 'increased_stat' => 'none', 'decreased_stat' => 'none'],
        ['name' => 'hasty', 'increased_stat' => 'speed', 'decreased_stat' => 'defense'],
        ['name' => 'impish', 'increased_stat' => 'defense', 'decreased_stat' => 'special-attack'],
        ['name' => 'jolly', 'increased_stat' => 'speed', 'decreased_stat' => 'special-attack'],
        ['name' => 'lax', 'increased_stat' => 'defense', 'decreased_stat' => 'special-defense'],
        ['name' => 'lonely', 'increased_stat' => 'attack', 'decreased_stat' => 'defense'],
        ['name' => 'mild', 'increased_stat' => 'special-attack', 'decreased_stat' => 'defense'],
        ['name' => 'modest', 'increased_stat' => 'special-attack', 'decreased_stat' => 'attack'],
        ['name' => 'naive', 'increased_stat' => 'speed', 'decreased_stat' => 'special-defense'],
        ['name' => 'naughty', 'increased_stat' => 'attack', 'decreased_stat' => 'special-defense'],
        ['name' => 'quiet', 'increased_stat' => 'special-attack', 'decreased_stat' => 'speed'],
        ['name' => 'quirky', 'increased_stat' => 'none', 'decreased_stat' => 'none'],
        ['name' => 'rash', 'increased_stat' => 'special-attack', 'decreased_stat' => 'special-defense'],
        ['name' => 'relaxed', 'increased_stat' => 'defense', 'decreased_stat' => 'speed'],
        ['name' => 'sassy', 'increased_stat' => 'special-defense', 'decreased_stat' => 'speed'],
        ['name' => 'serious', 'increased_stat' => 'none', 'decreased_stat' => 'none'],
        ['name' => 'timid', 'increased_stat' => 'speed', 'decreased_stat' => 'attack'],
    ];
    $inserted = [];
    $errors = [];

    if (!tableExists($conn, 'natures')) {
        createNaturesTable($conn);
    }

    foreach ($natures as $nature) {
        $name = $nature['name'];
        $increasedStat = $nature['increased_stat'];
        $decreasedStat = $nature['decreased_stat'];

        $stmt = $conn->prepare("SELECT id FROM natures WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO natures (name, increased_stat, decreased_stat) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $increasedStat, $decreasedStat);

            if ($stmt->execute()) {
                $inserted[] = $name;
            } else {
                $errors[] = "Error inserting $name: " . $conn->error;
            }
        } 
    }

    echo json_encode([
        'inserted' => $inserted,
        'errors' => $errors,
    ]);
}

function tableExists($conn, $tableName) {
    $tableCheckSql = "SHOW TABLES LIKE '$tableName'";
    $tableResult = $conn->query($tableCheckSql);
    return $tableResult->num_rows > 0;
}

function pokemonExists($conn, $name) {
    $name = adjustPokemonName($name);
    $stmt = $conn->prepare("SELECT id FROM pokemons WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function itemExists($conn, $name) {
    $stmt = $conn->prepare("SELECT id FROM items WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function adjustPokemonName($name) {
    if ($name == "basculin-red-striped") {
        return "basculin";
    } elseif ($name == "zygarde-50") {
        return "zygarde";
    }
    return $name;
}

function calculateRarity($url) {
    $pokemonDetailResponse = file_get_contents($url);
    $pokemonDetailData = json_decode($pokemonDetailResponse, true);

    $totalStats = array_reduce($pokemonDetailData['stats'], function ($carry, $stat) {
        return $carry + $stat['base_stat'];
    }, 0);

    $speciesUrl = $pokemonDetailData['species']['url'];
    $speciesResponse = file_get_contents($speciesUrl);
    $speciesData = json_decode($speciesResponse, true);

    $isMythical = $speciesData['is_mythical'];
    $isLegendary = $speciesData['is_legendary'];

    if ($totalStats > 600) {
        $rarity = $totalStats * 4;
    } elseif ($totalStats > 400) {
        $rarity = $totalStats * 2;
    } elseif ($totalStats <= 200) {
        $rarity = rand(10, 50);
    } else {
        $rarity = $totalStats;
    }

    if ($isLegendary) {
        $rarity *= 10;
    } elseif ($isMythical) {
        $rarity = 1000000;
    }

    return ceil($rarity);
}

function insertPokemon($conn, $name, $url, $rarity, $catchRate, $isLegendary, $isMythical) {
    $stmt = $conn->prepare("INSERT INTO pokemons (name, url, rarity, catch_rate, is_legendary, is_mythical) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiii", $name, $url, $rarity, $catchRate, $isLegendary, $isMythical);
    return $stmt->execute();
}

function insertItem($conn, $name, $url) {
    $stmt = $conn->prepare("INSERT INTO items (name, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $url);
    return $stmt->execute();
}

function createPokemonsTable($conn) {
    $createTableSql = "CREATE TABLE pokemons (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        url VARCHAR(255) NOT NULL,
        rarity INT(11) NOT NULL,
        catch_rate INT(11) NOT NULL,
        is_legendary BOOLEAN NOT NULL,
        is_mythical BOOLEAN NOT NULL
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createItemsTable($conn) {
    $createTableSql = "CREATE TABLE items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        url VARCHAR(255) NOT NULL
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createUsersTable($conn) {
    $createTableSql = "CREATE TABLE users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createNaturesTable($conn) {
    $createTableSql = "CREATE TABLE natures (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        increased_stat VARCHAR(255) NOT NULL,
        decreased_stat VARCHAR(255) NOT NULL
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createCaughtStatsTable($conn) {
    $createTableSql = "CREATE TABLE caught_stats (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        total_iv INT(11) NOT NULL,
        iv_hp INT(11) NOT NULL,
        iv_attack INT(11) NOT NULL,
        iv_defense INT(11) NOT NULL,
        iv_sp_attack INT(11) NOT NULL,
        iv_sp_defense INT(11) NOT NULL,
        iv_speed INT(11) NOT NULL,
        total_ev INT(11) DEFAULT 0,
        ev_hp INT(11) DEFAULT 0,
        ev_attack INT(11) DEFAULT 0,
        ev_defense INT(11) DEFAULT 0,
        ev_sp_attack INT(11) DEFAULT 0,
        ev_sp_defense INT(11) DEFAULT 0,
        ev_speed INT(11) DEFAULT 0
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createUserPokemonsTable($conn) {
    $createTableSql = "CREATE TABLE user_pokemons (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        pokemon_id INT(11) NOT NULL,
        nickname VARCHAR(255) NULL,
        level INT(11) DEFAULT 5,
        current_hp INT(11) NOT NULL,
        attack INT(11) NOT NULL,
        defense INT(11) NOT NULL,
        sp_attack INT(11) NOT NULL,
        sp_defense INT(11) NOT NULL,
        speed INT(11) NOT NULL,
        is_fainted BOOLEAN DEFAULT FALSE,
        is_shiny BOOLEAN DEFAULT FALSE,
        nature_id INT(11) NOT NULL,
        item_id INT(11) NULL,
        caught_stat_id INT(11) NOT NULL,
        caught_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createUserItemsTable($conn) {
    $createTableSql = "CREATE TABLE user_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        item_id INT(11) NOT NULL,
        quantity INT(11) NOT NULL
    )";
    if (!$conn->query($createTableSql)) {
        die("Error creating table: " . $conn->error);
    }
}

function createAllTables($conn) {
    createPokemonsTable($conn);
    createItemsTable($conn);
    createNaturesTable($conn);
    createCaughtStatsTable($conn);
    createUsersTable($conn);
    createUserPokemonsTable($conn);
    createUserItemsTable($conn);
    echo "All tables created successfully\n";
}

function deleteAllTables($conn) {
    $tables = ['pokemons', 'items', 'natures', 'caught_stats', 'users' ,'user_pokemons', 'user_items'];
    foreach ($tables as $table) {
        $sql = "DROP TABLE IF EXISTS $table";
        if (!$conn->query($sql)) {
            die("Error deleting table $table: " . $conn->error);
        }
    }
    echo "All tables deleted successfully\n";
}

function clearAllTables($conn) {
    $tables = ['pokemons', 'items', 'natures', 'caught_stats', 'users', 'user_pokemons', 'user_items'];
    foreach ($tables as $table) {
        $sql = "TRUNCATE TABLE $table";
        if (!$conn->query($sql)) {
            die("Error clearing table $table: " . $conn->error);
        }
    }
    echo "All tables cleared successfully\n";
}