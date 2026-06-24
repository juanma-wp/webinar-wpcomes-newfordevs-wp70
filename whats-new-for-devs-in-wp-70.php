<?php
/**
 * Plugin Name: What's New for Devs in WP 7.0 — AI Demos
 * Description: Live demo plugin for the "WordPress 7.0: Novedades para desarrollo" webinar. Showcases the WordPress 7.0 AI Building Blocks (PHP AI Client, Abilities API, MCP). Ships with a Content Summarizer ability callable from the block editor via @wordpress/abilities.
 * Version:     0.1.0
 * Author:      JuanMa Garrido
 * License:     GPL-2.0-or-later
 * Requires at least: 7.0
 * Requires PHP:      8.1
 * Text Domain: wp-ai-workshop
 *
 * Based on the wptrainingteam/wp-ai-workshop teaching repo (section-5, the
 * @wordpress/abilities client build). Extracted here as a clean, installable
 * starting point to grow more AI examples on for the webinar.
 */

// The PHP AI Client (`wp_ai_client_prompt()`) ships in WordPress 7.0 core.
// On older versions the function isn't defined yet — bail out silently so
// the plugin doesn't fatal during an upgrade.
if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
	return;
}

// All plugin logic lives in includes/summarizer.php.
require_once __DIR__ . '/includes/summarizer.php';
