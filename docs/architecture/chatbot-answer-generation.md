# Chatbot Answer Generation Architecture Decision

## Status

Approved.

## Context

The application currently provides a deterministic Knowledge Base retrieval chain:

Knowledge Base files
→ KnowledgeBaseRegistry
→ KnowledgeBaseDocumentLoader
→ KnowledgeBaseChunker
→ LexicalKnowledgeBaseRetriever
→ KnowledgeBaseTopicResolver
→ KnowledgeBasePolicyResolver
→ KnowledgeBaseRetrievalPipeline
→ KnowledgeBaseGroundedContextBuilder

The grounded context layer produces ordered retrieval sources containing internal retrieval metadata and provenance.

The application does not yet provide answer generation, an LLM provider integration, an HTTP chatbot endpoint, or an interactive chatbot UI.

This decision defines the architectural boundary for answer generation before provider-specific implementation is introduced.

## Decision

### Provider Strategy

OpenAI is the initial selected LLM provider.

Application and domain answer-generation contracts must remain provider-neutral.

Provider-specific SDK types, response objects, exceptions, and configuration must not leak into provider-neutral application contracts.

### Grounding Input

Answer generation receives `KnowledgeBaseGroundedContext` as its only Knowledge Base grounding input.

The generator must answer only from the supplied grounded context.

The generator must not use unsupported model knowledge to answer questions presented as official Knowledge Base information.

If the grounded context does not contain sufficient information, the result must state that the official Knowledge Base does not contain enough information.

### Public Answer Contract

The future public chatbot answer contract contains:

- answer text;
- safe source references.

Each public source reference contains only:

- `document_id`;
- `document_title`;
- `section_title`.

The public answer contract must not expose:

- lexical score;
- `source_file`;
- `source_sha256`;
- raw chunk content;
- `policyRelations`.

### Empty Grounded Context

When the grounded context contains no sources:

- the provider must not be called;
- the application returns a deterministic insufficient-information fallback.

### Provider Timeout and Failure

Provider timeout or failure must not be represented as a successful generated answer.

The provider-neutral answer-generation boundary must preserve a structured failure outcome so that a future HTTP adapter can map failures safely.

### HTTP Access

The future chatbot HTTP endpoint is intended to be public.

Before the chatbot UI is connected, the HTTP boundary must provide:

- request validation;
- rate limiting;
- safe response serialization.

### Unsupported Metadata

Page numbers are not part of the current Knowledge Base metadata and must not be fabricated.

Confidence percentages are not supported because lexical retrieval scores are not calibrated model confidence values.

### Expected Architecture

Knowledge Base
→ Retrieval Pipeline
→ Grounded Context Builder
→ Provider-Neutral Answer Generation Contract
→ OpenAI Provider Adapter
→ Safe Answer + Source References
→ HTTP Adapter
→ Chatbot UI

## Consequences

The answer-generation contract can be tested independently of OpenAI.

Provider integration can be replaced without changing retrieval or HTTP contracts.

Internal retrieval metadata remains private.

Empty-context behavior is deterministic and does not consume provider requests.

Provider failures remain distinguishable from valid insufficient-information answers.

HTTP and UI implementation are deferred until the answer-generation boundary and provider adapter are stable.

## Out of Scope

This decision does not implement:

- OpenAI SDK or API integration;
- provider credentials or configuration;
- prompt formatting;
- provider request or response mapping;
- HTTP routes or controllers;
- request validation;
- rate limiting;
- chatbot JavaScript;
- landing-page UI changes;
- retrieval, scoring, topic-resolution, or policy-resolution changes.
