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
/*
$map = [
// Y: 0    1    2    3    4    // X:
    ['_', '_', '_', '_', '_', '_', 'X', '_'], // 0
    ['_', '_', 'X', '_', 'X', '_', '_', '_'], // 0
    ['_', 'X', 'X', '_', 'X', '_', 'X', '_'], // 0
    ['_', '_', '_', '1', '_', '_', 'X', '_'], // 1
    ['_', 'X', 'X', '_', 'X', 'X', 'X', '_'], // 2
    ['_', '_', 'X', '_', 'X', '_', '_', 'E'], // 3
    ['_', '_', 'X', '_', 'X', 'X', 'X', 'X'], // 3
    ['_', '_', '_', '_', '_', '_', '_', '_'], // 4
];
*/


initScreen();

$m = new Map($map);
echo vsprintf('Start: [%d, %d]', $m->getStart()) . PHP_EOL;
echo vsprintf('Exit: [%d, %d]', $m->getExit()) . PHP_EOL;
echo $m->renderHumanizedView();

$cursor = Cli::getCursor();
$paths = Map::findPaths($m, static function(Map $m) use ($cursor)
{
	Cli::setCursor($cursor); // restore location
	echo PHP_EOL;
	echo $m->renderHumanizedView();
	usleep(500_000);
});

print PHP_EOL;
print 'PATHS: ' . PHP_EOL;
foreach ($paths as $i => $path) {
	$length = Map::computePathLength($path);
	$exitFound = end($path) === $m->getExit();
	$points = array_map(static fn(array $xy) => formatXY($xy), $path);

	$s = sprintf('%02d: %s', $i , implode(', ', $points));
	$s .= ' (distance: ' . $length . ')';
	if ($exitFound) {
		$s.= '  ---> EXIT!';
	} else {
		$s = Cli::shadowStyle($s);
	}
	print $s . PHP_EOL;
}

// MINIMAL PATH
$paths = array_filter($paths, static fn(array $path) => end($path) === $m->getExit());
uasort($paths, static fn(array $path1, array $path2) =>
	Map::computePathLength($path1) - Map::computePathLength($path2)
);

$i = array_key_first($paths);
if ($i === null) {
	print Cli::errorStyle('PATH NOT FOUND') . PHP_EOL;
	exit;
}

print PHP_EOL;
print 'MINIMAL PATH: ' . PHP_EOL;
$path = $paths[$i];
$length = Map::computePathLength($path);
$points = array_map(static fn(array $xy) => formatXY($xy), $path);
$s = "$i: " . implode(", ", $points);
$s .= ' (distance: ' . $length . ')';
print $s . PHP_EOL;



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
