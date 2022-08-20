<?php

declare(strict_types=1);

require_once __DIR__ . '/Map.php';

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
