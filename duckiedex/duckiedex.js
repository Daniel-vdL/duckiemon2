document.addEventListener('DOMContentLoaded', () => {
    const firstPokemon = 1;
    console.log(firstPokemon);
    showPokemonInfo(firstPokemon);
});

function showPokemonInfo(pokemonId) {
    fetch(`https://pokeapi.co/api/v2/pokemon/${pokemonId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('pokemon-name').innerText = data.name.charAt(0).toUpperCase() + data.name.slice(1);
            document.getElementById('pokemon-image').src = data.sprites.front_default;
            document.getElementById('pokemon-image').alt = data.name;
            
            fetch(data.species.url)
                .then(response => response.json())
                .then(speciesData => {
                    document.getElementById('pokemon-height').innerText = (data.height / 10).toFixed(1);
                    document.getElementById('pokemon-weight').innerText = (data.weight / 10).toFixed(1);
                    document.getElementById('pokemon-base-experience').innerText = data.base_experience;
                    document.getElementById('pokemon-types').innerHTML = data.types.map(typeInfo => `<span class="badge badge-primary me-1">${typeInfo.type.name}</span>`).join(' ');
                    document.getElementById('pokemon-legendary').innerText = speciesData.is_legendary ? 'Yes' : 'No';
                    document.getElementById('pokemon-abilities').innerText = data.abilities.map(abilityInfo => abilityInfo.ability.name).join(', ');

                    const typeUrls = data.types.map(typeInfo => typeInfo.type.url);
                    Promise.all(typeUrls.map(url => fetch(url).then(response => response.json())))
                        .then(typesData => {
                            const weaknesses = new Set();
                            typesData.forEach(typeData => {
                                typeData.damage_relations.double_damage_from.forEach(weakness => weaknesses.add(weakness.name));
                            });
                            document.getElementById('pokemon-type-weakness').innerText = Array.from(weaknesses).join(', ');
                        });

                    fetch(speciesData.evolution_chain.url)
                        .then(response => response.json())
                        .then(evolutionData => {
                            const evolutions = [];
                            let current = evolutionData.chain;

                            do {
                                const evolvesTo = current.evolves_to.map(evo => evo.species.name);
                                evolutions.push({ name: current.species.name, evolvesTo: evolvesTo });
                                current = current.evolves_to[0];
                            } while (current && current.evolves_to);

                            const currentEvolution = evolutions.find(evo => evo.name == data.name);
                            const previousEvolution = evolutions.find(evo => evo.evolvesTo.includes(data.name));
                            const nextEvolutions = currentEvolution ? currentEvolution.evolvesTo : [];

                            let evolutionHtml = '';
                            if (previousEvolution) {
                                evolutionHtml += `<a href="#${previousEvolution.name}" class="evolution-link" onclick="showPokemonInfo('${previousEvolution.name}')">${previousEvolution.name}</a> ← `;
                            }
                            evolutionHtml += `<strong>${data.name}</strong>`;
                            if (nextEvolutions.length > 0) {
                                evolutionHtml += ` → ${nextEvolutions.map(evo => `<a href="#${evo}" class="evolution-link" onclick="showPokemonInfo('${evo}')">${evo}</a>`).join(', ')}`;
                            }

                            document.getElementById('pokemon-evolutions').innerHTML = evolutionHtml;
                        });
                });

            document.getElementById('pokemon-hp').innerText = data.stats.find(stat => stat.stat.name == 'hp').base_stat;
            document.getElementById('pokemon-attack').innerText = data.stats.find(stat => stat.stat.name == 'attack').base_stat;
            document.getElementById('pokemon-defense').innerText = data.stats.find(stat => stat.stat.name == 'defense').base_stat;
            document.getElementById('pokemon-special-attack').innerText = data.stats.find(stat => stat.stat.name == 'special-attack').base_stat;
            document.getElementById('pokemon-special-defense').innerText = data.stats.find(stat => stat.stat.name == 'special-defense').base_stat;
            document.getElementById('pokemon-speed').innerText = data.stats.find(stat => stat.stat.name == 'speed').base_stat;
            document.getElementById('pokemon-total-stats').innerText = data.stats.reduce((total, stat) => total + stat.base_stat, 0);

            const bottomBar = document.getElementById('bottom-bar');
            const expandButton = document.getElementById('expand-button');
            const bottomBarNav = document.getElementById('bottombar-nav');
            const pokemonFilter = document.getElementById('pokemon-filter');
            const pokemonSearch = document.getElementById('pokemon-search');
            if (bottomBar.classList.contains('expanded')) {
                bottomBar.classList.remove('expanded');
                bottomBar.style.maxHeight = '200px';
                bottomBar.style.minHeight = '200px';
                expandButton.innerText = '▲';
                expandButton.style.bottom = '200px';
                bottomBarNav.style.bottom = '200px';
                pokemonFilter.style.bottom = '200px';
                pokemonSearch.style.bottom = '200px';
            }
        });
}

function toggleBottomBar() {
    const bottomBar = document.getElementById('bottom-bar');
    const expandButton = document.getElementById('expand-button');
    const bottomBarNav = document.getElementById('bottombar-nav');
    const pokemonFilter = document.getElementById('pokemon-filter');
    const pokemonSearch = document.getElementById('pokemon-search');
    if (!bottomBar || !expandButton || !bottomBarNav || !pokemonFilter || !pokemonSearch) {
        console.error('One or more elements not found in the DOM');
        return;
    }
    bottomBar.classList.toggle('expanded');

    if (bottomBar.classList.contains('expanded')) {
        bottomBar.style.maxHeight = '730px';
        bottomBar.style.minHeight = '730px';
        expandButton.innerText = '▼';
        expandButton.style.bottom = '730px';
        bottomBarNav.style.bottom = '730px';
        pokemonFilter.style.bottom = '730px';
        pokemonSearch.style.bottom = '730px';
    } else {
        bottomBar.style.maxHeight = '200px';
        bottomBar.style.minHeight = '200px';
        expandButton.innerText = '▲';
        expandButton.style.bottom = '200px';
        bottomBarNav.style.bottom = '200px';
        pokemonFilter.style.bottom = '200px';
        pokemonSearch.style.bottom = '200px';
    }
}

function filterPokemons(filter) {
    const pokemonList = document.getElementById('pokemon-list');
    const pokemons = pokemonList.getElementsByTagName('li');

    for (let i = 0; i < pokemons.length; i++) {
        const pokemon = pokemons[i];
        const isLegendary = pokemon.getAttribute('data-legendary') == '1';
        const isMythical = pokemon.getAttribute('data-mythical') == '1';

        if (filter === 'all') {
            pokemon.style.display = '';
        } else if (filter == 'legendary' && isLegendary) {
            pokemon.style.display = '';
        } else if (filter == 'mythical' && isMythical) {
            pokemon.style.display = '';
        } else if (filter == 'non-legendary' && !isLegendary && !isMythical) {
            pokemon.style.display = '';
        } else {
            pokemon.style.display = 'none';
        }
    }
}

function searchPokemon() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const pokemonList = document.getElementById('pokemon-list');
    const pokemons = pokemonList.getElementsByTagName('li');

    for (let i = 0; i < pokemons.length; i++) {
        const pokemon = pokemons[i];
        const name = pokemon.getAttribute('data-name').toLowerCase();
        const id = pokemon.getAttribute('data-id').toLowerCase();

        if (name.includes(searchInput) || id.includes(searchInput)) {
            pokemon.style.display = '';
        } else {
            pokemon.style.display = 'none';
        }
    }
}