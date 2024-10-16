
--   Copyright (C) 2010-2012 Tobias Leupold <tobias.leupold@web.de>
--
--   This file is part of the b8 package
--
--   This program is free software; you can redistribute it and/or modify it
--   under the terms of the GNU Lesser General Public License as published by
--   the Free Software Foundation in version 2.1 of the License.
--
--   This program is distributed in the hope that it will be useful, but
--   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
--   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
--   License for more details.
--
--   You should have received a copy of the GNU Lesser General Public License
--   along with this program; if not, write to the Free Software Foundation,
--   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

CREATE TABLE `b8_wordlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  `count_ham` int unsigned default NULL,
  `count_spam` int unsigned default NULL,
  PRIMARY KEY (`token`),
  INDEX (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `b8_wordlist` (`token`, `count_ham`) VALUES ('b8*dbversion', '3');
INSERT INTO `b8_wordlist` (`token`, `count_ham`, `count_spam`) VALUES ('b8*texts', '0', '0');