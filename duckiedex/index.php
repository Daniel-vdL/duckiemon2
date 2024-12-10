<?php 
require_once '../config/conn.php';

$pokemonsQuery = "SELECT * FROM pokemons";
$currentPokemons = $conn->query($pokemonsQuery)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once '../frontend/head.php'; ?>
<link rel="stylesheet" href="<?php echo $base_url; ?>frontend/css/duckiedex.css">
<body class="duckiedex-body">
<div class="container-fluid">
    <div class="row">
        <main class="pokemon-info-main col-12 col-lg-10 mx-auto">
            <div class="pokemon-info-con rounded p-4 shadow-lg">
                <div class="row">
                    <div class="pokemon-info-head col-md-4 text-center rounded py-3">
                        <h2 id="pokemon-name" class="text-white"></h2>
                        <img id="pokemon-image" src="" alt="" class="img-fluid rounded" style="border: 3px solid var(--accent-color);">
                        <div id="pokemon-types" class="mt-3"></div>
                        <div id="pokemon-evolutions" class="mt-3 evolution-chain"></div>
                    </div>
                    <div class="col-md-8 d-flex flex-column justify-content-around">
                        <div class="poke-info mb-3 p-3 rounded d-flex">
                            <div class="col-md-4 pt-5">
                                <h5>General Info</h5>
                            </div>
                            <div class="col-md-4">
                                <p><strong>-- Height:</strong> <span id="pokemon-height"> </span> m</p>
                                <p><strong>-- Weight:</strong> <span id="pokemon-weight"></span> kg</p>
                                <p><strong>-- Base Experience:</strong> <span id="pokemon-base-experience"></span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>-- Legendary:</strong> <span id="pokemon-legendary"></span></p>
                                <p><strong>-- Abilities:</strong> <span id="pokemon-abilities"></span></p>
                                <p><strong>-- Type weaknesses:</strong> <span id="pokemon-type-weakness"></span></p>
                            </div>
                        </div>
                        <div class="poke-stats p-3 rounded d-flex">
                            <div class="col-md-4">
                                <h5>Base Stats</h5>
                                <p>Total: <span id="pokemon-total-stats"></span><p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>-- HP:</strong> <span id="pokemon-hp"></span></p>
                                <p><strong>-- Attack:</strong> <span id="pokemon-attack"></span></p>
                                <p><strong>-- Defense:</strong> <span id="pokemon-defense"></span></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>-- Special Attack:</strong> <span id="pokemon-special-attack"></span></p>
                                <p><strong>-- Special Defense:</strong> <span id="pokemon-special-defense"></span></p>
                                <p><strong>-- Speed:</strong> <span id="pokemon-speed"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <div class="row mt-4">
        <nav id="bottom-bar" class="bottom-bar col-12">
            <ul class="nav flex-row flex-wrap justify-content-center pokemon-grid list-unstyled" id="pokemon-list">
                <?php foreach ($currentPokemons as $pokemon) { ?>
                    <li class="nav-item p-2" data-legendary="<?php echo $pokemon['is_legendary']; ?>" data-mythical="<?php echo $pokemon['is_mythical']; ?>" data-name="<?php echo $pokemon['name']; ?>" data-id="<?php echo $pokemon['id']; ?>">
                        <a class="nav-link text-decoration-none text-center text-white" href="#<?php echo $pokemon['name']; ?>" onclick="showPokemonInfo('<?php echo $pokemon['id']; ?>')">
                            <img src="https://img.pokemondb.net/sprites/x-y/normal/<?php echo $pokemon['name']; ?>.png" alt="<?php echo $pokemon['name']; ?>" class="img-fluid">
                            <div><?php echo ucfirst($pokemon['id']); ?></div>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </div>
    <button class="expand-button" id="expand-button" onclick="toggleBottomBar()">▲</button>
    <div class="bottombar-nav d-flex" id="bottombar-nav">
        <a href="<?php echo $base_url; ?>">Home</a>
        <p>|</p>
        <a href="<?php echo $base_url; ?>duckiecatch">Catch a Pokemon</a>
    </div>
    <div class="pokemon-filter dropdown" id="pokemon-filter">
        <button class="dropdown-btn dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            Filter Pokemons
        </button>
        <ul class="dropdown-menu" aria-labelledby="filterDropdown">
            <li><a class="dropdown-item" href="#" onclick="filterPokemons('all')">All</a></li>
            <li><a class="dropdown-item" href="#" onclick="filterPokemons('legendary')">Legendary</a></li>
            <li><a class="dropdown-item" href="#" onclick="filterPokemons('mythical')">Mythical</a></li>
            <li><a class="dropdown-item" href="#" onclick="filterPokemons('non-legendary')">Non-Legendary</a></li>
        </ul>
    </div>
    <div class="pokemon-search d-flex" id="pokemon-search">
        <input type="text" id="searchInput" placeholder="Search by name or number" autocomplete="off" onkeypress="if(event.key === 'Enter') searchPokemon()">
        <button onclick="searchPokemon()">Search</button>
    </div>
</div>
<script src="duckiedex.js"></script>
</body>
</html>
