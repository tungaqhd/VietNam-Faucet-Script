CREATE TABLE `history` (
  `id` int(32) UNSIGNED NOT NULL,
  `owner` varchar(10) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `amount` int(20) NOT NULL,
  `last_claim` int(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `link` (
  `address` varchar(75) NOT NULL,
  `linkkey` varchar(75) NOT NULL,
  `ip` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE `users` (
  `id` int(32) NOT NULL,
  `address` varchar(50) NOT NULL,
  `ip_address` varchar(150) NOT NULL,
  `balance` int(20) NOT NULL,
  `last_active` int(32) NOT NULL,
  `last_claim` int(32) NOT NULL,
  `joined` int(32) NOT NULL,
  `bot` varchar(50) NOT NULL,
  `referred_by` int(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `history`
  MODIFY `id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `users`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;