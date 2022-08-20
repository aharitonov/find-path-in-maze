<?php

declare(strict_types=1);

class Map {

	private const START = '1';
	private const FULL = 'X';
	private const FREE = '_';
	private const EXIT = 'E';

	private array $map;
	private int $sizeX, $sizeY;
	private int $exitX, $exitY;
	private int $startX, $startY;

	public function __construct(
		array $map,
		int $startX  = null,
		int $startY  = null,
		int $exitX = null,
		int $exitY = null
	) {

		$this->map = $map;
		$this->sizeX = count($map);
		$this->sizeY = count($map[0]);

		// init start position
		[$x, $y] = self::findStart($this);
		if (null !== $x || null !== $y) {
			$this->map[$x][$y] = self::FREE;
		}
		$startX ??= $x;
		$startY ??= $y;
		if ($startX === null || $startY === null) {
			throw new RuntimeException('Start position undefined');
		}
		$this->startX = $startX;
		$this->startY = $startY;
		$this->map[$this->startX][$this->startY] = self::START;

		// init exit position
		[$x, $y] = self::findExit($this);
		if (null !== $x || null !== $y) {
			$this->map[$x][$y] = self::FREE;
		}
		$exitX ??= $x;
		$exitY ??= $y;
		if ($exitX === null || $exitY === null) {
			throw new RuntimeException('Exit position undefined');
		}
		$this->exitX = $exitX;
		$this->exitY = $exitY;
		$this->map[$this->exitX][$this->exitY] = self::EXIT;
	}

	public function getSize(): array {
		return [$this->sizeX, $this->sizeY];
	}

	public function getStart(): array {
		return [$this->startX, $this->startY];
	}

	public function getExit(): array {
		return [$this->exitX, $this->exitY];
	}

	public static function findStart(self $me): array {
		foreach ($me->map as $i => $row) {
			foreach ($row as $j => $value) {
				if ($value === self::START) {
					return [$i, $j];
				}
			}
		}
		return [null, null];
	}

	public static function findExit(self $me): array {
		foreach ($me->map as $i => $row) {
			foreach ($row as $j => $value) {
				if ($value === self::EXIT) {
					return [$i, $j];
				}
			}
		}
		return [null, null];
	}

	public function moveStart(int $x, int $y): void {
		$this->map[$this->startX][$this->startY] = self::FREE;
		$this->startX = $x;
		$this->startY = $y;
		$this->map[$this->startX][$this->startY] = self::START;
	}

	public function renderNormalizedView(): string {
		$m = $this->map;
		$myMap = [];
		foreach ($m as $i => $row) {
			$cols = [];
			foreach ($row as $j => $col) {
				$cols[] = $m[$j][$i];
			}
			$myMap[] = $cols;
		}
		$myMap = array_reverse($myMap);
		return self::renderMap($myMap);
	}

	public function render(): string {
		return self::renderMap($this->map);
	}

	private static function renderMap(array $map): string {
		$s = '';
		$rows = $map;
		foreach ($rows as $cols) {
			$s .= ' ' . implode(' ', $cols) . ' ';
			$s .= PHP_EOL;
		}
		$frontLine = str_repeat('-', 2 * count($rows[0]) + 1) . PHP_EOL;
		return $frontLine . $s . $frontLine;
	}

	private function getDirections(int $x, int $y) {
		$a = [];
		if ($this->moveable($x-1, $y)) { // Left
			$a[] = [$x-1, $y];
		}
		if ($this->moveable($x+1, $y)) { // Right
			$a[] = [$x+1, $y];
		}
		if ($this->moveable($x, $y-1)) { // Up
			$a[] = [$x, $y-1];
		}
		if ($this->moveable($x, $y+1)) { // Down
			$a[] = [$x, $y+1];
		}
		return $a;
	}

	public function isExit(int $x, int $y): bool {
		return $this->map[$x][$y] === self::EXIT;
	}

