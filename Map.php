<?php

declare(strict_types=1);

class Map {

	private const START = '1';
	private const FULL = 'X';
	private const SPACE = '_';
	private const EXIT = 'E';

	private array $map;
	private int $sizeX, $sizeY;
	private int $exitX, $exitY;
	private int $startX, $startY;

	public function __construct(
		array $map,
		int $startX = null,
		int $startY = null,
		int $exitX = null,
		int $exitY = null
	) {

		$this->map = $map;
		$this->sizeX = count($map);
		$this->sizeY = count($map[0]);

		// INIT START POINT
		// ~~~~~~~~~~~~~~~~

		// Точка старта, когда она есть, удаляется с карты,
		// а при отрисовке она накладывается сверху

		if ($startPoint = self::findStart($this)) {
			$this->setPoint($startPoint, self::SPACE);
		}

		[$x, $y] = $startPoint;
		$startX ??= $x;
		$startY ??= $y;

		if ($startX === null || $startY === null) {
			throw new RuntimeException('Start position undefined');
		}

		$this->setStart($startX, $startY);

		// INIT EXIT POINT
		// ~~~~~~~~~~~~~~~

		if ($exitPoint = self::findExit($this)) {
			$this->setPoint($exitPoint, self::SPACE); // remove EXIT point
		}

		[$x, $y] = $exitPoint;
		$exitX ??= $x;
		$exitY ??= $y;

		if ($exitX === null || $exitY === null) {
			throw new RuntimeException('Exit position undefined');
		}

		$this->setExit($exitX, $exitY);
		$this->setPoint($this->getExit(), self::EXIT); // setup EXIT point

		// VALIDATION
		// ~~~~~~~~~~

		if ($this->getStart() === $this->getExit()) {
			throw new RuntimeException('Start position can not be equals exit position');
		}
	}

	private function setPoint(array $point, string $mapValue): void {
		[$x, $y] = $point;
		$this->map[$x][$y] = $mapValue;
	}

	public static function findStart(self $me): ?array {
		foreach ($me->map as $i => $row) {
			foreach ($row as $j => $value) {
				if ($value === self::START) {
					return [$i, $j];
				}
			}
		}
		return null;
	}

	public static function findExit(self $me): ?array {
		foreach ($me->map as $i => $row) {
			foreach ($row as $j => $value) {
				if ($value === self::EXIT) {
					return [$i, $j];
				}
			}
		}
		return null;
	}

	public function getSize(): array {
		return [$this->sizeX, $this->sizeY];
	}

	public function getStart(): array {
		return [$this->startX, $this->startY];
	}

	public function setStart(int $x, int $y): void {
		$this->startX = $x;
		$this->startY = $y;
	}

	public function getExit(): array {
		return [$this->exitX, $this->exitY];
	}

	public function setExit(int $x, int $y): void {
		$this->exitX = $x;
		$this->exitY = $y;
	}

	public function renderHumanizedView(): string {

		$m = $this->map;
		$m[$this->startX][$this->startY] = self::START; // override cell

		$myMap = [];
		foreach ($m as $i => $row) {
			foreach ($row as $j => $cell) {
				$myMap[$j][$i] = $cell;
			}
		}
		$myMap = array_reverse($myMap);
		return $this->renderMap($myMap);
	}

	private function renderMap(array $map): string {

		$s = '';
		$rows = $map;
		foreach ($rows as $cols) {
			$s .= ' ' . implode(' ', $cols) . ' ';
			$s .= PHP_EOL;
		}
		$frontLine = str_repeat('-', 2 * count($rows[0]) + 1) . PHP_EOL;
		return $frontLine . $s . $frontLine;
	}

	private function isExit(int $x, int $y): bool {
		return $this->map[$x][$y] === self::EXIT;
	}

	/**
	 * Проверяем выход по какому либо направлению
	 *
	 * @param int $x
	 * @param int $y
	 * @return array|null
	 */
	private function checkExit(int $x, int $y): ?array {

		$i = $y + 1;
		while ($this->moveable($x, $i)) {
			if ($this->isExit($x, $i)) {
				return [$x, $i];
			}
			$i++;
		}

		$i = $y - 1;
		while ($this->moveable($x, $i)) {
			if ($this->isExit($x, $i)) {
				return [$x, $i];
			}
			$i--;
		}

		$i = $x - 1;
		while ($this->moveable($i, $y)) {
			if ($this->isExit($i, $y)) {
				return [$i, $y];
			}
			$i--;
		}

		$i = $x + 1;
		while ($this->moveable($i, $y)) {
			if ($this->isExit($i, $y)) {
				return [$i, $y];
			}
			$i++;
		}

		return null;
	}

	private function moveable($x, $y): bool {
		return isset($this->map[$x][$y]) && $this->map[$x][$y] !== self::FULL;
	}

