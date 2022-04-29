const Query = function(){
  this.query = ''
  this.type = ''
  this.table = ''
  this.fields = []
  this.where = []
  this.limit = null
  this.howMany = null
  this.values = []
  this.set = []
  this.order = []
  this.group = []
  this.joins = {}
  this.having = []
  this.AVAILABLE_TYPES = ['SELECT', 'UPDATE', 'DELETE', 'INSERT']

  this._isValidType = (type) => {
    return this.AVAILABLE_TYPES.includes(strtoupper(type))
  }

  this._select = (fields = [], table = '', where = []) => {
    this.type = 'SELECT'
    this.fields = fields
    this.table = table
    this.where = where
    this._cleanLimit()
    return this._outputInterface()
  }

   this._insert = (table, fields = [], values = []) => {
    this.type = 'INSERT'
    this.table = table
    this.fields = fields
    this.values = values
    this._cleanLimit()
    return this._outputInterface()
  }

  this._delete = (table) => {
    this.type = 'DELETE'
    this.table = table
    this._cleanLimit()
    return this._outputInterface()
  }

  this._update = (table) => {
    this.type = 'UPDATE'
    this.table = table
    this._cleanLimit()
    return this._outputInterface()
  }

  this._set = (data) => {
    this.set = data
    return this._outputInterface()
  }

  this._from = (table = '') => {
    this.table = table
    return this._outputInterface()
  }

  this._where = (conditions = []) => {
    this.where = conditions
    return this._outputInterface()
  }

  this._andWhere = (conditions = []) => {
    this.where = [...this.where, ...conditions]
    return this._outputInterface()
  }

  this._values = (values = []) => {
    this.values = values
    return this._outputInterface()
  }

  this._fields = (fields) => {
    this.fields = fields
    return this._outputInterface()
  }

  this._having = (having) => {
    this.having = having
    return this._outputInterface()
  }

  this._getQuery = () => {
    return this._buildQuery()
  }

  this._limit = (from, howMany = null) => {
    this._cleanLimit()
    if(!isNaN(from)) this.limit = +from
    if(!isNaN(howMany)) this.howMany = +howMany
    return this._outputInterface()
  }

  this._orderBy = (order) => {
    this.order = order
    return this._outputInterface()
  }

  this._groupBy = (group) => {
    this.group = group
    return this._outputInterface()
  }

  this._join = (joinType = '', table = '', on = null) => {
    if(!this.joins[joinType]){
      this.joins[joinType] = {}
    }
    this.joins[joinType][table] = Array.isArray(on) ? on : null
    return this._outputInterface()
  }

  this._innerJoin = (table, on = null) => {
    this._join('INNER', table, on)
    return this._outputInterface()
  }

  this._leftJoin = (table, on = null) => {
    this._join('LEFT', table, on)
    return this._outputInterface()
  }

  this._rightJoin = (table, on = null) => {
    this._join('RIGHT', table, on)
    return this._outputInterface()
  }

  this._cleanLimit = () => {
    this.limit = null
    this.howMany = null
    return this._outputInterface()
  }

  this._buildQuery = () => {
    this.query = this.type
    this[`_build_${this.type}`]()
    this._addWhere()
    this._addGroupBy()
    this._addHaving()
    this._addOrder()
    this._addLimit()
    return this.query
  }

  this._build_SELECT = () => {
    this.query += ` ${this.fields.join(', ')}`
    this.query += ` FROM ${this.table}`
    this._addJoins()
    return this.query
  }

  this._build_DELETE = () => {
    this.query += ` FROM ${this.table}`
    return this.query
  }

  this._build_UPDATE = () => {
    this.query += ` ${this.table} SET `
    this.query += this.set.join(', ')
    return this.query
  }

  this._build_INSERT = () => {
    this.query += ` INTO ${this.table}`
    if(this.fields.length){
      this.query += ` (${this.fields.join(', ')}) `
    }
    this.query += ` VALUES (${this.values})`
    return this.query
  }

  this._addLimit = () => {
    if(this.limit !== null) this.query += ` LIMIT ${this.limit}`
    if(this.limit !== null && this.howMany !== null) this.query += `, ${this.howMany}`
    return this.query
  }

  this._addJoins = () => {
    if(!Object.keys(this.joins).length){
      return this.query
    }
    for(let joinType of this.joins){
      for(let table of this.joins[joinType]){
        this.query += ` ${joinType} JOIN ${table}`
        if(this.joins[joinType][table]){
          this.query += ` ON ${this.joins[joinType][table].join(' AND ')}`
        }
      }
    }
    return this.query
  }

  this._addWhere = () => {
    if(![ 'SELECT', 'UPDATE', 'DELETE' ].includes(this.type)){
      return this.query
    }
    if(this.where.length > 0){
      this.query += ` WHERE ${this.where.join(' AND ')}`
    }
    return this.query
  }

  this._addHaving = () => {
    if(this.type !== 'SELECT'){
      return this.query
    }
    if(this.having){
      this.query += ` HAVING ${this.having.join(' AND ')}`
    }
    return this.query
  }

  this._addGroupBy = () => {
    if(!this.group.length || this.type !== 'SELECT'){
      return this.query
    }
    this.query += ` GROUP BY ${this.group.join(', ')}`
    return this.query
  }

  this._addOrder = () => {
    if(!this.order.length || this.type !== 'SELECT'){
      return this.query
    }
    this.query += ` ORDER BY ${this.order.join(', ')}`
    return this.query
  }

  this._outputInterface = () => {
    return {
      select: this._select,
      insert: this._insert,
      delete: this._delete,
      update: this._update,
      set: this._set,
      from: this._from,
      where: this._where,
      andWhere: this._andWhere,
      values: this._values,
      fields: this._fields,
      having: this._having,
      getQuery: this._getQuery,
      limit: this._limit,
      orderBy: this._orderBy,
      groupBy: this._groupBy,
      join: this._join,
      innerJoin: this._innerJoin,
      leftJoin: this._leftJoin,
      rightJoin: this._rightJoin,
      cleanLimit: this._cleanLimit,
    }
  }

  return this._outputInterface()
}


module.exports = Query
