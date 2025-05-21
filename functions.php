<?php
// functions.php

function seed_initial_data($conn, &$message, &$message_type) {
    $sql_check = "SELECT COUNT(*) as count FROM pokemons";
    $result_check = $conn->query($sql_check);
    if (!$result_check) {
        $message = "Chyba při kontrole databáze: " . $conn->error;
        $message_type = "error";
        return false;
    }
    $row_check = $result_check->fetch_assoc();

    if ($row_check['count'] == 0) { 
        $initial_pokemons = [ 
            ['name' => 'Bulbasaur', 'hp' => 45, 'attack' => 49, 'defense' => 49, 'sp_atk' => 65, 'sp_def' => 65, 'speed' => 45, 'image_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/1.png', 'type1' => 'Grass', 'type2' => 'Poison'],
            ['name' => 'Ivysaur', 'hp' => 60, 'attack' => 62, 'defense' => 63, 'sp_atk' => 80, 'sp_def' => 80, 'speed' => 60, 'image_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/2.png', 'type1' => 'Grass', 'type2' => 'Poison'],
            ['name' => 'Venusaur', 'hp' => 80, 'attack' => 82, 'defense' => 83, 'sp_atk' => 100, 'sp_def' => 100, 'speed' => 80, 'image_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/3.png', 'type1' => 'Grass', 'type2' => 'Poison'],
            ['name' => 'Charmander', 'hp' => 39, 'attack' => 52, 'defense' => 43, 'sp_atk' => 60, 'sp_def' => 50, 'speed' => 65, 'image_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/4.png', 'type1' => 'Fire', 'type2' => null],
            ['name' => 'Charmeleon', 'hp' => 58, 'attack' => 64, 'defense' => 58, 'sp_atk' => 80, 'sp_def' => 65, 'speed' => 80, 'image_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/5.png', 'type1' => 'Fire', 'type2' => null],
        ];

        $stmt = $conn->prepare("INSERT INTO pokemons (name, hp, attack, defense, sp_atk, sp_def, speed, image_url, type1, type2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $message = "Chyba při přípravě dotazu pro vkládání: " . $conn->error;
            $message_type = "error";
            return false;
        }
        foreach ($initial_pokemons as $p) { 
            $stmt->bind_param("siiiiiisss", $p['name'], $p['hp'], $p['attack'], $p['defense'], $p['sp_atk'], $p['sp_def'], $p['speed'], $p['image_url'], $p['type1'], $p['type2']);
            if (!$stmt->execute()) {
                error_log("Chyba při vkládání Pokémona " . $p['name'] . ": " . $stmt->error); 
            }
        }
        $stmt->close();
        $message = "Tabulka Pokémonů byla prázdná, vloženo 5 základních Pokémonů s typy.";
        $message_type = "success";
        return true;
    }
    return false;
}

