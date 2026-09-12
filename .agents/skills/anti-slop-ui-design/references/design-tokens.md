# Design Tokens & UI Craft Recipes

Curated design patterns and token usage for building authentic, production-ready interfaces with Tailwind CSS and Radix Vue.

---

## 1. Surfaces & Elevation

Avoid muddy `shadow-2xl` drop shadows. Instead, use clean 1px border contrast with subtle dark-mode ring shadows.

| Surface Level | Tailwind Classes | Usage |
| :--- | :--- | :--- |
| **Base** | `bg-background text-foreground` | App background, page root. |
| **Panel / Card** | `bg-card text-card-foreground border border-border/80 shadow-xs rounded-lg` | Main content cards, tables, dashboards. |
| **Muted Container** | `bg-muted/50 border border-border/50 rounded-md` | Inset metrics, code blocks, empty state containers. |
| **Interactive Row** | `hover:bg-muted/40 transition-colors duration-150` | Data table rows, dropdown list items. |

---

## 2. Typography Hierarchy

| Level | Styling Tokens | Context |
| :--- | :--- | :--- |
| **Page Title** | `text-2xl font-semibold tracking-tight text-foreground` | Top of page/view headers. |
| **Section Header** | `text-base font-medium text-foreground` | Form group headers, card titles. |
| **Body Primary** | `text-sm font-normal text-foreground leading-relaxed` | Primary descriptions, table cells. |
| **Body Secondary** | `text-xs text-muted-foreground` | Timestamps, metadata, helper text. |
| **Data / Numbers** | `font-mono text-sm tabular-nums tracking-tight` | Prices, statistics, counters, IDs. |

---

## 3. Status Badges & Indicators

Keep badges subtle and accessible—never use blinding neon colors.

```vue
<!-- Neutral -->
<span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-muted/60 px-2 py-0.5 text-xs font-medium text-muted-foreground">
  Draft
</span>

<!-- Success / Active -->
<span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
  <span class="size-1.5 rounded-full bg-emerald-500" />
  Active
</span>

<!-- Warning / Pending -->
<span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/20 bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-600 dark:text-amber-400">
  <span class="size-1.5 rounded-full bg-amber-500" />
  Pending
</span>

<!-- Destructive / Failed -->
<span class="inline-flex items-center gap-1.5 rounded-full border border-destructive/20 bg-destructive/10 px-2 py-0.5 text-xs font-medium text-destructive">
  <span class="size-1.5 rounded-full bg-destructive" />
  Failed
</span>
```

---

## 4. Empty State Pattern

Never leave an empty list as a blank space. Always provide guidance:

```vue
<div class="flex min-h-[280px] flex-col items-center justify-center rounded-lg border border-dashed border-border p-8 text-center">
  <div class="flex size-10 items-center justify-center rounded-full bg-muted text-muted-foreground">
    <FolderPlus class="size-5" />
  </div>
  <h3 class="mt-3 text-sm font-semibold text-foreground">No projects found</h3>
  <p class="mt-1 text-xs text-muted-foreground max-w-xs">
    Get started by creating your first project to begin tracking deployments.
  </p>
  <Button size="sm" class="mt-4 gap-1.5">
    <Plus class="size-3.5" />
    Create Project
  </Button>
</div>
```
