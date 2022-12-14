<?php

declare(strict_types=1);

class Map {

	private const START = '1';
	private const FULL = 'X';
	private const SPACE = '_';
	private const EXIT = 'E';
	private const POS = '?'; // cursor

	private array $map;
	private int $sizeX, $sizeY;
	private int $exitX, $exitY;
	private int $startX, $startY;
	private int $posX, $posY;

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

		if ($startPoint = self::findStart($this)) {
			$this->setPoint($startPoint, self::SPACE); // remove START point
		}

		[$x, $y] = $startPoint;
		$startX ??= $x;
		$startY ??= $y;

		if ($startX === null || $startY === null) {
			throw new RuntimeException('Start position undefined');
		}

		$this->setStart($startX, $startY);
		$this->setPoint($this->getStart(), self::START); // setup START point
		$this->setPos($startX, $startY);

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

	protected static function findStart(self $me): ?array {
		foreach ($me->map as $i => $row) {
			foreach ($row as $j => $value) {
				if ($value === self::START) {
					return [$i, $j];
				}
			}
		}
		return null;
	}

	protected static function findExit(self $me): ?array {
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

	private function setPos(int $x, int $y): void {
		$this->posX = $x;
		$this->posY = $y;
	}

	public function getPos(): array {
		return [$this->posX, $this->posY];
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
		$m[$this->posX][$this->posY] = self::POS;
		$m[$this->startX][$this->startY] = self::START;

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
	 * ?????????????????? ?????????? ???? ???????????? ???????? ??????????????????????
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
	 * ?????????????? ?????????????????? ?????????????????????? ???? ???????????????? ??????????
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
	 * ?????????????? ?????????????????? ?????????????????? ???? ???????????????? ??????????
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
	 * ?????????????? ?????? ??????????????????????
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

		// ?? ?????????????????? ???? ???? ???????????????????? ???????????????????????? ?????????????????????? ???? ??????
		//uasort($axes, static fn($a, $b) => ($a[2] - $a[1]) < ($b[2] - $b[1]));

		// ?????????????????? ?????????? ???? ?????????????????????? ???? ???????????????? ??????????
		usort($axes, static function (array $axis1, array $axis2) use ($Y) {
			return abs($Y - $axis1[0]) - abs($Y - $axis2[0]);
		});

		return $axes;
	}

	/**
	 * ?????????????? ?????? ??????????????????
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

		// ?? ?????????????????? ???? ???? ???????????????????? ???????????????????????????? ??????????????????????
		//uasort($axes, static fn($a, $b) => ($a[2] - $a[1]) < ($b[2] - $b[1]));

		// ?????????????????? ?????????? ???? ?????????????????????? ???? ???????????????? ??????????
		usort($axes, static function (array $axis1, array $axis2) use ($X) {
			return abs($X - $axis1[0]) - abs($X - $axis2[0]);
		});

		return $axes;
	}

	/**
	 * @var array [id => [child_id_1, child_id_2, ...]]
	 */
	protected array $tree = [];
	/**
	 * @var array [id => [x, y]]
	 */
	protected array $nodes = [];
	/**
	 * @var array ["x,y" => id]
	 */
	private array $xyToNode = [];

	private function resetPathHistory(): void {
		$this->tree = [];
		$this->nodes = [];
		$this->xyToNode = [];
	}

	public static function findPaths(self $m, callable $onMove=null): array {

		$routes = [];

		[$startX, $startY] = $m->getStart();
		$m->setPos($startX, $startY);

		$moves = $m->findNextMoves($startX, $startY);
		foreach ($moves as [$x, $y]) {
			$m->resetPathHistory();
			$rootNode = $m->newNode([$startX, $startY]);
			$m->move($rootNode, $x, $y, $onMove);
			foreach (self::treeToRoutes($m) as $r) {
				$routes[] = $r;
			}
		}
		return $routes;
	}

	public static function findPathToExit(
		self $m,
		callable $onMove = null,
		bool $stopOnFirstFound = true
	): ?array {

		$m->resetPathHistory();

		$result = null;
		$rootNode = $m->newNode([$m->startX, $m->startY]);
		$moves = $m->findNextMoves($m->startX, $m->startY);
		foreach ($moves as [$x, $y]) {
			$found = $m->move($rootNode, $x, $y, $onMove);
			if ($found) {
				$result = $found;
				if ($stopOnFirstFound) {
					break;
				}
			}
		}
		return $result;
	}

	private function findRouteFrameOnX(int $X, int $Y): array {
		$max = $this->sizeX;
		$min = -1;
		foreach ($this->nodes as [$x, $y]) {
			if ($y === $Y) {
				if ($x === $X) {
					continue;
				}
				if ($x > $X) {
					if ($max > $x) {
						$max = $x;
					}
				} else {
					if ($min < $x) {
						$min = $x;
					}
				}
			}
		}
		return [$min, $max];
	}

	private function findRouteFrameOnY(int $X, int $Y): array {
		$max = $this->sizeY;
		$min = -1;
		foreach ($this->nodes as [$x, $y]) {
			if ($x === $X) {
				if ($y === $Y) {
					continue;
				}
				if ($y > $Y) {
					if ($max > $y) {
						$max = $y;
					}
				} else {
					if ($min < $y) {
						$min = $y;
					}
				}
			}
		}
		return [$min, $max];
	}

	/**
	 * @return array[[int,int]]
	 */
	private function findNextMoves(int $X, int $Y): array {

		// ?????????????????????????? ?????????????????? ??????????????????
		[$minX, $maxX] = $this->findRouteFrameOnX($X, $Y);
		[$minY, $maxY] = $this->findRouteFrameOnY($X, $Y);

		$modes = [];
		foreach ($ax = $this->getAllAxesX($X, $Y) as [$y, $beginX, $endX]) {
			foreach ($ay = $this->getAllAxesY($X, $y) as [$x, $beginY, $endY]) {
				if ($x < $maxX && $x > $minX && $y > $minY && $y < $maxY) {
					$modes[] = [$x, $y];
				}
			}
		}

		// ?????????????????? ?????????????????? ?????????? ?????? ????????????????
		$nextPositions = [];

		foreach ($modes as [$x, $y]) {
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

		return $nextPositions;
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
			$this->setPos($X, $Y);
			$onMove($this);
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
		$this->nodes[] = $xy;
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
	 * @param array $points
	 * @return int
	 */
	public static function calcPathLength(array $points): int {
		$length = 0;
		[$x0, $y0] = $points[0];
		for ($i=1, $iMax = count($points); $i < $iMax; $i++) {
			[$x, $y] = $points[$i];
			if ($x0 === $x) {
				$length += abs($y - $y0);
			} else {
				$length += abs($x - $x0);
			}
			[$x0, $y0] = [$x, $y];
		}
		return $length;
	}

	/**
	 * Utility. Make sense after building the tree
	 *
	 * @param Map $m
	 * @return array
	 */
	public static function treeToRoutes(self $m): array {

		self::$me = $m;
		$routes = self::toRoutes();

		return array_map(static function (array $route) use ($m) {
			return array_map(static fn ($id) => $m->nodes[$id], $route);
		}, $routes);
	}

	private static self $me;

	/**
	 * Recursive building route list from tree
	 *
	 * Tree:
	 * <code>
	 * 	[
	 * 		0  => [10, 20, 40],
	 * 		10 => [30, 40],
	 * 		20 => [60, 70, 110],
	 * 		40 => [80],
	 * 	];
	 * </code>
	 *
	 * Result:
	 * <code>
	 * 	[
	 * 		[0, 10, 30    ],
	 * 		[0, 10, 40, 80],
	 * 		[0, 20, 60    ],
	 * 		[0, 20, 70    ],
	 * 		[0, 20, 110   ],
	 * 		[0, 40, 80    ],
	 * 	]
	 * </code>
	 *
	 * @param int $node
	 * @param array $route
	 * @return array
	 */
	private static function toRoutes(int $node=0, array $route=[]): array {
		$routes = [];
		$route[] = $node;

		if (!isset(self::$me->tree[$node])) {
			$routes[] = $route;
			return $routes;
		}

		foreach (self::$me->tree[$node] as $n) {
			foreach (self::toRoutes($n, $route) as $v) {
				$routes[] = $v;
			}
		}
		return $routes;
	}
}
