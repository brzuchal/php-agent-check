Zaktualizowana wersja **PROJECT.md** z uwzględnieniem:

* nazwy pakietu: **`brzuchal/php-agent-check`**
* binarki: **`vendor/bin/agentchk`**
* minimalnej wersji PHP: **`^8.1`**
* architektury pod agenty
* zasad outputu i integracji z PHPUnit / PHPStan / PHPCS / Psalm

````md
# PROJECT.md

## Project

**Package name:** `brzuchal/php-agent-check`  
**Binary:** `vendor/bin/agentchk`  
**Minimum PHP version:** `^8.1`

This project provides a unified, machine-oriented validation runner for PHP projects, optimized for:

- AI agents
- CI pipelines
- deterministic automation
- reduced token usage
- normalized output across tools

The binary entrypoint is:

```bash
vendor/bin/agentchk
````

---

## Goals

The project exists to solve the following problems:

* raw tool output is too verbose for AI agents
* multiple tools produce inconsistent formats
* passed checks generate noise without diagnostic value
* human-oriented CLI output wastes tokens in agent workflows
* projects need one normalized validation entrypoint

This tool must:

* execute project validation tools
* prefer machine-readable formats
* normalize results into one unified report
* support human, CI, and agent execution modes
* minimize output noise
* provide stable exit codes

---

## Supported Modes

The system supports three runtime modes:

| Mode  | Purpose                                                  |
| ----- | -------------------------------------------------------- |
| human | readable output for developers                           |
| ci    | CI-friendly execution and reporting                      |
| agent | minimal machine-readable output optimized for LLM agents |

Mode may be selected through CLI flags or environment variables.

### CLI

```bash
vendor/bin/agentchk --mode=human
vendor/bin/agentchk --mode=ci
vendor/bin/agentchk --mode=agent
```

### Environment

```bash
AGENT_MODE=1
CI=1
AGENTCHK_OUTPUT=json
NO_COLOR=1
```

---

## Profiles

Profiles define which tools should run.

### Fast

For quick local feedback and short agent loops.

```bash
vendor/bin/agentchk --profile=fast
```

Typical tools:

* phpstan
* phpunit (selected / reduced scope)

### Full

For pre-push and complete validation.

```bash
vendor/bin/agentchk --profile=full
```

Typical tools:

* phpunit
* phpstan
* phpcs or php-cs-fixer
* optionally psalm

### Agent

For AI agents.

```bash
vendor/bin/agentchk --profile=agent --format=json
```

Rules:

* no ANSI
* no progress bars
* no passed checks in final report
* failures/errors only
* deterministic JSON output

---

## Core Principles

1. Do not parse human-readable stdout unless unavoidable.
2. Prefer JSON, XML, JUnit, Checkstyle, or other machine-readable formats.
3. Normalize all tool results into one report model.
4. Keep the final agent output small.
5. Do not mutate project configuration files permanently.
6. Use runtime flags, wrappers, or dedicated config files instead.
7. Use explicit mode selection, not heuristics.

---

## Package Naming

### Composer package

```json
{
  "name": "brzuchal/php-agent-check",
  "require": {
    "php": "^8.1"
  }
}
```

### Binary

```bash
vendor/bin/agentchk
```

`agentchk` is intentionally short for CLI ergonomics.
The Composer package name remains descriptive and explicit.

---

## PHP Compatibility

The project targets:

```json
{
  "require": {
    "php": "^8.1"
  }
}
```

### Why PHP 8.1

PHP 8.1 is the best compatibility baseline because it:

* supports modern language constructs
* still covers many older active projects
* allows clean architecture without excessive legacy compromises
* is a practical minimum for modern Symfony-era PHP

### Consequences

The codebase may use:

* enums
* readonly properties
* constructor property promotion
* union types
* first-class callables

The codebase must avoid requiring:

* PHP 8.2-only features when unnecessary
* PHP 8.3-only features
* PHP 8.4-only features

This project should remain portable across projects that are modern but not bleeding-edge.

---

## Entrypoint

All automated validation should go through:

```bash
vendor/bin/agentchk
```

Do not call tools directly from agent workflows unless explicitly needed.

Preferred patterns:

```bash
vendor/bin/agentchk --profile=fast
vendor/bin/agentchk --profile=full
vendor/bin/agentchk --profile=agent --format=json
```

---

## Architecture

The architecture is split into clear responsibilities.

### 1. AgentCheck

Main orchestration service.

Responsibilities:

* load configuration
* determine mode
* determine profile
* execute tools
* aggregate results
* produce final report
* return correct exit code

### 2. ProcessRunner

Tool execution abstraction built on:

```php
Symfony\Component\Process\Process
```

Responsibilities:

* execute commands
* set working directory
* pass environment variables
* capture stdout and stderr
* enforce timeouts
* capture exit code
* return execution result

### 3. Check

Represents a single integration with a validation tool.

Each check should define:

* name
* support conditions
* command
* arguments
* output strategy
* parser

Examples:

* `PhpUnitCheck`
* `PhpStanCheck`
* `PhpCsCheck`
* `PsalmCheck`

### 4. OutputParser

Responsible for transforming raw tool output into normalized issues.

Examples:

* `PhpUnitJunitParser`
* `PhpStanJsonParser`
* `PhpCsJsonParser`
* `PsalmJsonParser`

### 5. Report Model

Common normalized structures used across tools.

#### Issue

```json
{
  "type": "string",
  "tool": "string",
  "severity": "error|warning",
  "message": "string",
  "file": "string|null",
  "line": "int|null",
  "test": "string|null",
  "code": "string|null"
}
```

#### ToolResult

```json
{
  "name": "phpunit",
  "status": "passed|failed|error",
  "issues": []
}
```

#### Report

```json
{
  "status": "passed|failed|error",
  "tools": []
}
```

---

## Tool Execution Strategy

The project should prefer machine-readable output formats for every supported tool.

### PHPUnit

Preferred execution:

```bash
vendor/bin/phpunit --log-junit var/agentchk/phpunit.junit.xml --no-progress
```

Recommended strategy:

* do not parse default stdout
* generate JUnit XML
* parse XML
* extract only failures and errors
* discard passed tests from final agent report

Future enhancement:

* dedicated PHPUnit extension emitting direct JSON or NDJSON

### PHPStan

Preferred execution:

```bash
vendor/bin/phpstan analyse --error-format=json
```

Recommended strategy:

* parse JSON output
* normalize issues into common model

### PHPCS

Preferred execution:

```bash
vendor/bin/phpcs --report=json
```

or use a machine-readable alternative if needed.

### PHP CS Fixer

Preferred execution:

```bash
vendor/bin/php-cs-fixer fix --dry-run --format=json
```

### Psalm

Preferred execution:

```bash
vendor/bin/psalm --output-format=json
```

---

## Final Agent Output

The final agent output should be compact and deterministic.

Example:

```json
{
  "status": "failed",
  "tools": [
    {
      "name": "phpunit",
      "status": "failed",
      "issues": [
        {
          "type": "test_failure",
          "tool": "phpunit",
          "severity": "error",
          "test": "App\\Tests\\Unit\\ExampleTest::testSomething",
          "file": "tests/Unit/ExampleTest.php",
          "line": 42,
          "message": "Failed asserting that X is identical to Y",
          "code": null
        }
      ]
    },
    {
      "name": "phpstan",
      "status": "passed",
      "issues": []
    }
  ]
}
```

### Output Rules

* include only failures and warnings that matter
* do not include passed tests in agent mode
* do not include progress lines
* do not include ANSI escape codes
* do not include decorative formatting
* keep stack traces short or omitted unless necessary

---

## Streaming Format

For future incremental execution, NDJSON may be supported.

Example:

```json
{"type":"run_started","tool":"phpunit"}
{"type":"issue","tool":"phpunit","severity":"error","test":"App\\Tests\\Unit\\ExampleTest::testSomething","file":"tests/Unit/ExampleTest.php","line":42,"message":"Failed asserting that X is identical to Y"}
{"type":"run_finished","tool":"phpunit","status":"failed"}
```

This is useful for:

* long-running agent workflows
* streamed CI reporting
* interactive orchestration

Default final format remains standard JSON.

---

## Configuration

Project configuration file:

```text
agentchk.yaml
```

Example:

```yaml
profiles:
  fast:
    tools:
      - phpstan
      - phpunit

  full:
    tools:
      - phpstan
      - phpunit
      - phpcs

  agent:
    tools:
      - phpstan
      - phpunit