function add_pokemon($conn, $data, &$message, &$message_type) { 
    if (empty($data['name']) || !is_numeric($data['hp']) || (int)$data['hp'] < 0 ||
        !is_numeric($data['attack']) || (int)$data['attack'] < 0 ||
        !is_numeric($data['defense']) || (int)$data['defense'] < 0 ||
        !is_numeric($data['sp_atk']) || (int)$data['sp_atk'] < 0 ||
        !is_numeric($data['sp_def']) || (int)$data['sp_def'] < 0 ||
        !is_numeric($data['speed']) || (int)$data['speed'] < 0) {
        $message = "Všechna pole (kromě typů a obrázku) musí být vyplněna a staty musí být kladná čísla.";
        $message_type = 'error';
        return false;
    }
    
    $image_url = !empty($data['image_url']) ? filter_var($data['image_url'], FILTER_SANITIZE_URL) : null; 
    if (!empty($data['image_url']) && !filter_var($image_url, FILTER_VALIDATE_URL) && $image_url !== null) {
         $image_url = 'https://via.placeholder.com/96/FFA500/FFFFFF?Text=Invalid+URL';
    }

    $type1 = !empty($data['type1']) ? htmlspecialchars(trim($data['type1'])) : null;
    $type2 = !empty($data['type2']) ? htmlspecialchars(trim($data['type2'])) : null;
    if ($type1 === $type2 && $type1 !== null) { 
        $type2 = null;
    }

    $sql = "INSERT INTO pokemons (name, hp, attack, defense, sp_atk, sp_def, speed, image_url, type1, type2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $message = "Chyba při přípravě dotazu: " . $conn->error;
        $message_type = 'error';
        return false;
    }
    
    $hp = (int)$data['hp']; 
    $attack = (int)$data['attack'];
    $defense = (int)$data['defense'];
    $sp_atk = (int)$data['sp_atk'];
    $sp_def = (int)$data['sp_def'];
    $speed = (int)$data['speed'];

    $stmt->bind_param("siiiiiisss", $data['name'], $hp, $attack, $defense, $sp_atk, $sp_def, $speed, $image_url, $type1, $type2);
    
    if ($stmt->execute()) {
        $message = "Pokémon " . htmlspecialchars($data['name']) . " byl úspěšně přidán.";
        $message_type = 'success';
        $stmt->close();
        return true;
    } else {
        $message = "Chyba při přidávání Pokémona: " . $stmt->error;
        $message_type = 'error';
        $stmt->close();
        return false;
    }
}

function delete_pokemon($conn, $id, &$message, &$message_type) {
    $pokemon_id = (int)$id; 
    if ($pokemon_id <= 0) { 
        $message = "Neplatné ID Pokémona.";
        $message_type = 'error';
        return false;
    }

    $sql = "DELETE FROM pokemons WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $message = "Chyba při přípravě dotazu: " . $conn->error;
        $message_type = 'error';
        return false;
    }
    $stmt->bind_param("i", $pokemon_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) { 
            $message = "Pokémon byl úspěšně smazán.";
            $message_type = 'success';
        } else {
            $message = "Pokémon s ID " . $pokemon_id . " nebyl nalezen.";
            $message_type = 'error';
        }
        $stmt->close();
        return true; 
    } else {
        $message = "Chyba při mazání Pokémona: " . $stmt->error;
        $message_type = 'error';
        $stmt->close();
        return false;
    }
}

function delete_multiple_pokemons($conn, $ids, &$message, &$message_type) {
    if (empty($ids) || !is_array($ids)) {
        $message = "Nebyly vybrány žádné Pokémony k smazání.";
        $message_type = 'error';
        return false;
    }

    $sanitized_ids = [];
    foreach ($ids as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT) && (int)$id > 0) {
            $sanitized_ids[] = (int)$id;
        }
    }

    if (empty($sanitized_ids)) {
        $message = "Výběr neobsahoval platná ID Pokémonů.";
        $message_type = 'error';
        return false;
    }

    $placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));
    $sql = "DELETE FROM pokemons WHERE id IN ($placeholders)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $message = "Chyba při přípravě dotazu pro vícenásobné smazání: " . $conn->error;
        $message_type = 'error';
        return false;
    }

    $types_string = str_repeat('i', count($sanitized_ids));
    $stmt->bind_param($types_string, ...$sanitized_ids); 

    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        if ($affected_rows > 0) {
            $message = "Úspěšně smazáno " . $affected_rows . " Pokémonů.";
            $message_type = 'success';
        } else {
            $message = "Žádní Pokémoni neodpovídali výběru pro smazání (možná již byli smazáni).";
            $message_type = 'info'; 
        }
        $stmt->close();
        return true;
    } else {
        $message = "Chyba při vícenásobném mazání Pokémonů: " . $stmt->error;
        $message_type = 'error';
        $stmt->close();
        return false;
    }
}

function get_all_pokemons($conn) {
    $pokemons_list = []; 
    $sql = "SELECT id, name, hp, attack, defense, sp_atk, sp_def, speed, image_url, type1, type2 FROM pokemons ORDER BY id ASC"; 
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) { 
            $pokemons_list[] = $row;
        }
    }
    return $pokemons_list;
}
?>