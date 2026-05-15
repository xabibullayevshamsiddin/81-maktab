# Design System: 81-IDUM (81-maktab)
## "Pro Max Ultra" Educational Experience

This document serves as the Source of Truth for all UI/UX decisions in the 81-IDUM project, following the **UI/UX Pro Max** intelligence framework.

---

### 1. Product Identity
- **Product Type**: Educational Platform / School Management System
- **Core Values**: Trust, Innovation, Excellence, Accessibility
- **Target Audience**: Students (Engagement), Teachers (Efficiency), Parents (Transparency)

---

### 2. Visual Style: "Aurora Glassmorphism"
We combine the **Aurora UI** vibrancy with **Glassmorphism** depth to create a premium, state-of-the-art feel.

- **Backgrounds**: Mesh gradients (Blue, Purple, Indigo) with subtle 12s animations.
- **Surfaces**: Glass cards with `backdrop-filter: blur(20px)`, `background: rgba(255, 255, 255, 0.72)` (Light) and `rgba(15, 23, 42, 0.65)` (Dark).
- **Borders**: Thin, semi-transparent borders `rgba(148, 163, 184, 0.3)` to define edges without clutter.
- **Effects**: Subtle outer glows and soft shadows for depth.

---

### 3. Color Palette
| Purpose | Key Color | Logic |
|---------|-----------|-------|
| **Primary** | `#3b82f6` (Prime Blue) | Represents trust and authority in education. |
| **Secondary** | `#a855f7` (Aurora Purple) | Adds a modern, innovative spark. |
| **Accent** | `#fbbf24` (Success Gold) | Used for results, certificates, and achievements. |
| **Danger** | `#ef4444` (Alert Red) | Used for destructive actions (e.g., delete modals). |
| **Surface** | `Slate 50/900` | Clean neutrals for content readability. |

---

### 4. Typography
- **Primary Font**: `Inter` (Sans-serif) - Default for body and UI.
- **Heading Font**: `Outfit` or `Plus Jakarta Sans` - For premium, geometric headings.
- **Hierarchy**:
  - `H1`: 600-700 weight, tight tracking (-0.02em).
  - `Body`: 400 weight, standard tracking.
  - `Monospace`: `Fira Code` for any technical/data displays.

---

### 5. Interaction Rules
- **Cursors**: `cursor-pointer` mandatory for all hoverable cards and buttons.
- **Hover States**:
  - Scale: `scale(1.02)` for cards (subtle).
  - Glow: Add a primary color glow on hover.
  - Duration: `300ms` cubic-bezier(0.4, 0, 0.2, 1).
- **Transitions**: Every state change (color, opacity, transform) must be animated.

---

### 6. Components Standard
- **Icons**: SVG ONLY (FontAwesome 6 / Lucide). No emojis in UI logic.
- **Buttons**:
  - `Prime`: Gradient background, white text, subtle shadow.
  - `Glass`: Transparent with border and blur.
- **Modals**: Center-screen, scale-in animation, backdrop blur.

---

### 7. UX Guidelines (Anti-Patterns to Avoid)
- **NO Generic Colors**: Never use `#ff0000` or `#0000ff`. Use HSL-curated shades.
- **NO Layout Shift**: Skeleton loaders or fixed dimensions for dynamic content.
- **NO Missing Feedback**: Every click must trigger a visual/audible response (where appropriate).
- **NO Low Contrast**: Ensure 4.5:1 ratio for all textual content.

---

> [!IMPORTANT]
> This design system is persistent. All future components (Courses, Exams, Profiles, Chat) must adhere to these tokens to maintain a cohesive "Pro Max" experience.
