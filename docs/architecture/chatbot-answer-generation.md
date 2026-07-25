# Chatbot Answer Generation Architecture Decision

## Status

Approved and implemented.

## Current Context

KP-DKP has an interactive chatbot UI and public web API endpoints. Requests are validated, rate-limited, persisted as conversations/messages, and answered synchronously from the internal Knowledge Base.

The active chain is deterministic:

```text
Markdown Knowledge Base
→ KnowledgeBaseRegistry
→ KnowledgeBaseDocumentLoader
→ KnowledgeBaseChunker
→ LexicalKnowledgeBaseRetriever
→ KnowledgeBaseTopicResolver
→ KnowledgeBasePolicyResolver
→ KnowledgeBaseRetrievalPipeline
→ KnowledgeBaseGroundedContextBuilder
→ GroundedChatbotResponder
→ ChatbotController / JSON API / Chat UI
```

There is a provider-neutral `KnowledgeBaseAnswerGenerator` contract and result value object, but no provider implementation is bound or invoked by the active chat path. The current chatbot is not an LLM-generated-answer system.

## Decision

### Grounded answer behavior

`GroundedChatbotResponder` must compose answers only from sections returned by the retrieval pipeline. It must not contain rules tied to a document ID, a particular Knowledge Base title, a named business topic, or a fixed number of steps.

Responder retrieval uses `topK=20`. It includes complete eligible sections in retrieval order until the source body reaches a 5,500-character budget. A section is included whole or omitted; it must not be truncated in the middle. The budget is not an instruction to add weak candidates merely to fill remaining space.

### Retrieval eligibility

Lexical score remains a ranking mechanism. A positive score alone is not sufficient to make a section eligible for presentation.

Each result must have `hasDirectMatch` evidence from one of:

- query phrase/token match in the section title; or
- query phrase/token match in the section content.

Document-title matches may contribute to score and ordering, but cannot be the only reason a section is returned. This avoids unrelated sections inheriting relevance from a broad document title.

The retriever, not the responder, computes eligibility because it owns normalization, tokenization, and individual scoring signals.

### Topic and policy resolution

Topic resolution and policy resolution remain part of the retrieval pipeline. They are not replaced by responder-level keyword routing. Policy relations continue to act only on retrieved results.

`prosedur_magang_pkl` uses token-based matching rather than exact normalized substring matching. It recognizes separated `alur` and `magang`/`pkl` terms, while preserving higher-precedence topics. When an otherwise ambiguous `langkah` or `alur` query explicitly targets `contoh_surat_permohonan` or `informasi_wajib_surat_permohonan`, it defers to that more specific topic.

KB-007 declares an `overrides` policy relation for KB-003 only when the resolved topic is `prosedur_magang_pkl`. This is not a blanket retirement of KB-003: KB-003 remains available for other resolved topics, including `penelitian_permintaan_data`. The relation is stored identically in both the KB-007 Markdown frontmatter and the registry because the document loader validates that metadata remains synchronized.

### Known limitation: broad overview queries

The chatbot deliberately does not guarantee every official step for a broad overview query such as `bagaimana alur utama pengajuan magang`. Eligibility requires `hasDirectMatch` evidence in the section title or content. A closing section that does not repeat the overview query's lexical terms can therefore be omitted even when it belongs to the same official document.

This is an accepted limitation of the current lexical retrieval design, not a truncation defect and not an LLM or semantic-retrieval behavior. Specific questions that directly match a step's title or content continue to retrieve that step correctly. No document-ID-specific responder route is used to override this rule.

### Public answer contract

The public response contains answer text and safe source references only:

- `document_id`
- `document_title`
- `section_title`

It must not expose lexical score, raw chunk content, source file path, source checksum, frontmatter, or policy relations.

### Internal metadata

Knowledge Base frontmatter is removed before chunking. Internal instruction fields such as `chatbot_notes` must remain in frontmatter and must never become retrievable sections or public answer text.

### Empty context and failures

When no eligible source is available, return the deterministic insufficient-information response. Unexpected errors remain failures and must not be presented as successful grounded answers.

## Active Flow

```text
Query
→ lexical scoring and direct-match eligibility
→ topic/policy resolution
→ top 20 eligible results
→ complete sections within 5,500-character body budget
→ safe answer and source references
→ persisted JSON response and Markdown-rendered UI
```

## Verification Requirements

- A section with two document-title token matches but no section/content match is rejected.
- Section-title and content matches remain retrievable.
- Existing D1-D3 KB-004 versus KB-007 topic regressions remain correct.
- Broad Magang/PKL flow questions follow direct-match eligibility and may omit a lexically unmatched official step; they must not use document-specific routing to force completeness.
- Queries containing `alur`, `langkah`, and `berurutan` do not expose `chatbot_notes` or other frontmatter instructions.
- Full existing test suite passes before the change is committed.

## Out of Scope

- OpenAI or other LLM provider integration;
- provider credentials, prompts, and provider request/response mapping;
- routes, controllers, request validation, rate limiting, or UI redesign;
- changing Knowledge Base business content or document IDs;
- embedding/vector retrieval;
- confidence percentages or fabricated page references.