	/**
	 * Проверяем выход по какому либо направлению
	 *
	 * @param int $x
	 * @param int $y
	 * @return array|null
	 */
	public function checkExit(int $x, int $y): ?array {

		// вверх
		$i = $y+1;
		while ($this->moveable($x, $i)) {
			if ($this->isExit($x, $i)) {
				return [$x, $i];
			}
			$i++;
		}

		// вниз
		$i = $y-1;
		while ($this->moveable($x, $i)) {
			if ($this->isExit($x, $i)) {
				return [$x, $i];
			}
			$i--;
		}

		// влево
		$i = $x-1;
		while ($this->moveable($i, $y)) {
			if ($this->isExit($i, $y)) {
				return [$i, $y];
			}
			$i--;
		}

		// вправо
		$i = $x+1;
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

	private function moveableByX($x, $y): bool {
		return $this->moveable($x-1, $y) || $this->moveable($x+1, $y);
	}

	private function moveableByY($x, $y): bool {
		return $this->moveable($x, $y-1) || $this->moveable($x, $y+1);
	}

	/**
	 * Находим возможные горизонтали от заданной точки
	 */
	private function findAxisX(int $x, int $y): array {

		$min = $y;
		$max = $y;

		$i = $y-1;
		while ($this->moveable($x, $i)) {
			//if ($this->moveableByX($x, $i)) {
				$min = $i;
			//}
			$i--;
		}

		$i = $y+1;
		while ($this->moveable($x, $i)) {
			//if ($this->moveableByX($x, $i)) {
				$max = $i;
			//}
			$i++;
		}

		return [$min, $max];
	}

	/**
	 * Находим возможные вертикали от заданной точки
	 */
	private function findAxisY(int $x, int $y): array {

		$min = $x;
		$max = $x;

		$i = $x-1;
		while ($this->moveable($i, $y)) {
			//if ($this->moveableByY($i, $y)) {
				$min = $i;
			//}
			$i--;
		}

		$i = $x+1;
		while ($this->moveable($i, $y)) {
			//if ($this->moveableByY($i, $y)) {
				$max = $i;
			//}
			$i++;
		}
		return [$min, $max];
	}

	private function getAllAxesX(int $X, int $Y): array {

		// находим все горизонтальные линии
		$axes = [];
		[$y1, $y2] = $this->findAxisX($X, $Y);
		for ($n = $y1; $n <= $y2; $n++) {
			[$a, $b] = $this->findAxisY($X, $n);
			if (($b - $a) > 0) {
				$axes[] = [$n, $a, $b];
			}
		}

		// и сортируем их по количеству вертикальных пересечений на них
		//uasort($axes, static fn($a, $b) => ($a[2] - $a[1]) < ($b[2] - $b[1]));

		// сортируем их по удалённости
		usort($axes, static function (array $axis1, array $axis2) use ($Y) {
			return abs($Y - $axis1[0]) > abs($Y - $axis2[0]);
		});

		return $axes;
	}

	private function getAllAxesY(int $X, int $Y): array {

		// находим все горизонтальные линии
		$axes = [];
		[$x1, $x2] = $this->findAxisY($X, $Y);
		for ($n = $x1; $n <= $x2; $n++) {
			[$a, $b] = $this->findAxisX($n, $Y);
			if (($b - $a) > 0) {
				$axes[] = [$n, $a, $b];
			}
		}

		// и сортируем их по количеству горизонтальных пересечений
		//uasort($axes, static fn($a, $b) => ($a[2] - $a[1]) < ($b[2] - $b[1]));

		// сортируем их по удалённости
		usort($axes, static function (array $axis1, array $axis2) use ($X) {
			return abs($X - $axis1[0]) > abs($X - $axis2[0]);
		});

		return $axes;
	}

	public static function findPathToExit(self $m, callable $onMove=null) {
		return $m->move($m->startX, $m->startY, $onMove);
	}


	public static array $xyToIndex = [];
	public static array $nodes = [];
	public static array $tree = [];

	/**
	 * Returns [x,y] on success, otherwise returns false
	 *
	 * @param int $X
	 * @param int $Y
	 * @param callable|null $onMove
	 * @return array|null
	 */
	private function move(int $X, int $Y, callable $onMove=null): ?array {

		if ($onMove) {
			$onMove($this, $X, $Y);
		}

		$parentNode = self::addNodeTo([$X, $Y]);

		if ($foundXY = $this->checkExit($X, $Y)) {
			return $foundXY;
		}

		$axesX = $this->getAllAxesX($X, $Y);
		foreach ($axesX as [$y, $beginX, $endX]) {

			$axesY = $this->getAllAxesY($beginX, $y);
			foreach ($axesY as [$x, $beginY, $endY]) {

				$point = [$x, $y];
				//print("crossroad: " . formatXY($point) . "\n");
				if (null === self::getNode($point)) {
					self::addNodeTo($point, $parentNode);
					if ($result = $this->move($x, $y, $onMove)) {
						return $result;
					}
				}
			}
		}

		return null;
	}

	public static function addNodeTo(array $xy, int $parentNode=0): int {
		$index = self::getNode($xy) ?? self::newNode($xy);
		self::$tree[$parentNode][] = $index;
		self::$xyToIndex[implode(',', $xy)] = $index;
		return $index;
	}

	public static function newNode(array $xy): int {
		$point = self::formatXY($xy);
		self::$nodes[] = $point;
		return count(self::$nodes) - 1;
	}

	public static function getNode(array $xy) {
		$key = implode(',', $xy);
		return self::$xyToIndex[$key] ?? null;
	}

	private static function formatXY(array $xy) {
		return vsprintf('[%d,%d]', $xy);
	}
}
