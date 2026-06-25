# What's New for Devs in WP 7.0 — AI Demos

A live-demo WordPress plugin for the **"WordPress 7.0: Novedades para desarrollo"** webinar.
It showcases new **WordPress 7.0** developer features and is meant to grow. It currently bundles
two demos: an AI **Content Summarizer** (built on the AI Building Blocks) and a **PHP-only block**
registered with no JavaScript ([see below](#php-only-block-registered-with-no-javascript)).

The AI Building Blocks demonstrated:

- **PHP AI Client** — `wp_ai_client_prompt( '…' )->generate_text()`, a provider-agnostic SDK in 7.0 core.
- **Abilities API** — server-side ability registration (shipped in 6.9) **called from JavaScript**
  via the new `@wordpress/abilities` client (`executeAbility()`), new in 7.0.
- **MCP** — abilities opted into `meta.mcp.public` are discoverable by MCP agents (Cursor, Claude Desktop)
  through the `mcp-adapter` (bundled with the official `ai` plugin).

> Derived from the [`wptrainingteam/wp-ai-workshop`](https://github.com/wptrainingteam/wp-ai-workshop)
> teaching repo — this is the **finished** code (its "section 5", the `@wordpress/abilities`
> client build) extracted as a clean, installable starting point.

## What it does today

Adds a **Content Summarizer** to the block editor. In the Post panel you get a **Summary length**
dropdown (short / medium / long) and a **Generate AI Summary** button. Clicking it calls the
`wp-ai-workshop/summarization` ability from JavaScript, which runs the PHP AI Client against your
configured provider and inserts the summary as a quote block at the top of the post.

## Requirements

- **WordPress 7.0+** (the plugin bails out silently if `wp_ai_client_prompt()` isn't available).
- A configured **AI provider connector** — install one of the provider plugins
  (`ai-provider-for-anthropic`, `ai-provider-for-openai`, `ai-provider-for-google`) **plus** the
  official **`ai`** plugin, then add an API key under **Settings → Connectors**.
- **Node.js 20+** to build the editor assets.
- No Composer required at runtime — `wp_ai_client_prompt()` and the Abilities API are in core.

## Install & build

```bash
# from this repo, inside your site's wp-content/plugins/
npm install
npm run build      # outputs build/index.js (gitignored)
# then activate "What's New for Devs in WP 7.0 — AI Demos" in wp-admin
```

For live development: `npm start` (rebuilds on change).

## How it's wired

| File | Role |
| --- | --- |
| `webinar-wpcomes-newfordevs-wp70.php` | Bootstrap; bails if not on WP 7.0; loads `includes/`. |
| `includes/summarizer.php` | Registers the ability category + `wp-ai-workshop/summarization` ability (schemas, permission, `execute_callback` calling the PHP AI Client), and enqueues the editor **script module**. |
| `src/index.js` | Editor UI; calls `executeAbility( 'wp-ai-workshop/summarization', { content, length } )` from `@wordpress/abilities`. |
| `includes/recent-posts-block.php` | The PHP-only block (`webinar-wp70/recent-posts`) — see the section below. |
| `assets/recent-posts.css` | Styles for that block, enqueued on `enqueue_block_assets`. |

The ability is registered with `meta.show_in_rest = true` (auto-creates a REST endpoint) and
`meta.mcp.public = true` (opts into the MCP server), so the **same** ability is callable from the
editor, over REST, and from MCP agents.

### Note on the build

`src/index.js` loads `@wordpress/abilities` via a top-level `await import()` because that package
ships only as a runtime ES module. If `npm run build` errors with *"Top-level-await is only
available in modules"*, add a minimal `webpack.config.js` extending `@wordpress/scripts` with
`experiments.topLevelAwait = true`.

### REST / `apiFetch` alternative

The upstream "section 4" variant calls the same ability over REST with `apiFetch` to
`/wp-abilities/v1/abilities/wp-ai-workshop/summarization/run` instead of the `@wordpress/abilities`
client. It's a more conservative build (classic script enqueue) and useful as a fallback or to
contrast the two approaches.

## PHP-only block (registered with no JavaScript)

The plugin also ships a second, AI-independent demo: a block registered **entirely in PHP** — no
`block.json`, no JavaScript, no build step — using WordPress 7.0's new `autoRegister` support.
`includes/recent-posts-block.php` registers `webinar-wp70/recent-posts`, which server-renders a
list of recent posts.

```php
register_block_type(
	'webinar-wp70/recent-posts',
	array(
		'title'           => __( 'Recent Posts (PHP)', 'wp-ai-workshop' ),
		'attributes'      => array(
			// numberOfPosts (integer), order (enum), showDate (boolean)
		),
		'render_callback' => 'wp_ai_workshop_render_recent_posts_block',
		'supports'        => array( 'autoRegister' => true ),
	)
);
```

**What's new in 7.0 is just one line:** `'supports' => array( 'autoRegister' => true )`. Everything
else (`register_block_type`, `render_callback`, `attributes`) already existed. That single flag —
paired with a `render_callback` — tells core to do everything that previously required a separate
client-side JavaScript registration:

- the block appears in the **inserter** automatically;
- it gets an editor preview via **ServerSideRender** (no `edit` function to write);
- its **Inspector controls are generated from the PHP `attributes` schema** — `enum` → select,
  `integer` → number, `boolean` → checkbox, each captioned by the attribute's `label`.

Because it renders in PHP, the output is **dynamic** — re-queried on every load, which is exactly
the use case server-rendered blocks are for. The data is site-global (not tied to the current
post), so it also renders **live in the editor preview**, and adjusting the controls (number of
posts, order, show date) updates that preview immediately.

> Note: auto-registered blocks render in the editor *without* the current post in context, so a
> block that depends on the post being edited (e.g. reading time) can only show real values on the
> published page. Site-global data like this avoids that limitation.

**Limitations (by design, this iteration):** no inner blocks, no custom React `edit` UI (the editor
shows the ServerSideRender preview), and a small attribute-type set (`string` / `integer` /
`boolean` / `string`+`enum`; no media). Reach for a JavaScript-built block when you need inner
blocks or a rich editing experience.

> Under the hood, core exposes these blocks to the editor via a `window.__unstableAutoRegisterBlocks`
> global — the `__unstable` prefix is a reminder the API is still early-stage.

## Roadmap

- More example abilities (e.g. image alt text via vision, excerpt/meta generation).
- A shareable Playground/Studio **`blueprint.json`** that installs WordPress 7.0, the provider
  connectors, the `ai` plugin, and this plugin in one click.

## License

GPL-2.0-or-later. See [`LICENSE`](./LICENSE).
