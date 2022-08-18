<?php

declare(strict_types=1);

//Дано прямоугольное поле размерами MxN.
//Каждая клетка поля — или свободное пространство (_), или стена (X).
//Перемещаться через стены или выходить за границы поля нельзя.

$map = [// Y: 0    1    2    3    4    // X:
			['1', 'X', '_', '_', '_'], // 0
			['_', 'X', '_', 'X', '_'], // 1
			['_', 'X', '_', 'X', '_'], // 2 line
			['_', 'X', '_', 'X', '_'], // 3
			['_', '_', '_', 'X', '1'], // 4
];

//Задача: написать функцию, которая проверяет,
//существует ли путь от клетки с координатами (x1, y1) до выхода, заданного координатами (x2, y2).

// pathExists($map, 0, 0, 4, 4); // True
// pathExists($map, 0, 0, 2, 1); // False


class Map {

	private const START = '1';
	private const FULL = 'X';
	private const FREE = '_';

	private array $map;
	private int $sizeX, $sizeY;
	private int $targetX, $targetY;
	public int $startX, $startY;

	public function __construct(array $map, int $startX, int $startY) {
		$this->map = $map;
		$this->sizeX = count($map);
		$this->sizeY = count($map[0]);
		$this->startX = $startX;
		$this->startY = $startY;
		$this->targetX = 4;
		$this->targetY = 4;

		$this->showln(sprintf('Start: [%d, %d]', $this->startX, $this->startY));
		$this->showln(sprintf('Target: [%d, %d]', $this->targetX, $this->targetY));
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

	private function moveable($x, $y): bool {
		return isset($this->map[$x][$y]) && $this->map[$x][$y] === self::FREE;
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
			if ($this->moveableByX($x, $i)) {
				$min = $i;
			}
			$i--;
		}

		$i = $y+1;
		while ($this->moveable($x, $i)) {
			if ($this->moveableByX($x, $i)) {
				$max = $i;
			}
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
			if ($this->moveableByY($i, $y)) {
				$min = $i;
			}
			$i--;
		}

		$i = $x+1;
		while ($this->moveable($i, $y)) {
			if ($this->moveableByY($i, $y)) {
				$max = $i;
			}
			$i++;
		}
		return [$min, $max];
	}


	private function getAllAxesX(int $X, int $Y): array {

		// находим все горизонтальные линии
		$axes = [];
		[$x1, $x2] = $this->findAxisX($X, $Y);
		for ($n = $x1; $n <= $x2; $n++) {
			[$a, $b] = $this->findAxisY($X, $n);
			if (($b - $a) > 0) {
				$axes[$n] = [$a, $b];
			}
		}

		// и сортируем их по количеству вертикальных пересечений на них
		uasort($axes, static fn($a, $b) => ($a[1] - $a[0]) < ($b[1] - $b[0]));

		return $axes;
	}

	private function getAllAxesY(int $X, int $Y): array {

		// находим все горизонтальные линии
		$axes = [];
		[$y1, $y2] = $this->findAxisY($X, $Y);
		for ($n = $y1; $n <= $y2; $n++) {
			[$a, $b] = $this->findAxisX($n, $Y);
			if (($b - $a) > 0) {
				$axes[$n] = [$a, $b];
			}
		}

		// и сортируем их по количеству горизонтальных пересечений
		uasort($axes, static fn($a, $b) => ($a[1] - $a[0]) < ($b[1] - $b[0]));

		return $axes;
	}

	private static $nodes = [];

	public static function find(self $m, int $X, int $Y) {

		print("point [$X, $Y]\n");

		$axesX = $m->getAllAxesX($X, $Y);
		foreach ($axesX as $y => [$beginX, $endX]) {
			for ($xn = $beginX; $xn <= $endX; $xn++) {

				$axesY = $m->getAllAxesY($y, $xn); // arguments: $x <-> $y
				foreach ($axesY as $x => [$beginY, $endY]) {
					for ($yn = $beginY; $yn <= $endY; $yn++) {
						//self::find($m, $x, $yn);
						print("[$x, $yn]\n");
					}
				}
			}
		}

		return self::$nodes;
	}
}


$m = new Map($map, 0, 0);
$result = Map::find($m, $m->startX, $m->startY);
print_r($result);


function dd($a=[], ...$args) {
	var_dump($a, ...$args);die;
}

function showXY(array $xy) {
	print vsprintf('[%d, %d]', $xy) . PHP_EOL;
}
