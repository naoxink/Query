<?php

class Query {

  /**
   * Consulta final
   * @var string
   */
  private $query = '';

  /**
   * Tipo de consulta
   * @var string
   */
  private $type = 'SELECT';

  /**
   * Tabla donde se ejecutará la consulta (principal)
   * @var string
   */
  private $table = '';

  /**
   * Campos a devolver
   * @var array
   */
  private $fields = [];

  /**
   * Condiciones de la consulta
   * @var array
   */
  private $where = [];

  /**
   * Límite de resultados
   * @var null
   */
  private $limit = null;

  /**
   * Cantidad de resultados (paginación)
   * @var null
   */
  private $howMany = null;

  /**
   * Valores a insertar (Sólo INSERT)
   * @var array
   */
  private $values = [];

  /**
   * Valores a actualizar (Sólo UPDATE)
   * @var array
   */
  private $set = [];

  /**
   * Orden de los resultados
   * @var array
   */
  private $order = [];

  /**
   * Agrupación de resultados
   * @var array
   */
  private $group = [];

  /**
   * Tablas con las que hacer JOINs
   * @var array
   */
  private $joins = [];

  /**
   * Condiciones del HAVING
   * @var array
   */
  private $having = [];

  /**
   * Tipos soportados
   * @var [string]
   */
  private $AVAILABLE_TYPES = [ 'SELECT', 'UPDATE', 'DELETE', 'INSERT' ];

  public function __construct($type = 'SELECT'){
    if(!$this->isValidType($type)){
      throw new Exception('Invalid query type: ' . $type . '. Available types: ' . implode(', ', $this->AVAILABLE_TYPES), 1);
    }
    $this->type = strtoupper($type);
    return $this;
  }

  /**
   * Control de tipo válido
   * @param  [type]  $type Tipo de consulta
   * @return boolean       válido|no válido
   */
  private function isValidType($type){
    return in_array(strtoupper($type), $this->AVAILABLE_TYPES);
  }

  /**
   * Inicia una consulta SELECT
   * @param  array  $fields Campos
   * @param  string $table  Tabla
   * @param  array  $where  Condiciones
   * @return object         Instancia actual Query
   */
  public function select($fields = [], $table = '', $where = []){
    $this->type = 'SELECT';
    $this->addFields($fields);
    $this->table = $table;
    $this->where = $where;
    $this->cleanLimit();
    return $this;
  }

  /**
   * Inicia una consulta INSERT
   * @param  string $table  Tabla
   * @param  array  $fields Campos
   * @param  array  $values Valores
   * @return object         Instancia actual Query
   */
  public function insert($table, $fields = [], $values = []){
    $this->type = 'INSERT';
    $this->addFields($fields);
    $this->table = $table;
    $this->values = $values;
    $this->cleanLimit();
    return $this;
  }

  /**
   * Inicia una consulta DELETE
   * @return object Instancia actual Query
   */
  public function delete(){
    $this->type = 'DELETE';
    $this->cleanLimit();
    return $this;
  }

  /**
   * Inicia una consulta UPDATE
   * @param  string $table Tabla
   * @return object        Instancia actual Query
   */
  public function update($table){
    $this->type = 'UPDATE';
    $this->table = $table;
    $this->cleanLimit();
    return $this;
  }

  /**
   * Establece los campos a actualizar de un UPDATE
   * @param array $data Campos a actualizar
   */
  public function set($data){
    $this->set = $data;
    return $this;
  }

  /**
   * Establece la tabla en la que se hará la consulta
   * @param  string $table Tabla
   * @return object        Instancia actual Query
   */
  public function from($table = ''){
    $this->table = $table;
    return $this;
  }

  /**
   * Establece las condiciones de la consulta
   * @param  array  $conditions Condiciones
   * @return object             Instancia actual Query
   */
  public function where($conditions = []){
    if(is_string($conditions)) $conditions = [$conditions];
    $this->where = $conditions;
    return $this;
  }

  /**
   * Añade condiciones al where de la consulta
   * @param  array  $conditions Condiciones
   * @return object             Instancia actual Query
   */
  public function andWhere($conditions = []){
    if(is_string($conditions)) $conditions = [$conditions];
    $this->where = array_merge($this->where, $conditions);
    return $this;
  }

  /**
   * Establece los valores a insertar en una consulta INSERT
   * @param  array  $values Valores
   * @return object         Instancia actual Query
   */
  public function values($values = []){
    $this->values = $values;
    return $this;
  }

  /**
   * Establece los campos en una consulta
   * @param  array $fields Campos
   * @return object        Instancia actual Query
   */
  public function fields($fields){
    $this->addFields($fields);
    return $this;
  }

