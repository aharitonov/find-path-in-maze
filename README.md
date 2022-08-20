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

TODO

Смотри "[find-path.php](find-path.php)"