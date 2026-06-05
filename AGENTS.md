<!-- code-review-graph MCP tools -->
## MCP Tools: code-review-graph

**IMPORTANT: This project has a knowledge graph. ALWAYS use the
code-review-graph MCP tools BEFORE using Grep/Glob/Read to explore
the codebase.** The graph is faster, cheaper (fewer tokens), and gives
you structural context (callers, dependents, test coverage) that file
scanning cannot.

### When to use graph tools FIRST

- **Exploring code**: `semantic_search_nodes` or `query_graph` instead of Grep
- **Understanding impact**: `get_impact_radius` instead of manually tracing imports
- **Code review**: `detect_changes` + `get_review_context` instead of reading entire files
- **Finding relationships**: `query_graph` with callers_of/callees_of/imports_of/tests_for
- **Architecture questions**: `get_architecture_overview` + `list_communities`

Fall back to Grep/Glob/Read **only** when the graph doesn't cover what you need.

### Key Tools

| Tool | Use when |
| ------ | ---------- |
| `detect_changes` | Reviewing code changes — gives risk-scored analysis |
| `get_review_context` | Need source snippets for review — token-efficient |
| `get_impact_radius` | Understanding blast radius of a change |
| `get_affected_flows` | Finding which execution paths are impacted |
| `query_graph` | Tracing callers, callees, imports, tests, dependencies |
| `semantic_search_nodes` | Finding functions/classes by name or keyword |
| `get_architecture_overview` | Understanding high-level codebase structure |
| `refactor_tool` | Planning renames, finding dead code |

### Workflow

1. The graph auto-updates on file changes (via hooks).
2. Use `detect_changes` for code review.
3. Use `get_affected_flows` to understand impact.
4. Use `query_graph` pattern="tests_for" to check coverage.

<!-- Stitch MCP tools -->
## MCP Tools: Stitch

The Stitch MCP server is configured in local `config.toml` with the API
key. Use Stitch tools when working with generated UI designs, design
systems, screen variants, or visual references for this project.

### When to use Stitch

- **List projects/screens**: use `list_projects`, `get_project`,
  `list_screens`, or `get_screen` to inspect existing Stitch work.
- **Generate UI concepts**: use `generate_screen_from_text` for new
  screens based on Azraq storefront/admin requirements.
- **Edit designs**: use `edit_screens` for targeted changes to existing
  Stitch screens.
- **Create variants**: use `generate_variants` when comparing layout,
  color, typography, or image directions.
- **Design systems**: use `list_design_systems`, `create_design_system`,
  `update_design_system`, or `apply_design_system` to keep screens aligned
  with the Azraq visual language.

### Current Stitch Context

- Primary Azraq Stitch project: `projects/16612745489371227338`
- Project title: `Extracted text from https://azraqbd.com/`
- Preferred design system direction: premium Azraq luxury commerce with
  Plus Jakarta Sans, deep obsidian primary color, white/soft neutral
  surfaces, restrained blue/gold accents, glassy navigation, and product-led
  ecommerce layouts.

Never commit API keys or MCP secrets into this repository. Keep credentials
in the local MCP config only.

## Imported Claude Cowork project instructions
