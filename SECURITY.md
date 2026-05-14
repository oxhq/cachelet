# Security Policy

## Supported Versions

Security fixes are prioritized for the current `0.2.x` line until `1.0` is released.

## Reporting a Vulnerability

Please do not open a public issue for a suspected vulnerability.

Report security concerns through GitHub private vulnerability reporting for this repository with:

- affected package name and version
- a concise description of the impact
- reproduction steps or proof-of-concept details when safe to share
- any known mitigations

We aim to acknowledge reports within 72 hours. If the issue is confirmed, we will coordinate a fix, release, and disclosure timeline with the reporter.

## Scope

Reports are in scope when they affect the Cachelet package family, including:

- cache key exposure or incorrect tenant isolation
- unsafe invalidation behavior
- telemetry export leakage
- dependency or package publishing risks

General support questions and feature requests should use GitHub issues instead.
