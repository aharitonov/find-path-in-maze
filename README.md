# find-path-in-maze
Решаем алгоритмическую задачу по нахождению пути в лабиринте.

Лабиринт задается массивом из символов и имеет вид:

```
 1 _ _ _ _
 X X X X _
 _ E X _ _
 X _ _ X _
 _ _ _ _ _
```


## 1. Проверка существования пути


```php
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
```
Смотри "[path-exists.php](path-exists.php)"


## 2. Найти кратчайший путь

```php
function findMinimalPath(array $map, $startX, $startY, $exitX, $exitY): ?array
{
    $m = new Map($map, $startX, $startY, $exitX, $exitY);
    $paths = Map::findPaths($m);

    $paths = array_filter($paths, static function (array $path) use ($m) {
        return end($path) === $m->getExit();
    });

    uasort($paths, static function (array $path1, array $path2) {
        return Map::calcPathLength($path1) - Map::calcPathLength($path2);
    });

    $index = array_key_first($paths);
    if ($index !== null) {
        return $paths[$index];
    }
    return null; // path not found
}
```

TODO

Смотри "[find-path.php](find-path.php)"
