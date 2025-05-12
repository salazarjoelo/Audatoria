CREATE TABLE IF NOT EXISTS `#__audatoria_timelines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `created_by` INT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `#__audatoria_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `timeline_id` INT NOT NULL,
  `title` VARCHAR(255),
  `description` TEXT,
  `start_date` DATETIME,
  `end_date` DATETIME,
  `media_type` VARCHAR(50),
  `media_url` TEXT,
  `lat` DECIMAL(10,8),
  `lng` DECIMAL(11,8),
  `location_name` VARCHAR(255),
  `external_source_id` VARCHAR(255),
  `created_by` INT,
  `published` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`timeline_id`) REFERENCES `#__audatoria_timelines`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `#__audatoria_channels` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `channel_id` VARCHAR(255),
  `title` VARCHAR(255),
  `timeline_id` INT NOT NULL,
  `enabled` TINYINT(1) DEFAULT 1,
  `last_checked` DATETIME,
  FOREIGN KEY (`timeline_id`) REFERENCES `#__audatoria_timelines`(`id`) ON DELETE CASCADE
);