# Autenticação

Dois públicos distintos: **usuários** do app e **admins** que gerenciam versões. Ambos usam **Laravel Sanctum** (tokens Bearer); o login social passa por rotas **web** (OAuth), não por `/api`.

## Guards (`config/auth.php`)

| Guard | Driver | Model | Rotas típicas |
|-------|--------|-------|---------------|
| `users` | sanctum | `User` | `GET /api/user` (`auth:users`) |
| `admins` | sanctum | `Admin` | `/api/admin/*` (`auth:admins`) |
| `web` | session | `User` | Base Sanctum / fluxo OAuth |

Models `User` e `Admin` usam `HasApiTokens` e UUID como chave.

## Fluxo OAuth (web)

Rotas em `routes/web.php` (prefixo **sem** `/api`):

| Público | Redirect | Callback |
|---------|----------|----------|
| Usuário | `GET /auth/user/redirect/{provider}` | `GET /auth/user/callback/{provider}` |
| Admin | `GET /auth/admin/redirect/{provider}` | `GET /auth/admin/callback/{provider}` |

`{provider}` resolve para `UserAuthProvider` ou `AdminAuthProvider` (hoje: `google`).

### Usuário (`UserSocialAuthService`)

1. Socialite redireciona para Google.
2. No callback: perfil OAuth → `User::firstOrCreate` por e-mail.
3. `createToken('user-token')` → JSON `{ "token": "..." }`.
4. Cliente envia `Authorization: Bearer {token}` em `/api/user`.

### Admin (`AdminSocialAuthService`)

1. Mesmo fluxo Socialite.
2. **Admin deve existir previamente** (`Admin::where(email, auth_provider)->firstOrFail()`).
3. Criação local de admin: `php artisan admin:create {email}` (comando `CreateAdminCommand`).
4. Token `admin-token` → rotas `POST/PUT/DELETE /api/admin/versions`, `GET /api/admin/me`.

## Endpoints autenticados na API

| Método | Rota | Guard |
|--------|------|-------|
| `GET` | `/api/user` | `auth:users` |
| `GET` | `/api/admin/me` | `auth:admins` |
| `POST/PUT/DELETE` | `/api/admin/versions` | `auth:admins` |

Rotas de leitura bíblica (`versions`, `books`, `chapters`) e `POST /api/support` são **públicas**.

## Variáveis de ambiente

| Variável | Uso |
|----------|-----|
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | OAuth (`config/services.php`) |
| `SANCTUM_STATEFUL_DOMAINS` | Domínios SPA (cookie/session, se aplicável) |
| `FRONTEND_URL` | Referência para redirects do frontend (documentado no `.env.example`) |

## Estender autenticação

### Novo provedor OAuth

1. Case em `UserAuthProvider` / `AdminAuthProvider`.
2. Entrada em `config/services.php` para o driver Socialite.
3. Controllers de redirect/callback já recebem `{provider}` por binding.

### Novo tipo de ator (ex.: moderador)

1. Model + guard em `config/auth.php`.
2. Middleware nas rotas.
3. Serviço de auth espelhando `UserSocialAuthService` ou política própria.

## Testes

Ver [testing/overview.md](../../testing/overview.md) — exemplos em `tests/Feature/User/` e `tests/Feature/Admin/`; helpers `actAsUser()` / `actAsAdmin()` em `tests/TestCase.php`.

## Relacionado

- [Importação de versões](../versions/import.md) — requer admin autenticado
- [Arquitetura](../../architecture/overview.md) — OAuth em `web.php`, API em `api.php`
