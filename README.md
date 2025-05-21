# Pokédex Kniha Dobrodružství

**Odkaz na živou verzi projektu:** [https://app.opent2.com/it3a/hanzliks/dyn2/](https://app.opent2.com/it3a/hanzliks/dyn2/)

## Popis Projektu

Tento projekt je interaktivní webová aplikace ve stylu Pokédexu, která uživatelům umožňuje procházet, přidávat a mazat informace o Pokémonech. Aplikace je navržena s důrazem na vizuálně přitažlivý a "knižní" design s parallax efekty a plynulými animacemi.

## Klíčové Funkce

*   **Zobrazení Pokémonů:** Karty Pokémonů s jejich obrázky, základními staty (HP, Attack, Defense, Sp. Atk, Sp. Def, Speed) a typy.
*   **Přidávání Pokémonů:** Uživatelé mohou přidávat nové Pokémony prostřednictvím formuláře, včetně jejich statů, typů a URL obrázku.
*   **Mazání Pokémonů:**
    *   Možnost smazat jednotlivé Pokémony.
    *   Možnost vybrat více Pokémonů pomocí checkboxů a smazat je hromadně.
*   **Filtrování podle Typu:** "Kniha typů" umožňuje uživatelům filtrovat zobrazené Pokémony podle jejich primárního typu. Nevyfiltrované karty jsou vizuálně ztlumeny.
*   **Interaktivní UI:**
    *   Kliknutím na kartu Pokémona se označí pro hromadné smazání.
    *   Plynulé animace při otevírání/zavírání formuláře a při interakci s kartami.
    *   Parallax efekt na pozadí pro dojem hloubky a "kresleného" papíru.
*   **Responzivní Design:** Aplikace je navržena tak, aby byla použitelná na různých velikostech obrazovek.
*   **Počáteční Data:** Při prvním spuštění (nebo pokud je databáze prázdná) se automaticky vloží 5 základních Pokémonů.

## Použité Technologie

*   **Frontend:**
    *   HTML5
    *   CSS3 (včetně Flexbox, Grid, CSS proměnných, přechodů a animací)
    *   JavaScript (pro DOM manipulaci, event listenery, parallax efekt a interaktivitu)
*   **Backend:**
    *   PHP (pro serverovou logiku, zpracování formulářů a interakci s databází)
*   **Databáze:**
    *   MySQL (pro ukládání dat o Pokémonech)
*   **Fonty:**
    *   Google Fonts (Lora, Cinzel, Overlock)
*   **Ikony:**
    *   Font Awesome

## Struktura Projektu

Projekt je rozdělen do následujících souborů:

*   `index.php`: Hlavní vstupní bod aplikace, generuje HTML strukturu a zpracovává uživatelské požadavky.
*   `style.css`: Obsahuje všechny CSS styly pro vizuální prezentaci.
*   `script.js`: Obsahuje JavaScript kód pro klientskou logiku a interaktivitu.
*   `functions.php`: Knihovna PHP funkcí pro logiku aplikace (práce s databází, manipulace s daty Pokémonů).
*   `database.php`: Skript pro navázání spojení s MySQL databází.
*   `config.php`: Konfigurační soubor s přihlašovacími údaji k databázi.

## Požadavky na Splnění (Školní Projekt)

Aplikace splňuje následující požadavky:
*   **Spojení s DB MySQL:** Použity dotazy `SELECT`, `INSERT`, `DELETE`.
*   **Proměnné:** Rozsáhlé využití v PHP i JavaScriptu.
*   **Operátory:** Využití aritmetických, přiřazovacích, porovnávacích, logických a dalších operátorů.
*   **Větvení:** Použito více než 3x (např. `if/else if/else` v PHP pro zpracování akcí, v JS pro logiku UI).
*   **Cykly:** Použito více než 3x (např. `foreach` v PHP pro generování seznamů, `forEach` v JS pro iteraci přes elementy).
*   **Pole:** Použito více než 3x (např. pro ukládání dat Pokémonů, seznamy typů, zpracování `$_POST`).
*   **Funkce:** Použito více než 3x (v PHP pro modularizaci logiky, v JS pro obsluhu událostí a UI).
