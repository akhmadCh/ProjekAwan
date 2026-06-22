CREATE TABLE `users` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `name` varchar(255), `username` varchar(255) UNIQUE, `email` varchar(255) UNIQUE, `password` varchar(255), `role` ENUM ('admin', 'customer'), `status` ENUM ('active', 'suspended'), `remember_token` varchar(255), `created_at` timestamp, `updated_at` timestamp );

CREATE TABLE `sessions` ( `id` varchar(255) PRIMARY KEY, `user_id` int, `ip_address` varchar(255), `payload` text, `last_activity` int );

CREATE TABLE `subscription_packages` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `name` varchar(255), `storage_limit_gb` float, `max_buckets` int, `vcpu_limit` int, `ram_limit_mb` int, `bandwidth_limit_gb` float, `price_per_month` decimal, `description` text, `is_active` boolean, `created_at` timestamp, `updated_at` timestamp );

CREATE TABLE `user_subscriptions` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `user_id` int, `package_id` int, `start_date` date, `end_date` date, `status` ENUM ('active', 'expired', 'cancelled'), `created_at` timestamp, `updated_at` timestamp );

CREATE TABLE `credentials` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `user_id` int UNIQUE, `access_key` varchar(255) UNIQUE, `secret_key` varchar(255), `created_at` timestamp, `updated_at` timestamp );

CREATE TABLE `resources` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `user_id` int, `user_subscription_id` int, `name` varchar(255), `type` ENUM ('storage\_bucket', 'compute\_metadata', 'network\_metadata'), `status` ENUM ('pending', 'running', 'stopped', 'failed'), `metadata` json, `created_at` timestamp, `updated_at` timestamp, `deleted_at` timestamp );

CREATE TABLE `buckets` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `resource_id` int UNIQUE, `bucket_name` varchar(255) UNIQUE, `storage_limit_gb` float, `used_storage_gb` float, `object_count` int, `created_at` timestamp, `updated_at` timestamp, `deleted_at` timestamp );

CREATE TABLE `objects` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `bucket_id` int, `object_key` varchar(255), `original_filename` varchar(255), `size_mb` float, `mime_type` varchar(255), `storage_path` varchar(255), `uploaded_at` timestamp, `deleted_at` timestamp );

CREATE TABLE `logs` ( `id` int PRIMARY KEY AUTO\_INCREMENT, `user_id` int, `action` varchar(255), `target_type` varchar(255), `target_id` int, `description` text, `logged_at` timestamp );

ALTER TABLE `sessions` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_subscriptions` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `user_subscriptions` ADD FOREIGN KEY (`package_id`) REFERENCES `subscription_packages` (`id`);

ALTER TABLE `credentials` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `resources` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `resources` ADD FOREIGN KEY (`user_subscription_id`) REFERENCES `user_subscriptions` (`id`);

ALTER TABLE `buckets` ADD FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`);

ALTER TABLE `objects` ADD FOREIGN KEY (`bucket_id`) REFERENCES `buckets` (`id`);

ALTER TABLE `logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
