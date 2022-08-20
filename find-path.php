<?php

declare(strict_types=1);

require_once __DIR__ . '/Cli.php';
require_once __DIR__ . '/Map.php';

//Дано прямоугольное поле размерами MxN.
//Каждая клетка поля — или свободное пространство (_), или стена (X).
//Перемещаться через стены или выходить за границы поля нельзя.

$map = [// Y: 0    1    2    3    4    // X:
			['_', '_', '1', '_', '_'], // 0
			['X', '_', '_', 'X', '_'], // 1
			['_', '_', '_', 'X', '_'], // 2
			['_', 'X', '_', 'X', '_'], // 3
			['_', '_', '_', 'X', 'E'], // 4
];

$map = [// Y: 0    1    2    3    4    // X:
			['1', 'X', '_', '_', '_'], // 0
			['_', 'X', '_', 'X', '_'], // 1
			['_', 'X', '_', 'X', '_'], // 2
			['_', 'X', '_', 'X', '_'], // 3
			['_', '_', '_', 'X', 'E'], // 4
];

//Задача: написать функцию, которая проверяет,
//существует ли путь от клетки с координатами (x1, y1) до выхода, заданного координатами (x2, y2).

// pathExists($map, 0, 0, 4, 4); // True
// pathExists($map, 0, 0, 2, 1); // False

initScreen();

$m = new Map($map);
echo vsprintf('Start: [%d, %d]', $m->getStart()) . PHP_EOL;
echo vsprintf('Exit: [%d, %d]', $m->getExit()) . PHP_EOL;
echo $m->renderNormalizedView();

$cursorXY = Cli::getCursor();
$xy = Map::findPathToExit($m, function(Map $m, int $x, int $y) use ($cursorXY) {
	$m->moveStart($x, $y);
	Cli::setCursor($cursorXY);
	echo PHP_EOL;
	echo $m->renderNormalizedView();
	usleep(500_000);
});

print ($xy ? vsprintf("Path found to: [%d,%d]", $xy) : 'Path not found');
print PHP_EOL;
print PHP_EOL;
print 'NODE INFO: ';
print_r(Map::$nodes);
print 'TREE: ';
print_r(Map::$tree);


function initScreen() {
	Cli::clearScreen();
	$progName = basename(__FILE__);
	echo basename(__FILE__) . PHP_EOL;
	echo str_repeat("=", strlen($progName)) . PHP_EOL;
	echo PHP_EOL;
}

function dd(...$arguments) {
	array_map('var_dump', $arguments);
	exit(1);
}
