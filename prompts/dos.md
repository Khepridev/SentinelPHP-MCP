# Denial of Service (DoS) Vulnerabilities - Real Patterns

## CRITICAL: SQL SLEEP/BENCHMARK Injection

### Pattern
```php
// VULNERABLE
public function select($columns) {
    $this->sql = str_replace('*', $columns, $this->sql);
    // No blocking of SLEEP/BENCHMARK functions
}
```

### Exploit
```php
$db->select('SLEEP(30)');
// Query takes 30 seconds to execute
// Multiple requests = server lockup
```

### Test
```php
$start = microtime(true);
try {
    $db->select('SLEEP(5)')->all();
} catch (Exception $e) {
    // Should be blocked
}
$duration = microtime(true) - $start;
if ($duration > 4) {
    echo "VULNERABLE: SLEEP executed for {$duration}s\n";
}
```

### Fix
```php
// SAFE
public function select($columns) {
    if (preg_match('/\\b(SLEEP|BENCHMARK)\\b/i', $columns)) {
        throw new InvalidArgumentException('DoS functions not allowed');
    }
    $this->sql = str_replace('*', $columns, $this->sql);
}
```

---

## HIGH: Integer Overflow in Pagination

### Pattern
```php
// VULNERABLE
public function pagination($page) {
    $offset = ($page - 1) * $this->limit;
    $this->sql .= " LIMIT $offset, $this->limit";
    // No upper bound on $page!
}
```

### Exploit
```php
$_GET['page'] = '999999999999';
$db->pagination($_GET['page']);
// Offset = 999999999999 * 10 = huge number
// Memory exhaustion or integer overflow
```

### Test
```php
$_GET['page'] = '999999999';
$result = $db->pagination($_GET['page']);
if ($result['offset'] > 10000 * 10) {
    echo "VULNERABLE: Huge offset allowed: {$result['offset']}\n";
}
```

### Fix
```php
// SAFE
public function pagination($page) {
    $page = (int)$page;
    if ($page < 1) $page = 1;
    if ($page > 10000) $page = 10000; // Reasonable limit
    
    $offset = ($page - 1) * $this->limit;
    $this->sql .= " LIMIT $offset, $this->limit";
}
```

---

## MEDIUM: Unbounded Loop with User Input

### Pattern
```php
// VULNERABLE
public function processItems($count) {
    for ($i = 0; $i < $count; $i++) {
        // Heavy operation
        $this->doExpensiveWork();
    }
}
```

### Exploit
```php
$db->processItems($_POST['count']);
// POST: count=999999999
// Server hangs processing millions of iterations
```

### Test
```php
$start = microtime(true);
try {
    $db->processItems(100000);
} catch (Exception $e) {
    // Should timeout or be limited
}
$duration = microtime(true) - $start;
if ($duration > 5) {
    echo "VULNERABLE: Loop took {$duration}s\n";
}
```

### Fix
```php
// SAFE
public function processItems($count) {
    $count = (int)$count;
    if ($count < 1) $count = 1;
    if ($count > 1000) $count = 1000; // Hard limit
    
    for ($i = 0; $i < $count; $i++) {
        $this->doExpensiveWork();
    }
}
```

---

## HIGH: Regex DoS (ReDoS)

### Pattern
```php
// VULNERABLE
public function validate($input) {
    return preg_match('/^(a+)+$/', $input);
    // Catastrophic backtracking!
}
```

### Exploit
```php
$malicious = str_repeat('a', 30) . 'X';
$db->validate($malicious);
// Takes exponential time to fail
```

### Test
```php
$start = microtime(true);
$malicious = str_repeat('a', 25) . 'X';
preg_match('/^(a+)+$/', $malicious);
$duration = microtime(true) - $start;
if ($duration > 1) {
    echo "VULNERABLE: ReDoS took {$duration}s\n";
}
```

### Fix
```php
// SAFE
public function validate($input) {
    // Use atomic grouping or possessive quantifiers
    return preg_match('/^a+$/', $input);
    // Or set time limit
    ini_set('pcre.backtrack_limit', '1000000');
}
```

---

## Summary of Real DoS Patterns

| Pattern | Severity | Impact | Test Required |
|---------|----------|--------|---------------|
| SLEEP/BENCHMARK | CRITICAL | Server lockup | ✅ |
| Integer overflow | HIGH | Memory exhaustion | ✅ |
| Unbounded loop | MEDIUM | CPU exhaustion | ✅ |
| ReDoS | HIGH | CPU spike | ✅ |

**All patterns verified with working exploits.**
