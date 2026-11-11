-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : pa65uk.myd.infomaniak.com
-- Généré le : mer. 11 nov. 2026 à 18:33
-- Version du serveur : 10.6.28-MariaDB-deb11-log
-- Version de PHP : 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pa65uk_chezbouy`
--

-- --------------------------------------------------------

--
-- Structure de la table `annuaires`
--

CREATE TABLE `annuaires` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `fonction` varchar(255) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `adresse_postale` text DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `annuaires`
--

INSERT INTO `annuaires` (`id`, `nom`, `fonction`, `telephone`, `mail`, `adresse_postale`, `observations`, `created_at`, `updated_at`) VALUES
(1, 'PINON Marie', 'Gardienne', '0784294991', NULL, NULL, NULL, '2026-11-02 16:18:31', '2026-11-09 17:53:58'),
(2, 'Caroline BORDIER', 'Conciergerie des Montenay', '0674495421', NULL, NULL, NULL, '2026-11-09 17:53:26', '2026-11-09 17:53:26');

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('chez-bouy-cache-082d302c3dcf88ffd95b0aa92423071aadd88a97', 'i:2;', 1793640187),
('chez-bouy-cache-082d302c3dcf88ffd95b0aa92423071aadd88a97:timer', 'i:1793640187;', 1793640187),
('chez-bouy-cache-09cb6e2bac9c80cec475d37e78ee289516140981', 'i:2;', 1794081508),
('chez-bouy-cache-09cb6e2bac9c80cec475d37e78ee289516140981:timer', 'i:1794081508;', 1794081508),
('chez-bouy-cache-23631b742bd16a798a1e2e4c3110ffaa8364a76c', 'i:2;', 1793640075),
('chez-bouy-cache-23631b742bd16a798a1e2e4c3110ffaa8364a76c:timer', 'i:1793640075;', 1793640075),
('chez-bouy-cache-3dd259ca7463553bb53c884cdb2bb59bb3043a12', 'i:2;', 1794417827),
('chez-bouy-cache-3dd259ca7463553bb53c884cdb2bb59bb3043a12:timer', 'i:1794417827;', 1794417827),
('chez-bouy-cache-4683e018a5163c7d729a09593840b659a01d1136', 'i:1;', 1794068943),
('chez-bouy-cache-4683e018a5163c7d729a09593840b659a01d1136:timer', 'i:1794068943;', 1794068943),
('chez-bouy-cache-6652f50850684e3e4e9227de921a01ba7befd3d5', 'i:2;', 1793557054),
('chez-bouy-cache-6652f50850684e3e4e9227de921a01ba7befd3d5:timer', 'i:1793557054;', 1793557054),
('chez-bouy-cache-7f1364e9bdf329addd27d70f529e9a5498e15367', 'i:2;', 1794051192),
('chez-bouy-cache-7f1364e9bdf329addd27d70f529e9a5498e15367:timer', 'i:1794051192;', 1794051192),
('chez-bouy-cache-825a054523923775f50e0f1e6f8ca2519efc8e95', 'i:2;', 1793992139),
('chez-bouy-cache-825a054523923775f50e0f1e6f8ca2519efc8e95:timer', 'i:1793992139;', 1793992139),
('chez-bouy-cache-8b3df776fa8b3fc9e61bf15323641dfb68e16df5', 'i:2;', 1793557140),
('chez-bouy-cache-8b3df776fa8b3fc9e61bf15323641dfb68e16df5:timer', 'i:1793557140;', 1793557140),
('chez-bouy-cache-admin-device:239b190784ae1f2cb5b09d49e52f65931f8ee5b9bdae02bdd5a38cd54ca4f66c', 'a:2:{s:7:\"user_id\";i:22;s:5:\"until\";i:1810056187;}', 1810056187),
('chez-bouy-cache-af08df1e33f66f4f6392a22c204b3e2c32ed10ee', 'i:2;', 1793474736),
('chez-bouy-cache-af08df1e33f66f4f6392a22c204b3e2c32ed10ee:timer', 'i:1793474736;', 1793474736),
('chez-bouy-cache-c9418206432f1c6f70b80e80556927c3b67f6fe2', 'i:2;', 1794250402),
('chez-bouy-cache-c9418206432f1c6f70b80e80556927c3b67f6fe2:timer', 'i:1794250402;', 1794250402),
('chez-bouy-cache-contact12c6fc06c99a462375eeb3f43dfd832b08ca9e17', 'i:1;', 1794076193),
('chez-bouy-cache-contact12c6fc06c99a462375eeb3f43dfd832b08ca9e17:timer', 'i:1794076193;', 1794076193),
('chez-bouy-cache-d456a97a812962df862ef817bf8b2684da6ad75b', 'i:2;', 1793818683),
('chez-bouy-cache-d456a97a812962df862ef817bf8b2684da6ad75b:timer', 'i:1793818683;', 1793818683),
('chez-bouy-cache-dab08afdbf7c28197d7b9fd58fbd7bf893a34dbb', 'i:2;', 1793815084),
('chez-bouy-cache-dab08afdbf7c28197d7b9fd58fbd7bf893a34dbb:timer', 'i:1793815084;', 1793815084),
('chez-bouy-cache-de2cd6765d98b32fa67e52bddeb2317882265543', 'i:1;', 1794134108),
('chez-bouy-cache-de2cd6765d98b32fa67e52bddeb2317882265543:timer', 'i:1794134108;', 1794134108),
('chez-bouy-cache-e96a0c7105bb38f0801bb514656096dfb6ed8524', 'i:2;', 1793894719),
('chez-bouy-cache-e96a0c7105bb38f0801bb514656096dfb6ed8524:timer', 'i:1793894719;', 1793894719),
('chez-bouy-cache-ec0c94c53642fa73e607ded94137ba2e6b233c6c', 'i:2;', 1793903116),
('chez-bouy-cache-ec0c94c53642fa73e607ded94137ba2e6b233c6c:timer', 'i:1793903116;', 1793903116),
('chez-bouy-cache-login-code:b652265992bc1599a899dfbd6e975542c205ff976ec52c7b0a9874f8c8e34f24:send', 'i:1;', 1794417828),
('chez-bouy-cache-login-code:b652265992bc1599a899dfbd6e975542c205ff976ec52c7b0a9874f8c8e34f24:send:timer', 'i:1794417828;', 1794417828),
('chez-bouy-cache-login-code:f61fdb81f3b460991045fa2e191c60332d5f2d8cc035fa15565997f01af1ee28:send', 'i:1;', 1793640187),
('chez-bouy-cache-login-code:f61fdb81f3b460991045fa2e191c60332d5f2d8cc035fa15565997f01af1ee28:send:timer', 'i:1793640187;', 1793640187);

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `files`
--

CREATE TABLE `files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `locale` varchar(20) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `files`
--

