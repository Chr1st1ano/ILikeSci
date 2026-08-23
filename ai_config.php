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

// API Keys — paste your free key here
define('AI_API_KEY_GROQ', '');
define('AI_API_KEY_GEMINI', 'AIzaSyC-xWmj6Aa45907bti_cKbl_aGcqfvXnFU');

// Models
define('AI_MODEL_GROQ', 'llama-3.3-70b-versatile');
define('AI_MODEL_GEMINI', 'gemini-2.0-flash');

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
function getActiveAIKey()
{
    if (AI_PROVIDER === 'gemini') {
        return AI_API_KEY_GEMINI;
    }
    return AI_API_KEY_GROQ;
}

/**
 * Helper: Get the active model based on provider
 */
function getActiveAIModel()
{
    if (AI_PROVIDER === 'gemini') {
        return AI_MODEL_GEMINI;
    }
    return AI_MODEL_GROQ;
}
?>