tools:
  phpunit:
    command: ["vendor/bin/phpunit"]
    args:
      - "--log-junit"
      - "var/agentchk/phpunit.junit.xml"
      - "--no-progress"

  phpstan:
    command: ["vendor/bin/phpstan"]
    args:
      - "analyse"
      - "--error-format=json"

  phpcs:
    command: ["vendor/bin/phpcs"]
    args:
      - "--report=json"
```

### Configuration Rules

* configuration must be explicit
* tool commands should be overridable
* no permanent mutation of project config files
* if special config is required, prefer separate dedicated config files, such as:

  * `phpunit.agent.xml`
  * `phpstan.agent.neon`

---

## Environment Variables

Supported environment variables may include:

| Variable               | Purpose                              |
| ---------------------- | ------------------------------------ |
| `AGENT_MODE=1`         | enables agent mode                   |
| `AGENTCHK=1`           | indicates execution through agentchk |
| `AGENTCHK_OUTPUT=json` | forces JSON output                   |
| `NO_COLOR=1`           | disables ANSI colors                 |
| `CI=1`                 | indicates CI mode                    |

These variables are useful for:

* tool adapters
* custom reporters
* wrapper scripts
* future extension points

---

## Exit Codes

The binary must use stable exit codes.

| Code | Meaning                                    |
| ---- | ------------------------------------------ |
| `0`  | all checks passed                          |
| `1`  | validation issues found                    |
| `2`  | tool execution or runtime error            |
| `3`  | invalid configuration or invalid CLI usage |

---

## What the Tool Must Not Do

The system must not:

* parse decorative human output when a structured format exists
* emit all passed tests in agent mode
* rely on fragile parent-process detection
* rewrite project configs in place
* assume all projects use the same tools
* assume all projects run on the latest PHP version
* force one global toolchain beyond explicit config

---

## Recommended Workflow

### Developer quick feedback

```bash
vendor/bin/agentchk --profile=fast --mode=human
```

### Full local validation

```bash
vendor/bin/agentchk --profile=full --mode=human
```

### CI

```bash
vendor/bin/agentchk --profile=full --mode=ci
```

### Agent

```bash
vendor/bin/agentchk --profile=agent --mode=agent --format=json
```

---

## Future Enhancements

Potential future work:

* PHPUnit extension that emits direct agent JSON
* NDJSON streaming support
* changed-files-only execution
* integration with Git diff
* caching
* parallel tool execution
* plugin architecture per tool
* specialized Symfony integration package
* per-project adapters

Examples of future packages:

* `brzuchal/php-agent-check-symfony`
* `brzuchal/php-agent-check-phpunit`

---

## Summary

`brzuchal/php-agent-check` is a unified PHP validation runner for humans, CI, and AI agents.

It provides:

* one consistent entrypoint: `vendor/bin/agentchk`
* modern but broadly compatible baseline: `PHP ^8.1`
* normalized machine-readable output
* reduced noise for LLM workflows
* explicit execution modes
* support for multiple validation tools
* deterministic automation-friendly reporting

```

Jeżeli chcesz, następny krok to przygotowanie z tego od razu:
- `README.md`
- `composer.json`
- szkielet katalogów `src/`
- pierwsze klasy pod `Symfony Process` i `agentchk.yaml`.
```
