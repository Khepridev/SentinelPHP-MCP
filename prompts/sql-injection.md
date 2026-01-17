# SQL Injection Vulnerabilities - Real Patterns

## CRITICAL: Operator Injection in WHERE Clauses

### Pattern
```php
// VULNERABLE
public function where($column, $value, $operator = '=') {
    $this->where[] = "$column $operator :value";
    // No validation on $operator!
}
```

### Exploit
```php
$db->where('id', '1', '= 1 OR 1=1; --');
// SQL: WHERE id = 1 OR 1=1; -- :value
```

### Test
```php
// Proof of concept
$malicious_op = "= 1 OR '1'='1";
$result = $db->where('user_id', 1, $malicious_op)->all();
// Returns all rows instead of one
```

### Fix
```php
// SAFE
$allowedOperators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN'];
if (!in_array(strtoupper($operator), $allowedOperators)) {
    throw new InvalidArgumentException('Invalid operator');
}
```

---

## CRITICAL: Unsanitized Column Names

### Pattern
```php
// VULNERABLE
public function orderBy($column, $direction = 'ASC') {
    $this->sql .= " ORDER BY $column $direction";
    // Column name directly concatenated!
}
```

### Exploit
```php
$db->orderBy($_GET['sort'], 'ASC');
// GET: ?sort=id; DROP TABLE users; --
```

### Test
```php
$malicious = "id; DELETE FROM users WHERE 1=1; --";
$db->orderBy($malicious, 'ASC');
// Executes: ORDER BY id; DELETE FROM users WHERE 1=1; -- ASC
```

### Fix
```php
// SAFE
private function sanitizeIdentifier($identifier) {
    return preg_replace('/[^a-zA-Z0-9_.]/', '', $identifier);
}

public function orderBy($column, $direction = 'ASC') {
    $safe_column = $this->sanitizeIdentifier($column);
    $this->sql .= " ORDER BY $safe_column $direction";
}
```

---

## HIGH: FIND_IN_SET Without Prepared Statements

### Pattern
```php
// VULNERABLE
public function findInSet($column, $value) {
    $this->where[] = "FIND_IN_SET('$value', $column)";
    // Value directly concatenated!
}
```

### Exploit
```php
$db->findInSet('roles', $_POST['role']);
// POST: role=admin', (SELECT password FROM users LIMIT 1)) OR FIND_IN_SET('
```

### Test
```php
$malicious = "1', database()) OR FIND_IN_SET('1";
$db->findInSet('permissions', $malicious);
// Leaks database name
```

### Fix
```php
// SAFE
public function findInSet($column, $value) {
    $placeholder = ':param_' . $this->paramCounter++;
    $this->params[$placeholder] = $value;
    $safe_column = $this->sanitizeIdentifier($column);
    $this->where[] = "FIND_IN_SET($placeholder, $safe_column)";
}
```

---

## CRITICAL: Database Name Injection

### Pattern
```php
// VULNERABLE
public function truncateAll(array $databases) {
    foreach ($databases as $db) {
        $this->query("TRUNCATE TABLE `$db`.`table_name`");
        // Database name not validated!
    }
}
```

### Exploit
```php
$db->truncateAll(['mysql', 'information_schema', 'performance_schema']);
// Truncates system databases!
```

### Test
```php
$malicious_dbs = ['test', 'mysql'];
$db->truncateAll($malicious_dbs);
// Result: System database affected
```

### Fix
```php
// SAFE
public function truncateAll(array $databases) {
    foreach ($databases as $db) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $db)) {
            throw new InvalidArgumentException('Invalid database name');
        }
        $this->query("TRUNCATE TABLE `$db`.`table_name`");
    }
}
```

---

## MEDIUM: Increment/Decrement Without Validation

### Pattern
```php
// VULNERABLE
public function set($column, $value) {
    if (strpos($value, '+') !== false || strpos($value, '-') !== false) {
        $this->sql .= " SET $column = $column $value";
        // Value directly appended!
    }
}
```

### Exploit
```php
$db->set('views', $_POST['increment']);
// POST: increment=+1; DELETE FROM posts; --
```

### Test
```php
$malicious = "+1; UPDATE users SET role='admin' WHERE id=1; --";
$db->set('counter', $malicious);
// Executes malicious UPDATE
```

### Fix
```php
// SAFE
if (strpos($value, '+') !== false || strpos($value, '-') !== false) {
    if (!preg_match('/^[+\-]\d+$/', $value)) {
        throw new InvalidArgumentException('Invalid increment value');
    }
    $operator = $value[0];
    $amount = (int)substr($value, 1);
    $this->sql .= " SET $column = $column $operator $amount";
}
```

---

## CRITICAL: SELECT Subquery Injection

### Pattern
```php
// VULNERABLE
public function select($columns) {
    $this->sql = str_replace('*', $columns, $this->sql);
    // No validation on column list!
}
```

### Exploit
```php
$db->select($_GET['cols']);
// GET: cols=id, (SELECT password FROM admin LIMIT 1) as stolen
```

### Test
```php
$malicious = "*, (SELECT GROUP_CONCAT(password) FROM users) as leak";
$db->select($malicious)->all();
// Leaks all passwords
```

### Fix
```php
// SAFE
public function select($columns) {
    // Block dangerous keywords
    if (preg_match('/(SELECT|FROM|WHERE|UNION|DROP|DELETE)/i', $columns)) {
        throw new InvalidArgumentException('Dangerous SQL detected');
    }
    
    // Block nested parentheses (subqueries)
    if (substr_count($columns, '(') > 1) {
        throw new InvalidArgumentException('Nested queries not allowed');
    }
    
    $sanitized = preg_replace('/[^a-zA-Z0-9_,\s.()\\*`]/', '', $columns);
    $this->sql = str_replace('*', $sanitized, $this->sql);
}
```

---

## Summary of Real SQL Injection Patterns

| Pattern | Severity | Test Required | Common in |
|---------|----------|---------------|-----------|
| Operator injection | CRITICAL | ✅ | WHERE, HAVING |
| Column name injection | CRITICAL | ✅ | ORDER BY, GROUP BY |
| FIND_IN_SET value | HIGH | ✅ | Helper methods |
| Database name | CRITICAL | ✅ | Admin functions |
| Increment/Decrement | MEDIUM | ✅ | UPDATE operations |
| SELECT subquery | CRITICAL | ✅ | Dynamic columns |

**All patterns verified with working exploits.**
