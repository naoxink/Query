# Query
 
### Todavía en desarrollo

## Y esto.. por qué?
Pues por el simple echo de hacerlo :)

# Ejemplos de uso:

**Instancia**
```PHP
$q = new Query();
```

**Consultas**

```PHP
$q->select([ 'id', 'nombre' ])
  ->from('usuarios u')
  ->join('INNER', 'datos_usuarios d', [ 'u.id = d.id_usuario' ])
  ->where([ 'edad > 30', 'pelo = "marrón"' ])
  ->andWhere([ 'nombre = "maikel"' ])
  ->orderBy([ 'nombre ASC' ])
  ->groupBy([ 'edad' ])
  ->having([ 'edad > 3' ])
  ->limit(2, 10);
```
También puede separarse:

```PHP
$q->select([ 'id', 'nombre' ])->from('usuarios u')->where([ 'color_pelo = "castaño"' ]);

if($mayoresDeEdad){
  $q->andWhere([ 'edad >= 18' ]);
}

$q->limit(5);
```

```PHP
$q->insert('usuarios')
  ->fields([ 'id', 'nombre' ])
  ->values([ 1234, '"Gaby"' ]);
```

```PHP
$q->delete()
  ->from('usuarios')
  ->where([ 'nombre = "raule"' ])
  ->limit(1);
```

```PHP
$q->update('usuarios')
  ->set([ 'edad = 50' ])
  ->where([ 'id = 2' ])
  ->limit(1);
```

**Para obtener la consulta final**
```PHP
$strQuery = $q->getQuery();
```

### JOINS
Se pueden usar los distintos tipos de métodos para unir tablas
El método genérico `join` se puede usar para los joins que carecen de aliases como CROSS JOIN, LEFT OUTTER JOIN, etc.

* `->join($joinType, $table, $on)`: `$q->join('INNER', 'usuarios_datos ud', [ u.id = ud.id_usuario ])`
* `->leftJoin($table, $on)`: `$q->leftJoin('usuarios_datos ud', [ u.id = ud.id_usuario ])`
* `->rightJoin($table, $on)`: `$q->rightJoin('usuarios_datos ud', [ u.id = ud.id_usuario ])`
