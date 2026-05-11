# Architecture

## Direction
- Modular monolith on Laravel 13
- PostgreSQL as primary relational store
- Filament panel as the internal back-office surface
- Workspace isolation as the first-class boundary across all business tables

## Core Rules
- Every business table must be scoped by `workspace_id` unless it is global metadata
- Workspace access is membership-based through `workspace_users`
- Roles are workspace-scoped
- Permissions are global capability definitions attached to roles

## Phase 1 Decisions
- Use UUID primary keys for core business entities
- Use Filament tenancy for workspace switching in the admin panel
- Keep role and permission schema aligned with the ERD in `kantor-digital.md`
- Defer advanced auth concerns like SSO and 2FA until the schema and tenancy layer are stable

## Near-Term Modules
- `App\Models\Workspace`
- `App\Models\WorkspaceUser`
- `App\Models\Role`
- `App\Models\Permission`
- Updated `App\Models\User` with workspace tenancy support

## Expected Expansion
- Add module models under `App\Models`
- Add Filament resources under `App\Filament\Resources`
- Add policies and workspace-aware query scoping as modules are introduced
