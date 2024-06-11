<?php

namespace JDS\Framework\Dbal;

use Doctrine\DBAL\Connection;
use JDS\Framework\Dbal\Events\PostPersist;
use JDS\Framework\EventDispatcher\EventDispatcher;

class DataMapper
{
	public function __construct(
		private Connection $connection,
		private EventDispatcher $eventDispatcher
	)
	{
	}

	public function getConnection(): Connection
	{
		return $this->connection;
	}

	public function save(Entity $subject): int|string|null
	{
		// dispatch postpersis event
		$this->eventDispatcher->dispatch(new PostPersist($subject));

		// return lastinsertid
		return $this->connection->lastInsertId();
	}

	public function getNewId(int $length = 12, bool $symbol = false): string
	{
		$id = "";
		$counter = 0;
		$gni = array();
		while (strlen($id) < $length) {
			$gni = $this->getRandomLetter($symbol, $counter);
			$counter = $gni['counter'];
			$id .= $gni['letter'];
			unset($gni);
			$gni = array();
		}
		return $id;
	}

	private function getRandomLetter(bool $symbol = false, int $cnt = 0): array
	{
		$letter = "";
		$lettNum = 4;
		switch ($this->getRandomValue(1, ($symbol ? 4 : 3))) { // change 3 to 4 when symbols are defined in case
			case 1:
				$letter = chr($this->getRandomValue(48, 57)); // number
				break;
			case 2:
				$letter = chr($this->getRandomValue(65, 90)); // upper case letter
				break;
			case 3:
				$letter = chr($this->getRandomValue(97, 122)); // lower case letter
				break;
			case 4:
				if ($cnt == 0) { // only allow 1 symbol
					$letter = ($symbol ? $this->getRandomSymbol(1, 7) : ""); // symbols
					$cnt++;
				}
				break;
		}
		return ['letter' => $letter, 'counter' => $cnt];
	}

	private function getRandomSymbol(int $min = 1, $max = 7): string
	{
		$symbol = "";
		switch ($this->getRandomValue($min, $max)) {
			case 1:
				$symbol = chr($this->getRandomValue(58, 64)); // : ; < = > ? @
				break;
			case 2:
				$symbol = chr(91); // [
				break;
			case 3:
				$symbol = chr($this->getRandomValue(93, 94)); // ] ^
				break;
			case 4:
				$symbol = chr(123); // {
				break;
			case 5:
				$symbol = chr($this->getRandomValue(125, 126)); // } ~
				break;
			case 6:
				$symbol = chr($this->getRandomValue(33, 38)); // ! " # $ % &
				break;
			case 7:
				$symbol = chr($this->getRandomValue(40, 47)); // ( ) * + , - . /
				break;
		}
		return $symbol;
	}

	private function getRandomValue(int $min = 1, int $max = 3): int
	{
		return mt_rand($min, $max);
	}

}