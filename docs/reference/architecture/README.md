# Architecture Reference

Architecture reference documents capture the intended shape of the platform and the boundaries between major concerns (truth/ingestion vs read/query, search, storage, etc.).

This folder is the long-lived, canonical architecture reference. Other architecture notes in `docs/` may exist as introductions or scaffolding notes, but should link here when the content is intended to be durable.

These documents should:

- describe *intended direction* without over-claiming implementation status
- explain reasoning and trade-offs where the choice matters for future work
- remain fairly stable (avoid writing about transient implementation details)

Start with:

- [platform-shape.md](platform-shape.md)
