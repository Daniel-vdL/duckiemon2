<?php
require_once('../config/conn.php');
require_once('../config/auth.php');

$pokemonsQuery = "SELECT * FROM pokemons";
$pokemons = $conn->query($pokemonsQuery)->fetch_all(MYSQLI_ASSOC);

function getRandomPokemon($pokemons) {
    $rand_pokemon = null;
    foreach ($pokemons as $pokemon) {
        if (rand(1, $pokemon['rarity']) == 1) {
            $rand_pokemon = $pokemon;
            break;
        }
    }
    if ($rand_pokemon == null) {
        $rand_pokemon = $pokemons[array_rand($pokemons)];
    }
    return $rand_pokemon;
}

$rand_pokemon = getRandomPokemon($pokemons);
$isLegendary = $rand_pokemon['rarity'] >= 10000 && $rand_pokemon['rarity'] < 1000000;
$isMythical = $rand_pokemon['rarity'] >= 1000000;

$response = [
    'pokemon' => $rand_pokemon,
    'isLegendary' => $isLegendary,
    'isMythical' => $isMythical
];

header('Content-Type: application/json');
echo json_encode($response);
?>