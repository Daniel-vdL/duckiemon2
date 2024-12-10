<?php 
require_once '../config/conn.php';

$pokemonsQuery = "SELECT * FROM pokemons";
$pokemons = $conn->query($pokemonsQuery)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../frontend/head.php'; ?>
<link rel="stylesheet" href="<?php echo $base_url; ?>frontend/css/duckiecatch.css">
<body class="duckiecatch-body">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 duckiecatch-con">
            <div id="legendary-banner" class="legendary-banner" style="display: none;">Legendary!</div>
            <div id="mythical-banner" class="mythical-banner" style="display: none;">Mythical!</div>
            <img id="pokemon-image" src="" alt="" class="img-fluid">
            <div id="pokemon-info" class="mt-3" style="display: none;">
                <h3 id="pokemon-name"></h3>
                <p>Rarity: 1 in <span id="pokemon-rarity"></span></p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div id="bottom-bar" class="bottom-bar col-12">
        <div class="container">
            <div class="d-flex justify-content-between">
                <div>
                <a href="<?php echo $base_url; ?>" class="btn btn-primary">Home</a>
                <a href="<?php echo $base_url; ?>duckiedex" class="btn btn-primary">Duckiedex</a>
                
                </div>
                <div>
                    <button id="throw-pokeball" class="btn btn-success">Throw Pokeball</button>
                    <button id="randomize" class="btn btn-primary">Randomize Pokemon</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pokemonImage = document.getElementById('pokemon-image');
    const pokemonName = document.getElementById('pokemon-name');
    const pokemonRarity = document.getElementById('pokemon-rarity');
    const pokemonInfo = document.getElementById('pokemon-info');
    const legendaryBanner = document.getElementById('legendary-banner');
    const mythicalBanner = document.getElementById('mythical-banner');
    const throwPokeballButton = document.getElementById('throw-pokeball');

    let pokeballs = 10;
    let currentPokemonId = null;

    function updatePokemon(pokemonData) {
        const { pokemon, isLegendary, isMythical } = pokemonData;
        currentPokemonId = pokemon.id;
        pokemonName.textContent = "Rolling...";
        pokemonRarity.textContent = "???";
        const randomNames = <?php echo json_encode(array_column($pokemons, 'name')); ?>;
        let rollCount = 0;
        const maxRolls = Math.floor(Math.random() * (30 - 10 + 1)) + 10;

        const interval = setInterval(() => {
            const randomName = randomNames[Math.floor(Math.random() * randomNames.length)];
            pokemonImage.src = `https://img.pokemondb.net/sprites/x-y/normal/${randomName}.png`;
            rollCount++;

            if (rollCount >= maxRolls) {
                clearInterval(interval);
                
                pokemonImage.src = `https://img.pokemondb.net/sprites/x-y/normal/${pokemon.name}.png`;
                
                setTimeout(() => {
                    pokemonName.textContent = pokemon.name.charAt(0).toUpperCase() + pokemon.name.slice(1);
                    pokemonRarity.textContent = pokemon.rarity;
                    pokemonInfo.style.display = "block";

                    pokemonImage.classList.remove('legendary', 'mythical');
                    legendaryBanner.style.display = "none";
                    mythicalBanner.style.display = "none";

                    if (isLegendary) {
                        pokemonImage.classList.add('legendary');
                        legendaryBanner.style.display = "block";
                    } else if (isMythical) {
                        pokemonImage.classList.add('mythical');
                        mythicalBanner.style.display = "block";
                    }
                }, 500);
            }
        }, 100);
    }

    function rollPokemon() {
        fetch('get_random_pokemon.php')
        .then(response => response.json())
        .then(data => updatePokemon(data))
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to fetch a new Pokémon. Please try again!');
        });
    }

    rollPokemon();

    document.getElementById('randomize').addEventListener('click', rollPokemon);

    throwPokeballButton.addEventListener('click', function() {
        if (pokeballs > 0) {
            pokeballs--;

            if (Math.random() < 0.5) {
                const nickname = prompt("You caught the Pokémon! Enter a nickname (optional):");
                const userId = 1;

                fetch('save_pokemon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `user_id=${userId}&pokemon_id=${currentPokemonId}&nickname=${nickname}`
                })
                .then(response => response.text())
                .then(data => {
                    alert("Pokémon saved successfully!");
                    rollPokemon();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                alert("The Pokémon escaped!");
                rollPokemon();
            }
        } else {
            alert("No more Pokéballs left!");
        }
    });
});
</script>
</body>
</html>
