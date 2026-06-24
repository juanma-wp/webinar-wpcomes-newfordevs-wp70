# What's New for Devs in WP 7.0 — AI Demos

A live-demo WordPress plugin for the **"WordPress 7.0: Novedades para desarrollo"** webinar.
It showcases the **WordPress 7.0 AI Building Blocks** and is meant to grow: it starts with one
working example (a Content Summarizer) and is structured so more AI examples can be added.

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
| `whats-new-for-devs-in-wp-70.php` | Bootstrap; bails if not on WP 7.0; loads `includes/`. |
| `includes/summarizer.php` | Registers the ability category + `wp-ai-workshop/summarization` ability (schemas, permission, `execute_callback` calling the PHP AI Client), and enqueues the editor **script module**. |
| `src/index.js` | Editor UI; calls `executeAbility( 'wp-ai-workshop/summarization', { content, length } )` from `@wordpress/abilities`. |

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

## Roadmap

- More example abilities (e.g. image alt text via vision, excerpt/meta generation).
- A shareable Playground/Studio **`blueprint.json`** that installs WordPress 7.0, the provider
  connectors, the `ai` plugin, and this plugin in one click.

## License

GPL-2.0-or-later. See [`LICENSE`](./LICENSE).
