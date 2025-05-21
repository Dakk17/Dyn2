<?php
// index.php
session_start(); 

require_once 'database.php'; 
require_once 'functions.php'; 

$message = '';      
$message_type = ''; 

$conn = connect_db(); 

$all_pokemons = []; 
$unique_types = []; 

$pokemon_types_list = [
    "Normal", "Fire", "Water", "Grass", "Electric", "Ice", "Fighting", "Poison", 
    "Ground", "Flying", "Psychic", "Bug", "Rock", "Ghost", "Dragon", "Dark", "Steel", "Fairy"
];
sort($pokemon_types_list);


if (!$conn) {
    $message = "Kritická chyba: Nepodařilo se připojit k databázi.";
    $message_type = "error";
} else {
    $seed_message = '';
    $seed_message_type = '';
    if (seed_initial_data($conn, $seed_message, $seed_message_type)) {
        // Zpráva je nastavena uvnitř funkce
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action == 'add' && isset($_POST['name'])) {
                add_pokemon($conn, $_POST, $message, $message_type);
            } elseif ($action == 'delete' && isset($_POST['pokemon_id'])) {
                delete_pokemon($conn, $_POST['pokemon_id'], $message, $message_type);
            } elseif ($action == 'delete_multiple' && isset($_POST['pokemon_ids']) && is_array($_POST['pokemon_ids'])) {
                delete_multiple_pokemons($conn, $_POST['pokemon_ids'], $message, $message_type);
            }
            
            if (!empty($message)) {
                $_SESSION['message'] = $message;
                $_SESSION['message_type'] = $message_type;
            }

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    } elseif (!empty($seed_message)) { 
        $message = $seed_message;
        $message_type = $seed_message_type;
    }

    $all_pokemons = get_all_pokemons($conn);

    if (!empty($all_pokemons)) {
        foreach ($all_pokemons as $pokemon_item) { 
            $type_key = !empty($pokemon_item['type1']) ? ucfirst(strtolower($pokemon_item['type1'])) : 'Neznámý typ';
            if (!in_array($type_key, $unique_types) && $type_key !== 'Neznámý typ') {
                $unique_types[] = $type_key;
            }
        }
        sort($unique_types); 
    }

    $conn->close(); 
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokédex Kniha Dobrodružství</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="https://img.icons8.com/external-those-icons-lineal-those-icons/24/external-Pokedex-geek-those-icons-lineal-those-icons.png">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Cinzel:wght@400..900&family=Overlock:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="parallax-bg">
        <div class="parallax-layer layer-far"></div>
        <div class="parallax-layer layer-mid"></div>
        <div class="parallax-layer layer-near"></div> 
    </div>

    <div class="page-content">
        <header class="main-header">
            <h1>Pokédex Dobrodružství</h1>
            <div class="header-buttons">
                <button id="toggle-form-btn" class="action-button main-action-btn">
                    <i class="fas fa-plus-circle"></i> Přidat Záznam
                </button>
                <button id="delete-selected-btn" class="action-button delete-multiple-btn"> 
                    <i class="fas fa-trash-alt"></i> Smazat Vybrané
                </button>
            </div>
        </header>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div id="add-pokemon-form-container" class="form-section">
            <h2>Přidat Nového Pokémona</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="action" value="add">
                <div><label for="name">Jméno:</label><input type="text" id="name" name="name" required></div>
                <div><label for="hp">HP:</label><input type="number" id="hp" name="hp" min="1" required></div>
                <div><label for="attack">Attack:</label><input type="number" id="attack" name="attack" min="0" required></div>
                <div><label for="defense">Defense:</label><input type="number" id="defense" name="defense" min="0" required></div>
                <div><label for="sp_atk">Sp. Atk:</label><input type="number" id="sp_atk" name="sp_atk" min="0" required></div>
                <div><label for="sp_def">Sp. Def:</label><input type="number" id="sp_def" name="sp_def" min="0" required></div>
                <div><label for="speed">Speed:</label><input type="number" id="speed" name="speed" min="0" required></div>
                
                <div>
                    <label for="type1">Typ 1:</label>
                    <select id="type1" name="type1">
                        <option value="">-- Vyberte typ --</option>
                        <?php foreach ($pokemon_types_list as $type_option): ?>
                            <option value="<?php echo htmlspecialchars($type_option); ?>"><?php echo htmlspecialchars($type_option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="type2">Typ 2 (volitelný):</label>
                    <select id="type2" name="type2">
                        <option value="">-- Žádný --</option>
                        <?php foreach ($pokemon_types_list as $type_option): ?>
                            <option value="<?php echo htmlspecialchars($type_option); ?>"><?php echo htmlspecialchars($type_option); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div><label for="image_url">URL obrázku:</label><input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.png"></div>
                <button type="submit" class="action-button form-submit-button">Uložit Pokémona</button>
            </form>
        </div>

        <div class="type-book-container">
            <div class="type-book-navigation">
                <button id="prev-type-page" class="book-nav-arrow"><i class="fas fa-chevron-left"></i></button>
                <h2 id="current-type-title">Všechny Typy</h2>
                <button id="next-type-page" class="book-nav-arrow"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="type-list-icon-container">
                 <button id="show-all-types-btn" class="action-button type-select-btn" data-type="all">
                    <i class="fas fa-book-open"></i> Všechny
                </button>
                <?php foreach ($unique_types as $type_name_loop): 
                    $type_id = strtolower(str_replace(' ', '-', $type_name_loop));
                ?>
                <button class="action-button type-select-btn type-icon-<?php echo $type_id; ?>" data-type="<?php echo $type_id; ?>">
                    <?php echo htmlspecialchars($type_name_loop); ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <main class="pokedex-content">
            <form id="multiple-delete-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="action" value="delete_multiple">
                <div class="pokedex-grid">
                    <?php if (!$conn && empty($all_pokemons)): ?>
                         <p class="no-pokemon-message">Chyba připojení k databázi. Zkuste to prosím později.</p>
                    <?php elseif (!empty($all_pokemons)): ?>
                        <?php foreach ($all_pokemons as $pokemon): 
                            $image_display_url = !empty($pokemon['image_url']) ? htmlspecialchars($pokemon['image_url']) : 'https://via.placeholder.com/140/CCCCCC/FFFFFF?Text=No+Image';
                            $type1_class = !empty($pokemon['type1']) ? 'type-' . strtolower(htmlspecialchars($pokemon['type1'])) : '';
                            $type2_class = !empty($pokemon['type2']) ? 'type-' . strtolower(htmlspecialchars($pokemon['type2'])) : '';
                            
                            $data_type1_attr = !empty($pokemon['type1']) ? strtolower(htmlspecialchars($pokemon['type1'])) : '';
                            $data_type2_attr = !empty($pokemon['type2']) ? strtolower(htmlspecialchars($pokemon['type2'])) : '';
                            $checkbox_id = "pokemon-check-" . $pokemon['id']; 
                        ?>
                            <div class="pokemon-card <?php echo $type1_class; ?>" data-pokemon-id="<?php echo $pokemon['id']; ?>" data-type1="<?php echo $data_type1_attr; ?>" data-type2="<?php echo $data_type2_attr; ?>">
                                <div class="card-checkbox-area">
                                    <input type="checkbox" name="pokemon_ids[]" value="<?php echo $pokemon['id']; ?>" class="pokemon-select-checkbox" id="<?php echo $checkbox_id; ?>">
                                    <label for="<?php echo $checkbox_id; ?>" class="checkbox-custom-label"></label>
                                </div>
                                <div class="card-content-clickable"> 
                                    <div class="card-header">
                                        <h3><?php echo htmlspecialchars($pokemon['name']); ?></h3>
                                        <div class="pokemon-types">
                                            <?php if(!empty($pokemon['type1'])): ?>
                                                <span class="type-badge <?php echo $type1_class; ?>"><?php echo htmlspecialchars($pokemon['type1']); ?></span>
                                            <?php endif; ?>
                                            <?php if(!empty($pokemon['type2'])): ?>
                                                <span class="type-badge <?php echo $type2_class; ?>"><?php echo htmlspecialchars($pokemon['type2']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <img src="<?php echo $image_display_url; ?>" alt="<?php echo htmlspecialchars($pokemon['name']); ?>">
                                    <div class="stats">
                                        <p>HP: <span><?php echo $pokemon['hp']; ?></span></p>
                                        <p>Attack: <span><?php echo $pokemon['attack']; ?></span></p>
                                        <p>Defense: <span><?php echo $pokemon['defense']; ?></span></p>
                                        <p>Sp. Atk: <span><?php echo $pokemon['sp_atk']; ?></span></p>
                                        <p>Sp. Def: <span><?php echo $pokemon['sp_def']; ?></span></p>
                                        <p>Speed: <span><?php echo $pokemon['speed']; ?></span></p>
                                    </div>
                                </div> 
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="delete-form" onsubmit="return confirm('Opravdu chcete smazat Pokémona <?php echo htmlspecialchars(addslashes($pokemon['name'])); ?>?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="pokemon_id" value="<?php echo $pokemon['id']; ?>">
                                    <button type="submit" class="delete-button"><i class="fas fa-trash-alt"></i> Smazat</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-pokemon-message">Žádní Pokémoni nebyli nalezeni. Přidejte nějaké do své knihy!</p>
                    <?php endif; ?>
                </div> 
            </form> 
        </main>
    </div> 
    <script src="script.js"></script>
</body>
</html>