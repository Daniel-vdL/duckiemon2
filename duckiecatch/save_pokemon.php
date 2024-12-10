<?php
require_once '../config/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $pokemonId = $_POST['pokemon_id'];
    $nickname = $_POST['nickname'];

    if ($nickname == '') {
        $nickname = null;
    }

    saveCaughtPokemon($userId, $pokemonId, $nickname);
}

function getBaseStats($pokemonId) {
    $url = "https://pokeapi.co/api/v2/pokemon/" . $pokemonId;
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data['stats'];
}

function generateRandomIVs() {
    $ivs = [];
    for ($i = 0; $i < 6; $i++) {
        $ivs[] = rand(0, 31);
    }
    return $ivs;
}

function saveCaughtPokemon($userId, $pokemonId, $nickname = null) {
    global $conn;
    $baseStats = getBaseStats($pokemonId);
    $ivs = generateRandomIVs();
    $evs = array_fill(0, 6, 0);
    $total_iv = array_sum($ivs);
    $total_evs = 0;

    $stmt = $conn->prepare("INSERT INTO caught_stats (total_iv, iv_hp, iv_attack, iv_defense, iv_sp_attack, iv_sp_defense, iv_speed, total_ev, ev_hp, ev_attack, ev_defense, ev_sp_attack, ev_sp_defense, ev_speed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiiiiiiiiiiii", $total_iv, $ivs[0], $ivs[1], $ivs[2], $ivs[3], $ivs[4], $ivs[5], $total_evs, $evs[0], $evs[1], $evs[2], $evs[3], $evs[4], $evs[5]);
    $stmt->execute();
    $caughtStatId = $stmt->insert_id;
    $stmt->close();

    $natureResult = $conn->query("SELECT * FROM natures ORDER BY RAND() LIMIT 1");
    $nature = $natureResult->fetch_assoc();
    $natureId = $nature['id'];
    $natureIncrease = $nature['increased_stat'];
    $natureDecrease = $nature['decreased_stat'];

    $level = 5;
    $stats = calculateStats($baseStats, $ivs, $evs, $level, $natureIncrease, $natureDecrease);

    $stmt = $conn->prepare("INSERT INTO user_pokemons (user_id, pokemon_id, nickname, level, current_hp, attack, defense, sp_attack, sp_defense, speed, nature_id, caught_stat_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisiiiiiiiii", $userId, $pokemonId, $nickname, $level, $stats['hp'], $stats['attack'], $stats['defense'], $stats['sp_attack'], $stats['sp_defense'], $stats['speed'], $natureId, $caughtStatId);
    $stmt->execute();
    $stmt->close();

}

function calculateStats($baseStats, $ivs, $evs, $level, $natureIncrease, $natureDecrease) {
    $stats = [];

    $stats['hp'] = floor((2 * $baseStats[5]['base_stat'] + $ivs[0] + floor($evs[0] / 4)) * $level / 100 + $level + 10);
    $stats['attack'] = floor((2 * $baseStats[4]['base_stat'] + $ivs[1] + floor($evs[1] / 4)) * $level / 100 + 5);
    $stats['defense'] = floor((2 * $baseStats[3]['base_stat'] + $ivs[2] + floor($evs[2] / 4)) * $level / 100 + 5);
    $stats['sp_attack'] = floor((2 * $baseStats[2]['base_stat'] + $ivs[3] + floor($evs[3] / 4)) * $level / 100 + 5);
    $stats['sp_defense'] = floor((2 * $baseStats[1]['base_stat'] + $ivs[4] + floor($evs[4] / 4)) * $level / 100 + 5);
    $stats['speed'] = floor((2 * $baseStats[0]['base_stat'] + $ivs[5] + floor($evs[5] / 4)) * $level / 100 + 5);

    if ($natureIncrease == 'attack') {
        $stats['attack'] = floor($stats['attack'] * 1.1);
    } else if ($natureIncrease == 'defense') {
        $stats['defense'] = floor($stats['defense'] * 1.1);
    } else if ($natureIncrease == 'sp_attack') {
        $stats['sp_attack'] = floor($stats['sp_attack'] * 1.1);
    } else if ($natureIncrease == 'sp_defense') {
        $stats['sp_defense'] = floor($stats['sp_defense'] * 1.1);
    } else if ($natureIncrease == 'speed') {
        $stats['speed'] = floor($stats['speed'] * 1.1);
    }

    if ($natureDecrease == 'attack') {
        $stats['attack'] = floor($stats['attack'] * 0.9);
    } else if ($natureDecrease == 'defense') {
        $stats['defense'] = floor($stats['defense'] * 0.9);
    } else if ($natureDecrease == 'sp_attack') {
        $stats['sp_attack'] = floor($stats['sp_attack'] * 0.9);
    } else if ($natureDecrease == 'sp_defense') {
        $stats['sp_defense'] = floor($stats['sp_defense'] * 0.9);
    } else if ($natureDecrease == 'speed') {
        $stats['speed'] = floor($stats['speed'] * 0.9);
    }

    return $stats;
}