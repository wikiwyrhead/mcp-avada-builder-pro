# Contributing to MCP Avada Builder Pro

Thanks for contributing.

## Quick Start

1. Fork the repository.
2. Create a branch from `main`:
   - `feat/<short-name>` or `fix/<short-name>`
3. Make focused changes with tests/validation.
4. Run local checks:
   - `php -l mcp-avada-builder-pro.php`
   - `php -l includes/class-avada-parser.php`
   - `php -l includes/class-avada-elements.php`
5. Open a pull request with:
   - problem statement
   - approach
   - safety/risk notes
   - before/after behavior

## Development Guidelines

- Keep edits schema-aware for Avada structure changes.
- Avoid raw content manipulation patterns that risk data loss.
- Prefer dry-run and diff-first behavior for bulk mutations.
- Keep APIs backwards compatible when possible.
- Document any new ability in `README.md`.

## Commit Guidance

- Use clear commit messages:
  - `feat: add clone-element-style ability`
  - `fix: guard replace-content against empty structures`
  - `docs: update ability examples`

## Pull Request Checklist

- [ ] Change is scoped and reversible.
- [ ] PHP lint passes.
- [ ] README/changelog updated (if behavior changed).
- [ ] Security/safety impact reviewed.
- [ ] No unrelated file changes.
