<!-- graphify workflow -->
## Graphify

Use `/graphify .` when the project graph needs to be refreshed for the
current repository.

### Workflow

1. Run `/graphify .` from the project root.
2. Use the generated graph/status output as optional context.
3. If graph output is unavailable or stale, continue with normal local
   inspection tools such as `rg`, file reads, and targeted tests.

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
