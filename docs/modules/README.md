# Módulos de domínio

Documentação **por área do produto** — fluxos, adapters, env vars e extensão. Para o que vale em qualquer feature (camadas, testes, erros HTTP), veja a [documentação geral](../README.md) em `docs/`.

| Módulo | Documentos |
|--------|------------|
| `chapters/` | [text-sources.md](./chapters/text-sources.md) — leitura e fontes (DB / Api.Bible) |
| `versions/` | [import.md](./versions/import.md) — importação admin (USFM / JSON) |
| `auth/` | [authentication.md](./auth/authentication.md) — Sanctum e OAuth |
| `support/` | [integration.md](./support/integration.md) — formulário e OlieFlow |

Ao implementar, abra o módulo mais próximo e **replique o estilo daquele módulo** (Action, Service injetado, adapters, etc.) — não assuma que tudo funciona como capítulos. Padrões do projeto: [architecture/overview.md](../architecture/overview.md).
