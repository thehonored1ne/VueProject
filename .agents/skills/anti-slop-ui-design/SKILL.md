---
name: anti-slop-ui-design
description: >-
  Guides the creation of authentic, high-craft, and intentional UI/UX while strictly forbidding AI slop, generic templates, and frontend anti-patterns. Use when designing, building, or refactoring Vue components, pages, design systems, or layouts.
---

# Anti-Slop UI & High-Craft Frontend Design Skill

This skill enforces intentional, production-grade, human-centered UI/UX design. It establishes explicit guardrails against generic "AI slop" aesthetics and frontend anti-patterns.

---

## 🚫 What is "AI Slop UI"? (Strictly Forbidden)

AI Slop UI is the default, uninspired aesthetic produced by naive AI prompts:
- **Gratuitous Purple/Indigo Gradients**: Slapping vibrant violet-to-cyan radial gradients behind every card, hero section, or button.
- **Card-in-Card-in-Card Nesting**: Nesting endless rounded cards with identical borders, drop shadows, and padding.
- **Uncontrolled Low-Density Layouts**: Massive 80px paddings that push critical data off-screen, rendering dashboards ineffective.
- **Generic Metric Cards**: Identical rows of 4 square cards with a huge number, a generic sparkline, and a random colored Lucide icon in a rounded circle.
- **Decorative Without Purpose**: Blobs, floating glow circles, and mesh gradients that distract from actual content and data.
- **Low Contrast / Unreadable Text**: Light gray text (`text-gray-400`) on white or light gray backgrounds failing WCAG standards.
- **Missing Real-World States**: Designing only the happy path, ignoring zero-data empty states, network loading skeletons, validation errors, and text overflows.

---

## 💎 The High-Craft Principles (What to Build Instead)

### 1. Intentional Typography & Information Density
- **Establish Visual Hierarchy**: Use distinct font weights (`font-semibold`, `font-medium`, `font-normal`), scale, and line heights (`leading-snug`, `leading-relaxed`) rather than relying only on size.
- **Purposeful Density**: Match information density to user intent. Dashboards, data tables, and settings should be compact, scannable, and efficient—not spread out like a marketing landing page.
- **Tabular Figures for Data**: Apply `font-mono` or `tabular-nums` when displaying numbers, monetary values, timestamps, and statistics so columns align neatly.

### 2. Curated & Contextual Color System
- **Restraint Over Rainbows**: Pick a cohesive, grounded neutral palette (zinc, slate, or stone) and **one** primary accent color. Use color functionally (e.g. amber for warnings, emerald for success, rose for destructive actions).
- **Subtle Elevation**: Use crisp 1px borders (`border-border/60` or `border-sidebar-border`) and subtle dark-mode ring shadows rather than muddy, heavy drop shadows (`shadow-2xl`).
- **Tactile Surfaces**: Differentiate surface tiers with clean contrast:
  - Base background: `bg-background`
  - Elevated container / table / panel: `bg-card` or `bg-sidebar`
  - Subtle interactive hover: `hover:bg-muted/50` or `hover:bg-accent`

### 3. Tactile Feedback & Micro-Interactions
- **Physical Feel**: Buttons, inputs, and clickable list items must have distinct:
  - Hover states (`hover:bg-primary/90`)
  - Active/pressed states (`active:scale-[0.99]` or `active:bg-primary/80`)
  - Clear keyboard focus rings (`focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2`)
  - Disabled states with `disabled:cursor-not-allowed disabled:opacity-50`
- **Meaningful Transitions**: Keep transitions fast (150ms–200ms) with `transition-colors` or `transition-opacity`. Never use sluggish animations that delay productivity.

### 4. Resilient Edge Cases (Production Hardening)
- **Long Text & Truncation**: Always plan for long strings using `truncate`, `line-clamp-2`, or `break-words`. Pair truncated text with tooltips or expandable panels.
- **Empty States with Action**: When data is empty, never display an empty blank card. Provide an empathetic message, an illustrative muted icon, and a primary call-to-action button (e.g., "Create your first project").
- **Loading & Skeleton States**: Use animated skeleton placeholders matching the exact geometry of incoming data instead of a single centered spinning spinner.
- **Action Confirmation**: Destructive actions (delete, reset, cancel) must always have a secondary confirmation modal or popover with explicit warning text.

---

## 🛠 Design Checklist for Every Component & Page

Before finalizing any frontend work, verify:

- [ ] **No AI Slop Gradients**: Are all backgrounds and surfaces intentional, high-contrast, and purposeful?
- [ ] **Scannability**: Can a user understand the page hierarchy within 3 seconds?
- [ ] **Information Density**: Is screen real estate used efficiently without unnecessary whitespace?
- [ ] **WCAG Contrast**: Is all body text easily readable across both light and dark themes?
- [ ] **Edge Cases Handled**: Are empty states, loading skeletons, error states, and long string overflows built?
- [ ] **Keyboard & Focus**: Can the entire view be navigated with <kbd>Tab</kbd> and <kbd>Enter</kbd>?
- [ ] **Clean Code**: Is business logic extracted into composables, with props typed in `<script setup lang="ts">`?

---

## 📚 Deep Dive References
- [Anti-Patterns & Solutions Guide](references/anti-patterns.md): Concrete examples of bad patterns vs. crafted implementations.
- [Design Tokens & UI Recipes](references/design-tokens.md): Tailwind classes, layouts, and component patterns.
