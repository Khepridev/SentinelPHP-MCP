# PHP SECURITY AUDIT PROTOCOL

**Version**: 1.0.0  
**Purpose**: Systematic security vulnerability detection in PHP projects  
**Status**: MANDATORY - Zero tolerance for deviations

---

## CORE PRINCIPLES

### 1. ZERO TRUST
- Assume every input is malicious until proven safe
- Never trust: user input, database data, config files, environment variables
- Always verify: sanitization, validation, escaping, encoding

### 2. TEST-DRIVEN VERIFICATION
- Every claimed vulnerability MUST be proven with exploit code
- No theoretical vulnerabilities without proof
- Distinguish between:
  - **Real vulnerabilities** (exploitable)
  - **Design choices** (not security issues)
  - **Best practices** (improvements, not critical)

### 3. HONEST REPORTING
- Report only REAL, EXPLOITABLE vulnerabilities
- Do not exaggerate severity
- Do not report design preferences as security issues
- Admit when uncertain

---

## VULNERABILITY CATEGORIES

### CRITICAL: Immediate Exploitation Possible

1. **SQL Injection**
   - Unsanitized input in SQL structure (table/column names)
   - Missing prepared statements for values
   - Operator injection in WHERE/HAVING
   - Database name injection

2. **Remote Code Execution (RCE)**
   - `eval()`, `system()`, `exec()` with user input
   - Unsafe deserialization
   - Template injection

3. **Authentication Bypass**
   - Missing authentication checks
   - Weak password hashing (MD5, SHA1)
   - Session fixation

### HIGH: Significant Security Impact

4. **Cross-Site Scripting (XSS)**
   - Unescaped output in HTML context
   - JavaScript protocol in URLs
   - DOM-based XSS

5. **File Upload Vulnerabilities**
   - Missing MIME type validation
   - Path traversal in file operations
   - Executable file uploads

6. **Denial of Service (DoS)**
   - Unbounded loops with user input
   - Resource exhaustion (SLEEP, BENCHMARK)
   - Integer overflow

### MEDIUM: Requires Specific Conditions

7. **Cross-Site Request Forgery (CSRF)**
   - State-changing operations without tokens
   - Missing SameSite cookie flags

8. **Information Disclosure**
   - Error messages revealing structure
   - Debug mode in production
   - Sensitive data in logs

9. **Insecure Direct Object References (IDOR)**
   - Missing authorization checks
   - Predictable IDs without validation

### LOW: Limited Impact or Requires Chaining

10. **Session Management Issues**
    - Missing HttpOnly/Secure flags
    - Weak session ID generation
    - No session timeout

---

## AUDIT METHODOLOGY

### PHASE 1: RECONNAISSANCE

**Objective**: Understand the codebase structure

**Steps**:
1. Identify all entry points (GET, POST, COOKIE, SESSION, FILES)
2. Map data flow from input to output/storage
3. List all database operations
4. Identify authentication/authorization mechanisms
5. Find file operations and external command execution

**Deliverable**: Complete inventory of attack surface

---

### PHASE 2: STATIC ANALYSIS

**Objective**: Find vulnerabilities through code review

**For Each Method**:

#### Question Set A: Input Analysis
1. What inputs does this accept?
2. Can user control ANY part of these inputs (direct or indirect)?
3. What's the WORST possible input value?
4. Is there validation? Is it sufficient?
5. Can validation be bypassed?

#### Question Set B: SQL Operations
1. Are table/column names sanitized?
2. Are values using prepared statements?
3. Can operators be injected?
4. Are special SQL functions whitelisted?
5. Test: `'; DROP TABLE users; --`

#### Question Set C: Output Operations
1. Where does data get displayed?
2. Is output escaped for context (HTML, JS, URL)?
3. Can HTML tags be injected?
4. Test: `<script>alert(1)</script>`

#### Question Set D: File Operations
1. Are file paths validated?
2. Can path traversal occur (`../../../etc/passwd`)?
3. Are file extensions whitelisted?
4. Is MIME type verified?
5. Test: `shell.php.jpg`

---

### PHASE 3: DYNAMIC TESTING

**Objective**: Prove vulnerabilities are exploitable

**For Each Suspected Vulnerability**:

1. **Write Exploit Code**
   ```php
   // Example: SQL Injection test
   $malicious = "1' OR '1'='1";
   $result = $db->where('id', $malicious)->all();
   ```

2. **Execute Test**
   - Run exploit in controlled environment
   - Document actual behavior
   - Capture error messages

3. **Verify Impact**
   - Did exploit succeed?
   - What data was exposed/modified?
   - Can it be chained with other vulnerabilities?

4. **Classification**
   - ✅ **VULNERABLE**: Exploit succeeded
   - ⚠️ **PARTIAL**: Works under specific conditions
   - ❌ **FALSE ALARM**: Protected or not exploitable

