<?php

//Дано прямоугольное поле размерами MxN.
//Каждая клетка поля — или свободное пространство (_), или стена (X).
//Перемещаться через стены или выходить за границы поля нельзя.

$map = [
// Y: 0    1    2    3    4    // X:
    ['_', '_', '_', '_', '_'], // 0
    ['X', 'X', 'X', 'X', '_'], // 1
    ['_', '_', 'X', '_', '_'], // 2
    ['X', 'X', 'X', '_', 'X'], // 3
    ['_', '_', '_', '_', '_'], // 4
];

//Задача: написать функцию, которая проверяет,
//существует ли путь от клетки с координатами (x1, y1) до выхода, заданного координатами (x2, y2).

// pathExists($map, 0, 0, 4, 4); // True
// pathExists($map, 0, 0, 2, 1); // False

// РЕШЕНИЕ:

require_once __DIR__ . '/Map.php';


function pathExists(array $map, $startX, $startY, $exitX, $exitY): bool
{
	$m = new Map($map, $startX, $startY, $exitX, $exitY);

	echo vsprintf("Map condition: from [%d, %d] to [%d, %d]\n", array_merge(
		$m->getStart(),
		$m->getExit()
	));
	echo $m->renderHumanizedView() . "\n";

	return (bool) Map::findPathToExit($m);
}

var_dump(
	pathExists($map, 0, 0, 4, 4),
	pathExists($map, 0, 0, 2, 1)
);
