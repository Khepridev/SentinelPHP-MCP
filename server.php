#!/usr/bin/env php
<?php

/**
 * PHP Security Audit MCP Server
 * 
 * General-purpose security vulnerability scanner for PHP projects
 * Based on PROTOCOL.md systematic methodology
 * WITH LEARNING SYSTEM - Tracks history, learns from real vulnerabilities
 * 
 * @version 1.1.0
 * @author Khepridev
 * @link https://github.com/Khepridev
 * @link https://khepridev.xyz
 * @link https://x.com/Khepridev
 */

require_once __DIR__ . '/src/LearningSystem.php';

class PhpSecurityAuditServer
{
    private $stdin;
    private $stdout;
    private $stderr;
    private $protocol;
    private $learningSystem;

    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');

        // Load protocol
        $this->protocol = $this->loadProtocol();

        // Initialize learning system
        $this->learningSystem = new LearningSystem(__DIR__);
        $this->log("Learning System initialized");
        $stats = $this->learningSystem->getStatistics();
        $this->log("Knowledge Base: {$stats['total_analyses']} analyses, {$stats['unique_patterns']} patterns");
    }

    private function loadProtocol()
    {
        $protocolFile = __DIR__ . '/PROTOCOL.md';
        if (file_exists($protocolFile)) {
            return file_get_contents($protocolFile);
        }
        return null;
    }

    public function run()
    {
        $this->log("PHP Security Audit MCP Server started");
        $this->log("Protocol: " . (__DIR__ . '/PROTOCOL.md'));

        while (true) {
            $line = fgets($this->stdin);
            if ($line === false) {
                break;
            }

            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                $request = json_decode($line, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->sendError(-32700, 'Parse error');
                    continue;
                }

                $response = $this->handleRequest($request);
                if ($response !== null) {
                    $this->sendResponse($response);
                }
            } catch (Exception $e) {
                $this->log("Error: " . $e->getMessage());
                $this->sendError(-32603, 'Internal error: ' . $e->getMessage());
            }
        }
    }

    private function handleRequest($request)
    {
        if (!isset($request['method'])) {
            return $this->createErrorResponse(-32600, 'Invalid Request');
        }

        $method = $request['method'];
        $params = $request['params'] ?? [];
        $id = $request['id'] ?? null;

        switch ($method) {
            case 'initialize':
                return $this->handleInitialize($params, $id);

            case 'tools/list':
                return $this->handleToolsList($id);

            case 'tools/call':
                return $this->handleToolsCall($params, $id);

            case 'prompts/list':
                return $this->handlePromptsList($id);

            case 'prompts/get':
                return $this->handlePromptsGet($params, $id);

            case 'notifications/initialized':
                return null;

            default:
                return $this->createErrorResponse(-32601, 'Method not found', $id);
        }
    }

    private function handleInitialize($params, $id)
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => ['listChanged' => false],
                    'prompts' => ['listChanged' => false]
                ],
                'serverInfo' => [
                    'name' => 'php-security-audit-mcp',
                    'version' => '1.0.0'
                ]
            ]
        ];
    }

    private function handleToolsList($id)
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'tools' => [
                    [
                        'name' => 'analyze_php_security',
                        'description' => 'Analyzes PHP code for security vulnerabilities following PROTOCOL.md',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => [
                                    'type' => 'string',
                                    'description' => 'PHP code to analyze'
                                ],
                                'vulnerability_types' => [
                                    'type' => 'array',
                                    'description' => 'Types to check: sql_injection, xss, file_upload, csrf, auth, dos, info_disclosure',
                                    'items' => ['type' => 'string'],
                                    'default' => ['sql_injection', 'xss', 'dos']
                                ],
                                'severity_filter' => [
                                    'type' => 'string',
                                    'description' => 'Minimum severity: low, medium, high, critical',
                                    'default' => 'medium'
                                ],
                                'test_mode' => [
                                    'type' => 'boolean',
                                    'description' => 'Generate proof-of-concept exploits',
                                    'default' => true
                                ]
                            ],
                            'required' => ['code']
                        ]
                    ],
                    [
                        'name' => 'get_protocol',
                        'description' => 'Returns the security audit protocol (PROTOCOL.md)',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => (object) []
                        ]
                    ],
                    [
                        'name' => 'get_knowledge_base',
                        'description' => 'Returns learned patterns and statistics from previous analyses',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => (object) []
                        ]
                    ],
                    [
                        'name' => 'get_recent_analyses',
                        'description' => 'Returns recent analysis history',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'limit' => [
                                    'type' => 'number',
                                    'description' => 'Number of recent analyses to return',
                                    'default' => 10
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function handleToolsCall($params, $id)
    {
        if (!isset($params['name'])) {
            return $this->createErrorResponse(-32602, 'Invalid params', $id);
        }

        $toolName = $params['name'];
        $arguments = $params['arguments'] ?? [];

        switch ($toolName) {
            case 'analyze_php_security':
                return $this->analyzePhpSecurity($arguments, $id);

            case 'get_protocol':
                return $this->getProtocol($id);

            case 'get_knowledge_base':
                return $this->getKnowledgeBase($id);

            case 'get_recent_analyses':
                $limit = $arguments['limit'] ?? 10;
                return $this->getRecentAnalyses($limit, $id);

            default:
                return $this->createErrorResponse(-32601, 'Tool not found', $id);
        }
    }

    private function handlePromptsList($id)
    {
        $prompts = [];
        $promptsDir = __DIR__ . '/prompts';

        if (is_dir($promptsDir)) {
            $files = glob($promptsDir . '/*.md');
            foreach ($files as $file) {
                $name = basename($file, '.md');
                $prompts[] = [
                    'name' => $name,
                    'description' => 'Real vulnerability patterns for ' . str_replace('-', ' ', $name)
                ];
            }
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'prompts' => $prompts
            ]
        ];
    }

    private function handlePromptsGet($params, $id)
    {
        if (!isset($params['name'])) {
            return $this->createErrorResponse(-32602, 'Invalid params', $id);
        }

        $promptFile = __DIR__ . '/prompts/' . $params['name'] . '.md';
        if (!file_exists($promptFile)) {
            return $this->createErrorResponse(-32602, 'Prompt not found', $id);
        }

        $content = file_get_contents($promptFile);

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            'type' => 'text',
                            'text' => $content
                        ]
                    ]
                ]
            ]
        ];
    }

    private function analyzePhpSecurity($arguments, $id)
    {
        try {
            $code = $arguments['code'] ?? '';
            $vulnTypes = $arguments['vulnerability_types'] ?? ['sql_injection', 'xss', 'dos'];
            $severityFilter = $arguments['severity_filter'] ?? 'medium';
            $testMode = $arguments['test_mode'] ?? true;

            if (empty($code)) {
                throw new Exception('No code provided');
            }

            // Perform analysis
            $vulnerabilities = $this->performSecurityAnalysis($code, $vulnTypes, $testMode);

            // Filter by severity
            $vulnerabilities = $this->filterBySeverity($vulnerabilities, $severityFilter);

            // Log analysis to learning system
            $logId = $this->learningSystem->logAnalysis($code, $vulnerabilities, [
                'vulnerability_types' => $vulnTypes,
                'severity_filter' => $severityFilter,
                'test_mode' => $testMode
            ]);

            $this->log("Analysis logged: $logId - Found " . count($vulnerabilities) . " vulnerabilities");

            // Format results
            $result = $this->formatSecurityResults($vulnerabilities, $testMode);

            // Add log info to result
            $result .= "\n\n---\n\n";
            $result .= "📊 **Analysis ID**: `$logId`\n";
            $result .= "📁 **HTML Report**: `analysis/" . date('Y-m-d_H-i-s') . "_report.html`\n";

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $result
                        ]
                    ]
                ]
            ];
        } catch (Exception $e) {
            return $this->createErrorResponse(-32603, 'Analysis failed: ' . $e->getMessage(), $id);
        }
    }

    private function getProtocol($id)
    {
        if ($this->protocol === null) {
            return $this->createErrorResponse(-32603, 'Protocol not found', $id);
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $this->protocol
                    ]
                ]
            ]
        ];
    }

    private function getKnowledgeBase($id)
    {
        $stats = $this->learningSystem->getStatistics();
        $patterns = $this->learningSystem->getLearnedPatterns();

        $result = "📚 **KNOWLEDGE BASE**\n\n";
        $result .= "## Statistics\n\n";
        $result .= "- **Total Analyses**: {$stats['total_analyses']}\n";
        $result .= "- **Total Vulnerabilities Found**: {$stats['total_vulnerabilities']}\n";
        $result .= "- **Unique Patterns**: {$stats['unique_patterns']}\n";
        $result .= "- **Verified Exploits**: {$stats['verified_exploits']}\n";
        $result .= "- **Last Updated**: {$stats['last_updated']}\n\n";

        if (!empty($patterns)) {
            $result .= "## Learned Patterns\n\n";

            // Sort by count
            uasort($patterns, function ($a, $b) {
                return $b['count'] - $a['count'];
            });

            foreach ($patterns as $pattern => $data) {
                $result .= "### `$pattern`\n";
                $result .= "- **Occurrences**: {$data['count']}\n";
                $result .= "- **Severity**: {$data['severity']}\n";
                $result .= "- **First Seen**: {$data['first_seen']}\n\n";
            }
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $result
                    ]
                ]
            ]
        ];
    }

    private function getRecentAnalyses($limit, $id)
    {
        $analyses = $this->learningSystem->getRecentAnalyses($limit);

        $result = "📊 **RECENT ANALYSES**\n\n";

        if (empty($analyses)) {
            $result .= "No analyses found.\n";
        } else {
            foreach ($analyses as $analysis) {
                $result .= "### Analysis: `{$analysis['id']}`\n";
                $result .= "- **Timestamp**: {$analysis['timestamp']}\n";
                $result .= "- **Vulnerabilities**: {$analysis['vulnerabilities_found']}\n";
                $result .= "- **Severity Breakdown**: ";
                $result .= "Critical: {$analysis['severity_breakdown']['CRITICAL']}, ";
                $result .= "High: {$analysis['severity_breakdown']['HIGH']}, ";
                $result .= "Medium: {$analysis['severity_breakdown']['MEDIUM']}, ";
                $result .= "Low: {$analysis['severity_breakdown']['LOW']}\n";
                $result .= "- **Code Size**: {$analysis['code_length']} bytes\n\n";
            }
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $result
                    ]
                ]
            ]
        ];
    }

    private function performSecurityAnalysis($code, $vulnTypes, $testMode)
    {
        $vulnerabilities = [];

        // SQL Injection checks
        if (in_array('sql_injection', $vulnTypes)) {
            $vulnerabilities = array_merge($vulnerabilities, $this->checkSqlInjection($code, $testMode));
        }

        // XSS checks
        if (in_array('xss', $vulnTypes)) {
            $vulnerabilities = array_merge($vulnerabilities, $this->checkXss($code, $testMode));
        }

        // DoS checks
        if (in_array('dos', $vulnTypes)) {
            $vulnerabilities = array_merge($vulnerabilities, $this->checkDos($code, $testMode));
        }

        return $vulnerabilities;
    }

    private function checkSqlInjection($code, $testMode)
    {
        $vulnerabilities = [];

        // Check for unsanitized WHERE operators
        if (preg_match('/->where\s*\([^)]+\$[a-zA-Z_][a-zA-Z0-9_]*\s*\)/', $code)) {
            $vulnerabilities[] = [
                'name' => 'SQL Injection - Operator Injection',
                'severity' => 'CRITICAL',
                'description' => 'WHERE clause accepts unsanitized operator parameter',
                'pattern' => 'where($column, $value, $operator) without validation',
                'test' => $testMode ? '$db->where("id", 1, "= 1 OR 1=1")' : null
            ];
        }

        // Check for unsanitized ORDER BY
        if (preg_match('/ORDER\s+BY\s+[\'"]?\$[a-zA-Z_]/', $code)) {
            $vulnerabilities[] = [
                'name' => 'SQL Injection - Column Name Injection',
                'severity' => 'CRITICAL',
                'description' => 'ORDER BY uses unsanitized column name',
                'pattern' => 'ORDER BY $column without sanitization',
                'test' => $testMode ? '$db->orderBy($_GET["sort"])' : null
            ];
        }

        // Check for FIND_IN_SET without prepared statements
        if (preg_match('/FIND_IN_SET\s*\([^:][^,]+,/', $code)) {
            $vulnerabilities[] = [
                'name' => 'SQL Injection - FIND_IN_SET Value',
                'severity' => 'HIGH',
                'description' => 'FIND_IN_SET value not using prepared statement',
                'pattern' => 'FIND_IN_SET($value, $column) without binding',
                'test' => $testMode ? '$db->findInSet("roles", $_POST["role"])' : null
            ];
        }

        return $vulnerabilities;
    }

    private function checkXss($code, $testMode)
    {
        $vulnerabilities = [];

        // Check for unescaped echo
        if (preg_match('/echo\s+\$[a-zA-Z_][a-zA-Z0-9_]*(?!\s*,\s*ENT_)/', $code)) {
            $vulnerabilities[] = [
                'name' => 'XSS - Unescaped Output',
                'severity' => 'HIGH',
                'description' => 'User data echoed without htmlspecialchars()',
                'pattern' => 'echo $variable without escaping',
                'test' => $testMode ? 'echo $_GET["msg"]' : null
            ];
        }

        // Check for javascript: protocol
        if (preg_match('/href\s*=.*\$[a-zA-Z_]/', $code) && !preg_match('/javascript:/i', $code)) {
            $vulnerabilities[] = [
                'name' => 'XSS - JavaScript Protocol',
                'severity' => 'CRITICAL',
                'description' => 'URL in href not checked for javascript: protocol',
                'pattern' => '<a href="$url"> without protocol validation',
                'test' => $testMode ? 'href="javascript:alert(1)"' : null
            ];
        }

        return $vulnerabilities;
    }

    private function checkDos($code, $testMode)
    {
        $vulnerabilities = [];

        // Check for SLEEP/BENCHMARK
        if (preg_match('/\b(SLEEP|BENCHMARK)\b/i', $code) && !preg_match('/preg_match.*SLEEP/i', $code)) {
            $vulnerabilities[] = [
                'name' => 'DoS - SQL Time Functions',
                'severity' => 'CRITICAL',
                'description' => 'SLEEP or BENCHMARK functions not blocked',
                'pattern' => 'SELECT SLEEP(30) possible',
                'test' => $testMode ? '$db->select("SLEEP(10)")' : null
            ];
        }

        // Check for unbounded pagination
        if (preg_match('/\$_GET\[[\'"]page[\'"]\]/', $code) && !preg_match('/if\s*\(\s*\$page\s*>/', $code)) {
            $vulnerabilities[] = [
                'name' => 'DoS - Integer Overflow',
                'severity' => 'HIGH',
                'description' => 'Pagination page number not bounded',
                'pattern' => '$_GET["page"] without upper limit',
                'test' => $testMode ? '?page=999999999' : null
            ];
        }

        return $vulnerabilities;
    }

    private function filterBySeverity($vulnerabilities, $minSeverity)
    {
        $severityLevels = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        $minLevel = $severityLevels[strtolower($minSeverity)] ?? 1;

        return array_filter($vulnerabilities, function ($vuln) use ($severityLevels, $minLevel) {
            $vulnLevel = $severityLevels[strtolower($vuln['severity'])] ?? 1;
            return $vulnLevel >= $minLevel;
        });
    }

    private function formatSecurityResults($vulnerabilities, $testMode)
    {
        if (empty($vulnerabilities)) {
            return "🛡️ **SECURITY ANALYSIS COMPLETE**\n\n✅ No vulnerabilities detected matching the specified criteria.\n\nNote: This is static analysis. Dynamic testing and penetration testing are recommended.";
        }

        $result = "🛡️ **SECURITY ANALYSIS RESULTS**\n\n";
        $result .= "⚠️ Found " . count($vulnerabilities) . " potential vulnerabilities:\n\n";
        $result .= "---\n\n";

        foreach ($vulnerabilities as $vuln) {
            $severityIcon = $this->getSeverityIcon($vuln['severity']);

            $result .= "## {$severityIcon} {$vuln['name']}\n\n";
            $result .= "**Severity**: {$vuln['severity']}\n\n";
            $result .= "**Description**: {$vuln['description']}\n\n";
            $result .= "**Pattern**: `{$vuln['pattern']}`\n\n";

            if ($testMode && isset($vuln['test'])) {
                $result .= "**Proof of Concept**:\n```php\n{$vuln['test']}\n```\n\n";
            }

            $result .= "---\n\n";
        }

        $result .= "\n**Recommendation**: Review each finding and apply fixes according to PROTOCOL.md\n";

        return $result;
    }

    private function getSeverityIcon($severity)
    {
        switch (strtolower($severity)) {
            case 'critical':
                return '🔴';
            case 'high':
                return '🟠';
            case 'medium':
                return '🟡';
            case 'low':
                return '🔵';
            default:
                return '⚪';
        }
    }

    private function createErrorResponse($code, $message, $id = null)
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message
            ]
        ];
    }

    private function sendResponse($response)
    {
        fwrite($this->stdout, json_encode($response) . "\n");
        fflush($this->stdout);
    }

    private function sendError($code, $message, $id = null)
    {
        $this->sendResponse($this->createErrorResponse($code, $message, $id));
    }

    private function log($message)
    {
        fwrite($this->stderr, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n");
        fflush($this->stderr);
    }
}

// Start server
if (php_sapi_name() === 'cli') {
    $server = new PhpSecurityAuditServer();
    $server->run();
}
