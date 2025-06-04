INSERT INTO `auth_rule` (`name`, `data`, `created_at`, `updated_at`) VALUES
	('isOwner', _binary 0x4f3a31383a226170705c726261635c4f776e657252756c65223a343a7b733a343a226e616d65223b733a373a2269734f776e6572223b733a393a22637265617465644174223b693a313734373136323631373b733a393a22757064617465644174223b693a313734373136323631373b733a31303a226d6f64656c436c617373223b733a31393a226170705c6d6f64656c735c5065744f776e6572223b7d, 1747162617, 1747162617);
  
INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
	('petOwner', 2, 'Is owner of a pet', 'isOwner', NULL, 1747162617, 1747162617),
	('roleAdmin', 1, NULL, NULL, NULL, 1747162617, 1747162617),
	('roleSuperadmin', 1, NULL, NULL, NULL, 1747162617, 1747162617),
	('roleUser', 1, NULL, NULL, NULL, 1747162617, 1747162617);

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
	('roleAdmin', 'roleUser'),
	('roleSuperadmin', 'roleAdmin'),
	('roleUser', 'petOwner');

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
	('roleSuperadmin', '1', NULL);


