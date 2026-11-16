CREATE DATABASE IF NOT EXISTS `smart_office`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smart_office`;

DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `resources`;

CREATE TABLE `resources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL,
  `type` ENUM('desk', 'room') NOT NULL,
  `capacity` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reservations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `resource_id` INT NOT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_reservations_resource`
    FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `resources` (`name`, `type`, `capacity`) VALUES
('Sala Konferencyjna Alfa', 'room', 8),
('Sala Zarządu Beta', 'room', 12),
('Biurko A1', 'desk', 1),
('Biurko A2', 'desk', 1),
('Biurko A3', 'desk', 1),
('Biurko B1', 'desk', 1),
('Biurko B2', 'desk', 1),
('Biurko B3', 'desk', 1);

INSERT INTO `reservations` (`resource_id`, `user_name`, `start_time`, `end_time`) VALUES
(1, 'Anna Nowak', CONCAT(CURDATE() + INTERVAL 1 DAY, ' 09:00:00'), CONCAT(CURDATE() + INTERVAL 1 DAY, ' 11:00:00')),
(3, 'Jan Kowalski', CONCAT(CURDATE() + INTERVAL 1 DAY, ' 10:00:00'), CONCAT(CURDATE() + INTERVAL 1 DAY, ' 14:00:00'));