<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты анализа текста</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Результаты анализа текста</h2>
    
    <?php
    // Основная логика программы
    if(isset($_POST['data']) && $_POST['data']) { // если передан текст для анализа
        echo '<div class="src_text">' . htmlspecialchars($_POST['data']) . '</div>'; // выводим текст
        // перекодируем текст из UTF-8 в CP1251 перед анализом
        test_it(iconv("utf-8", "cp1251", $_POST['data'])); // анализируем текст
    } else { // если текста нет или он пустой
        echo '<div class="src_error">Нет текста для анализа</div>'; // выводим ошибку
    }
    
    // Функция для подсчета вхождений каждого символа в тексте (без учета регистра)
    function test_symbs($text) {
        $symbs = array(); // массив символов текста
        $l_text = strtolower($text); // переводим текст в нижний регистр
        
        // последовательно перебираем все символы текста
        for($i = 0; $i < strlen($l_text); $i++) {
            if(isset($symbs[$l_text[$i]])) { // если символ есть в массиве
                $symbs[$l_text[$i]]++; // увеличиваем счетчик повторов
            } else { // иначе
                $symbs[$l_text[$i]] = 1; // добавляем символ в массив
            }
        }
        
        return $symbs; // возвращаем массив с числом вхождений символов в тексте
    }
    
    // Основная функция анализа текста
    function test_it($text) {
        // Определяем ассоциированные массивы с группами символов
        $cifra = array( // цифры
            '0' => true, '1' => true, '2' => true, '3' => true, '4' => true,
            '5' => true, '6' => true, '7' => true, '8' => true, '9' => true
        );
        
        // русские буквы (строчные и заглавные в CP1251)
        $rus_lower = array();
        $rus_upper = array();
        // строчные русские буквы а-я (без ё)
        for($i = 224; $i <= 255; $i++) {
            if($i != 247) { // пропускаем символ №247
                $rus_lower[chr($i)] = true;
            }
        }
        // заглавные русские буквы А-Я (без Ё)
        for($i = 192; $i <= 223; $i++) {
            if($i != 215) { // пропускаем символ №215
                $rus_upper[chr($i)] = true;
            }
        }
        // добавляем букву "ё" в обоих регистрах (в CP1251)
        $rus_lower[chr(241)] = true; // ё
        $rus_upper[chr(240)] = true; // Ё
        
        // английские буквы
        $eng_lower = array();
        $eng_upper = array();
        for($i = 97; $i <= 122; $i++) { // a-z
            $eng_lower[chr($i)] = true;
        }
        for($i = 65; $i <= 90; $i++) { // A-Z
            $eng_upper[chr($i)] = true;
        }
        
        // знаки препинания (основные)
        $punct = array(
            '.' => true, ',' => true, '!' => true, '?' => true,
            ':' => true, ';' => true, '-' => true, '"' => true, "'" => true
        );
        
        // вводим переменные для хранения информации
        $cifra_amount = 0;          // количество цифр в тексте
        $punct_amount = 0;          // количество знаков препинания
        $lower_amount = 0;          // количество строчных букв
        $upper_amount = 0;          // количество заглавных букв
        $letter_amount = 0;         // количество всех букв
        $word_amount = 0;           // количество слов в тексте
        $word = '';                 // текущее слово
        $words = array();           // список всех слов
        
        // перебираем все символы текста
        for($i = 0; $i < strlen($text); $i++) {
            $current_char = $text[$i];
            
            // подсчет цифр
            if(array_key_exists($current_char, $cifra)) {
                $cifra_amount++;
            }
            
            // подсчет строчных букв
            if(array_key_exists($current_char, $rus_lower) || 
               array_key_exists($current_char, $eng_lower)) {
                $lower_amount++;
                $letter_amount++;
            }
            
            // подсчет заглавных букв
            if(array_key_exists($current_char, $rus_upper) || 
               array_key_exists($current_char, $eng_upper)) {
                $upper_amount++;
                $letter_amount++;
            }
            
            // подсчет знаков препинания
            if(array_key_exists($current_char, $punct)) {
                $punct_amount++;
            }
            
            // обработка слов (пробел)
            if($current_char == ' ' || $i == strlen($text) - 1) {
            // если это последний символ и не пробел
            if($i == strlen($text) - 1 && $current_char != ' ') {
                $word .= $current_char;
            }
            
            if($word != '') { // если есть текущее слово
                // переводим слово в нижний регистр для единообразия
                $word_lower = strtolower($word);
                // если текущее слово сохранено в списке слов
                if(isset($words[$word_lower])) {
                    $words[$word_lower]++; // увеличиваем число его повторов
                } else {
                    $words[$word_lower] = 1; // первый повтор слова
                }
                $word = ''; // сбрасываем текущее слово
            }
        } else { // если слово продолжается
            $word .= $current_char; // добавляем в текущее слово новый символ
        }
        }
        
        // Выводим основные результаты
        echo '<h3>Основная статистика:</h3>';
        echo '<table>';
        echo '<tr><th>Параметр</th><th>Значение</th></tr>';
        echo '<tr><td>Количество символов (включая пробелы)</td><td>' . strlen($text) . '</td></tr>';
        echo '<tr><td>Количество букв</td><td>' . $letter_amount . '</td></tr>';
        echo '<tr><td>Количество строчных букв</td><td>' . $lower_amount . '</td></tr>';
        echo '<tr><td>Количество заглавных букв</td><td>' . $upper_amount . '</td></tr>';
        echo '<tr><td>Количество знаков препинания</td><td>' . $punct_amount . '</td></tr>';
        echo '<tr><td>Количество цифр</td><td>' . $cifra_amount . '</td></tr>';
        echo '<tr><td>Количество слов</td><td>' . count($words) . '</td></tr>';
        echo '</table>';
        
        // 8. Количество вхождений каждого символа текста
        $symbs = test_symbs($text); // получаем массив символов
        ksort($symbs); // сортируем по ключам (символам)
        
        echo '<h3>Статистика символов:</h3>';
        echo '<table>';
        echo '<tr><th>Символ</th><th>Количество</th></tr>';

        if(count($symbs) > 0) {
            foreach($symbs as $char => $count) {
                // пропускаем непечатаемые символы
                if(ord($char) < 32) {
                    continue;
                }
                
                // ПРОСТО ВЫВОДИМ КАК У ПРЕПОДАВАТЕЛЯ
                echo '<tr>';
                echo '<td>' . htmlspecialchars(iconv("cp1251", "utf-8", $char)) . '</td>';
                echo '<td>' . $count . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="2">Текст не содержит символов</td></tr>';
        }
        echo '</table>';
        
        // 9. Список всех слов и количество их вхождений (отсортированный по алфавиту)
        ksort($words); // сортируем массив слов по алфавиту
        
        echo '<h3>Статистика слов:</h3>';
        echo '<table>';
        echo '<tr><th>Слово</th><th>Количество</th></tr>';
        
        if(count($words) > 0) {
            foreach($words as $word => $count) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars(iconv("cp1251", "utf-8", $word)) . '</td>';
                echo '<td>' . $count . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="2">В тексте не обнаружено слов</td></tr>';
        }
        echo '</table>';
    }
    ?>
    
    <br>
    <a href="index.html" class="back-link">Другой анализ</a>
</body>
</html>