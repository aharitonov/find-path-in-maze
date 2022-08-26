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

$map = [
// Y: 0    1    2    3    4    // X:
	['_', '_', '_', '_', '_'], // 0
    ['_', '_', 'X', '_', '_'], // 1
    ['X', ' ', '1', 'X', '_'], // 2
    ['X', '_', 'X', '_', '_'], // 3
    ['E', '_', '_', '_', '_'], // 4
];

initScreen();

$m = new Map($map);
echo vsprintf('Start: [%d, %d]', $m->getStart()) . PHP_EOL;
echo vsprintf('Exit: [%d, %d]', $m->getExit()) . PHP_EOL;
echo $m->renderHumanizedView();

$cursor = Cli::getCursor();
$xy = Map::findPathToExit($m, static function(Map $m, int $x, int $y) use ($cursor)
{
	$m->setStart($x, $y); // move to actual position for showing

	Cli::setCursor($cursor); // restore location
	echo PHP_EOL;
	echo $m->renderHumanizedView();
	usleep(500_000);
});

print ($xy ? vsprintf("Path found to: [%d,%d]", $xy) : 'Path not found');
print PHP_EOL;
print PHP_EOL;
print 'NODE INFO: ';
print_r($m->nodes);

array_walk_recursive($m->tree, static function(&$value, $key) use ($m) {
	$value = "#$value. " . $m->nodes[$value];
	//$value = "#$value. " . $m->nodes[$key] . ' => ' . $m->nodes[$value];
});
print 'TREE: ';
print_r($m->tree);

/*
$path = [];
array_walk_recursive($m->tree, static function(&$value) use ($m, &$path) {
	$path[] = $m->nodes[$value];
});
print 'PATH: ' . implode(', ', $path) . PHP_EOL;
*/

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
