# User token pages: a person's api tokens, listed, issued, revoked

The front of symfony-api's user tokens, first used by Syrtis, whose clients call
its api with tokens no page shows today. Built from the design system's
components, none drawn anew: if one is missing, a ticket to
symfony-design-system.

## What already exists in symfony-api

`UserTokenApiController`, routed when the application imports
`routes_user_tokens.yaml` (Syrtis does not yet):

- `GET /_api/user-tokens`: the signed-in person's tokens;
- `POST /_api/user-tokens`: issue one — the secret is returned once only;
- `DELETE /_api/user-tokens/{id}`: revoke one.

A token has a label, a hint (the readable start of the secret), scopes, an
optional expiration, a revocation date, a creation date and a last use.

## The page

Among the person's account pages (symfony-user-ds):

- a table of the tokens: label, hint, created, last used (« never » when
  empty), expiration (« none » when empty), revoked or not;
- issuing one: a label, an optional expiration — never required —, optional
  scopes; the secret then shown once, with a copy button and a word saying it
  will not be shown again;
- revoking one, after a confirmation.

## Later

- An administrator looking at another account's tokens, revoking one.
- Tokens per project (as GitLab has, beside the per-account ones): not now.
