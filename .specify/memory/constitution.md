<!-- Sync Impact Report
Version: 1.0.0
Modified Principles: Initial definition of all principles based on project context.
Added Sections: Core Principles, Governance.
Templates requiring updates: None pending.
Follow-up TODOs: None.
-->

# Shelve Constitution

## Core Principles

### I. Modern Laravel Stack

Built on Laravel 12, PHP 8.2+, and Vue.js 3. Adhere to Laravel best practices and modern PHP standards. All new features must leverage the latest framework capabilities and maintain compatibility with the core stack.

### II. AI-Native Integration

AI is intrinsic to the platform, not an afterthought. Leverage local (Ollama) and cloud (OpenAI) models for intelligent features such as extraction, classification, summaries, and chat. AI integrations must be modular and provider-agnostic where possible.

### III. API-First Design

All core functionality must be exposed via RESTful API. OpenAPI 3.0 documentation is mandatory for every endpoint. The API is the primary interface for all client interactions, ensuring consistency across web, mobile, and third-party integrations.

### IV. Security & Compliance

Zero trust architecture. Granular permissions, Sanctum authentication, and comprehensive audit logging are required for all sensitive operations. Data privacy and security best practices must be strictly followed.

### V. Quality & Reliability

Automated testing (Unit/Feature), static analysis (PHPStan), and code style (PHP-CS-Fixer) enforcement are non-negotiable. High test coverage and passing quality gates are required for all contributions.

## Technical Standards

### Technology Stack

- **Backend**: Laravel 12.x, PHP 8.2+
- **Frontend**: Vue.js 3, TailwindCSS
- **Database**: MySQL 8.0+
- **AI**: Ollama, OpenAI, LangChain/MCP
- **Search**: TNTSearch, Scout

### Development Workflow

- **Branching**: Feature branches merged via PR.
- **Commits**: Conventional Commits format.
- **CI/CD**: Automated pipelines for testing and deployment.

## Governance

### Amendment Procedure

This Constitution supersedes all other practices. Amendments require documentation, approval, and a migration plan if necessary.

### Compliance

All PRs and reviews must verify compliance with these principles. Complexity must be justified. Use `docs/` for runtime development guidance.

**Version**: 1.0.0 | **Ratified**: 2025-11-20 | **Last Amended**: 2025-11-20

