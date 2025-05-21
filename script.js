// script.js
document.addEventListener('DOMContentLoaded', function() {
    // --- Parallax Effect ---
    const parallaxBg = document.querySelector('.parallax-bg');
    if (parallaxBg) {
        const farLayer = document.querySelector('.parallax-layer.layer-far');
        const midLayer = document.querySelector('.parallax-layer.layer-mid');
        const nearLayer = document.querySelector('.parallax-layer.layer-near');

        window.addEventListener('scroll', function() {
            let offset = window.scrollY;
            if (farLayer) farLayer.style.transform = `translateY(${offset * 0.15}px)`;
            if (midLayer) midLayer.style.transform = `translateY(${offset * 0.35}px)`;
            if (nearLayer) nearLayer.style.transform = `translateY(${offset * 0.6}px)`;
        });
    }

    // --- Pokemon Form ---
    const toggleBtn = document.getElementById('toggle-form-btn');
    const addFormContainer = document.getElementById('add-pokemon-form-container');

    if (toggleBtn && addFormContainer) {
        let formVisible = false;

        function toggleAddForm() {
            formVisible = !formVisible;
            if (formVisible) {
                addFormContainer.classList.add('form-shown');
                // maxHeight se nyní plně řídí CSS třídou .form-shown
                toggleBtn.innerHTML = '<i class="fas fa-times-circle"></i> Skrýt Formulář';
            } else {
                addFormContainer.classList.remove('form-shown');
                toggleBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Přidat Záznam';
            }
        }
        toggleBtn.addEventListener('click', toggleAddForm);
    }

    // --- Filtrace Typu ---
    const typeButtons = document.querySelectorAll('.type-select-btn');
    const pokedexGrid = document.querySelector('.pokedex-grid');
    const currentTypeTitle = document.getElementById('current-type-title');
    let activeFilter = 'all';

    function filterPokemonCards(selectedType) {
        activeFilter = selectedType;
        const allPokemonCards = pokedexGrid.querySelectorAll('.pokemon-card');

        allPokemonCards.forEach(card => {
            const type1 = card.dataset.type1;
            const type2 = card.dataset.type2;
            let matchesFilter = false;

            if (selectedType === 'all') {
                matchesFilter = true;
            } else {
                if (type1 === selectedType || type2 === selectedType) {
                    matchesFilter = true;
                }
            }

            if (matchesFilter) {
                card.classList.remove('dimmed-by-filter');
                card.classList.add('highlighted-by-filter');
            } else {
                card.classList.remove('highlighted-by-filter');
                card.classList.add('dimmed-by-filter');
            }
        });

        if (currentTypeTitle) {
            currentTypeTitle.textContent = selectedType === 'all' ? 'Všechny Typy' : `Typ: ${selectedType.charAt(0).toUpperCase() + selectedType.slice(1)}`;
        }
        typeButtons.forEach(btn => {
            btn.classList.remove('active-type-filter');
            if (btn.dataset.type === selectedType) {
                btn.classList.add('active-type-filter');
            }
        });
    }
    
    typeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const selectedType = this.dataset.type;
            filterPokemonCards(selectedType);
        });
    });

    const prevTypePageBtn = document.getElementById('prev-type-page');
    const nextTypePageBtn = document.getElementById('next-type-page');
    const availableTypeFilters = Array.from(typeButtons).map(btn => btn.dataset.type);

    if (prevTypePageBtn && nextTypePageBtn && availableTypeFilters.length > 0) {
        let currentTypeIndex = availableTypeFilters.findIndex(type => type === 'all');
        if (currentTypeIndex === -1) currentTypeIndex = 0; 
        
        prevTypePageBtn.addEventListener('click', () => {
            currentTypeIndex--;
            if (currentTypeIndex < 0) {
                currentTypeIndex = availableTypeFilters.length - 1;
            }
            filterPokemonCards(availableTypeFilters[currentTypeIndex]);
        });

        nextTypePageBtn.addEventListener('click', () => {
            currentTypeIndex++;
            if (currentTypeIndex >= availableTypeFilters.length) {
                currentTypeIndex = 0;
            }
            filterPokemonCards(availableTypeFilters[currentTypeIndex]);
        });
    }

    const initialShowAllButton = document.querySelector('.type-select-btn[data-type="all"]');
    if (initialShowAllButton) {
        initialShowAllButton.classList.add('active-type-filter');
        filterPokemonCards('all'); 
    }

    // --- Mnohonásobný Výběr ---
    const selectCheckboxes = document.querySelectorAll('.pokemon-select-checkbox');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    const multipleDeleteForm = document.getElementById('multiple-delete-form');
    const allCards = document.querySelectorAll('.pokemon-card'); 
    const headerButtonsContainer = document.querySelector('.header-buttons'); 

    function updateDeleteSelectedButtonState() { 
        const anyChecked = Array.from(selectCheckboxes).some(checkbox => checkbox.checked);
        if (anyChecked) {
            headerButtonsContainer.classList.add('multiple-active');
            // deleteSelectedBtn.disabled = false;
        } else {
            headerButtonsContainer.classList.remove('multiple-active');
            // deleteSelectedBtn.disabled = true;
        }
    }

    selectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            updateDeleteSelectedButtonState(); // Aktualizuje zarovnání hlavičky
            const parentCard = checkbox.closest('.pokemon-card');
            if (parentCard) {
                parentCard.classList.toggle('card-selected', checkbox.checked);
            }
        });
    });

    allCards.forEach(card => { 
        card.addEventListener('click', function(event) {
            if (event.target.closest('.delete-form') || 
                event.target.classList.contains('pokemon-select-checkbox') ||
                event.target.classList.contains('checkbox-custom-label')) {
                return;
            }

            const checkbox = this.querySelector('.pokemon-select-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                const changeEvent = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(changeEvent);
            }
        });
    });


    if (deleteSelectedBtn && multipleDeleteForm) {
        deleteSelectedBtn.addEventListener('click', function(event) {
            event.preventDefault(); 
            const selectedCheckboxes = Array.from(selectCheckboxes).filter(checkbox => checkbox.checked);
            const selectedCount = selectedCheckboxes.length;
            
            if (selectedCount > 0) {
                if (confirm(`Opravdu chcete smazat ${selectedCount} vybraných Pokémonů?`)) {
                    multipleDeleteForm.innerHTML = ''; 
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'delete_multiple';
                    multipleDeleteForm.appendChild(actionInput);

                    selectedCheckboxes.forEach(cb => {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'pokemon_ids[]';
                        hiddenInput.value = cb.value;
                        multipleDeleteForm.appendChild(hiddenInput);
                    });
                    multipleDeleteForm.submit();
                }
            } else {
                alert('Nevybrali jste žádné Pokémony k smazání.');
            }
        });
    }
    // Počáteční nastavení stavu tlačítek v hlavičce
    updateDeleteSelectedButtonState(); 

});