INSERT INTO `files` (`id`, `filename`, `created_at`, `updated_at`, `title`, `description`, `locale`, `purpose`) VALUES
(1, 'file/BBZls9VcnnQgVrrEK8VFdarF5mgu40gVJAbovIwM.pdf', '2026-11-01 12:50:31', '2026-11-01 12:50:31', 'Livret d\'accueil', NULL, 'fr', 'welcome_guide'),
(2, 'file/SdwedVzzBonwLHHhoKQ5HrPkzIG0ecqN5NTo5yhG.pdf', '2026-11-02 16:20:47', '2026-11-02 17:46:59', 'Guida du benvenuto', NULL, 'it', 'welcome_guide'),
(3, 'file/c20GMzoFa6fVy4o8HaC3nHXo4kjVvU2WtKmaw0Og.pdf', '2026-11-02 17:46:40', '2026-11-02 17:46:40', 'Welcome guide', NULL, 'en', 'welcome_guide');

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_27_000001_add_role_to_users', 1),
(5, '2026_10_29_174115_add_login_statistics_to_users', 1),
(6, '2026_10_30_000001_add_profile_fields_to_users', 1),
(7, '2026_10_31_163603_create_photo_tables', 1),
(8, '2026_11_01_112615_create_file_tables', 2),
(9, '2026_11_01_114109_simplify_file_metadata', 2),
(10, '2026_11_01_114900_create_notes_table', 2),
(11, '2026_11_01_115659_create_annuaires_table', 2),
(12, '2026_11_01_121707_add_author_id_to_notes_table', 2),
(13, '2026_11_02_174909_define_page_photo_locations', 3),
(14, '2026_11_02_180252_add_purpose_to_files_table', 3),
(15, '2026_11_03_170358_create_stay_sections_tables', 4),
(16, '2026_11_04_172534_localize_stay_section_photos', 4),
(17, '2026_11_05_180219_add_original_filename_to_photos_table', 5);

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `author_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notes`
--

INSERT INTO `notes` (`id`, `title`, `description`, `created_at`, `updated_at`, `author_id`) VALUES
(1, 'protège oreiller', 'prévoir d\'en acheter ; vérifier les tailles', '2026-11-02 16:17:39', '2026-11-02 16:17:39', 22);

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--

CREATE TABLE `photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `photos`
--

INSERT INTO `photos` (`id`, `filename`, `published`, `created_at`, `updated_at`, `original_filename`) VALUES
(12, 'photos/1fcb306e-c410-480e-98bc-d18b9dba1291.webp', 1, '2026-11-02 17:41:25', '2026-11-02 17:41:25', NULL),
(13, 'photos/526970ae-08ef-4d68-af7e-d5419b1627b2.webp', 1, '2026-11-02 17:43:31', '2026-11-02 17:43:31', NULL),
(14, 'photos/9512d35a-d2ff-4d2e-af16-70e94fb03a1e.webp', 1, '2026-11-06 18:09:25', '2026-11-06 18:09:25', 'Chez BOUY chambre vue 1.jpg'),
(15, 'photos/0e61c86d-a895-41b3-ac89-aee5b16c9758.webp', 1, '2026-11-06 18:11:02', '2026-11-06 18:11:02', 'Chez BOUY pièce principale 2.jpg'),
(16, 'photos/bc45b208-f938-4479-807e-3152eccff4cb.webp', 1, '2026-11-06 18:11:58', '2026-11-06 18:11:58', 'Chez BOUY pièce principale 1.jpg'),
(17, 'photos/a14e7c32-c92e-4a19-80af-aae5845722d5.webp', 1, '2026-11-06 18:16:44', '2026-11-06 18:16:44', 'Chez BOUY balcon 1.jpg'),
(18, 'photos/8b0b0b2d-390a-4822-a7b3-019e903750f0.webp', 1, '2026-11-06 18:20:14', '2026-11-06 18:20:14', 'Chez BOUY vue depuis le balcon 4.jpg'),
(19, 'photos/3f687682-33bf-47d8-8eab-fa1b926f439e.webp', 1, '2026-11-06 18:21:15', '2026-11-06 18:21:15', 'Chez BOUY vue depuis le balcon 3.jpg'),
(20, 'photos/daaed47a-f122-4bd8-84a3-e30e0eacac93.webp', 1, '2026-11-06 18:25:03', '2026-11-06 18:25:03', 'Chamonix rue centre.jpg'),
(21, 'photos/68b74ef1-b7dc-47d4-8041-abb4e47d4cb9.webp', 1, '2026-11-07 10:40:45', '2026-11-07 10:40:45', 'plan.png'),
(22, 'photos/346996d2-9494-4060-937b-bc475e89a829.webp', 1, '2026-11-08 10:09:10', '2026-11-08 10:09:10', 'arrivee_parking_FR.png'),
(24, 'photos/802f712f-75a7-4d62-946f-156091a50265.webp', 1, '2026-11-08 10:10:09', '2026-11-08 10:10:09', 'Arrivee_parking_EN.png'),
(25, 'photos/0bdab86f-d326-4916-8ab4-6b34185b758d.webp', 1, '2026-11-08 10:10:42', '2026-11-08 10:10:42', 'Entree_immeuble_FR.png'),
(26, 'photos/f188a46c-5b3e-4c0e-9478-c1fb17c70580.webp', 1, '2026-11-08 10:11:33', '2026-11-08 10:11:33', 'Entree_immeuble_IT.png'),
(27, 'photos/22cc96ec-1baa-4df3-977c-98152386e3e9.webp', 1, '2026-11-08 10:12:10', '2026-11-08 10:12:10', 'Entree_immeuble_EN.png'),
(28, 'photos/c88051a4-d946-491f-9e36-9db0129a2758.webp', 1, '2026-11-08 10:35:44', '2026-11-08 10:35:44', 'Arrivee_parking_IT.png'),
(29, 'photos/5a31ec48-314a-4994-81a1-ae51a43780dc.webp', 1, '2026-11-08 10:45:00', '2026-11-08 10:45:00', 'Chez BOUY accès clef.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `photo_location`
--

CREATE TABLE `photo_location` (
  `photo_id` bigint(20) UNSIGNED NOT NULL,
  `photo_location_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `photo_location`
