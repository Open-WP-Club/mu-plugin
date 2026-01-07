<?php

/**
 * A utility plugin to disable AI crawlers and bots
 *
 * Plugin name:       Disable AI Crawlers
 * Plugin URI:        https://openwpclub.com
 * Description:       Blocks all known AI crawlers and bots from accessing your site content via robots.txt.
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            OpenWP Club
 * License:           Apache-2.0
 * Text Domain:       disable-ai-crawlers
 */

// Comprehensive list of known AI crawlers and bots
// Last updated: September 2025
const AI_CRAWLERS = [
  // OpenAI
  'GPTBot',
  'ChatGPT-User',
  'OpenAI-SearchBot',

  // Anthropic
  'ClaudeBot',
  'Claude-Web',

  // Google AI
  'Google-Extended',
  'GoogleOther',

  // Meta/Facebook AI
  'FacebookBot',
  'Meta-ExternalAgent',
  'Meta-ExternalFetcher',

  // Microsoft/Bing AI
  'Bingbot',
  'MSNBot',

  // Common Crawl (used by many AI companies)
  'CCBot',

  // Perplexity
  'PerplexityBot',

  // Various AI research crawlers
  'ChatGPT',
  'AI2Bot',
  'Claude',
  'Bard',
  'YouBot',
  'YandexBot',

  // Academic/Research AI crawlers
  'Applebot-Extended',
  'Amazonbot',
  'anthropic-ai',
  'Bytespider',
  'ChatGPT-User-Agent',
  'cohere-ai',
  'DataForSeoBot',
  'Diffbot',
  'iaskspider',
  'img2dataset',
  'omgili',
  'omgilibot',
  'peer39_crawler',
  'YouBot',

  // Generic AI/ML crawlers
  'AdsBot-Google',
  'AI-Bot',
  'AIBot',
  'bot-ai',
  'crawler-ai',
  'ML-Bot',
  'mlbot',
  'scrapy',
  'spider-ai'
];

add_filter(
  'robots_txt',
  static function ($output, $is_public) {
    // Only add restrictions for public sites
    if (!$is_public) {
      return $output;
    }

    $ai_rules = [];
    $ai_rules[] = '# AI Crawlers and Bots - Blocked by Disable AI Crawlers Plugin';

    foreach (AI_CRAWLERS as $bot) {
      $ai_rules[] = "User-agent: {$bot}";
      $ai_rules[] = 'Disallow: /';
      $ai_rules[] = ''; // Empty line for readability
    }

    // Add general AI bot patterns
    $ai_rules[] = '# Generic AI bot patterns';
    $ai_rules[] = 'User-agent: *AI*';
    $ai_rules[] = 'Disallow: /';
    $ai_rules[] = '';
    $ai_rules[] = 'User-agent: *bot*AI*';
    $ai_rules[] = 'Disallow: /';
    $ai_rules[] = '';

    return implode("\n", $ai_rules) . $output;
  },
  -1,
  2
);

// Optional: Log blocked AI crawlers if WP_DEBUG_LOG is enabled
if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
  add_action(
    'init',
    static function () {
      $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

      if (empty($user_agent)) {
        return;
      }

      // Check if the user agent matches any known AI crawler
      foreach (AI_CRAWLERS as $bot) {
        if (stripos($user_agent, $bot) !== false) {
          error_log("AI Crawler blocked: {$user_agent}");
          break;
        }
      }

      // Check for generic AI patterns
      if (
        stripos($user_agent, 'AI') !== false ||
        stripos($user_agent, 'bot') !== false ||
        stripos($user_agent, 'crawler') !== false
      ) {
        error_log("Potential AI Crawler detected: {$user_agent}");
      }
    }
  );
}
