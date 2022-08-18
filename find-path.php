<?php

declare(strict_types=1);

//Дано прямоугольное поле размерами MxN.
//Каждая клетка поля — или свободное пространство (_), или стена (X).
//Перемещаться через стены или выходить за границы поля нельзя.

$map = [// Y: 0    1    2    3    4    // X:
			['_', '_', '1', '_', '_'], // 0
			['X', '_', '_', 'X', '_'], // 1
			['_', '_', '_', 'X', '_'], // 2 line
			['_', 'X', '_', 'X', '_'], // 3
			['_', '_', '_', 'X', 'E'], // 4
];

//Задача: написать функцию, которая проверяет,
//существует ли путь от клетки с координатами (x1, y1) до выхода, заданного координатами (x2, y2).

// pathExists($map, 0, 0, 4, 4); // True
// pathExists($map, 0, 0, 2, 1); // False


class Map {

	private const START = '1';
	private const FULL = 'X';
	private const FREE = '_';
	private const EXIT = 'E';

	private array $map;
	private int $sizeX, $sizeY;
	private int $targetX, $targetY;
	private int $startX, $startY;

	public function __construct(
		array $map,
		int $startX  = null,
		int $startY  = null,
		int $targetX = null,
		int $targetY = null
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
		$targetX ??= $x;
		$targetY ??= $y;
		if ($targetX === null || $targetY === null) {
			throw new RuntimeException('Exit position undefined');
		}
		$this->targetX = $targetX;
		$this->targetY = $targetY;
		$this->map[$this->targetX][$this->targetY] = self::EXIT;

		$this->showln(sprintf('Start: [%d, %d]', $this->startX, $this->startY));
		$this->showln(sprintf('Exit: [%d, %d]', $this->targetX, $this->targetY));
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
		return $frontLine . $s . $frontLine . PHP_EOL;
	}

	private function showln(string $s) {
		print($s . PHP_EOL);
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

	private function checkExitInX(int $beginX, int $endX, int $y): ?int {
		for ($xn = $beginX; $xn < $endX; $xn++) {
			if ($this->checkExit($xn, $y)) {
				return $xn;
			}
		}
		return null;
	}

	private function checkExitInY($x, int $beginY, int $endY): ?int {
		for ($yn = $beginY; $yn < $endY; $yn++) {
			if ($this->checkExit($x, $yn)) {
				return $yn;
			}
		}
		return null;
	}

	public function checkExit(int $x, int $y): bool {
		return isset($this->map[$x][$y]) && $this->map[$x][$y] === self::EXIT;
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
		usort($axes, static function (array $axis1, array $axis2) use ($X) {
			return abs($X - $axis1[0]) > abs($X - $axis2[0]);
		});

		return $axes;
	}

	public static function findPathToExit(self $m, int $startX=null, int $startY=null) {
		$m->startX ??= $startX;
		$m->startY ??= $startY;
		return self::find($m, $m->startX, $m->startY);
	}


	public static array $xyToIndex = [];
	public static array $nodes = [];
	public static array $tree = [];

	/**
	 * Returns [x,y] on success, otherwise returns false
	 *
	 * @param self $m
	 * @param int $X
	 * @param int $Y
	 * @return array|false
	 */
	protected static function find(self $m, int $X, int $Y) {

		$m->moveStart($X, $Y);
		echo $m->renderNormalizedView();

		$parentNode = self::addNodeTo([$X, $Y]);

		$axesX = $m->getAllAxesX($X, $Y);
		foreach ($axesX as [$y, $beginX, $endX]) {
			if ($xN = $m->checkExitInX($beginX, $endX, $y)) {
				return [$xN, $y];
			}

			$axesY = $m->getAllAxesY($beginX, $y);
			foreach ($axesY as [$x, $beginY, $endY]) {
				if ($yN = $m->checkExitInY($x, $beginY, $endY)) {
					return [$x, $yN];
				}

				$point = [$x, $y];
				//print("crossroad: " . formatXY($point) . "\n");
				if (null === self::getNode($point)) {
					self::addNodeTo($point, $parentNode);
					if ($result = self::find($m, $x, $y)) {
						return $result;
					}
				}
			}
		}

		return false;
	}

	public static function addNodeTo(array $xy, int $parentNode=0): int {
		$index = self::getNode($xy) ?? self::newNode($xy);
		self::$tree[$parentNode][] = $index;
		self::$xyToIndex[implode(',', $xy)] = $index;
		return $index;
	}

	public static function newNode(array $xy): int {
		$point = formatXY($xy);
		self::$nodes[] = $point;
		return count(self::$nodes) - 1;
	}

	public static function getNode(array $xy) {
		$key = implode(',', $xy);
		return self::$xyToIndex[$key] ?? null;
	}
}


$m = new Map($map, 0, 4);
//echo $m->renderNormalizedView();
$xy = Map::findPathToExit($m);
print ($xy ? vsprintf("Exit found: [%d,%d]", $xy) : 'no path found');
print PHP_EOL;

print 'NODE INFO: ';
print_r(Map::$nodes);
print 'TREE: ';
print_r(Map::$tree);


function dd($a=[], ...$args) {
	var_dump($a, ...$args);die;
}

function formatXY(array $xy) {
	return vsprintf('[%d,%d]', $xy);
}