--

INSERT INTO `photo_location` (`photo_id`, `photo_location_id`, `position`) VALUES
(13, 14, 1),
(16, 14, 2),
(15, 14, 3),
(14, 14, 4),
(17, 14, 7),
(18, 14, 8),
(19, 14, 9),
(21, 14, 14),
(12, 14, 15),
(20, 14, 16);

-- --------------------------------------------------------

--
-- Structure de la table `photo_locations`
--

CREATE TABLE `photo_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `route_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `photo_locations`
--

INSERT INTO `photo_locations` (`id`, `route_name`, `name`, `created_at`, `updated_at`) VALUES
(14, 'home', 'Accueil', '2026-11-02 17:39:29', '2026-11-02 17:39:29'),
(15, 'stay.arrivee', 'Arrivée', '2026-11-02 17:39:29', '2026-11-02 17:39:29'),
(16, 'stay.installation', 'Installation', '2026-11-02 17:39:29', '2026-11-02 17:39:29'),
(17, 'stay.appartement', 'Séjour', '2026-11-02 17:39:29', '2026-11-02 17:39:29'),
(18, 'stay.depart', 'Départ', '2026-11-02 17:39:29', '2026-11-02 17:39:29'),
(19, 'stay.chamonix', 'Chamonix', '2026-11-02 17:39:29', '2026-11-02 17:39:29');

-- --------------------------------------------------------

--
-- Structure de la table `photo_stay_section`
--

