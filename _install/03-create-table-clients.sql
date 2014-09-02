CREATE TABLE IF NOT EXISTS `oauther`.`clients` (
 `client_no` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing no of each appKey, unique index',
 `client_id` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'appKey hash',
 `client_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'client''s name',
 `client_redirect_uri` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'client''s redirect URL',
 `client_scope` varchar(256) COLLATE utf8_unicode_ci NOT NULL COMMENT 'client''s scope',
 `client_display` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'client''s display',
 `client_account_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'appKey type (basic, premium, etc)',
 `client_creation_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of the creation of appKey',
 `client_last_login_timestamp` bigint(20) DEFAULT NULL COMMENT 'timestamp of last use',
 PRIMARY KEY (`client_no`),
 UNIQUE KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='client data';
