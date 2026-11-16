CREATE DATABASE IF NOT EXISTS `helpdesk_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `helpdesk_db`;

DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `room` VARCHAR(20) NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('niski', 'sredni', 'wysoki') NOT NULL DEFAULT 'sredni',
  `status` ENUM('nowe', 'w_trakcie', 'zamkniete') NOT NULL DEFAULT 'nowe',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_tickets_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Minimum 4 kategorie usterek
INSERT INTO `categories` (`name`) VALUES
('Sprzęt komputerowy'),
('Sieć i Wi-Fi'),
('Oprogramowanie'),
('Drukarki i skanery'),
('Urządzenia multimedialne');

-- Minimum 5 przykładowych zgłoszeń o różnych statusach i priorytetach
INSERT INTO `tickets` (`category_id`, `room`, `title`, `description`, `priority`, `status`, `created_at`) VALUES
(1, 'Sala 204', 'Brak obrazu na monitorze stanowiska nr 5', 'Po włączeniu komputera jednostka pracuje, ale monitor wyświetla komunikat Brak sygnału (kabel HDMI sprawdzony).', 'wysoki', 'nowe', NOW() - INTERVAL 15 MINUTE),
(2, 'Pracownia 108', 'Brak dostępu do Internetu na stanowiskach uczniowskich', 'Karta sieciowa pokazuje sieć niezidentyfikowaną, brak przypisanego adresu IP z serwera DHCP.', 'wysoki', 'w_trakcie', NOW() - INTERVAL 2 HOUR),
(3, 'Sala 312', 'Wymagana aktualizacja pakietu XAMPP i PHP', 'Na stanowisku nauczycielskim zainstalowana jest starsza wersja PHP 7.4, potrzebna wersja PHP 8.2 do zajęć INF.03.', 'sredni', 'nowe', NOW() - INTERVAL 5 HOUR),
(4, 'Sekretariat', 'Zacięcie papieru i komunikat o niskim poziomie tonera', 'Drukarka laserowa HP wciąga po kilka kartek naraz oraz wymaga wymiany kasety z czarnym tonerem.', 'niski', 'w_trakcie', NOW() - INTERVAL 1 DAY),
(5, 'Aula', 'Brak dźwięku z projektora multimedialnego', 'Obraz z laptopa jest wyświetlany poprawnie, ale wbudowane głośniki nie odtwarzają dźwięku.', 'sredni', 'zamkniete', NOW() - INTERVAL 2 DAY),
(1, 'Pracownia 105', 'Uszkodzona klawiatura przy stanowisku nr 12', 'Nie działają klawisze Enter oraz Backspace, konieczna wymiana klawiatury na sprawną.', 'niski', 'zamkniete', NOW() - INTERVAL 3 DAY);