CREATE TABLE `photo_stay_section` (
  `photo_id` bigint(20) UNSIGNED NOT NULL,
  `stay_section_id` bigint(20) UNSIGNED NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `locale` varchar(2) NOT NULL DEFAULT 'fr'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `photo_stay_section`
--

INSERT INTO `photo_stay_section` (`photo_id`, `stay_section_id`, `position`, `locale`) VALUES
(22, 2, 0, 'fr'),
(24, 2, 0, 'en'),
(25, 2, 1, 'fr'),
(26, 2, 1, 'it'),
(27, 2, 1, 'en'),
(28, 2, 0, 'it'),
(29, 3, 0, 'en'),
(29, 3, 0, 'fr'),
(29, 3, 0, 'it');

-- --------------------------------------------------------

--
-- Structure de la table `photo_translations`
--

CREATE TABLE `photo_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `photo_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `photo_translations`
--

INSERT INTO `photo_translations` (`id`, `photo_id`, `locale`, `title`, `description`) VALUES
(6, 12, 'fr', 'Mont-Blanc et Aiguille du Midi', NULL),
(7, 12, 'en', 'Mont-Blanc and Aiguille du Midi', NULL),
(8, 12, 'it', 'Monte Bianco ed Aiguille du Midi', NULL),
(9, 13, 'fr', 'La résidences d\'Aiguilles du Brévent', NULL),
(10, 13, 'en', 'Residence Les Aiguilles du Brévent', NULL),
(11, 13, 'it', 'Residenza Les Aiguilles du Brévent', NULL),
(12, 14, 'fr', 'La chambre', NULL),
(13, 14, 'en', 'The bedroom', NULL),
(14, 14, 'it', 'La camera del letto', NULL),
(15, 15, 'fr', 'La pièce principale', NULL),
(16, 15, 'en', 'The living-room', NULL),
(17, 15, 'it', 'Il soggiorno', NULL),
(18, 16, 'fr', 'La pièce principale', NULL),
(19, 16, 'en', 'The living-room', NULL),
(20, 16, 'it', 'Il soggiorno', NULL),
(21, 17, 'fr', 'Le balcon', NULL),
(22, 17, 'en', 'The balcony', NULL),
(23, 17, 'it', 'Il balcone', NULL),
(24, 18, 'fr', 'Vue depuis le balcon (été)', NULL),
(25, 18, 'en', 'View from the balcony (summer)', NULL),
(26, 18, 'it', 'Vista dal balcone (estate)', NULL),
(27, 19, 'fr', 'Vue depuis le balcon (hiver)', NULL),
(28, 19, 'en', 'View from the balcony (winter)', NULL),
(29, 19, 'it', 'Vista dal balcone (inverno)', NULL),
(30, 20, 'fr', 'Chamonix centre ville', NULL),
(31, 20, 'en', 'Chamonix town centre', NULL),
(32, 20, 'it', 'Centro di Chamonix', NULL),
(33, 21, 'fr', 'Plan de l\'appartement', NULL),
(34, 21, 'en', 'Apartment floor plan', NULL),
(35, 21, 'it', 'Pianta dell\'appartamento', NULL),
(36, 22, 'fr', 'Arrivée dans la résidence', NULL),
(37, 22, 'en', 'Arrival at the residence', NULL),
(38, 22, 'it', 'Arrivo al residence', NULL),
(42, 24, 'fr', 'Arrivée dans la résidence', NULL),
(43, 24, 'en', 'Arrival at the residence', NULL),
(44, 24, 'it', 'Arrivo al residence', NULL),
(45, 25, 'fr', 'Entrée de l\'immeuble', NULL),
(46, 25, 'en', 'How to enter the residence ?', NULL),
(47, 25, 'it', 'Ingresso del palazzo', NULL),
(48, 26, 'fr', 'Comment entrer dans la résidence ?', NULL),
(49, 26, 'en', 'How to enter the residence ?', NULL),
(50, 26, 'it', 'Ingresso del palazzo', NULL),
(51, 27, 'fr', 'Entrée de l\'immeuble', NULL),
(52, 27, 'en', 'How to enter the residence ?', NULL),
(53, 27, 'it', 'Ingresso del palazzo', NULL),
(54, 28, 'fr', 'Arrivée dans la résidence', NULL),
(55, 28, 'en', 'Arrival at the residence', NULL),
(56, 28, 'it', 'Arrivo al residence', NULL),
(57, 29, 'fr', 'Récupérer les clefs', NULL),
(58, 29, 'en', 'Collect the keys', NULL),
(59, 29, 'it', 'Ritirare le chiavi', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('RJE6Ae6avrHYQvQxaB86a7RzQDlSBXWUu7ReU5bS', 22, '2a01:cb16:43:aeff:0:42:8b54:a01', 'Mozilla/5.0 (Android 17; Mobile; rv:159.0) Gecko/159.0 Firefox/159.0', 'eyJfdG9rZW4iOiJWQkJGVHFtNDBTeUNkTEszdmU2UDdWRGtBMkZHWWhFTDFDVDMxS3N6IiwibG9jYWxlIjoiZnIiLCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvY2hlemJvdXkubWljaGVsYW5nZS5vcmdcL3Bob3Rvc1wvMTJcL2ltYWdlIiwicm91dGUiOiJob21lLnBob3Rvcy5pbWFnZSJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MjIsInBhc3N3b3JkX2hhc2hfd2ViIjoiYzBlOTM1YzkyZTY1MjBiYmY0MDVmZjJlYjI3YjZiNjIwYzhiNmRlNTgzMTg3N2NkYjdiMTJkMWFjN2EwMGMzNiIsImFkbWluX2RldmljZV91bnRpbCI6MTgxMDA1NjE4N30=', 1794417798),
('TG4f8NSONTRzzSCaxyD5TKoCB3W3TekR7Z4Vxxft', NULL, '45.157.188.125', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI5ajZjQkNPVUM0RG4zZmVxbmY3MGpqMmRkTGxVU0VnTjBaQVBFREwwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9jaGV6Ym91eS5taWNoZWxhbmdlLm9yZ1wvcGhvdG9zXC8xN1wvaW1hZ2UiLCJyb3V0ZSI6ImhvbWUucGhvdG9zLmltYWdlIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1794413180),
('UBCit99ULE6M68ID74FdXxuvt1v1Zar0FtsVw80a', NULL, '10.92.195.210', 'GuzzleHttp/7', 'eyJfdG9rZW4iOiI5T1NxVTkzY0lxZ3lROWZQM0MyNG9Ua0VWcFdHTEZKRzE0OEVvbmxPIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1794413180);

-- --------------------------------------------------------

--
-- Structure de la table `stay_sections`
--

CREATE TABLE `stay_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page` varchar(255) NOT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  `translations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`translations`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stay_sections`
--

INSERT INTO `stay_sections` (`id`, `page`, `position`, `published`, `translations`, `created_at`, `updated_at`) VALUES
(1, 'arrivee', 1, 1, '{\"fr\":{\"title\":\"Arriver dans la r\\u00e9sidence\",\"body\":\"Notre appartement est dans une r\\u00e9sidence appel\\u00e9e \\u00ab\\u00a0**les Aiguilles du Br\\u00e9vent**\\u00a0\\u00bb, \\u00e0 l\\u2019adresse postale suivante\\u00a0:\\r\\n&nbsp;&nbsp;&nbsp;&nbsp;***90 route Henriette d\\u2019Angeville\\r\\n&nbsp;&nbsp;&nbsp;&nbsp;74400 Chamonix Mont Blanc***\\r\\n\\r\\n##     a. En transports\\r\\n> **Par Gen\\u00e8ve**\\u00a0: rejoindre Gen\\u00e8ve par TGV ou tout autre moyen. Prendre ensuite une navette. Plusieurs op\\u00e9rateurs en proposent, voir sur leur site respectif. Attention, certains partent de l\\u2019a\\u00e9roport de Gen\\u00e8ve.\\r\\n>**Par Bellegarde**\\u00a0: rejoindre la gare de Bellegarde sur Valserine par TGV puis prendre l\\u2019autocar jusqu\\u2019\\u00e0 Chamonix (assez fastidieux\\u2026).\\r\\n\\r\\n##      b. En voiture\\r\\nD\\u2019une mani\\u00e8re g\\u00e9n\\u00e9rale, des autoroutes (A6 puis A40 selon le lieu de d\\u00e9part) et une voie rapide permettent d\\u2019arriver jusque dans Chamonix. Une fois arriv\\u00e9s, il y a deux possibilit\\u00e9s pour rejoindre l\\u2019appartement\\u00a0: \\r\\n> **En direct par la route dite \\u00ab\\u00a0la Mollard\\u00a0\\u00bb**. Attention, cette route est tr\\u00e8s pentue\\u2026\\r\\n> **En faisant un mini d\\u00e9tour** et en passant par \\u00ab\\u00a0le chalet h\\u00f4tel la Sapini\\u00e8re\\u00a0\\u00bb, rue Mummery.\"},\"en\":{\"title\":\"Arrival at the residence\",\"body\":\"Our apartment is in a residence called \\u201c**Les Aiguilles du Br\\u00e9vent**\\u201d, at the following address:\\r\\n&nbsp;&nbsp;&nbsp;***90 route Henriette d\\u2019Angeville\\r\\n&nbsp;&nbsp;&nbsp;74400 Chamonix Mont Blanc***\\r\\n##     a. By public transport\\r\\n>**Via Geneva:** travel to Geneva by TGV or any other means, then take a shuttle. Several companies offer this service; check their websites. Please note that some leave from Geneva Airport.\\r\\n>**Via Bellegarde:** take the TGV to Bellegarde-sur-Valserine station, then a coach to Chamonix (a rather long-winded journey\\u2026).\\r\\n##     b. By car\\r\\nMotorways (the A6, then the A40, depending on where you start) and a dual carriageway take you all the way to Chamonix. Once there, you have two options for reaching the apartment:\\r\\n>**Take the direct route** via the road known as \\u201cLa Mollard\\u201d. Be warned: it is very steep!\\r\\n> **Take a short detour** past \\u201cChalet H\\u00f4tel La Sapini\\u00e8re\\u201d, on rue Mummery.\"},\"it\":{\"title\":\"Come arrivare\",\"body\":\"Il nostro appartamento si trova nel residence \\u00ab **Les Aiguilles du Br\\u00e9vent** \\u00bb, al seguente indirizzo:\\r\\n&nbsp;&nbsp;&nbsp;&nbsp;***90 route Henriette d\\u2019Angeville\\r\\n&nbsp;&nbsp;&nbsp;&nbsp;74400 Chamonix Mont Blanc***\\r\\n##     a. Con i mezzi pubblici\\r\\n>**Via Ginevra**: raggiungete Ginevra in TGV o con un altro mezzo, poi prendete una navetta. Diverse compagnie offrono questo servizio: consultate i rispettivi siti. Attenzione, alcune partono dall\\u2019aeroporto di Ginevra.\\r\\n>**Via Bellegarde**: raggiungete la stazione di Bellegarde-sur-Valserine in TGV, poi prendete l\\u2019autobus per Chamonix (un viaggio piuttosto laborioso\\u2026).\\r\\n##     b. In auto\\r\\nLe autostrade (A6 e poi A40, a seconda della provenienza) e una superstrada permettono di arrivare fino a Chamonix. Una volta in citt\\u00e0, ci sono due possibilit\\u00e0 per raggiungere l\\u2019appartamento:\\r\\n>**Prendere la strada diretta** detta \\u00ab La Mollard \\u00bb. Attenzione: \\u00e8 molto ripida!\\r\\n>**Fare una piccola deviazione** passando davanti allo \\u00ab Chalet H\\u00f4tel La Sapini\\u00e8re \\u00bb, in rue Mummery.\"}}', '2026-11-04 16:58:46', '2026-11-04 17:58:50'),
(2, 'arrivee', 2, 1, '{\"fr\":{\"title\":\"Entrer dans la r\\u00e9sidence\",\"body\":\"L\\u2019entr\\u00e9e se fait par le 90 de la route Henriette d\\u2019Angeville (une seule entr\\u00e9e).\\r\\n\\u00c0 votre arriv\\u00e9e, vous pouvez **temporairement** vous garer sur le petit parking, le temps de sortir vos bagages et de r\\u00e9cup\\u00e9rer la t\\u00e9l\\u00e9commande du garage dans l\\u2019appartement pour pouvoir ensuite lib\\u00e9rer la place.\\r\\nIl y a 3 entr\\u00e9es dans l\\u2019immeuble :\\r\\n> - L\\u2019entr\\u00e9e principale sous digicode en bas des marches\\r\\n> - L\\u2019entr\\u00e9e par le local \\u00e0 skis sous digicode en bas des marches\\r\\n> - L\\u2019entr\\u00e9e par le local \\u00e0 skis sous digicode sur le c\\u00f4t\\u00e9 gauche de l\\u2019immeuble, en allant vers le garage\\r\\n\\r\\n**Le code est le m\\u00eame pour les 3 digicodes, il vous sera communiqu\\u00e9 avant votre arriv\\u00e9e.**\"},\"en\":{\"title\":\"How to enter the residence ?\",\"body\":\"The entrance is at 90 route Henriette d\\u2019Angeville (there is only one entrance). You can park **briefly** in the small car park, for example while unloading your luggage.\\r\\nThere are 3 entrances to the building:\\r\\n>- The main entrance, with a keypad, at the bottom of the steps\\r\\n>- The ski-room entrance, with a keypad, at the bottom of the steps\\r\\n>- The ski-room entrance, with a keypad, on the left-hand side of the building as you head towards the garage\\r\\n\\r\\n**All 3 entrance keypads use the same code**. We will send it to you in good time.\"},\"it\":{\"title\":\"Ingresso nel palazzo\",\"body\":\"L\\u2019ingresso si trova al numero 90 di route Henriette d\\u2019Angeville (c\\u2019\\u00e8 un solo accesso). Potete sostare **temporaneamente** nel piccolo parcheggio, ad esempio per scaricare i bagagli.\\r\\nCi sono 3 ingressi nell\\u2019edificio:\\r\\n>- L\\u2019ingresso principale, con tastierino numerico, in fondo ai gradini\\r\\n>- L\\u2019ingresso del deposito sci, con tastierino numerico, in fondo ai gradini\\r\\n>- L\\u2019ingresso del deposito sci, con tastierino numerico, sul lato sinistro dell\\u2019edificio, andando verso il garage\\r\\n\\r\\n**Il codice \\u00e8 lo stesso per i tastierini dei 3 ingressi**. Ve lo comunicheremo a tempo debito.\"}}', '2026-11-04 17:04:38', '2026-11-08 11:00:21'),
(3, 'arrivee', 3, 1, '{\"fr\":{\"title\":\"R\\u00e9cup\\u00e9rer les cl\\u00e9s de l\'appartement\",\"body\":\"Rejoindre le local \\u00e0 skis, directement en bas de marches de l\\u2019entr\\u00e9e et chercher le casier \\u00ab BOUY \\u00bb. La boite \\u00e0 cl\\u00e9s se trouvent \\u00e0 l\\u2019int\\u00e9rieur. \\r\\nLe code vous sera aussi fourni avant votre arriv\\u00e9e.\\r\\nL\\u2019appartement se trouve au premier \\u00e9tage, \\u00e0 droite en sortant de l\\u2019ascenseur.\"},\"en\":{\"title\":\"Collecting the apartment keys\",\"body\":\"Go to the ski room, directly at the bottom of the entrance steps, and look for the locker marked \\u201cBOUY\\u201d. The key safe is inside. We will also send you its code in good time.\\r\\nThe apartment is on the first floor, on your right as you leave the lift.\"},\"it\":{\"title\":\"Ritirare le chiavi di casa\",\"body\":\"Andate al deposito sci, direttamente in fondo ai gradini dell\\u2019ingresso, e cercate l\\u2019armadietto \\u00ab BOUY \\u00bb. La cassetta portachiavi si trova al suo interno. Anche questo codice vi sar\\u00e0 comunicato a tempo debito.\\r\\nL\\u2019appartamento si trova al primo piano, a destra uscendo dall\\u2019ascensore.\"}}', '2026-11-04 17:08:51', '2026-11-04 18:05:50'),
(4, 'arrivee', 4, 1, '{\"fr\":{\"title\":\"Garer sa voiture\",\"body\":\"Vous disposez d\\u2019un emplacement pour une voiture dans le garage couvert et ferm\\u00e9 qui se trouve derri\\u00e8re l\\u2019immeuble.\\r\\nPour ouvrir le garage, vous devrez utiliser la t\\u00e9l\\u00e9commande qui est accroch\\u00e9e dans le couloir face \\u00e0 la cuisine avec\\r\\ntoutes les clefs de l\\u2019appartement. \\r\\nLa place est au fond et \\u00e0 gauche du garage. Notre nom \\u00ab BOUY \\u00bb est marqu\\u00e9 contre le mur. \\r\\nMerci de respecter imp\\u00e9rativement l\\u2019emplacement et de ne pas occuper un autre emplacement m\\u00eame vacant.\"},\"en\":{\"title\":\"Park your car\",\"body\":\"You have a parking space in the covered, enclosed garage located behind the building.\\r\\nTo open the garage, use the remote control attached to the key set hanging in the hallway opposite the kitchen.\\r\\nYour parking space is at the far end of the garage, on the left. Our name, \\\"BOUY\\\", is marked on the wall.\\r\\n**Please make sure to use only this designated parking space** and do not park in any other space, even if it is vacant.\"},\"it\":{\"title\":\"Parccheggiare l\'auto\",\"body\":\"Avete a disposizione un posto auto nel garage coperto e chiuso situato dietro l\'edificio.\\r\\nPer aprire il garage, dovrete utilizzare il telecomando attaccato al mazzo di chiavi dell\'appartamento, appeso nel corridoio di fronte alla cucina.\\r\\nIl vostro posto auto si trova in fondo al garage, sulla sinistra. Il nostro cognome, \\u00ab BOUY \\u00bb, \\u00e8 indicato sul muro.\\r\\n**Vi preghiamo di utilizzare esclusivamente il posto auto assegnato** e di non occupare nessun altro posto, anche se libero.\"}}', '2026-11-05 15:06:26', '2026-11-08 10:59:04'),
(5, 'installation', 1, 1, '{\"fr\":{\"title\":\"L\\u2019appartement est strictement non-fumeur\",\"body\":\"Mais il est possible de fumer sur le balcon \\u00e0 condition d\\u2019y laisser le cendrier et de le vider avant votre d\\u00e9part directement dans les bennes \\u00e0 ordures (cf. gestion des d\\u00e9chets).\"},\"en\":{\"title\":null,\"body\":null},\"it\":{\"title\":null,\"body\":null}}', '2026-11-05 15:12:18', '2026-11-05 15:12:18'),
(6, 'installation', 2, 1, '{\"fr\":{\"title\":\"Ouverture et fermeture de la porte\",\"body\":\"Pour entrer dans l\\u2019appartement, il suffit d\\u2019utiliser la cl\\u00e9 r\\u00e9cup\\u00e9r\\u00e9e dans la boite \\u00e0 cl\\u00e9.\\r\\nPour sortir de l\\u2019appartement et fermer \\u00e0 cl\\u00e9, il faut:\\r\\n>- enclencher le petit bouton poussoir qui se trouve au milieu de la poign\\u00e9e\\r\\n>- sortir de l\\u2019appartement\\r\\n>- claquer doucement la porte\\r\\n>- NE PAS OUBLIER LA CL\\u00c9 !\"},\"en\":{\"title\":null,\"body\":null},\"it\":{\"title\":null,\"body\":null}}', '2026-11-05 15:16:08', '2026-11-05 15:16:08'),
(7, 'installation', 3, 1, '{\"fr\":{\"title\":\"Eau et \\u00e9lectricit\\u00e9\",\"body\":\"Avant votre arriv\\u00e9e nous vous pr\\u00e9viendrons pour vous dire si l\\u2019eau, l\\u2019\\u00e9lectricit\\u00e9 et le chauffage sont en route.\\r\\nSi ce n\\u2019est pas le cas, il faut ouvrir les robinets d\\u2019eau et enclencher le disjoncteur. \\r\\nVoici la proc\\u00e9dure pour le faire\\r\\n## Ouverture \\/ fermeture de l\\u2019eau\\r\\nDans le couloir, il y a un placard \\u00e0 gauche lorsqu\\u2019on regarde notre porte d\\u2019entr\\u00e9e d\\u2019appartement. Ouvrir les 2 robinets \\u00e0 notre nom \\u00ab BOUY \\u00bb pour avoir de l\\u2019eau froide et de l\'eau chaude.\\r\\n## Ouverture \\/ fermeture du disjoncteur principal \\u00e9lectrique\\r\\nIl faut entrer dans l\\u2019appartement et ouvrir le placard de l\\u2019entr\\u00e9e. Eclairer avec son t\\u00e9l\\u00e9phone portable. Remonter le disjoncteur.\"},\"en\":{\"title\":null,\"body\":null},\"it\":{\"title\":null,\"body\":null}}', '2026-11-05 15:21:34', '2026-11-05 15:21:34'),
(8, 'installation', 4, 1, '{\"fr\":{\"title\":\"Chauffage\",\"body\":\"Toutes les pi\\u00e8ces sont \\u00e9quip\\u00e9es de radiateurs avec des vannes thermostatiques.\"},\"en\":{\"title\":null,\"body\":null},\"it\":{\"title\":null,\"body\":null}}', '2026-11-05 15:25:35', '2026-11-05 15:25:35'),
(9, 'installation', 5, 1, '{\"fr\":{\"title\":\"Internet et WiFi\",\"body\":\"Une Livebox vous fournit un acc\\u00e8s internet via le Wi-Fi.\\r\\nPlusieurs fa\\u00e7ons de s\\u2019y connecter :\\r\\n>- Soit appuyer sur le bouton WPS de la livebox,\\r\\n>-  Soit flasher le qr-code suivant ci-dessous. \\r\\n>- Soit rentrer les informations suivantes:\\r\\n*Login* : Cumulus\\r\\n*Mot de passe* : maisouestdoncornicar\"},\"en\":{\"title\":null,\"body\":null},\"it\":{\"title\":null,\"body\":null}}', '2026-11-05 15:30:24', '2026-11-05 15:30:24'),
(10, 'installation', 6, 1, '{\"fr\":{\"title\":\"Tri des ordures m\\u00e9nag\\u00e8res\",\"body\":\"Tous les d\\u00e9chets doivent \\u00eatre tri\\u00e9s. \\r\\nVous avez diff\\u00e9rents points de collecte :\\r\\n- En sortant de la r\\u00e9sidence, vous traversez la route Henriette d\\u2019Angeville en\\r\\nallant l\\u00e9g\\u00e8rement sur la droite. A c\\u00f4t\\u00e9 de l\\u2019arr\\u00eat de bus, vous trouverez des\\r\\nbacs pour :\\r\\no Le verre\\r\\no Les emballages, cartons, papier, journaux\\r\\no Les ordures m\\u00e9nag\\u00e8res restantes\\r\\n- Dans Chamonix pour les d\\u00e9chets compostables. Il existe un point d\\u2019apport\\r\\nvolontaire \\u00e0 c\\u00f4t\\u00e9 du Majestic. En descendant la Mollard, tournez \\u00e0 droite au niveau de la gendarmerie. Le composteur se trouve sous des arbres, \\u00e0 c\\u00f4t\\u00e9 de l\\u2019ancien palace le Majestic.\\r\\n- Deux d\\u00e9chetteries existent aussi :\\r\\no La d\\u00e9chetterie du Closy \\u00e0 Chamonix, avenue Cachat le G\\u00e9ant. Y aller en voiture, se renseigner sur les\\r\\nhoraires pr\\u00e9alablement. Ils prennent aussi des v\\u00eatements en sacs bien ferm\\u00e9s.\\r\\no La d\\u00e9chetterie du Bocher aux Houches, route de Bocher. Y aller en voiture et v\\u00e9rifier les horaires.\\r\\nCette d\\u00e9chetterie poss\\u00e8de une recyclerie. Attention, les horaires sont diff\\u00e9rents. A v\\u00e9rifier avant d\\u2019y aller.\\r\\n- Enfin pour le textile (la d\\u00e9chetterie de Bocher ne les prend pas), vous pouvez aussi aller \\u00e0 Cham\\u2019Aide, 21 avenue du Savoy dans Chamonix. Y passer avant pour v\\u00e9rifier les horaires et ce qu\\u2019ils r\\u00e9cup\\u00e8rent.\"},\"en\":{\"title\":null,\"body\":null},\"it\":{\"title\":null,\"body\":null}}', '2026-11-05 15:33:23', '2026-11-05 15:33:23'),
(11, 'home', 1, 1, '{\"fr\":{\"title\":\"Un appartement confortable\",\"body\":\"Il s\'agit d\'un appartement familial situ\\u00e9 au premier \\u00e9tage de la r\\u00e9sidence \\\"Les Aiguilles du Br\\u00e9vent\\\". \\r\\nL\'appartement comprend:\\r\\n>- Une pi\\u00e8ce principale avec un couchage (canap\\u00e9 clic-clac)\\r\\n>- Une chambre avec deux lits en 90 qui peuvent \\u00eatre accol\\u00e9s pour faire un grand lit\\r\\n>- Une cuisine avec des plaques vitroc\\u00e9ramiques et un combin\\u00e9 four\\/micro-onde\\r\\n>- Une salle de bains avec une baignoire\\r\\n>- Un WC s\\u00e9par\\u00e9.\\r\\n\\r\\nUn petit balcon offre une vue sur l\'Aiguille du midi et le Mont-blanc.\\r\\n\\r\\nUn acc\\u00e8s au WiFi est disponible\\r\\nVous disposerez d\'une **place de stationnement r\\u00e9serv\\u00e9e et couverte** dans un parking ferm\\u00e9.\\r\\nUn local \\u00e0 ski est disponible dans l\'entr\\u00e9e de l\'immeuble et vous pourrez d\\u00e9poser vos ski dans un placard r\\u00e9serv\\u00e9.\"},\"en\":{\"title\":\"A comfortable apartment\",\"body\":\"This is a family apartment located on the first floor of the \\\"Les Aiguilles du Br\\u00e9vent\\\" residence.\\r\\nThe apartment consists of:\\r\\n> - A living room with a sofa bed (click-clack)\\r\\n> - A bedroom with two 90 cm single beds that can be pushed together to make a double bed\\r\\n> - A kitchen equipped with a ceramic hob and a combined oven\\/microwave\\r\\n> - A bathroom with a bathtub\\r\\n> - A separate toilet\\r\\n> -A small balcony offers views of the Aiguille du Midi and Mont Blanc.\\r\\n\\r\\nWi-Fi access is available.\\r\\nYou will have a **reserved, covered parking space** in a secure, enclosed car park.\\r\\nA ski room is available in the building entrance, where you can store your skis in a dedicated locker.\"},\"it\":{\"title\":\"Un appartamento confortevole\",\"body\":\"Si tratta di un appartamento familiare situato al primo piano del residence \\\"Les Aiguilles du Br\\u00e9vent\\\".\\r\\nL\'appartamento \\u00e8 composto da:\\r\\n> - Un soggiorno con un divano letto (tipo clic-clac)\\r\\n> - Una camera da letto con due letti singoli da 90 cm, che possono essere accostati per formare un letto matrimoniale\\r\\n> - Una cucina con piano cottura in vetroceramica e forno combinato con microonde\\r\\n> - Un bagno con vasca\\r\\n> - Un WC separato\\r\\n> - Un piccolo balcone offre una vista sull\'Aiguille du Midi e sul Monte Bianco.\\r\\n\\r\\n\\u00c8 disponibile la connessione Wi-Fi.\\r\\nAvrete a disposizione **un posto auto riservato e coperto** in un parcheggio chiuso e sicuro.\\r\\nAll\'ingresso dell\'edificio \\u00e8 disponibile un deposito sci, dove potrete riporre i vostri sci in un armadietto riservato.\"}}', '2026-11-07 15:37:16', '2026-11-08 09:38:32'),
(13, 'home', 3, 1, '{\"fr\":{\"title\":\"Une localisation id\\u00e9ale\",\"body\":\"## Ski et randonn\\u00e9es\\r\\nPour les **skieurs d\\u00e9butants** ou les enfants, la piste de la C\\u00f4te se situe juste derri\\u00e8re la r\\u00e9sidence.\\r\\nPour les **skieurs confirm\\u00e9s**, le t\\u00e9l\\u00e9cabine du Br\\u00e9vent est \\u00e0 moins d\'une centaine de m\\u00e8tres de l\'appartement. Ce t\\u00e9l\\u00e9cabine vous donne acc\\u00e8s \\u00e0 un vaste domaine gr\\u00e2ce \\u00e0 la liaison Br\\u00e9vent-Fl\\u00e9g\\u00e8re.\\r\\nPour ceux qui veulent explorer l\'ensemble du domaine skiable de la vall\\u00e9e, **un arr\\u00eat de bus** se trouve juste \\u00e0 c\\u00f4t\\u00e9 de la r\\u00e9sidence.\\r\\nDe nombreuses ballades de fond de vall\\u00e9e peuvent se faire en partant directement de l\'appartement. **Pour les randonn\\u00e9es en montagne**, le t\\u00e9l\\u00e9ph\\u00e9rique de l\'Aiguille du midi ou le t\\u00e9l\\u00e9cabine du Br\\u00e9vent sont accessible \\u00e0 pied. Pour les autres randonn\\u00e9es, le r\\u00e9seaux de bus et m\\u00eame le train qui remonte la vall\\u00e9e vous permettront de superbes d\\u00e9couvertes. \\r\\n## Centre ville\\r\\nEn une dizaine de minutes \\u00e0 pied vous rejoindrez le centre ville de Chamonix toujours anim\\u00e9 et vous permettant de faire vos courses, de vous attablez \\u00e0 une terrasse ou simplement de d\\u00e9ambuler dans les rues pi\\u00e9tonnes.\\r\\n\\r\\n**En conclusion, m\\u00eame sans voiture, tout vous sera accessible !**\"},\"en\":{\"title\":\"An ideal location\",\"body\":\"## Skiing and hiking\\r\\nFor **beginner skiers** and children, the C\\u00f4te ski run is located just behind the residence.\\r\\nFor **experienced skiers**, the Br\\u00e9vent gondola lift is less than a hundred metres from the apartment. It provides access to a vast ski area thanks to the Br\\u00e9vent-Fl\\u00e9g\\u00e8re link.\\r\\nFor those wishing to explore the entire ski area of the valley, **a bus stop** is located right next to the residence.\\r\\nThere are plenty of walks along the valley floor that you can enjoy directly from the apartment. **For mountain hikes**, the Aiguille du Midi cable car or the Br\\u00e9vent gondola lift are both within walking distance. For other hiking routes, the bus network and even the train running up the valley will allow you to discover some wonderful places.\\r\\n\\r\\n## Town centre\\r\\nA ten-minute walk will take you to the lively centre of Chamonix, where you can do your shopping, enjoy a drink or a meal on a terrace, or simply wander through the pedestrian streets.\\r\\n\\r\\n**In short, even without a car, everything is within easy reach!**\"},\"it\":{\"title\":\"An ideal location\",\"body\":\"## Sci ed escursioni\\r\\nPer gli **sciatori principianti** e i bambini, la pista della C\\u00f4te si trova proprio dietro il residence.\\r\\nPer gli **sciatori esperti**, la cabinovia del Br\\u00e9vent si trova a meno di cento metri dall\'appartamento. Permette di accedere a un vasto comprensorio sciistico grazie al collegamento Br\\u00e9vent-Fl\\u00e9g\\u00e8re.\\r\\nPer chi desidera esplorare l\'intero comprensorio sciistico della valle, **una fermata dell\'autobus** si trova proprio accanto al residence.\\r\\nNumerose passeggiate sul fondovalle possono essere fatte partendo direttamente dall\'appartamento. **Per le escursioni in montagna**, la funivia dell\'Aiguille du Midi o la cabinovia del Br\\u00e9vent sono raggiungibili a piedi. Per gli altri itinerari escursionistici, la rete degli autobus e persino il treno che risale la valle vi permetteranno di scoprire luoghi meravigliosi.\\r\\n\\r\\n## Centro citt\\u00e0\\r\\nIn una decina di minuti a piedi raggiungerete il vivace centro di Chamonix, dove potrete fare acquisti, accomodarvi a un tavolino all\'aperto o semplicemente passeggiare per le vie pedonali.\\r\\n\\r\\n**In conclusione, anche senza automobile, tutto \\u00e8 facilmente raggiungibile!**\"}}', '2026-11-07 15:52:18', '2026-11-08 09:42:03');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'locataire',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `login_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `first_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `last_login_at`, `login_count`, `first_name`, `is_active`) VALUES
(22, 'BOUY', 'michelange@wanadoo.fr', NULL, '$2y$12$zuu/KVagaLsHH40r1g0zjOwxu.OCaAbHTBU6vhc.jI.YCaCwQUUOC', NULL, '2026-10-31 18:05:19', '2026-11-11 16:23:07', 'admin', '2026-11-11 16:23:07', 18, 'Michel', 1),
(23, 'BOUY-DEBEC', 'd.bouydebec@gmail.com', NULL, '$2y$12$OCj5EukybZdUgUAnxbcgG.xINOTjVN10CyR1TK4MSPfzjtxTaCESa', NULL, '2026-11-02 16:15:29', '2026-11-02 16:22:23', 'admin', '2026-11-02 16:22:23', 1, 'DOMINIQUE', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annuaires`
--
ALTER TABLE `annuaires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Index pour la table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `files_locale_purpose_unique` (`locale`,`purpose`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notes_author_id_foreign` (`author_id`);

--
-- Index pour la table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Index pour la table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `photo_location`
--
ALTER TABLE `photo_location`
  ADD PRIMARY KEY (`photo_id`,`photo_location_id`),
  ADD UNIQUE KEY `photo_location_photo_id_unique` (`photo_id`),
  ADD KEY `photo_location_photo_location_id_position_index` (`photo_location_id`,`position`);

--
-- Index pour la table `photo_locations`
--
ALTER TABLE `photo_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photo_locations_code_unique` (`route_name`);

--
-- Index pour la table `photo_stay_section`
--
ALTER TABLE `photo_stay_section`
  ADD PRIMARY KEY (`photo_id`,`stay_section_id`,`locale`),
  ADD KEY `photo_stay_section_stay_section_id_foreign` (`stay_section_id`),
  ADD KEY `photo_stay_section_photo_id_index` (`photo_id`);

--
-- Index pour la table `photo_translations`
--
ALTER TABLE `photo_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photo_translations_photo_id_locale_unique` (`photo_id`,`locale`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `stay_sections`
--
ALTER TABLE `stay_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stay_sections_page_position_index` (`page`,`position`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annuaires`
--
ALTER TABLE `annuaires`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `files`
--
ALTER TABLE `files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `photo_locations`
--
ALTER TABLE `photo_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `photo_translations`
--
ALTER TABLE `photo_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT pour la table `stay_sections`
--
ALTER TABLE `stay_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `photo_location`
--
ALTER TABLE `photo_location`
  ADD CONSTRAINT `photo_location_photo_id_foreign` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `photo_location_photo_location_id_foreign` FOREIGN KEY (`photo_location_id`) REFERENCES `photo_locations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `photo_stay_section`
--
ALTER TABLE `photo_stay_section`
  ADD CONSTRAINT `photo_stay_section_photo_id_foreign` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `photo_stay_section_stay_section_id_foreign` FOREIGN KEY (`stay_section_id`) REFERENCES `stay_sections` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `photo_translations`
--
ALTER TABLE `photo_translations`
  ADD CONSTRAINT `photo_translations_photo_id_foreign` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
