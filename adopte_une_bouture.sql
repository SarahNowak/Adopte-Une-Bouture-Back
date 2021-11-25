-- Adminer 4.7.6 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plant_ads` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coordinates` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7EC9F62012469DE2` (`category_id`),
  CONSTRAINT `FK_7EC9F62012469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ads` (`id`, `plant_ads`, `city`, `coordinates`, `quantity`, `description`, `image`, `created_at`, `updated_at`, `category_id`) VALUES
(1,	'Alocasia Zebrina',	'Paris',	NULL,	2,	'je vends mes deux plantes d\'interieures',	NULL,	'2021-06-09 16:50:09',	NULL,	1),
(2,	'Aloe Vera',	'Lille',	NULL,	7,	'Deux petites boutures d\'Aloe Vera a donner avec beaucoup d\'amour',	NULL,	'2021-06-09 16:51:38',	NULL,	1),
(3,	'Tamaya',	'Marseille',	NULL,	3,	'A disposition des plantes Tamaya !!',	NULL,	'2021-06-09 16:52:59',	NULL,	1),
(4,	'Hetre',	'Tours',	NULL,	1,	'1 bel arbre a disposition\r\nAttention , prévoir pour le transport :)\r\n\r\nCet arbre est fortement rustique puisqu’il ne craint pas les températures chutant jusqu’à -30°C, ce qui veut donc dire qu’il pourra rester en terre dans toutes les régions de France, même celles réputées pour leurs hivers très froids. Il pourra être cultivé en isolé, ou encore en alignement pour former de belles haies très élégantes et colorées. En effet, ce Fagus sylvatica Dawyck gold possède un fort attrait esthétique que tous les jardiniers adorent de par son feuillage mais aussi sont port en forme de colonne qui le rend imposant et qui ne passe jamais inaperçu.\r\n',	NULL,	'2021-06-09 16:55:44',	NULL,	5),
(5,	'Bouleau Noir',	'Brest',	NULL,	1,	'Le bouleau est un arbre capable de résister à un long hiver, sans voir le jour et sous un froid terrible.\r\nLe bouleau noir est très attrayant par la couleur orangé teintée de noir de son écorce. Son port est souple, gracieux, et ses légères feuilles caduques se parent d\'or en automne.',	NULL,	'2021-06-09 16:56:37',	NULL,	5),
(6,	'Lierre Grimpant',	'Lagny',	NULL,	2,	'Superbe lierre grimpant offrant ses feuilles triangulaires vertes en toute saison. Vigoureux, il partira à l\'assaut de tous les supports mis à sa disposition : murs, treillis, gloriettes, grillages. Il pourra tout aussi bien cacher les surfaces inesthétiques, composer une belle haie colorée et persistante ou encore recouvrir le sol aux endroits difficiles à entretenir comme les talus, sous-bois à l\'ombre légère... Le lierre (Hedera helix) se plaît dans tout sol ordinaire mais restant frais même en été. il supporte très bien la taille et le froid jusqu\'à -7°C environ. Un superbe lierre vert foncé facile à cultiver, persistant et coloré, indispensable dans tous les jardins !',	NULL,	'2021-06-09 17:02:41',	NULL,	3),
(7,	'Chevrefeuille',	'Nantes',	NULL,	2,	'Superbe plante grimpante très appréciée pour son abondante floraison délicieusement parfumée durant tout l\'été. Culture facile en tout sol, même à mi-ombre, en prévoyant un support (treillis, grillage, tonnelle). Feuillage semi-persistant. ',	NULL,	'2021-06-09 17:03:34',	NULL,	3);

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `category` (`id`, `name`, `created_at`, `updated_at`, `status`) VALUES
(1,	'Plantes d\'intérieures',	'2021-06-09 16:42:14',	NULL,	1),
(3,	'Plantes Vivaces',	'2021-06-09 16:43:38',	NULL,	2),
(4,	'Plantes grimpantes',	'2021-06-09 16:44:37',	NULL,	3),
(5,	'Plantes Bulbeuses',	'2021-06-09 16:45:01',	NULL,	4),
(6,	'Arbres',	'2021-06-09 16:45:34',	NULL,	5),
(7,	'Arbustes',	'2021-06-09 16:45:44',	NULL,	6);

-- 2021-06-09 15:07:03
