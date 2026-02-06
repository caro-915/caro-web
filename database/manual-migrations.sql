-- Exécuter ces commandes SQL sur Laravel Cloud si les migrations n'ont pas été exécutées automatiquement
-- ⚠️ ATTENTION : Ces commandes sont pour dépannage uniquement. Utiliser 'php artisan migrate' est préférable.

-- Table search_histories
CREATE TABLE IF NOT EXISTS `search_histories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `marque` varchar(255) DEFAULT NULL,
  `modele` varchar(255) DEFAULT NULL,
  `price_max` int(11) DEFAULT NULL,
  `annee_min` int(11) DEFAULT NULL,
  `annee_max` int(11) DEFAULT NULL,
  `km_min` int(11) DEFAULT NULL,
  `km_max` int(11) DEFAULT NULL,
  `carburant` varchar(255) DEFAULT NULL,
  `wilaya` varchar(255) DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `search_histories_user_id_index` (`user_id`),
  KEY `search_histories_created_at_index` (`created_at`),
  CONSTRAINT `search_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table search_alerts
CREATE TABLE IF NOT EXISTS `search_alerts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `marque` varchar(255) DEFAULT NULL,
  `modele` varchar(255) DEFAULT NULL,
  `price_max` int(11) DEFAULT NULL,
  `annee_min` int(11) DEFAULT NULL,
  `annee_max` int(11) DEFAULT NULL,
  `km_min` int(11) DEFAULT NULL,
  `km_max` int(11) DEFAULT NULL,
  `carburant` varchar(255) DEFAULT NULL,
  `wilaya` varchar(255) DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `search_alerts_user_id_index` (`user_id`),
  KEY `search_alerts_is_active_index` (`is_active`),
  CONSTRAINT `search_alerts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vérifier que les migrations sont enregistrées
INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
('2026_02_06_105341_create_search_histories_table', (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations m)),
('2026_02_06_105939_create_search_alerts_table', (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations m));
