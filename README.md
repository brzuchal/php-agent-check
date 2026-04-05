# php-agent-check

Unified, machine-oriented validation runner for PHP projects.

**Package name:** `brzuchal/php-agent-check`  
**Binary:** `vendor/bin/agentchk`  
**Minimum PHP version:** `^8.1`

This project provides a wrapper/runner around common testing tools (PHPUnit, PHPStan, PHPCS) designed to produce standard machine-readable output or human-readable summary, optimizing validation workflows for CI pipelines and AI agents.

## Installation

```bash
composer require --dev brzuchal/php-agent-check
```

*Note: You also need to require the tools you want to use (`phpunit/phpunit`, `phpstan/phpstan`, `squizlabs/php_codesniffer`, etc).*

## Configuration

Create `agentchk.yaml` in your project root:

```yaml
profiles:
  fast:
    tools:
      - phpunit

  full:
    tools:
      - phpunit
      - phpstan
      - phpcs

  agent:
    tools:
      - phpunit
      - phpstan

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
      - "src"
      - "tests"
```

## Usage

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

### AI Agent

This mode returns standard JSON with minimum noise.

```bash
vendor/bin/agentchk --profile=agent --mode=agent --format=json
```

## Self-Testing

This project currently uses `agentchk` to test itself!
You can verify its functionality by running:

```bash
./bin/agentchk --profile=full --mode=human
```
