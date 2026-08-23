<?php
/**
 * ILikeSci — AI API Proxy
 * 
 * Server-side proxy for all AI features.
 * Browser sends simple JSON requests, this handles API calls, caching, and rate limiting.
 * 
 * Endpoints:
 *   GET  ?action=status              — Check AI availability
 *   POST action=generate_questions   — Generate curriculum-aligned questions
 *   POST action=explain_topic        — Generate kid-friendly topic explanation
 *   POST action=study_hint           — Generate hint for incorrect answer
 *   POST action=performance_insight  — Analyze student performance data
 *   POST action=rephrase_question    — Rephrase existing question
 */

require 'db.php';
require 'ai_config.php';

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ============================================================
// RATE LIMITING
// ============================================================
function checkRateLimit($pdo) {
    session_start();
    $sessionId = session_id();
    
    // Clean old entries (older than 1 minute)
    $pdo->exec("DELETE FROM ai_rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
    
    // Count requests in the last minute
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ai_rate_limits WHERE session_id = ?");
    $stmt->execute([$sessionId]);
    $count = $stmt->fetchColumn();
    
    if ($count >= AI_RATE_LIMIT) {
        return false;
    }
    
    // Log this request
    $stmt = $pdo->prepare("INSERT INTO ai_rate_limits (session_id) VALUES (?)");
    $stmt->execute([$sessionId]);
    
    return true;
}

// ============================================================
// CACHE FUNCTIONS
// ============================================================
function getCachedResponse($pdo, $promptHash) {
    $stmt = $pdo->prepare("SELECT response_text FROM ai_cache WHERE prompt_hash = ? AND created_at > DATE_SUB(NOW(), INTERVAL " . AI_CACHE_TTL . " SECOND)");
    $stmt->execute([$promptHash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['response_text'] : null;
}

function cacheResponse($pdo, $promptHash, $action, $promptText, $responseText) {
    $stmt = $pdo->prepare("INSERT INTO ai_cache (prompt_hash, action, prompt_text, response_text, provider) 
        VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE response_text = VALUES(response_text), created_at = NOW()");
    $stmt->execute([$promptHash, $action, $promptText, $responseText, AI_PROVIDER]);
}

// ============================================================
// AI API CALLS
// ============================================================
function callGroqAPI($prompt, $systemPrompt = '') {
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    
    $messages = [];
    if ($systemPrompt) {
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];
    }
    $messages[] = ['role' => 'user', 'content' => $prompt];
    
    $payload = json_encode([
        'model' => AI_MODEL_GROQ,
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 2048
    ]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY_GROQ
        ],
        CURLOPT_TIMEOUT => AI_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Connection failed: " . $error);
    }
    if ($httpCode !== 200) {
        $data = json_decode($response, true);
        $msg = isset($data['error']['message']) ? $data['error']['message'] : "HTTP $httpCode";
        throw new Exception("Groq API error: " . $msg);
    }
    
    $data = json_decode($response, true);
    return $data['choices'][0]['message']['content'] ?? '';
}

function callGeminiAPI($prompt, $systemPrompt = '') {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . AI_MODEL_GEMINI . ':generateContent?key=' . AI_API_KEY_GEMINI;
    
    $fullPrompt = $systemPrompt ? ($systemPrompt . "\n\n" . $prompt) : $prompt;
    
    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => $fullPrompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 2048
        ]
    ]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => AI_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Connection failed: " . $error);
    }
    if ($httpCode !== 200) {
        $data = json_decode($response, true);
        $msg = isset($data['error']['message']) ? $data['error']['message'] : "HTTP $httpCode";
        throw new Exception("Gemini API error: " . $msg);
    }
    
    $data = json_decode($response, true);
    return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
}

function callAI($prompt, $systemPrompt = '') {
    if (AI_PROVIDER === 'gemini') {
        return callGeminiAPI($prompt, $systemPrompt);
    }
    return callGroqAPI($prompt, $systemPrompt);
}

