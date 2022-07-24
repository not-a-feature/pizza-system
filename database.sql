--
-- Structure of table `ingredients`
--
DROP TABLE IF EXISTS `ingredients`;

CREATE TABLE `ingredients` (
  `uid` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `price` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Data of table `ingredients`
--

INSERT INTO `ingredients` (`uid`, `name`, `price`) VALUES
(1, 'Onion', 0.5),
(2, 'Garlic', 0.5),
(3, 'Mozzarella', 1),
(4, 'Mozzarella di Bufalla', 1.5),
(5, 'Olives', 1),
(6, 'Ham', 1),
(7, 'Funghi', 0.5),
(8, 'Anchovy', 1.5),
(9, 'Ruccola', 0.5),
(10, 'Corn', 0.5),
(11, 'Pineapple', 1),
(12, 'Vegan Mozzarella', 1),
(13, 'Regular Cheese', 0.5),
(14, 'Salami', 1);

-- --------------------------------------------------------

--
-- Structure of table `orders`
--

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `uid` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ingredients` varchar(100) NOT NULL,
  `time` int(11) NOT NULL,
  `remark` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Primary Key `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`uid`);

--
-- Primary Key `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;