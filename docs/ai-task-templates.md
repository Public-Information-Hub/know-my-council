# AI task templates

These templates are reusable prompts for contributors and maintainers using AI tools. They are designed to keep work grounded, scoped, and reviewable.

If you use these templates, also follow:

- [AGENTS.md](../AGENTS.md)
- [docs/ai-contributing.md](ai-contributing.md)

## Template 1: inspect before changing

Use this before any non-trivial change.

```
You are working in the KnowMyCouncil repository.

Before proposing any changes:
1. Inspect the current repo state (relevant files, existing scripts, current behaviour).
2. List what you found (with file paths).
3. Only then propose the smallest change that achieves the goal.

Hard rules:
- Do not invent facts, sources, maintainers, or implemented features.
- Keep changes small and reviewable.
- Use en_GB English.

Task: <describe the task>
```

## Template 2: focused bug fix (no broad refactors)

```
You are working in the KnowMyCouncil repository.

Goal: Fix <bug> with the smallest change possible.

Constraints:
- Do not do broad refactors.
- Do not add dependencies unless necessary; explain why if you do.
- Update or add tests if practical.
- State exactly what you verified (commands run).

Please:
1. Identify the likely cause (with file references).
2. Implement a minimal fix.
3. Add verification steps and run them.
4. Summarise the fix, verification, and any remaining risks.
```

## Template 3: documentation correction (reflect reality only)

```
You are working in the KnowMyCouncil repository.

Goal: Update documentation so it accurately reflects what currently exists.

Rules:
- Do not describe planned functionality as implemented.
- Do not add hype or vague fluff.
- Use en_GB English.
- Keep the change scoped to the requested docs.

Please:
1. Verify the current state (file paths, commands, behaviour).
2. Apply doc changes.
3. List any instructions that still require manual steps.
```

## Template 4: scaffold a new domain area (placeholders only)

Use this when adding a new domain/module structure without inventing business logic.

```
You are working in the KnowMyCouncil repository.

Goal: Add a new domain area scaffold for: <domain name>.

Constraints:
- Do not implement business logic.
- Add only the minimum directories/namespaces/README placeholders needed.
- Keep naming consistent with existing `backend/app/Domains/*`.
- Do not invent data, sources, or real council examples.

Please:
1. Propose the directory and namespace layout.
2. Create the placeholder files with short, practical descriptions.
3. Update any index docs if needed.
```