// ============================================================
// SYSTEM PROMPTS
// ============================================================
function getSystemPrompt() {
    $lang = AI_LANGUAGE;
    $langInstruction = '';
    if ($lang === 'filipino') {
        $langInstruction = 'Respond in Filipino (Tagalog).';
    } else if ($lang === 'mixed') {
        $langInstruction = 'Respond in English but include Filipino terms and examples when helpful for Filipino students.';
    } else {
        $langInstruction = 'Respond in clear, simple English.';
    }
    
    return "You are an AI teaching assistant for ILikeSci, a Science education platform for Filipino students in Grades 4-6 (ages 9-12). " .
           "All content must be aligned with the DepEd K-12 Science curriculum for the Philippines. " .
           "Keep explanations simple and age-appropriate. Use concrete examples from everyday life in the Philippines. " .
           "$langInstruction " .
           "Be encouraging and supportive in tone.";
}

// ============================================================
// ACTION HANDLERS
// ============================================================

$method = $_SERVER['REQUEST_METHOD'];
$action = '';

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
} else if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

try {
    // ---- STATUS CHECK ----
    if ($action === 'status') {
        $hasKey = !empty(getActiveAIKey());
        echo json_encode([
            'status' => 'success',
            'ai_enabled' => AI_ENABLED && $hasKey,
            'provider' => AI_PROVIDER,
            'model' => getActiveAIModel(),
            'has_key' => $hasKey,
            'language' => AI_LANGUAGE,
            'rate_limit' => AI_RATE_LIMIT,
            'cache_ttl' => AI_CACHE_TTL
        ]);
        exit;
    }
    
    // ---- All other actions require AI to be enabled ----
    if (!AI_ENABLED) {
        echo json_encode(['status' => 'error', 'message' => 'AI features are disabled', 'code' => 'disabled']);
        exit;
    }
    
    $apiKey = getActiveAIKey();
    if (empty($apiKey)) {
        echo json_encode(['status' => 'error', 'message' => 'No API key configured. Add your free key to ai_config.php', 'code' => 'no_key']);
        exit;
    }
    
    // Rate limit check
    if (!checkRateLimit($pdo)) {
        echo json_encode(['status' => 'error', 'message' => 'Rate limit exceeded. Please wait a moment.', 'code' => 'rate_limit']);
        http_response_code(429);
        exit;
    }
    
    // ---- GENERATE QUESTIONS ----
    if ($action === 'generate_questions') {
        $grade = $input['grade'] ?? '4';
        $topic = $input['topic'] ?? 'Matter';
        $count = min(intval($input['count'] ?? 5), 10);
        $types = $input['types'] ?? 'mixed'; // 'mc', 'identification', 'enumeration', 'mixed'
        
        $prompt = "Generate exactly $count Science questions for Grade $grade about \"$topic\". ";
        
        if ($types === 'mc' || $types === 'mixed') {
            $prompt .= "Include multiple choice questions with 4 options (A, B, C, D) and mark the correct answer. ";
        }
        if ($types === 'identification' || $types === 'mixed') {
            $prompt .= "Include identification questions (fill in the blank). ";
        }
        if ($types === 'enumeration' || $types === 'mixed') {
            $prompt .= "Include enumeration questions. ";
        }
        
        $prompt .= "\nFormat your response as a valid JSON array. Each question object must have these fields:\n" .
                   "- \"type\": \"mc\" or \"identification\" or \"enumeration\"\n" .
                   "- \"text\": the question text\n" .
                   "- \"difficulty\": \"Easy\", \"Medium\", or \"Hard\"\n" .
                   "- \"options\": array of 4 strings (only for mc type, omit for others)\n" .
                   "- \"answer\": the correct answer (letter for mc, word/phrase for identification)\n" .
                   "- \"explanation\": brief explanation of the correct answer\n\n" .
                   "IMPORTANT: Return ONLY the JSON array, no markdown formatting, no code blocks, no extra text.";
        
        $promptHash = hash('sha256', "generate_questions|$grade|$topic|$count|$types");
        
        // Check cache
        $cached = getCachedResponse($pdo, $promptHash);
        if ($cached) {
            echo json_encode(['status' => 'success', 'questions' => json_decode($cached, true), 'source' => 'cache']);
            exit;
        }
        
        $response = callAI($prompt, getSystemPrompt());
        
        // Clean response — remove markdown code blocks if present
        $response = preg_replace('/^```(?:json)?\s*/m', '', $response);
        $response = preg_replace('/```\s*$/m', '', $response);
        $response = trim($response);
        
        $questions = json_decode($response, true);
        if (!$questions || !is_array($questions)) {
            // Try to extract JSON from the response
            if (preg_match('/\[[\s\S]*\]/', $response, $matches)) {
                $questions = json_decode($matches[0], true);
            }
            if (!$questions || !is_array($questions)) {
                throw new Exception("AI returned invalid format. Please try again.");
            }
        }
        
        // Cache the response
        cacheResponse($pdo, $promptHash, 'generate_questions', "$grade|$topic|$count|$types", json_encode($questions));
        
        echo json_encode(['status' => 'success', 'questions' => $questions, 'source' => 'ai']);
        exit;
    }
    
    // ---- EXPLAIN TOPIC ----
    if ($action === 'explain_topic') {
        $topic = $input['topic'] ?? '';
        $grade = $input['grade'] ?? '4';
        $slideText = $input['slide_text'] ?? '';
        
        if (empty($topic) && empty($slideText)) {
            throw new Exception("No topic or slide text provided");
        }
        
        $prompt = "Explain the following Science topic for Grade $grade students (ages " . (intval($grade) + 5) . "-" . (intval($grade) + 6) . ") in a fun, simple way:\n\n";
        if ($topic) $prompt .= "Topic: $topic\n";
        if ($slideText) $prompt .= "Slide content: $slideText\n";
        $prompt .= "\nGuidelines:\n" .
                   "- Use simple words and short sentences\n" .
                   "- Include 1-2 real-life examples from the Philippines\n" .
                   "- Add a fun fact if possible\n" .
                   "- Keep it to 3-5 short paragraphs\n" .
                   "- Make it engaging and interesting for kids";
        
        $promptHash = hash('sha256', "explain_topic|$grade|$topic|" . substr($slideText, 0, 100));
        
        $cached = getCachedResponse($pdo, $promptHash);
        if ($cached) {
            echo json_encode(['status' => 'success', 'explanation' => $cached, 'source' => 'cache']);
            exit;
        }
        
        $response = callAI($prompt, getSystemPrompt());
        cacheResponse($pdo, $promptHash, 'explain_topic', "$grade|$topic", $response);
        
        echo json_encode(['status' => 'success', 'explanation' => $response, 'source' => 'ai']);
        exit;
    }
    
    // ---- STUDY HINT ----
    if ($action === 'study_hint') {
        $question = $input['question'] ?? '';
        $topic = $input['topic'] ?? '';
        $wrongAnswer = $input['wrong_answer'] ?? '';
        
        if (empty($question)) {
            throw new Exception("No question provided");
        }
        
        $prompt = "A student got this Science question wrong:\n\n" .
                  "Question: $question\n";
        if ($wrongAnswer) $prompt .= "Student's wrong answer: $wrongAnswer\n";
        if ($topic) $prompt .= "Topic: $topic\n";
        $prompt .= "\nGive a short, encouraging hint (2-3 sentences) that helps the student understand the concept without directly giving the answer. " .
                   "Use simple language appropriate for Filipino grade school students.";
        
        $promptHash = hash('sha256', "study_hint|$question|$wrongAnswer");
        
        $cached = getCachedResponse($pdo, $promptHash);
        if ($cached) {
            echo json_encode(['status' => 'success', 'hint' => $cached, 'source' => 'cache']);
            exit;
        }
        
        $response = callAI($prompt, getSystemPrompt());
        cacheResponse($pdo, $promptHash, 'study_hint', $question, $response);
        
        echo json_encode(['status' => 'success', 'hint' => $response, 'source' => 'ai']);
        exit;
    }
    
    // ---- PERFORMANCE INSIGHT ----
    if ($action === 'performance_insight') {
        $studentName = $input['student_name'] ?? '';
        $scores = $input['scores'] ?? [];
        $quarter = $input['quarter'] ?? '';
        $gradeLevel = $input['grade_level'] ?? '';
        
        if (empty($scores)) {
            throw new Exception("No score data provided");
        }
        
        $prompt = "Analyze this Filipino student's Science performance data and provide insights:\n\n";
        if ($studentName) $prompt .= "Student: $studentName\n";
        if ($gradeLevel) $prompt .= "Grade Level: $gradeLevel\n";
        if ($quarter) $prompt .= "Quarter: $quarter\n";
        $prompt .= "Scores: " . json_encode($scores) . "\n\n";
        $prompt .= "Provide:\n" .
                   "1. A 2-3 sentence summary of performance\n" .
                   "2. Specific strengths observed\n" .
                   "3. Areas needing improvement\n" .
                   "4. One concrete suggestion for the teacher\n\n" .
                   "Keep it professional but supportive. Format with clear headings.";
        
        $promptHash = hash('sha256', "performance_insight|$studentName|" . json_encode($scores));
        
        $cached = getCachedResponse($pdo, $promptHash);
        if ($cached) {
            echo json_encode(['status' => 'success', 'insight' => $cached, 'source' => 'cache']);
            exit;
        }
        
        $response = callAI($prompt, getSystemPrompt());
        cacheResponse($pdo, $promptHash, 'performance_insight', "$studentName|$quarter", $response);
        
        echo json_encode(['status' => 'success', 'insight' => $response, 'source' => 'ai']);
        exit;
    }
    
    // ---- REPHRASE QUESTION ----
    if ($action === 'rephrase_question') {
        $question = $input['question'] ?? '';
        $grade = $input['grade'] ?? '4';
        
        if (empty($question)) {
            throw new Exception("No question provided");
        }
        
        $prompt = "Rephrase this Grade $grade Science question using different wording, but test the SAME concept:\n\n" .
                  "Original: $question\n\n" .
                  "Rules:\n" .
                  "- Same difficulty level\n" .
                  "- Same correct answer concept\n" .
                  "- Different wording and structure\n" .
                  "- Appropriate for Filipino students ages " . (intval($grade) + 5) . "-" . (intval($grade) + 6) . "\n\n" .
                  "Return ONLY the rephrased question text, nothing else.";
        
        // No cache for rephrase — we want different versions each time
        $response = callAI($prompt, getSystemPrompt());
        
        echo json_encode(['status' => 'success', 'rephrased' => trim($response), 'source' => 'ai']);
        exit;
    }
    
    // ---- CACHE STATS (for admin) ----
    if ($action === 'cache_stats') {
        $stmt = $pdo->query("SELECT COUNT(*) as total, 
            SUM(CASE WHEN action = 'generate_questions' THEN 1 ELSE 0 END) as questions,
            SUM(CASE WHEN action = 'explain_topic' THEN 1 ELSE 0 END) as explanations,
            SUM(CASE WHEN action = 'study_hint' THEN 1 ELSE 0 END) as hints,
            SUM(CASE WHEN action = 'performance_insight' THEN 1 ELSE 0 END) as insights
            FROM ai_cache");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'cache_stats' => $stats]);
        exit;
    }
    
    // ---- CLEAR CACHE (admin only) ----
    if ($action === 'clear_cache') {
        $pdo->exec("DELETE FROM ai_cache");
        echo json_encode(['status' => 'success', 'message' => 'AI cache cleared']);
        exit;
    }
    
    // Unknown action
    echo json_encode(['status' => 'error', 'message' => "Unknown action: $action"]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
