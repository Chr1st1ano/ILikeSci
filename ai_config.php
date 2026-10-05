<?php
/**
 * ILikeSci — AI Configuration
 * 
 * Supports two free-tier AI providers:
 *   1. Groq  (free: 30 RPM, ~14K tokens/min with Llama 3)
 *   2. Google Gemini (free: 15 RPM, 1M tokens/day)
 * 
 * Get your free API key:
 *   Groq:   https://console.groq.com/keys
 *   Gemini: https://aistudio.google.com/app/apikey
 */

// Master switch — set to false to disable all AI features
define('AI_ENABLED', true);

// Provider: 'groq' or 'gemini'
define('AI_PROVIDER', 'gemini');

// Check for local uncommitted key file first
if (file_exists(__DIR__ . '/ai_key.local.php')) {
    require_once __DIR__ . '/ai_key.local.php';
}

// API Keys — loaded from local secure config, environment, or default
if (!defined('AI_API_KEY_GROQ')) {
    define('AI_API_KEY_GROQ', getenv('GROQ_API_KEY') ?: '');
}
if (!defined('AI_API_KEY_GEMINI')) {
    // Google Gemini API Key (Key name: sawsi | Project: projects/233949080756 | Number: 233949080756)
    define('AI_API_KEY_GEMINI', getenv('GEMINI_API_KEY') ?: '');
}

// Models
define('AI_MODEL_GROQ', 'llama-3.3-70b-versatile');
define('AI_MODEL_GEMINI', 'gemini-3.8-flash');

// Rate limiting (requests per minute per session)
define('AI_RATE_LIMIT', 10);

// Request timeout in seconds
define('AI_TIMEOUT', 15);

// Language for AI responses
// Options: 'english', 'filipino', 'mixed' (English with Filipino examples)
define('AI_LANGUAGE', 'english');

// Cache TTL in seconds (7 days default — cached responses reused for a week)
define('AI_CACHE_TTL', 604800);

/**
 * Helper: Get the active API key based on provider
 */
if (!function_exists('getActiveAIKey')) {
    function getActiveAIKey()
    {
        if (AI_PROVIDER === 'gemini') {
            return AI_API_KEY_GEMINI;
        }
        return AI_API_KEY_GROQ;
    }
}

if (!function_exists('getActiveAIModel')) {
    function getActiveAIModel()
    {
        if (AI_PROVIDER === 'gemini') {
            return AI_MODEL_GEMINI;
        }
        return AI_MODEL_GROQ;
    }
}
?>