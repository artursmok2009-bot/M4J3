<?php
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'errors' => [],
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $imie = trim($_POST['imie'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $wiadomosc = trim($_POST['wiadomosc'] ?? '');


    if (empty($imie)) {
        $response['errors'][] = 'Imię jest wymagane.';
    } elseif (mb_strlen($imie) < 2) {
        $response['errors'][] = 'Imię musi zawierać minimum 2 znaki.';
    }

    if (empty($email)) {
        $response['errors'][] = 'Adres e-mail jest wymagany.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors'][] = 'Podany e-mail jest nieprawidłowy.';
    }

    if (empty($wiadomosc)) {
        $response['errors'][] = 'Wiadomość nie może być pusta.';
    }

    if (empty($response['errors'])) {
    
        $nazwaPliku = 'wiadomosci.json';

        $dotychczasoweWiadomosci = [];
        if (file_exists($nazwaPliku)) {
            $zawartoscPliku = file_get_contents($nazwaPliku);
            $dotychczasoweWiadomosci = json_decode($zawartoscPliku, true) ?? [];
        }

      
        $nowyWpis = [
            'data_wyslania' => date('Y-m-d H:i:s'),
            'imie' => $imie,
            'email' => $email,
            'wiadomosc' => $wiadomosc
        ];

        $dotychczasoweWiadomosci[] = $nowyWpis;

        
        file_put_contents(
            $nazwaPliku, 
            json_encode($dotychczasoweWiadomosci, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
       

        $response['success'] = true;
        $response['message'] = 'Dziękujemy, ' . htmlspecialchars($imie, ENT_QUOTES, 'UTF-8') . '! Twoja wiadomość została zapisana w pliku wiadomosci.json.';
    }

} else {
    $response['errors'][] = 'Niepoprawna metoda żądania.';
}


echo json_encode($response, JSON_UNESCAPED_UNICODE);