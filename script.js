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

    // --- Toggle Add Pokemon Form ---
    const toggleBtn = document.getElementById('toggle-form-btn');
    const addFormContainer = document.getElementById('add-pokemon-form-container');
    if (toggleBtn && addFormContainer) {
        let formVisible = false;
        function toggleAddForm() {
            formVisible = !formVisible;
            if (formVisible) {
                addFormContainer.classList.add('form-shown');
                toggleBtn.innerHTML = '<i class="fas fa-times-circle"></i> Skrýt Formulář';
            } else {
                addFormContainer.classList.remove('form-shown');
                toggleBtn.innerHTML = '<i class="fas fa-plus-circle"></i> Přidat Záznam';
            }
        }
        toggleBtn.addEventListener('click', toggleAddForm);
    }

    // --- Type Book Filtering ---
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


    // --- Custom Modal Logic ---
    const modalOverlay = document.getElementById('custom-modal-overlay');
    const modal = document.getElementById('custom-modal');
    const modalText = document.getElementById('custom-modal-text');
    const modalConfirmBtn = document.getElementById('custom-modal-confirm');
    const modalCancelBtn = document.getElementById('custom-modal-cancel');
    let confirmCallback = null;

    function showCustomModal(text, onConfirm, showCancel = true) {
        modalText.textContent = text;
        confirmCallback = onConfirm;
        modalOverlay.classList.add('visible');
        modal.classList.add('visible');
        if (showCancel) {
            modalCancelBtn.style.display = 'inline-flex';
        } else {
            modalCancelBtn.style.display = 'none';
        }
    }
    function hideCustomModal() {
        modalOverlay.classList.remove('visible');
        modal.classList.remove('visible');
        confirmCallback = null;
    }
    modalConfirmBtn.addEventListener('click', () => { if (confirmCallback) { confirmCallback(); } hideCustomModal(); });
    modalCancelBtn.addEventListener('click', hideCustomModal);
    modalOverlay.addEventListener('click', (event) => { if (event.target === modalOverlay) { hideCustomModal(); } });


    // --- AJAX Message Handling ---
    const ajaxMessageContainer = document.getElementById('ajax-message-container');
    function displayAjaxMessage(message, type) {
        ajaxMessageContainer.innerHTML = `<div class="message ${type}">${message}</div>`;
        setTimeout(() => {
            ajaxMessageContainer.innerHTML = '';
        }, 5000); // Zpráva zmizí po 5 sekundách
    }

    // --- AJAX Jeden Pokemon ---
    const singleDeleteForms = document.querySelectorAll('.delete-form-single');
    singleDeleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const pokemonId = this.querySelector('input[name="pokemon_id"]').value;
            const pokemonCard = this.closest('.pokemon-card');
            const pokemonName = pokemonCard.querySelector('.card-header h3').textContent;

            showCustomModal(`Opravdu chcete smazat Pokémona ${pokemonName}?`, () => {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('pokemon_id', pokemonId);

                fetch(window.location.pathname, { // Odesílá na aktuální URL (index.php)
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Hlavička pro detekci AJAXu v PHP
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    displayAjaxMessage(data.message, data.message_type);
                    if (data.success) {
                        pokemonCard.remove(); // Odstraní kartu z DOMu
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    displayAjaxMessage('Došlo k chybě při komunikaci se serverem.', 'error');
                });
            });
        });
    });

    // --- AJAX Více Pokemonů---
    const selectCheckboxes = document.querySelectorAll('.pokemon-select-checkbox');
    const deleteSelectedBtn = document.getElementById('delete-selected-btn');
    // const multipleDeleteForm = document.getElementById('multiple-delete-form'); // Není potřeba
    const allCards = document.querySelectorAll('.pokemon-card'); 
    const headerButtonsContainer = document.querySelector('.header-buttons'); 

    function updateHeaderButtonsLayout() { 
        const anyChecked = Array.from(selectCheckboxes).some(checkbox => checkbox.checked);
        // Tlačítko #delete-selected-btn je vždy viditelné,
        if (anyChecked) {
            headerButtonsContainer.classList.add('multiple-selection-active');
            deleteSelectedBtn.disabled = false; // Povolíme tlačítko
        } else {
            headerButtonsContainer.classList.remove('multiple-selection-active');
            deleteSelectedBtn.disabled = true; // Zakáže tlačítko
        }
    }

    selectCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            updateHeaderButtonsLayout(); 
            const parentCard = checkbox.closest('.pokemon-card');
            if (parentCard) {
                parentCard.classList.toggle('card-selected', checkbox.checked);
            }
        });
    });

    allCards.forEach(card => { 
        card.addEventListener('click', function(event) {
            if (event.target.closest('.delete-form-single') || 
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

    if (deleteSelectedBtn) { // Kontrola existence tlačítka
        deleteSelectedBtn.addEventListener('click', function(event) {
            event.preventDefault(); 
            const selectedCheckboxes = Array.from(selectCheckboxes).filter(checkbox => checkbox.checked);
            const selectedCount = selectedCheckboxes.length;
            
            if (selectedCount > 0) {
                showCustomModal(`Opravdu chcete smazat ${selectedCount} vybraných Pokémonů?`, () => {
                    const formData = new FormData();
                    formData.append('action', 'delete_multiple');
                    selectedCheckboxes.forEach(cb => {
                        formData.append('pokemon_ids[]', cb.value);
                    });

                    fetch(window.location.pathname, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        displayAjaxMessage(data.message, data.message_type);
                        if (data.success) {
                            selectedCheckboxes.forEach(cb => {
                                cb.closest('.pokemon-card').remove();
                            });
                            updateHeaderButtonsLayout(); // Aktualizuje stav tlačítka po smazání
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        displayAjaxMessage('Došlo k chybě při komunikaci se serverem.', 'error');
                    });
                });
            } else {
                showCustomModal('Nevybrali jste žádné Pokémony k smazání.', () => {}, false);
            }
        });
    }
    updateHeaderButtonsLayout(); // Zavolá na začátku
});