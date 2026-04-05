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

## Architecture for Agents

The codebase must be organized into clear layers with explicit responsibilities.
Do not mix CLI parsing, process execution, tool-specific parsing, and report generation in one class.

### Design Goals

The architecture must provide:

* deterministic execution
* low-noise machine-readable output
* easy support for multiple tools
* replaceable parsers and reporters
* explicit separation between orchestration, execution, parsing, and presentation

---

## Layers

### 1. Entry Layer

Responsible for:

* CLI entrypoint
* reading arguments
* selecting mode and profile
* bootstrapping application services
* returning final exit code

Examples:

* `bin/agentchk`
* `Command\AgentCheckCommand`

This layer must not contain:

* tool-specific parsing logic
* process management details
* report normalization rules

---

### 2. Application Layer

Responsible for orchestration of the whole use case.

Main use case:

* run selected checks
* aggregate results
* build final report

Suggested class:

* `Application\AgentCheck`

This layer coordinates:

* configuration loading
* profile resolution
* tool selection
* execution flow
* final report creation

This layer must not know:

* raw stdout format details of each tool
* Symfony Process internals
* filesystem layout details beyond required abstractions

---

### 3. Domain Layer

Responsible for core concepts and rules.

Suggested domain objects:

* `Check`
* `CheckDefinition`
* `CheckResult`
* `Issue`
* `Report`
* `Profile`
* `ExecutionMode`
* `ToolStatus`
* `Severity`

This layer defines:

* what a validation issue is
* what a tool result is
* what final report status means
* how statuses aggregate

Example rules:

* if any tool has `error`, report status is `error`
* else if any tool has `failed`, report status is `failed`
* else report status is `passed`

This layer must not depend on:

* Symfony
* Process
* CLI
* XML/JSON parser implementations

---

### 4. Infrastructure Layer

Responsible for technical integrations.

Examples:

* process execution
* filesystem access
* YAML config loading
* XML parsing
* JSON decoding
* writing output files

Suggested components:

* `Infrastructure\Process\SymfonyProcessRunner`
* `Infrastructure\Config\YamlConfigurationLoader`
* `Infrastructure\Filesystem\LocalFilesystem`
* `Infrastructure\Reporter\JsonReportWriter`

This layer implements interfaces required by application/domain.

---

### 5. Tool Adapter Layer

Responsible for integration with specific tools.

Each tool should have its own adapter module.

Examples:

* `Tool\PhpUnit\PhpUnitCheck`
* `Tool\PhpUnit\PhpUnitJunitParser`
* `Tool\PhpStan\PhpStanCheck`
* `Tool\PhpStan\PhpStanJsonParser`
* `Tool\PhpCs\PhpCsCheck`
* `Tool\Psalm\PsalmCheck`

A tool adapter is responsible for:

* building command arguments
* declaring required output format
* parsing tool-specific output
* converting raw result into normalized `Issue` objects

A tool adapter must not:

* decide global profile selection
* aggregate whole-project results
* decide final CLI rendering

---

## Recommended Abstractions

### `Check`

Represents one runnable validation unit.

Responsibilities:

* provide tool name
* build execution request
* parse execution result

Suggested contract:

```php
interface Check
{
    public function name(): string;

    public function supports(ProjectContext $context): bool;

    public function createExecution(CheckContext $context): CheckExecution;

    public function parse(CheckExecutionResult $result): CheckResult;
}
```

---

### `ProcessRunner`

Abstraction for running external commands.

Responsibilities:

* run command
* capture stdout/stderr
* capture exit code
* enforce timeout

Suggested contract:

```php
interface ProcessRunner
{
    public function run(CheckExecution $execution): CheckExecutionResult;
}
```

Implementation:

* `SymfonyProcessRunner`

---

### `CheckExecution`

A value object describing how a tool should be executed.

Suggested fields:

* command array
* working directory
* environment variables
* timeout
* expected output files

This prevents leaking `Process` details into higher layers.

---

### `CheckExecutionResult`

Normalized raw execution result.

Suggested fields:

* exit code
* stdout
* stderr
* duration
* generated files

This is the handoff point between execution and parsing.

---

### `CheckResult`

Normalized result of one tool.

Suggested fields:

* tool name
* status
* issues
* metadata

Example:

```php
final readonly class CheckResult
{
    public function __construct(
        public string $tool,
        public ToolStatus $status,
        /** @var list<Issue> */
        public array $issues,
    ) {}
}
```

---

### `Issue`

Core normalized problem object.

Suggested fields:

* tool
* type
* severity
* message
* file
* line
* code
* test name

The agent should always work on `Issue`, never on raw tool output.

---

### `Report`

Aggregated result for the full run.

Responsibilities:

* collect all `CheckResult`
* compute final status
* expose machine-friendly structure

---

### `ConfigurationLoader`

Loads `agentchk.yaml` and maps it into config objects.

Suggested contract:

```php
interface ConfigurationLoader
{
    public function load(string $path): ProjectConfiguration;
}
```

---

### `ReportWriter`

Writes final output.

Implementations may include:

* JSON writer
* NDJSON writer
* human summary writer

Suggested contract:

```php
interface ReportWriter
{
    public function write(Report $report, OutputTarget $target): void;
}
```

---

## Execution Flow

The expected flow is:

1. entrypoint reads CLI arguments
2. configuration is loaded
3. mode and profile are resolved
4. matching checks are selected
5. each check builds a `CheckExecution`
6. `ProcessRunner` executes it
7. the corresponding tool parser converts output into `CheckResult`
8. results are aggregated into `Report`
9. `ReportWriter` renders final output
10. exit code is returned

---

## Separation Rules

The agent must preserve these rules:

### CLI layer

May know:

* arguments
* exit code
* output mode

May not know:

* how PHPUnit XML is parsed
* how PHPStan JSON is normalized

### Application layer

May know:

* which checks are selected
* execution order
* aggregation logic

May not know:

* Symfony Process API details
* DOM/XPath parsing details

### Tool adapter layer

May know:

* tool-specific command flags
* tool-specific output format
* tool-specific parsing rules

May not know:

* global report rendering
* CLI concerns
* profile selection policy

### Domain layer

May know:

* statuses
* issues
* report rules

May not know:

* filesystem
* external process details
* YAML/JSON/XML libraries

---

## Recommended Directory Structure

```text
src/
  Application/
    AgentCheck.php
    CheckRegistry.php

  Domain/
    Check.php
    CheckContext.php
    CheckDefinition.php
    CheckExecution.php
    CheckExecutionResult.php
    CheckResult.php
    Issue.php
    Report.php
    ToolStatus.php
    Severity.php
    ExecutionMode.php
    Profile.php

  Infrastructure/
    Config/
      YamlConfigurationLoader.php
    Process/
      SymfonyProcessRunner.php
    Reporter/
      JsonReportWriter.php
      HumanReportWriter.php
    Filesystem/
      LocalFilesystem.php

  Tool/
    PhpUnit/
      PhpUnitCheck.php
      PhpUnitJunitParser.php
    PhpStan/
      PhpStanCheck.php
      PhpStanJsonParser.php
    PhpCs/
      PhpCsCheck.php
      PhpCsJsonParser.php
    Psalm/
      PsalmCheck.php
      PsalmJsonParser.php

  UserInterface/
    Cli/
      AgentCheckCommand.php
```

---

## Extension Strategy

New tools must be added by introducing:

* one new `Check` implementation
* one parser for that tool
* optional config mapping

The agent must not modify existing tool adapters unless behavior must change.

This makes the system open for extension and closed for unrelated changes.

---

## Anti-Patterns

Do not implement:

* one giant `AgentCheckCommand` doing everything
* direct use of `Process` in tool parsers
* parsers returning arrays without domain objects
* report writing mixed with parsing
* raw stdout passed directly to agents
* tool-specific conditionals spread across the application layer

Bad example:

* `if ($tool === 'phpunit') { ... } elseif ($tool === 'phpstan') { ... }`

Preferred:

* dedicated tool adapters registered in a registry

---

## Recommended Agent Behavior

When changing the architecture, the agent must:

1. preserve layer boundaries
2. add new abstractions only when they remove real coupling
3. prefer explicit value objects over loose arrays
4. keep tool-specific logic isolated
5. keep final output normalized through shared domain models

---

## Practical Rule of Thumb

If a class:

* knows how to run a process,
* parse PHPUnit XML,
* aggregate results,
* and print JSON,

then it has too many responsibilities and must be split.

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
