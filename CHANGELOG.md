# Changelog

## 0.3.0

- Read the login claims with Axolotl-PM's JWT parser instead of depending on `libBedrock`.
- Ignore malformed or incomplete claims and use a safe fallback for an invalid disconnect message.
- Keep the original Android device model casing heuristic and document its false positives and spoofing limits.
- Add PHPStan, behavior checks, PHAR validation, and GitHub Actions builds.

The plugin still disconnects Android sessions when the first word of the reported model is not uppercase. Review this behavior before upgrading.
