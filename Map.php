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

	/**
	 * @var array [id => [child_id_1, child_id_2, ...]]
	 */
	public array $tree = [];
	/**
	 * @var array [id => [x, y]]
	 */
	public array $nodes = [];
	/**
	 * @var array ["x,y" => id]
	 */
	private array $xyToNode = [];


	public static function findPathToExit(self $m, callable $onMove=null): ?array {

		$m->tree     = [];
		$m->nodes    = [];
		$m->xyToNode = [];

		$result = null;
		$rootNode = $m->newNode([$m->startX, $m->startY]);
		$moves = $m->findNextMoves($m->startX, $m->startY);
		foreach ($moves as [$x, $y]) {
			$result ??= $m->move($rootNode, $x, $y, $onMove);
		}
		return $result;
	}

	/**
	 * @return array[[int,int]]
	 */
	private function findNextMoves(int $X, int $Y): array {

		// находим пределы за которыми возникает пересечение маршрутов

		$maxX = $this->sizeX;
		$minX = -1;
		$maxY = $this->sizeY;
		$minY = -1;

		$modes = [];
		foreach ($ax = $this->getAllAxesX($X, $Y) as [$y, $beginX, $endX]) {
			foreach ($ay = $this->getAllAxesY($X, $y) as [$x, $beginY, $endY]) {
				if ($x === $X && $y === $Y) {
					continue;
				}
				if (null !== $this->getNode([$x, $y])) {
					if ($x > $X) {
						$maxX = $x;
					} elseif($x < $X) {
						$minX = $x;
					}
					if ($y > $Y) {
						$maxY = $y;
					} elseif($y < $Y) {
						$minY = $y;
					}
					continue;
				}

				$modes[] = [$x, $y];
			}
		}

		// находим возможные следующие точки для маршрута

		$nextPositions = [];
		foreach ($modes as [$x, $y]) {
			if ($minX < $x && $x < $maxX && $minY < $y && $y < $maxY) {
				if ($x === $X) {
					if ($y > $Y && empty($nextPositions['U'])) {
						$nextPositions['U'] = [$x, $y];
					}
					if ($y < $Y && empty($nextPositions['D'])) {
						$nextPositions['D'] = [$x, $y];
					}
				}

				if ($y === $Y) {
					if ($x > $X && empty($nextPositions['R'])) {
						$nextPositions['R'] = [$x, $y];
					}
					if ($x < $X && empty($nextPositions['L'])) {
						$nextPositions['L'] = [$x, $y];
					}
				}
			}
		}

		// фильтруем точки, где возникают пересечения

		return array_filter($nextPositions, static fn (array $a) =>
			$minX < $a[0] && $a[0] < $maxX && $minY < $a[1] && $a[1] < $maxY
		);
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

		$node = $this->newNode([$X, $Y]);
		$this->bind($parentNode, $node);

		if ($foundXY = $this->checkExit($X, $Y)) {
			$this->bind($node, $this->newNode($foundXY));
			return $foundXY;
		}

		$result = null;
		$nextPositions = $this->findNextMoves($X, $Y);
		foreach ($nextPositions as [$x, $y]) {
			$result ??= $this->move($node, $x, $y, $onMove);
		}
		return $result;
	}

	private function bind(int $parentNode, int $node): void {
		$this->tree[$parentNode][] = $node;
	}

	private function newNode(array $xy): int {
		$point = self::formatXY($xy);
		$this->nodes[] = $point;
		$index = count($this->nodes) - 1;
		$this->xyToNode[implode(',', $xy)] = $index;
		return $index;
	}

	private function getNode(array $xy): ?int {
		$key = implode(',', $xy);
		return $this->xyToNode[$key] ?? null;
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