  /**
   * Establece las condiciones de un HAVING de la consulta
   * @param  array $having Condiciones
   * @return object        Instancia actual Query
   */
  public function having($having){
    if(is_string($having)) $having = [$having];
    $this->having = $having;
    return $this;
  }

  /**
   * Genera y devuelve la consulta con los parámetros actuales
   * @return string Consulta completa
   */
  public function getQuery(){
    return $this->buildQuery();
  }

  /**
   * Establece el límite de resultados de la consulta
   * @param  number $from    Límite de resultados
   * @param  number $howMany Cantidad de resultados (paginación)
   * @return object          Instancia actual Query
   */
  public function limit($from, $howMany = null){
    $this->cleanLimit();
    if(is_numeric($from)) $this->limit = (int)$from;
    if(is_numeric($howMany)) $this->howMany = (int)$howMany;
    return $this;
  }

  /**
   * Establece el orden de los resultados
   * @param  array $order Condiciones de orden
   * @return object       Instancia actual Query
   */
  public function orderBy($order){
    if(is_string($order)) $order = [$order];
    $this->order = $order;
    return $this;
  }

  /**
   * Establece el agrupado de los resultados
   * @param  array $group Condiciones de agrupado
   * @return object       Instancia actual Query
   */
  public function groupBy($group){
    if(is_string($group)) $group = [$group];
    $this->group = $group;
    return $this;
  }

  /**
   * Añade un JOIN a la consulta actual
   * @param  string $joinType Tipo de join (INNER, LEFT, RIGHT, CROSS, etc.)
   * @param  string $table    Tabla a unir
   * @param  array  $on       Condiciones del join
   * @return object           Instancia actual Query
   */
  public function join($joinType = '', $table = '', $on = null){
    if(!isset($this->joins[$joinType])){
      $this->joins[$joinType] = [];
    }
    $this->joins[$joinType][$table] = is_array($on) ? $on : null;
    return $this;
  }

  /**
   * Alias para INNER JOIN
   * @param  string $table Tabla
   * @param  array  $on    Condiciones del join
   * @return object        Instancia actual Query
   */
  public function innerJoin($table, $on = null){
    $this->join('INNER', $table, $on);
    return $this;
  }

  /**
   * Alias para LEFT JOIN
   * @param  string $table Tabla
   * @param  array  $on    Condiciones del join
   * @return object        Instancia actual Query
   */
  public function leftJoin($table, $on = null){
    $this->join('LEFT', $table, $on);
    return $this;
  }

  /**
   * Alias para RIGHT JOIN
   * @param  string $table Tabla
   * @param  array  $on    Condiciones del join
   * @return object        Instancia actual Query
   */
  public function rightJoin($table, $on = null){
    $this->join('RIGHT', $table, $on);
    return $this;
  }

  /**
   * Página de resultados (autocálculo de offset)
   * @param  integer $page Número de página (empieza por 1)
   * @return object        Instancia actual Query
   */
  public function page($page){
    if($page < 1 || !$this->limit) return $this;
    $this->howMany = ($page - 1) * $this->limit; // La primera página es la 1
    return $this;
  }

  /**
   * Limpia el límite de la consulta actual
   * @return object Instancia actual Query
   */
  private function cleanLimit(){
    $this->limit = null;
    $this->howMany = null;
    return $this;
  }

  /**
   * Construye la consulta final con los parámetros actuales
   * @return string Consulta final
   */
  private function buildQuery(){
    $this->query = $this->type;
    $this->{'build_' . $this->type}();
    $this->addWhere();
    $this->addGroupBy();
    $this->addHaving();
    $this->addOrder();
    $this->addLimit();
    return $this->query;
  }

  /**
   * Construye la parte de la consulta SELECT
   * @return string Consulta
   */
  private function build_SELECT(){
    $this->query .= ' ' . implode(', ', $this->fields);
    $this->query .= ' FROM ' . $this->table;
    $this->addJoins();
    return $this->query;
  }

  /**
   * Construye la parte de la consulta DELETE
   * @return string Consulta
   */
  private function build_DELETE(){
    $this->query .= ' FROM ' . $this->table;
    return $this->query;
  }

  /**
   * Construye la parte de la consulta UPDATE
   * @return string Consulta
   */
  private function build_UPDATE(){
    $this->query .= ' ' . $this->table . ' SET ';
    $this->query .= implode(', ', $this->set);
    return $this->query;
  }

