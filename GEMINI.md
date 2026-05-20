# Code Style Guide - Dashboard V2 (Inertia & Vue 3)

This document outlines the coding and architectural standards for developing Dashboard Version 2 using **Laravel 11, Inertia.js, and Vue 3**. All developers must adhere to these guidelines to ensure consistency and code quality.

---

## 1. Backend Architecture (Laravel)

### 1.1 Controller Placement
All controllers for the V2 dashboard must be placed within the `App\Http\Controllers\V2` namespace.
- **Path**: `app/Http/Controllers/V2/[Guard]/[Feature]Controller.php`
- **Naming**: Use PascalCase with the `Controller` suffix (e.g., `MatkulController.php`).

### 1.2 Routing
- **Prefix**: Use the `v2/` prefix for all Inertia routes.
- **Naming**: Use dot notation with the `v2.` prefix (e.g., `v2.admin.data-master.data-matkul`).
- **Grouping**: Group routes by guard and feature.

### 1.3 Database & Models
- **Foreign Keys**: When interacting with legacy tables that use plural keys (e.g., `matkuls_id`), define them explicitly in the model relationships.
- **Data Normalization**: Perform data cleaning in the controller (using `unique()`, `orderBy()`, etc.) before sending it to the view to avoid dirty or duplicate data from legacy databases.

---

## 2. Frontend Architecture (Vue 3 & Inertia)

### 2.1 Page Structure
- **Path**: `resources/js/Pages/[Guard]/[Feature]/[Action].vue`.
- **Layout**: Use `AdminLayout.vue` as the primary wrapper for all admin pages.

### 2.2 Design Tokens & UI
- **Primary Color**: `#4B49AC` (Use the `primary` Tailwind variable).
- **Danger Color**: `#FF4747` (Use the `danger` Tailwind variable).
- **Border Radius**: Standard radius is `8px` (`rounded-lg`).
- **Typography**: Use the `Nunito` font as the primary typeface.

### 2.3 Shadcn-Vue Component Standards
- **Modals (Dialog)**:
    - Headers must use the `#4B49AC` background with white text.
    - Every form within a modal must display a `loading` status on the submit button using `form.processing`.
- **Buttons**: Use subtle shadows and responsive transitions.
- **Toasts**: Implement a notification system that automatically monitors `page.props.flash` on every main index page.

---

## 3. Logic & State Management

### 3.1 Inertia Forms
Use `useForm` from `@inertiajs/vue3` for all CRUD operations. Avoid using `axios` directly unless for non-navigation requests.

### 3.2 Sidebar Navigation
- **Active State**: The active menu logic must ignore query parameters (using `.split('?')[0]`).
- **Auto-Expansion**: Parent menus must automatically expand if any child menu under them is active (using `onMounted` and a `watch` on `page.url`).

---

## 4. Coding Standards (Vue)

### 4.1 Import Order
1. Vue Core (`ref`, `watch`, `onMounted`, etc.).
2. Inertia Core (`Head`, `router`, `useForm`, `usePage`).
3. Layouts & Shared Components.
4. UI Components (shadcn-vue).
5. Icons (`lucide-vue-next`).

### 4.2 Script Setup
Mandatory use of `<script setup>` for all new components for efficiency and code clarity.
