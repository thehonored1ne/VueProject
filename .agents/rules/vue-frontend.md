# Vue 3 & Frontend Rules

## Component Design
- **Script Setup**: Always use `<script setup lang="ts">`.
- **Props and Emits**: Use type-based declaration for `defineProps<{ ... }>()` and `defineEmits<{ ... }>()`.
- **Naming**: Use PascalCase for component files and template references (e.g., `<UserProfile />`, `UserProfile.vue`).
- **Icons**: Use `lucide-vue-next` for all iconography.

## Inertia.js Conventions
- Pages live in `resources/js/pages/` and layouts in `resources/js/layouts/`.
- Use Inertia `<Link>` components instead of standard `<a>` tags for internal navigation.
- Access shared page props via `usePage().props` with typed interfaces.
- Use `router.visit()`, `router.post()`, etc., or Inertia form helpers (`useForm()`) for submitting data with automatic validation handling.

## Styling & Formatting
- Adhere to Tailwind CSS utility conventions. Use `cn()` helper (`clsx` + `tailwind-merge`) when conditionally combining class names.
- Verify formatting with `npm run format:check` and lint with `npm run lint`.
- Verify TypeScript types with `npx vue-tsc --noEmit`.