---

### PHASE 4: SEVERITY ASSESSMENT

**Use CVSS-like scoring**:

**CRITICAL** (9.0-10.0):
- Remote code execution
- SQL injection with admin access
- Authentication bypass

**HIGH** (7.0-8.9):
- XSS with session theft
- File upload leading to RCE
- Privilege escalation

**MEDIUM** (4.0-6.9):
- CSRF on sensitive operations
- Information disclosure
- DoS requiring resources

**LOW** (0.1-3.9):
- Missing security headers
- Weak session configuration
- Information leakage (minor)

---

## COMMON PITFALLS TO AVOID

### ❌ FALSE POSITIVES

1. **Public Properties ≠ Vulnerabilities**
   - If attacker can set properties, they already have code execution
   - Example: `$db->debug = true` is NOT a vulnerability

2. **Design Choices ≠ Security Issues**
   - Customizable templates are features, not XSS
   - Example: `$db->paginationItem` is by design

3. **Prepared Statements Work**
   - Don't second-guess PDO without proof
   - Example: `set(['col' => 'DATABASE()'])` binds as string, not executed

4. **Context Matters**
   - `htmlspecialchars()` is sufficient for HTML context
   - Don't require JSON encoding for HTML output

### ✅ TRUE POSITIVES

1. **Operator Injection**
   ```php
   // VULNERABLE
   $db->where('id', 1, $_GET['op']); // op = "= 1 OR 1=1"
   ```

2. **JavaScript Protocol XSS**
   ```php
   // VULNERABLE
   echo '<a href="' . htmlspecialchars($url) . '">'; // url = "javascript:alert(1)"
   ```

3. **Database Name Injection**
   ```php
   // VULNERABLE
   $db->truncateAll($_POST['dbs']); // dbs = ['mysql', 'information_schema']
   ```

---

## REPORTING FORMAT

### For Each Vulnerability:

```markdown
## [SEVERITY] Vulnerability Name

**Location**: `file.php:123` in `functionName()`

**Category**: SQL Injection / XSS / RCE / etc.

**Description**:
Clear explanation of the vulnerability

**Proof of Concept**:
```php
// Exploit code that demonstrates the issue
$malicious = "payload";
$result = vulnerable_function($malicious);
```

**Impact**:
- What can attacker achieve?
- What data is at risk?
- Can it be chained?

**Evidence**:
- Test results
- Error messages
- Screenshots (if applicable)

**Fix**:
```php
// Corrected code
$safe = sanitize($input);
$result = safe_function($safe);
```

**Severity Justification**:
Why this severity level was assigned
```

---

## TESTING CHECKLIST

### SQL Injection
- [ ] Test with `'; DROP TABLE users; --`
- [ ] Test with `1' OR '1'='1`
- [ ] Test operator injection
- [ ] Test UNION injection
- [ ] Test blind SQL injection
- [ ] Test time-based injection (SLEEP)

### XSS
- [ ] Test with `<script>alert(1)</script>`
- [ ] Test with `javascript:alert(1)`
- [ ] Test with `<img src=x onerror=alert(1)>`
- [ ] Test in different contexts (HTML, JS, URL)
- [ ] Test DOM-based XSS

### File Upload
- [ ] Test with `shell.php`
- [ ] Test with `shell.php.jpg`
- [ ] Test with `../../../shell.php`
- [ ] Test MIME type bypass
- [ ] Test double extension

### Authentication
- [ ] Test without credentials
- [ ] Test with weak passwords
- [ ] Test session fixation
- [ ] Test privilege escalation

---

## FINAL VERIFICATION

Before submitting report:

1. **Re-test All Findings**
   - Confirm each vulnerability is still exploitable
   - Verify severity assessment

2. **Remove False Positives**
   - Delete any unproven claims
   - Downgrade theoretical issues

3. **Honest Assessment**
   - Would I trust this code with my data?
   - Am I being accurate or trying to impress?
   - Did I test or just assume?

4. **External Review**
   - Have another person verify findings
   - Can they reproduce exploits?

---

## COMMITMENT

**I WILL**:
- ✅ Test every claimed vulnerability
- ✅ Provide proof of concept for each finding
- ✅ Distinguish real vulnerabilities from design choices
- ✅ Be honest about severity
- ✅ Admit when I'm uncertain

**I WILL NOT**:
- ❌ Report theoretical vulnerabilities without proof
- ❌ Exaggerate severity to appear thorough
- ❌ Confuse best practices with security issues
- ❌ Assume vulnerabilities without testing
- ❌ Report design choices as vulnerabilities

---

**This protocol is binding. Violations indicate failed audit.**

*Last Updated: 2026-01-17*  
*Version: 1.0.0*  
*Status: ACTIVE*
