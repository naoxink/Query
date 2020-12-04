<?php

require 'Query.class.php';

$q = new Query();
$q->select([ 'id', 'nombre' ])
  ->from('usuarios u')
  ->join('INNER', 'datos_usuarios d', [ 'u.id = d.id_usuario' ])
  ->where([ 'edad > 30', 'pelo = "marrón"' ])
  ->andWhere([ 'nombre = "maikel"' ])
  ->orderBy([ 'nombre ASC' ])
  ->groupBy([ 'edad' ])
  ->having([ 'edad > 3' ])
  ->limit(2, 10);

// $q->insert('usuarios')
//   ->fields([ 'id', 'nombre' ])
//   ->values([ '1234', '"Gaby"' ]);

// $q->delete()
//   ->from('usuarios')
//   ->where([ 'nombre = "raule"' ])
//   ->limit(1);

// $q->update('usuarios')
//   ->set([ 'edad = 50' ])
//   ->where([ 'id = 2' ])
//   ->limit(1);

echo $q->getQuery() . "\n\n";
die();