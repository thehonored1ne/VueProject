# Frontend Anti-Patterns vs. High-Craft Solutions

This reference catalogs common frontend anti-patterns and details how to refactor them into crafted, high-utility components.

---

## 1. Visual Anti-Patterns

### Anti-Pattern: The Purple Glow / AI Slop Hero
```vue
<!-- ❌ AI Slop: Meaningless gradients, floating blur spheres, poor contrast -->
<div class="relative bg-gradient-to-r from-purple-900 via-indigo-800 to-pink-900 p-16 rounded-3xl shadow-2xl overflow-hidden">
  <div class="absolute -top-10 -right-10 w-72 h-72 bg-fuchsia-500 rounded-full blur-3xl opacity-30"></div>
  <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-purple-200">
    Welcome to AI Workspace
  </h1>
  <p class="text-purple-300 mt-4 text-lg">Next-gen intelligence powered by algorithms.</p>
</div>
```

```vue
<!-- ✅ High-Craft: Crisp typographic scale, restrained palette, contextual actions -->
<header class="flex flex-col gap-4 border-b border-border pb-6 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
      <span>Operations</span>
      <span>/</span>
      <span class="text-foreground">Fleet Management</span>
    </div>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-foreground">Active Deployments</h1>
    <p class="text-sm text-muted-foreground mt-0.5">Overview of cluster throughput, node allocation, and recent deployment logs.</p>
  </div>
  <div class="flex items-center gap-2.5">
    <Button variant="outline" size="sm" class="gap-1.5">
      <Download class="size-3.5" />
      Export CSV
    </Button>
    <Button size="sm" class="gap-1.5">
      <Plus class="size-3.5" />
      Deploy Node
    </Button>
  </div>
</header>
```

---

## 2. Layout Anti-Patterns: "Card Soup"

### Anti-Pattern: Endless Nested Cards
```vue
<!-- ❌ AI Slop: Cards inside cards inside cards with redundant borders and heavy shadows -->
<div class="p-8 space-y-6">
  <div class="rounded-2xl border p-6 shadow-lg bg-card">
    <div class="rounded-xl border p-4 shadow-md bg-background">
      <div class="rounded-lg border p-3 bg-muted">
        <p>Information overload with zero hierarchy.</p>
      </div>
    </div>
  </div>
</div>
```

```vue
<!-- ✅ High-Craft: Clean table or segmented surface with visual rhythm -->
<div class="rounded-lg border border-border bg-card overflow-hidden">
  <div class="flex items-center justify-between border-b border-border px-4 py-3 bg-muted/30">
    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Cluster Nodes (12 Active)</span>
    <Badge variant="outline" class="font-mono text-[11px]">Region: us-east-1</Badge>
  </div>
  <div class="divide-y divide-border">
    <div v-for="node in nodes" :key="node.id" class="flex items-center justify-between px-4 py-3 hover:bg-muted/40 transition-colors">
      <div class="flex items-center gap-3">
        <span class="size-2 rounded-full" :class="node.isHealthy ? 'bg-emerald-500' : 'bg-amber-500'" />
        <span class="font-mono text-sm font-medium text-foreground">{{ node.name }}</span>
        <span class="text-xs text-muted-foreground">{{ node.ipAddress }}</span>
      </div>
      <div class="flex items-center gap-4 text-xs font-mono tabular-nums text-muted-foreground">
        <span>CPU: {{ node.cpuUsage }}%</span>
        <span>RAM: {{ node.ramUsage }}GB</span>
      </div>
    </div>
  </div>
</div>
```

---

## 3. Interaction & Form Anti-Patterns

### Anti-Pattern: Disappearing Labels & Missing States
```vue
<!-- ❌ AI Slop: Placeholder used as label, no validation feedback, no focus ring -->
<form @submit.prevent="submit">
  <input type="text" placeholder="Enter your full name" class="w-full p-4 rounded-xl border border-gray-200" />
  <button class="w-full bg-indigo-600 text-white p-4 rounded-xl mt-4">Submit</button>
</form>
```

```vue
<!-- ✅ High-Craft: Accessible label, error messages, disabled/loading state, focus ring -->
<form @submit.prevent="form.post(route('profile.update'))" class="space-y-4 max-w-md">
  <div class="space-y-1.5">
    <label for="fullName" class="text-xs font-medium text-foreground">
      Full Name <span class="text-destructive">*</span>
    </label>
    <Input
      id="fullName"
      v-model="form.name"
      type="text"
      autocomplete="name"
      :aria-invalid="!!form.errors.name"
      class="h-9 font-normal"
    />
    <p v-if="form.errors.name" class="text-xs font-medium text-destructive">
      {{ form.errors.name }}
    </p>
  </div>

  <div class="flex items-center justify-end gap-2 pt-2">
    <Button
      type="submit"
      size="sm"
      :disabled="form.processing || !form.isDirty"
      class="min-w-[80px]"
    >
      <Loader2 v-if="form.processing" class="size-3.5 animate-spin mr-1.5" />
      Save Changes
    </Button>
  </div>
</form>
```

---

## 4. Vue & Code Structure Anti-Patterns

### Anti-Pattern: Spaghetti Templates
- **Putting 50+ lines of reactive mutation and ternary logic in template directives (`v-if="a && (b === 1 || c !== null)"`)**.
- **Not separating business state from UI primitives**.

```vue
<!-- ✅ High-Craft Vue 3 SFC Setup Pattern -->
<script setup lang="ts">
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';

interface Props {
  apiKey: string;
  expiresAt: string;
}

const props = defineProps<Props>();
const { copy, copied } = useClipboard();

const isExpired = computed(() => new Date(props.expiresAt).getTime() < Date.now());
</script>
```
