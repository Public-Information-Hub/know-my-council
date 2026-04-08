# Users

User accounts, public identity, verification signals and account security.

The current scaffold now includes a native session-based auth flow with:

- registration
- login and logout
- email verification
- password reset
- profile updates
- default email-code sign-in checks, with magic-link fallback for login
- `kmc:user:superadmin` for granting or revoking unrestricted admin access

It deliberately stays package-light so we can keep the flow close to the project’s data model and adjust it as the account surface grows.
