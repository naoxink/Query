# Query

## Y esto.. por qué?
Pues por el simple echo de hacerlo :)

# Ejemplos de uso:

**Importación**

**PHP**
```PHP
require 'Query.class.php';
```

**JS**
```JS
const Query = require('Query.class.js')
```

**Vía NPM**

```
npm i querysqljs
```

```JS
const Query = require('querysqljs')
```

**Instancia**

**PHP**
```PHP
$q = new Query();
```

**JS**
```JS
const q = new Query()
```

**Consultas**

**PHP**
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

**JS**
```JS
q.select([ 'id', 'nombre' ])
 .from('usuarios u')
 .join('INNER', 'datos_usuarios d', [ 'u.id = d.id_usuario' ])
 .where([ 'edad > 30', 'pelo = "marrón"' ])
 .andWhere([ 'nombre = "maikel"' ])
 .orderBy([ 'nombre ASC' ])
 .groupBy([ 'edad' ])
 .having([ 'edad > 3' ])
 .limit(2, 10)
```

También puede separarse:

**PHP**
```PHP
$q->select([ 'id', 'nombre' ])->from('usuarios u')->where([ 'color_pelo = "castaño"' ]);

if($mayoresDeEdad){
  $q->andWhere([ 'edad >= 18' ]);
}

$q->limit(5);
```
**JS**
```JS
q.select([ 'id', 'nombre' ])->from('usuarios u')->where([ 'color_pelo = "castaño"' ])

if(mayoresDeEdad){
  q.andWhere([ 'edad >= 18' ])
}

$q->limit(5);
```

**PHP**
```PHP
$q->insert('usuarios')
  ->fields([ 'id', 'nombre' ])
  ->values([ 1234, '"Gaby"' ]);
```

**JS**
```JS
q.insert('usuarios')
 .fields([ 'id', 'nombre' ])
 .values([ 1234, '"Gaby"' ])
```

**PHP**
```PHP
$q->delete()
  ->from('usuarios')
  ->where([ 'nombre = "raule"' ])
  ->limit(1);
```

**JS**
```JS
q.delete()
 .from('usuarios')
 .where([ 'nombre = "raule"' ])
 .limit(1)
```

**PHP**
```PHP
$q->update('usuarios')
  ->set([ 'edad = 50' ])
  ->where([ 'id = 2' ])
  ->limit(1);
```

**JS**
```JS
q.update('usuarios')
 .set([ 'edad = 50' ])
 .where([ 'id = 2' ])
 .limit(1)
```

**Para obtener la consulta final**

**PHP**
```PHP
$strQuery = $q->getQuery();
```

**JS**
```JS
const strQuery = q.getQuery()
```

**NOTA:** Los métodos `select`, `where`, `andWhere`, `orderBy`, `groupBy` y `having` pueden recibir como parámetro un `array` o un `string`

Ejemplo:

**PHP**
```PHP
$q->select('id, nombre, edad')
  ->from('usuarios')
  ->where('edad > 18')
  ->orderBy('edad DESC')
  ->groupBy('edad')
  ->having('id > 2');
```

**JS**
```JS
q.select('id, nombre, edad')
 .from('usuarios')
 .where('edad > 18')
 .orderBy('edad DESC')
 .groupBy('edad')
 .having('id > 2')
```

### JOINS
Se pueden usar los distintos tipos de métodos para unir tablas
El método genérico `join` se puede usar para los joins que carecen de aliases como CROSS JOIN, LEFT OUTTER JOIN, etc.

* `->join($joinType, $table, $on)`: `$q->join('INNER', 'usuarios_datos ud', [ u.id = ud.id_usuario ])`
* `->innerJoin($table, $on)`: `$q->innerJoin('usuarios_datos ud', [ u.id = ud.id_usuario ])`
* `->leftJoin($table, $on)`: `$q->leftJoin('usuarios_datos ud', [ u.id = ud.id_usuario ])`
* `->rightJoin($table, $on)`: `$q->rightJoin('usuarios_datos ud', [ u.id = ud.id_usuario ])`

### Paginación
Existe un método `page` para el cálculo del offset en la paginación:

**PHP**
```PHP
$q->select('id, nombre, edad')
  ->from('usuarios')
  ->where('edad > 18')
  ->orderBy('edad DESC')
  ->groupBy('edad')
  ->having('id > 2')
  ->limit(5)
  ->page(2);
```

**JS**
```JS
q.select('id, nombre, edad')
 .from('usuarios')
 .where('edad > 18')
 .orderBy('edad DESC')
 .groupBy('edad')
 .having('id > 2')
 .limit(5)
 .page(2)
```
