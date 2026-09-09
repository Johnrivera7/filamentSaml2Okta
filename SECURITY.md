# Security Policy

## Supported versions

| Version | Supported          |
| ------- | ------------------ |
| 2.x     | Yes                |
| 1.x     | Security fixes only |
| < 1.0   | No                 |

## Reporting a vulnerability

Please do **not** open a public GitHub issue for security vulnerabilities.

Report privately by emailing **johnriveragonzalez7@gmail.com**, or use
[Report a vulnerability](https://github.com/Johnrivera7/filamentSaml2Okta/security/advisories/new)
on the Security tab of this repository (if private vulnerability reporting is enabled).

You will get a reply within **3 working days**. Once a fix is ready we will
release it, publish an advisory when appropriate, and credit you unless you
prefer otherwise.

## Scope notes for this package

This plugin handles SAML2 SSO credentials, X.509 certificates, and identity
provider metadata. Treat reports that could expose IdP/SP secrets, allow
authentication bypass, or leak user identity attributes as high priority.
