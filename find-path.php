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
    ['_', '_', 'X', '_',    ], // 0
    ['X', ' ', '1', '_',    ], // 1
    ['X', 'X', 'X', '_',    ], // 2
    ['E', '_', '_', '_',    ], // 3
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
//print 'TREE: ';
//print_r($m->tree);
print 'ROUTES: ' . PHP_EOL;

function all_routes(int $node=0, array $route=[])
{
	global $m;

	$routes = [];
	$route[] = $node;

	if (!isset($m->tree[$node])) {
		$routes[] = $route;
		return $routes;
	}

	foreach ($m->tree[$node] as $n) {
		foreach(all_routes($n, $route) as $v) {
			$routes[] = $v;
		}
	}
	return $routes;
}

$routes = all_routes();
foreach ($routes as $routePoints) {
	$routePoints = array_map(static fn ($id) => $m->nodes[$id], $routePoints);
	print implode(", ", $routePoints) . PHP_EOL;
}



/*
array_walk_recursive($m->tree, static function(&$value, $key) use ($m) {
	$value = "#$value. " . $m->nodes[$value];
	//$value = "#$value. " . $m->nodes[$key] . ' => ' . $m->nodes[$value];
});
*/
/*
function show_tree($nodes) {
	global $m;

	foreach ($nodes as $n) {
		if (!isset($m->tree[$n])) {
			echo PHP_EOL;
			continue;
		}
		print $m->nodes[$n] . PHP_EOL;
		show_tree($m->tree[$n]);
	}
}

show_tree($m->tree[0]);
*/


/*
$node = 0; // root
LOOP:
foreach ($m->tree[$node] as $n) {
	print $m->nodes[$n] . PHP_EOL;
	if (!isset($m->tree[$n])) {
		print "No $n" . PHP_EOL;
		continue;
	}
	$node = $n;
	goto LOOP;
}
*/

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
