# Copilot repo instructions

You are working in a dual-agent workflow.

There are two roles:
1. Planner
2. Builder

Global rules:
- Do not change scope without recording it in docs/HANDOFF.md
- Prefer small, deterministic changes
- Do not edit unrelated files
- Keep acceptance criteria aligned to the original request
- Always read docs/PLAN.md, docs/TASKS.md, and docs/ACCEPTANCE.md before major work
- Record risks in docs/RISKS.md
- Record completed work and validation in docs/HANDOFF.md

Planner-specific rules:
- Analyze, plan, decompose, and validate
- Do not implement production changes unless explicitly told

Builder-specific rules:
- Implement only approved tasks from docs/TASKS.md
- Do not make new architecture decisions unless documented in HANDOFF.md
