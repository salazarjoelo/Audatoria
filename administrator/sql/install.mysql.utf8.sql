-- Tabla para las Líneas de Tiempo
CREATE TABLE IF NOT EXISTS `#__audatoria_timelines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
  `title` VARCHAR(255) NOT NULL,
  `alias` VARCHAR(400) NOT NULL DEFAULT '' COMMENT 'The SEF alias for the timeline.',
  `description` TEXT NULL,
  `state` TINYINT NOT NULL DEFAULT 0 COMMENT 'The state of the timeline. 1 = published, 0 = unpublished, 2 = archived, -2 = trashed.',
  `access` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'FK to the #__viewlevels table.',
  `created_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_time` DATETIME NULL DEFAULT NULL,
  `modified_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `modified_time` DATETIME NULL DEFAULT NULL,
  `checked_out` INT UNSIGNED NULL DEFAULT NULL,
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  `language` CHAR(7) NOT NULL DEFAULT '*' COMMENT 'The language code for the timeline.',
  `params` TEXT NULL COMMENT 'JSON format parameters.',
  `ordering` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_state_access` (`state`, `access`),
  INDEX `idx_created_user_id` (`created_user_id`),
  INDEX `idx_language` (`language`),
  INDEX `idx_alias` (`alias`(100)),
  INDEX `idx_checkout` (`checked_out`),
   KEY `idx_asset_id` (`asset_id`)
  -- CONSTRAINT `fk_timelines_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `#__assets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE -- Descomentar si manejas assets explícitamente
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Tabla para los Ítems de las Líneas de Tiempo
CREATE TABLE IF NOT EXISTS `#__audatoria_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
  `timeline_id` INT UNSIGNED NOT NULL COMMENT 'FK to the #__audatoria_timelines table.',
  `title` VARCHAR(255) NOT NULL,
  `description` MEDIUMTEXT NULL, -- Usar MEDIUMTEXT si la descripción puede ser larga con HTML
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `media_type` VARCHAR(50) NULL COMMENT 'e.g., youtube, image, vimeo, soundcloud, text, embed',
  `media_url` TEXT NULL,
  `media_caption` VARCHAR(255) NULL,
  `media_credit` VARCHAR(255) NULL,
  `lat` DECIMAL(10,8) NULL,
  `lng` DECIMAL(11,8) NULL,
  `location_name` VARCHAR(255) NULL,
  `external_source_id` VARCHAR(255) NULL COMMENT 'e.g., YouTube video ID',
  `state` TINYINT NOT NULL DEFAULT 0 COMMENT '1 = published, 0 = unpublished, 2 = archived, -2 = trashed.',
  `access` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'FK to the #__viewlevels table.',
  `created_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_time` DATETIME NULL DEFAULT NULL,
  `modified_user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `modified_time` DATETIME NULL DEFAULT NULL,
  `checked_out` INT UNSIGNED NULL DEFAULT NULL,
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  `language` CHAR(7) NOT NULL DEFAULT '*' COMMENT 'The language code for the item.',
  `ordering` INT NOT NULL DEFAULT 0,
  `params` TEXT NULL COMMENT 'JSON format parameters specific to the item.',
  PRIMARY KEY (`id`),
  INDEX `idx_timeline_id_state_start_date` (`timeline_id`, `state`, `start_date`),
  INDEX `idx_external_source_id` (`external_source_id`),
  INDEX `idx_created_user_id` (`created_user_id`),
  INDEX `idx_access` (`access`),
  INDEX `idx_language` (`language`),
  INDEX `idx_checkout` (`checked_out`),
  KEY `idx_asset_id` (`asset_id`),
  CONSTRAINT `fk_items_timeline_id`
    FOREIGN KEY (`timeline_id`)
    REFERENCES `#__audatoria_timelines` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
  -- CONSTRAINT `fk_items_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `#__assets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE -- Descomentar si manejas assets explícitamente
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Tabla para los Canales de YouTube Asociados
CREATE TABLE IF NOT EXISTS `#__audatoria_channels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
  `channel_id` VARCHAR(255) NOT NULL COMMENT 'YouTube Channel ID (e.g., UC...).',
  `title` VARCHAR(255) NULL COMMENT 'Optional friendly name for the channel.',
  `timeline_id` INT UNSIGNED NOT NULL COMMENT 'FK to the #__audatoria_timelines table to import videos into.',
  `state` TINYINT NOT NULL DEFAULT 0 COMMENT '1 = enabled for import, 0 = disabled.',
  `last_checked` DATETIME NULL COMMENT 'Timestamp of the last import check/run.',
  `created_time` DATETIME NULL DEFAULT NULL,
  `modified_time` DATETIME NULL DEFAULT NULL,
  `checked_out` INT UNSIGNED NULL DEFAULT NULL,
  `checked_out_time` DATETIME NULL DEFAULT NULL,
  `params` TEXT NULL COMMENT 'JSON format parameters specific to the channel import.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_channel_id_timeline_id` (`channel_id`, `timeline_id`) COMMENT 'Prevent duplicate channel assignments per timeline.',
  INDEX `idx_state` (`state`),
  INDEX `idx_checkout` (`checked_out`),
   KEY `idx_asset_id` (`asset_id`),
  CONSTRAINT `fk_channels_timeline_id`
    FOREIGN KEY (`timeline_id`)
    REFERENCES `#__audatoria_timelines` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
   -- CONSTRAINT `fk_channels_asset_id` FOREIGN KEY (`asset_id`) REFERENCES `#__assets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE -- Descomentar si manejas assets explícitamente
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;