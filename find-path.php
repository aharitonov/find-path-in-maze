<?php

declare(strict_types=1);

require_once __DIR__ . '/Cli.php';
require_once __DIR__ . '/Map.php';

//Дано прямоугольное поле размерами MxN.
//Каждая клетка поля — или свободное пространство (_), или стена (X).
//Перемещаться через стены или выходить за границы поля нельзя.

$map = [
// Y: 0    1    2    3    4    // X:
    ['_', '_', '_', '_', '1'], // 0
    ['_', 'X', 'X', 'X', '_'], // 1
    ['_', 'E', 'X', '_', '_'], // 2
    ['X', '_', '_', 'X', '_'], // 3
    ['_', '_', '_', '_', '_'], // 4
];

/*
$map = [
// Y: 0    1    2    3    4    // X:
    ['_', '_', 'X', '_',    ], // 0
    ['X', ' ', '1', '_',    ], // 1
    ['X', 'X', 'X', '_',    ], // 2
    ['E', '_', '_', '_',    ], // 3
];
*/

initScreen();

$m = new Map($map);
echo vsprintf('Start: [%d, %d]', $m->getStart()) . PHP_EOL;
echo vsprintf('Exit: [%d, %d]', $m->getExit()) . PHP_EOL;
echo $m->renderHumanizedView();

$cursor = Cli::getCursor();
$routes = Map::findPaths($m, static function(Map $m, int $x, int $y) use ($cursor)
{
	$m->setStart($x, $y); // move to actual position for showing

	Cli::setCursor($cursor); // restore location
	echo PHP_EOL;
	echo $m->renderHumanizedView();
	usleep(500_000);
});

print 'ROUTES: ' . PHP_EOL;
foreach ($routes as $i => $route) {
	$points = array_map(static fn(array $xy) => formatXY($xy), $route);
	print "$i: " . implode(", ", $points) . PHP_EOL;
}
print PHP_EOL;
print PHP_EOL;
print 'ROUTE length: ';
$routes = array_map(static fn(array $xy) => Map::computeRouteLength($xy), $routes);
print_r($routes);
print PHP_EOL;
print PHP_EOL;




function formatXY(array $xy): string
{
	return vsprintf('[%d,%d]', $xy);
}

function initScreen()
{
	Cli::clearScreen();
	$progName = basename(__FILE__);
	echo basename(__FILE__) . PHP_EOL;
	echo str_repeat("=", strlen($progName)) . PHP_EOL;
	echo PHP_EOL;
}

function dd(...$arguments)
{
	array_map('var_dump', $arguments);
	exit(1);
}
