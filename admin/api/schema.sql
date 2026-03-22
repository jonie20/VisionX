-- ═══════════════════════════════════════════════
--  VisionX Admin — Database Schema
--  File: admin/api/schema.sql
--
--  HOW TO INSTALL:
--  1. cPanel → MySQL Databases → create DB + user
--  2. phpMyAdmin → select your DB → SQL tab
--  3. Paste this file → click Go
--  4. Edit admin/api/db.php with your credentials
--  5. Visit /admin/login.php  (admin / visionx2025)
-- ═══════════════════════════════════════════════

SET NAMES utf8mb4;

-- ── Admin users ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(60)   NOT NULL UNIQUE,
  `password_hash` VARCHAR(255)  NOT NULL,
  `name`          VARCHAR(100)  NOT NULL DEFAULT 'Admin',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default login: admin / visionx2025
-- Hash is bcrypt of "visionx2025" — CHANGE AFTER FIRST LOGIN
INSERT IGNORE INTO `admin_users` (`username`, `password_hash`, `name`) VALUES
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMUdfufOmvZ9YvFqn4iF3J0Mua', 'Admin');

-- ── Gallery ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `gallery` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(200)  NOT NULL,
  `brand`       VARCHAR(60)   NOT NULL DEFAULT '',
  `area`        VARCHAR(80)   NOT NULL DEFAULT '',
  `appliance`   ENUM('fridge','washing-machine','microwave','freezer') NOT NULL DEFAULT 'fridge',
  `status`      ENUM('after','before') NOT NULL DEFAULT 'after',
  `img_path`    VARCHAR(300)  NOT NULL DEFAULT '',
  `img_alt`     VARCHAR(300)  NOT NULL DEFAULT '',
  `fault`       VARCHAR(150)  NOT NULL DEFAULT '',
  `description` TEXT,
  `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
  `active`      TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_active` (`active`),
  KEY `idx_sort`   (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `gallery`
  (`title`,`brand`,`area`,`appliance`,`status`,`fault`,`description`,`sort_order`) VALUES
('Samsung Fridge Gas Refill – Westlands','Samsung','Westlands','fridge','after',
 'Not Cooling – Gas Refill','Samsung fridge stopped cooling. Refrigerant recharged same-day in Westlands.',1),
('LG Washing Machine UE Error – Kilimani','LG','Kilimani','washing-machine','after',
 'UE Error – Drum Bearing','Drum bearing replaced. Spin cycle fully restored in Kilimani.',2),
('Von Hotpoint Chest Freezer – Embakasi','Von Hotpoint','Embakasi','freezer','after',
 'Not Freezing – Relay Replaced','Compressor relay replaced. Restored to -18°C in Embakasi.',3);

-- ── Reviews ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `author`      VARCHAR(80)   NOT NULL,
  `area`        VARCHAR(80)   NOT NULL DEFAULT '',
  `rating`      TINYINT       NOT NULL DEFAULT 5,
  `body`        TEXT          NOT NULL,
  `status`      ENUM('approved','pending','hidden') NOT NULL DEFAULT 'pending',
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `reviews` (`author`,`area`,`rating`,`body`,`status`) VALUES
('James M.','Westlands',5,'Samsung fridge stopped cooling on Friday evening. VisionX came Saturday, refilled the gas and it\'s been perfect ever since. Highly recommend!','approved'),
('Aisha K.','Kilimani',5,'My LG washing machine had the UE error. Technician diagnosed and fixed it in under an hour. Very professional and honest with pricing.','approved'),
('Peter N.','Karen',5,'Called at 8pm for emergency fridge repair. They were at my door by 10pm. Incredible service — saved all my food!','approved'),
('Grace W.','Embakasi',5,'Bosch washing machine drum bearing replaced. Fair price, great workmanship. 90-day warranty gave me confidence.','approved'),
('David O.','Lavington',5,'They repaired my Von Hotpoint chest freezer that other repairers said was beyond repair. Brilliant technicians.','approved'),
('Mary A.','Parklands',4,'Quick response, good communication, fixed microwave same day. Slightly pricey but worth it for the peace of mind.','approved');

-- ── FAQs ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `faqs` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `question`    VARCHAR(400)  NOT NULL,
  `answer`      TEXT          NOT NULL,
  `sort_order`  SMALLINT      NOT NULL DEFAULT 0,
  `active`      TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `faqs` (`question`,`answer`,`sort_order`) VALUES
('How much does fridge repair cost in Nairobi?','Fridge repair in Nairobi typically costs KSh 1,500–8,000. Gas refill is KSh 3,500–6,500. Free diagnosis before quoting.',1),
('Do you offer same-day appliance repair in Nairobi?','Yes — call before noon for same-day fridge, washing machine or microwave repair across all Nairobi areas.',2),
('Which brands do you repair in Nairobi?','Samsung, LG, Bosch, Whirlpool, Von Hotpoint, Ramtons, Hisense and all major brands.',3),
('Is there a warranty on your repairs?','Yes — every VisionX repair carries a 90-day parts and labour warranty.',4),
('Do you repair commercial fridges in Nairobi?','Yes — restaurants, supermarkets, hospitals and offices across Nairobi.',5),
('How do I book a repair?','Call +254 797 340 140 or WhatsApp us with your area, appliance and fault. We often arrange same-day visits.',6);