---
name: aurora-glassmorphism-style
description: Apply the 81-IDUM Aurora Glassmorphism design system for UI styling tasks. Use when editing CSS, Blade views, frontend components, or when the user asks to "style", "UI", "design", "glassmorphism", "aurora", "pro max", or "mana mashi skildan foydalan style qilishda".
---

# 81-IDUM Aurora Glassmorphism Style

Use this skill as the default styling source of truth for this project.

## Trigger Conditions

Apply this skill when the task includes:
- Visual styling or UI polish
- CSS updates in `public/temp/css` or related frontend files
- Blade view UI adjustments
- Hover states, transitions, cards, buttons, modals, profile/course/post pages
- Requests mentioning "style", "design system", "aurora", "glass", "pro max", or Uzbek phrasing like "mana mashi skildan foydalan style qilishda"

## Core Identity

- Product type: educational platform / school management
- Values: trust, innovation, excellence, accessibility
- UX audience balance:
  - Students: engagement
  - Teachers: efficiency
  - Parents: transparency

## Visual Language

Use an "Aurora Glassmorphism" look:
- Backgrounds: blue/purple/indigo mesh gradients with subtle long animation
- Surfaces: glass cards with blur and translucency
- Borders: thin semi-transparent slate-like border
- Depth: soft shadow + subtle glow

Default references:
- Light glass: `rgba(255, 255, 255, 0.72)`
- Dark glass: `rgba(15, 23, 42, 0.65)`
- Border: `rgba(148, 163, 184, 0.3)`
- Suggested blur baseline: `backdrop-filter: blur(20px)`

## Color Tokens

- Primary: `#3b82f6`
- Secondary: `#a855f7`
- Accent: `#fbbf24`
- Danger: `#ef4444`
- Surface neutrals: slate-like 50/900 range

Do not introduce random hardcoded red/blue like `#ff0000` or `#0000ff`.

## Typography Rules

- Primary font: `Inter`
- Heading font: `Outfit` or `Plus Jakarta Sans`
- Heading feel: slightly tight tracking for large headings
- Body: clear readable defaults
- Monospace data/technical spots: `Fira Code`

## Interaction Rules (Mandatory)

- Interactive cards/buttons must visibly indicate clickability (`cursor: pointer`)
- Hover for cards should be subtle (`scale(1.02)` range)
- Use primary-colored glow where appropriate
- Animate state changes (color, opacity, transform)
- Transition baseline: `300ms cubic-bezier(0.4, 0, 0.2, 1)`

## Component Standards

- Icons: SVG only (FontAwesome 6 or Lucide style usage)
- Buttons:
  - Prime: gradient background, white text, subtle shadow
  - Glass: transparent + border + blur
- Modals: centered, scale-in behavior, backdrop blur

## Accessibility and UX Guardrails

- Keep text contrast at least WCAG-oriented readable levels (target 4.5:1 for text)
- Avoid layout shift on dynamic content (fixed space/skeleton where needed)
- Ensure click feedback exists for user actions

## Styling Workflow

1. Identify existing local style tokens/classes in the target file.
2. Map them to Aurora Glassmorphism tokens from this skill.
3. Preserve existing spacing/layout logic unless the task asks otherwise.
4. Add/adjust hover, transition, and focus states with subtle motion.
5. Verify readability in both light/dark contexts where applicable.
6. Keep changes cohesive with existing project CSS naming and structure.

## Implementation Notes

- Prefer updating existing CSS classes over introducing many new one-off classes.
- Use gradients, glow, and blur with restraint; avoid visual noise.
- Keep performance reasonable: moderate shadows/filters, avoid heavy stacked effects everywhere.
- For destructive actions, use danger token consistently.