  /**
   * Construye la parte de la consulta INSERT
   * @return string Consulta
   */
  private function build_INSERT(){
    $this->query .= ' INTO ' . $this->table;
    if(count($this->fields)){
      $this->query .= ' (' . implode(', ', $this->fields) . ') ';
    }
    $this->query .= ' VALUES (' . implode(', ', $this->values) . ')';
    return $this->query;
  }

  /**
   * Establece el LIMIT en la consulta actual
   * @return string Consulta
   */
  private function addLimit(){
    if($this->limit !== null) $this->query .= ' LIMIT ' . $this->limit;
    if($this->limit !== null && $this->howMany !== null) $this->query .= ', ' . $this->howMany;
    return $this->query;
  }

  /**
   * Establece los JOINS en la consulta actual
   * @return string Consulta
   */
  private function addJoins(){
    if(!count(array_keys($this->joins))){
      return $this->query;
    }
    foreach($this->joins as $joinType => $joins){
      foreach($joins as $table => $on){
        $this->query .= ' ' . $joinType . ' JOIN ' . $table;
        if(!is_null($on)){
          $this->query .= ' ON ' . implode(' AND ', $on);
        }
      }
    }
    return $this->query;
  }

  /**
   * Establece el WHERE en la consulta actual
   * @return string Consulta
   */
  private function addWhere(){
    if(!in_array($this->type, array( 'SELECT', 'UPDATE', 'DELETE' ))){
      return $this->query;
    }
    if(count($this->where) > 0){
      $this->query .= ' WHERE ';
      if($this->isAssociative($this->where)){
        $flatted = [];
        foreach($this->where as $field => $value){
          $flatted[] = $this->getConditionStr($field, $value);
        }
        $this->query .= implode(' AND ', $flatted);
      }else{
        $this->query .= implode(' AND ', $this->where);
      }
    }
    return $this->query;
  }

  /**
   * Establece el HAVING en la consulta actual
   * @return string Consulta
   */
  private function addHaving(){
    if($this->type !== 'SELECT' || !is_array($this->having) || !count($this->having)){
      return $this->query;
    }
    $this->query .= ' HAVING ' . implode(' AND ', $this->having);
    return $this->query;
  }

  /**
   * Establece el GROUP BY en la consulta actual
   * @return string Consulta
   */
  private function addGroupBy(){
    if(!count($this->group) || $this->type !== 'SELECT'){
      return $this->query;
    }
    $this->query .= ' GROUP BY ' . implode(', ', $this->group);
    return $this->query;
  }

  /**
   * Establece el ORDER BY en la consulta actual
   * @return string Consulta
   */
  private function addOrder(){
    if(!count($this->order) || $this->type !== 'SELECT'){
      return $this->query;
    }
    $this->query .= ' ORDER BY ' . implode(', ', $this->order);
    return $this->query;
  }

  /**
   * Establece los campos a seleccionar
   * @param mixed $fields Campos
   */
  private function addFields($fields){
    if(is_string($fields)){
      $this->fields = array_map(function($f){
        return trim($f);
      }, explode(',', $fields));
    }else if(is_array($fields)){
      $this->fields = $fields;
    }else{
      throw new Exception("Error: invalid fields format", 1);
    }
  }

  /**
   * Indica si es un array asociativo teniendo en cuenta
   * que todas sus keys son de tipo string
   * @param  array   $values Array de valores
   * @return boolean
   */
  private function isAssociative($values){
    if(!is_array($values) || !count($values)){
      return false;
    }
    $keys = array_keys($values);
    return count(array_filter($keys, function($k) { return is_string($k); })) > 0;
  }

  /**
   * Comprueba si un valor (string) contiene un operador
   * @param  string  $value Valor
   * @return boolean
   */
  private function hasOperator($value){
    return strpos($value, '=') !== false ||
           strpos($value, '<') !== false ||
           strpos($value, '>') !== false;
  }

  /**
   * Genera y devuelve en string el valor indicado
   * @param  string $value Valor
   * @return string
   */
  private function getPreparedValue($value){
    if(is_string($value)) return '"' . $value . '"';
    if(is_numeric($value)) return $value;
    if(is_bool($value)) return $value ? 'true' : 'false';
    if(is_null($value)) return 'IS NULL';
  }

  /**
   * Genera una condición según el campo y el valor
   * @param  string $field Campo
   * @param  string $value valor
   * @return string
   */
  private function getConditionStr($field, $value){
    $valueStr = '';
    if($this->hasOperator($value)){
      $valueStr = $value;
    }else{
      if(is_null($value)){
        $valueStr .= $this->getPreparedValue($value);
      }else{
        $valueStr .= '= ' . $this->getPreparedValue($value);
      }
    }
    return $field . ' ' . $valueStr;
  }

} // Fin class Query