	/**
	 * Находим возможные горизонтали от заданной точки
	 */
	private function findAxesOfX(int $x, int $y): array {

		$min = $y;
		$max = $y;

		$i = $y - 1;
		while ($this->moveable($x, $i)) {
			$min = $i;
			$i--;
		}

		$i = $y + 1;
		while ($this->moveable($x, $i)) {
			$max = $i;
			$i++;
		}

		return [$min, $max];
	}

	/**
	 * Находим возможные вертикали от заданной точки
	 */
	private function findAxesOfY(int $x, int $y): array {

		$min = $x;
		$max = $x;

		$i = $x - 1;
		while ($this->moveable($i, $y)) {
			$min = $i;
			$i--;
		}

		$i = $x + 1;
		while ($this->moveable($i, $y)) {
			$max = $i;
			$i++;
		}

		return [$min, $max];
	}

	/**
	 * Находим все горизонтали
	 */
	private function getAllAxesX(int $X, int $Y): array {

		$axes = [];
		[$y1, $y2] = $this->findAxesOfX($X, $Y);
		for ($n = $y1; $n <= $y2; $n++) {
			[$a, $b] = $this->findAxesOfY($X, $n);
			if (($b - $a) > 0) {
				$axes[] = [$n, $a, $b];
			}
		}

		// и сортируем их по количеству вертикальных пересечений на них
		//uasort($axes, static fn($a, $b) => ($a[2] - $a[1]) < ($b[2] - $b[1]));

		// сортируем линии по удалённости от заданной точки
		usort($axes, static function (array $axis1, array $axis2) use ($Y) {
			return abs($Y - $axis1[0]) - abs($Y - $axis2[0]);
		});

		return $axes;
	}

	/**
	 * Находим все вертикали
	 */
	private function getAllAxesY(int $X, int $Y): array {

		$axes = [];
		[$x1, $x2] = $this->findAxesOfY($X, $Y);
		for ($n = $x1; $n <= $x2; $n++) {
			[$a, $b] = $this->findAxesOfX($n, $Y);
			if (($b - $a) > 0) {
				$axes[] = [$n, $a, $b];
			}
		}

		// и сортируем их по количеству горизонтальных пересечений
		//uasort($axes, static fn($a, $b) => ($a[2] - $a[1]) < ($b[2] - $b[1]));

		// сортируем линии по удалённости от заданной точки
		usort($axes, static function (array $axis1, array $axis2) use ($X) {
			return abs($X - $axis1[0]) - abs($X - $axis2[0]);
		});

		return $axes;
	}

	public array $tree = [];
	public array $nodes = [];
	private array $xyToIndex = [];

	public static function findPathToExit(self $m, callable $onMove=null): ?array {
		$m->tree = [];
		$m->nodes = [];
		$m->xyToIndex = [];
		return $m->move(-1, $m->startX, $m->startY, $onMove);
	}

	/**
	 * Returns <code>[x,y]</code> on success, otherwise returns <code>false</code>
	 *
	 * @param int $parentNode
	 * @param int $X
	 * @param int $Y
	 * @param callable|null $onMove
	 * @return array|null
	 */
	private function move(int $parentNode, int $X, int $Y, callable $onMove=null): ?array {

		if ($onMove) {
			$onMove($this, $X, $Y);
		}

		$node = $this->addNodeTo([$X, $Y], $parentNode);

		if ($foundXY = $this->checkExit($X, $Y)) {
			$this->addNodeTo($foundXY, $parentNode);
			return $foundXY;
		}

		$nextPositions = [];
		foreach ($ax = $this->getAllAxesX($X, $Y) as [$y, $beginX, $endX]) {
			foreach ($ay = $this->getAllAxesY($X, $y) as [$x, $beginY, $endY]) {
				if ($x === $X && $y === $Y) {
					continue;
				}
				if ($x === $X || $y === $Y) {
					if (!$this->getNode([$x, $y])) {
						$nextPositions[] = [$x, $y];
					}
				}
			}
		}

		foreach ($nextPositions as [$x, $y]) {
			if ($result = $this->move($node, $x, $y, $onMove)) {
				return $result;
			}
		}

		return null;
	}

	private function addNodeTo(array $xy, int $parentNode=0): int {
		$index = $this->getNode($xy) ?? $this->newNode($xy);
		$this->tree[$parentNode][] = $index;
		$this->xyToIndex[implode(',', $xy)] = $index;
		return $index;
	}

	private function newNode(array $xy): int {
		$point = self::formatXY($xy);
		$this->nodes[] = $point;
		return count($this->nodes) - 1;
	}

	private function getNode(array $xy) {
		$key = implode(',', $xy);
		return $this->xyToIndex[$key] ?? null;
	}

	/**
	 * Utility
	 *
	 * @param array $xy
	 * @return string
	 */
	public static function formatXY(array $xy): string {
		return vsprintf('[%d,%d]', $xy);
	}
}
