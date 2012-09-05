
-- ----------------------------
--  Table structure for `rssgoemail`
-- ----------------------------
DROP TABLE IF EXISTS `rssgoemail`;
CREATE TABLE `rssgoemail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `guid` varchar(255) NOT NULL,
  `description` text NOT NULL,
  KEY `id_index` (`id`)
);

