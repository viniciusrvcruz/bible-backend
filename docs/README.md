# Documentação técnica — Bibleasy Backend

Guias para implementações futuras. [AGENTS.md](../AGENTS.md) resume convenções para assistentes; [README.md](../README.md) cobre setup e produto.

## Estrutura

```
docs/
├── README.md
├── architecture/          # geral (todo o projeto)
│   └── overview.md
├── api/
│   └── exceptions-and-responses.md
├── testing/
│   └── overview.md
└── modules/               # por área (capítulos, versões, …)
    ├── README.md
    ├── chapters/
    ├── versions/
    ├── auth/
    └── support/
```

**Documentação geral** — vale para qualquer parte da API: camadas, como delegar do controller, erros HTTP, testes.

**modules/** — só a área que você está mexendo (import, capítulos, login, suporte, etc.).

## Índice

### Documentação geral

| Documento | Conteúdo |
|-----------|----------|
| [architecture/overview.md](./architecture/overview.md) | Camadas, Factory+Strategy, checklist de feature |
| [api/exceptions-and-responses.md](./api/exceptions-and-responses.md) | `CustomException`, Resources sem wrapper |
| [testing/overview.md](./testing/overview.md) | Pest, como espelhar `app/` e copiar testes existentes |

### Módulos

Ver [modules/README.md](./modules/README.md).

## Mapa rápido de rotas

| Método | Rota | Doc do módulo |
|--------|------|---------------|
| `GET` | `/api/versions/{version}/books/{abbreviation}/chapters/{number}` | [chapters/text-sources.md](./modules/chapters/text-sources.md) |
| `POST/PUT/DELETE` | `/api/admin/versions` | [versions/import.md](./modules/versions/import.md) |
| `POST` | `/api/support` | [support/integration.md](./modules/support/integration.md) |
| `GET` | `/api/user`, `/api/admin/me`, `/auth/*` | [auth/authentication.md](./modules/auth/authentication.md) |

## Comandos no Docker

```bash
docker compose exec bible_api php artisan test
docker compose exec bible_api php artisan migrate
docker compose exec bible_api ./vendor/bin/pint
```
