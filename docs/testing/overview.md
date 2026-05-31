# Testes

Stack: **Pest** + plugin Laravel. Feature tests usam `Tests\TestCase` com `RefreshDatabase` (ver `tests/Pest.php`).

```bash
docker compose exec bible_api php artisan test
```

## Como achar exemplos

A pasta `tests/` espelha `app/`: alterou `app/Services/Support/` → procure `tests/Feature/Support/` ou `tests/Unit/Services/Support/`; alterou `app/Services/Version/` → o mesmo com `Version`, e assim por diante.

**Regra prática:** encontre o teste da feature **mais parecida** (mesmo tipo de delegação: Action, Service injetado, import, `Http::fake()`, auth) e copie estrutura de request, asserts e setup — não assuma que todo domínio testa igual ao de capítulos.

| O que você está fazendo | Onde buscar referência |
|-------------------------|------------------------|
| Novo endpoint REST | `tests/Feature/{Context}/` — rota + status + corpo |
| Service / adapter isolado | `tests/Unit/Services/{Context}/` ou subpasta `Adapters/` |
| Integração HTTP externa | Teste do **mesmo** contexto que usa `Http::fake()` (import, suporte, Api.Bible, etc.) |
| Auth Sanctum | `tests/Feature/User/` ou `Admin/`; helpers no `TestCase` |
| Middleware específico de rota | Teste na pasta do domínio da rota (não há pasta “global” de middleware) |
| Exceção de domínio | Assert `error` + status no teste da rota ou do service que lança |

Fixtures em `tests/Fixtures/` quando o payload for grande ou compartilhado.

Contrato JSON do domínio: [docs/modules/](../modules/) do módulo que você altera.

## Padrões do projeto

- **Auth:** `$this->actAsAdmin()` ou `actAsUser()` no `TestCase` (Sanctum).
- **HTTP externo:** `Http::fake()` — sem APIs reais nos testes.
- **Dados:** factories; seed só se o teste exigir.
- **Determinismo:** sem testes flake.
- **Comentários no código:** inglês.
- **Antes do PR:** `./vendor/bin/pint`.

## Relacionado

- [Arquitetura](../architecture/overview.md) — como delegar (Action vs Service; sem “padrão capítulo”)
- [Módulos](../modules/) — comportamento assertável por área
