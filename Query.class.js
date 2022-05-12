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
    this._addFields(fields)
    this.table = table
    this.where = where
    this._cleanLimit()
    return this._interface
  }

   this._insert = (table, fields = [], values = []) => {
    this.type = 'INSERT'
    this.table = table
    this._addFields(fields)
    this.values = values
    this._cleanLimit()
    return this._interface
  }

  this._delete = (table) => {
    this.type = 'DELETE'
    this.table = table
    this._cleanLimit()
    return this._interface
  }

  this._update = (table) => {
    this.type = 'UPDATE'
    this.table = table
    this._cleanLimit()
    return this._interface
  }

  this._set = (data) => {
    this.set = data
    return this._interface
  }

  this._from = (table = '') => {
    this.table = table
    return this._interface
  }

  this._where = (conditions = []) => {
    if(typeof conditions === 'string') conditions = [conditions]
    this.where = conditions
    return this._interface
  }

  this._andWhere = (conditions = []) => {
    if(typeof conditions === 'string') conditions = [conditions]
    this.where = [...this.where, ...conditions]
    return this._interface
  }

  this._values = (values = []) => {
    this.values = values
    return this._interface
  }

  this._fields = (fields) => {
    this._addFields(fields)
    return this._interface
  }

  this._having = (having) => {
    if(typeof having === 'string') having = [having]
    this.having = having
    return this._interface
  }

  this._getQuery = () => {
    return this._buildQuery()
  }

  this._limit = (from, howMany = null) => {
    this._cleanLimit()
    if(from && !isNaN(from)) this.limit = +from
    if(howMany && !isNaN(howMany)) this.howMany = +howMany
    return this._interface
  }

  this._orderBy = (order) => {
    if(typeof order === 'string') order = [order]
    this.order = order
    return this._interface
  }

  this._groupBy = (group) => {
    if(typeof group === 'string') group = [group]
    this.group = group
    return this._interface
  }

  this._join = (joinType = '', table = '', on = null) => {
    if(!this.joins[joinType]){
      this.joins[joinType] = {}
    }
    this.joins[joinType][table] = Array.isArray(on) ? on : null
    return this._interface
  }

  this._innerJoin = (table, on = null) => {
    this._join('INNER', table, on)
    return this._interface
  }

  this._leftJoin = (table, on = null) => {
    this._join('LEFT', table, on)
    return this._interface
  }

  this._rightJoin = (table, on = null) => {
    this._join('RIGHT', table, on)
    return this._interface
  }

  this._cleanLimit = () => {
    this.limit = null
    this.howMany = null
    return this._interface
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

  this._page = (page) => {
    if(!this.limit || page < 1) return this._interface
    this.howMany = (page - 1) * this.limit // La primera página es la 1
    return this._interface
  }

  this._addFields = (fields) => {
    if(typeof fields === 'string'){
      this.fields = fields.split(',').map(f => f.trim())
    }else if(Array.isArray(fields)){
      this.fields = fields
    }else{
      throw new Error('Error: invalid fields format')
    }
  }

  this._interface = {
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
      page: this._page,
    }

  return this._interface
}


module.exports = Query
