--
-- Tabellenstruktur für Tabelle `ingredients`
--

CREATE TABLE `ingredients` (
  `uid` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `price` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `ingredients`
--

INSERT INTO `ingredients` (`uid`, `name`, `price`) VALUES
(1, 'Onion', 0.5),
(2, 'Garlic', 0.5),
(3, 'Mozzarella', 1),
(4, 'Mozzarella di Bufalla', 1.5),
(5, 'Olives', 1),
(6, 'Ham', 1),
(7, 'Funghi', 1),
(8, 'Anchovy', 1.5),
(9, 'Tuna', 1),
(10, 'Ruccola', 0.5),
(11, 'Corn', 0.5),
(12, 'Pineapple', 1),
(13, 'Vegan Cheese', 1.5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orders`
--

CREATE TABLE `orders` (
  `uid` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ingredients` varchar(100) NOT NULL,
  `time` int(11) NOT NULL,
  `remark` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Indizes für die Tabelle `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`uid`);

--
-- Indizes für die Tabelle `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`uid`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT für Tabelle `orders`
--
ALTER TABLE `orders`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
COMMIT;